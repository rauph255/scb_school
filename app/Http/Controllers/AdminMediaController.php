<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Media;
use App\Support\MediaFileManager;
use App\Support\MediaUsageInspector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AdminMediaController extends Controller
{
    public function store(Request $request, MediaFileManager $files): RedirectResponse
    {
        Gate::authorize('create', Media::class);

        $validated = $request->validate([
            ...$this->uploadRules(),
            'alt_text' => ['nullable', 'string', 'max:500'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'credit' => ['nullable', 'string', 'max:255'],
            'consent_required' => ['nullable', 'boolean'],
            'consent_confirmed' => ['nullable', 'boolean'],
            'consent_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $upload = $request->file('file');
        $mimeType = $files->mimeType($upload);
        $this->validateAltText($mimeType, $validated['alt_text'] ?? null);
        $consent = $this->consentValues($request, $validated);
        $stored = $files->store($upload);
        $storedAttributes = $files->attributes($stored);
        $variants = $files->variants($stored);

        try {
            DB::transaction(function () use ($request, $storedAttributes, $variants, $validated, $consent): void {
                $media = Media::query()->create([
                    'uuid' => (string) Str::uuid(),
                    ...$storedAttributes,
                    'alt_text' => $this->nullableText($validated['alt_text'] ?? null),
                    'caption' => $this->nullableText($validated['caption'] ?? null),
                    'credit' => $this->nullableText($validated['credit'] ?? null),
                    ...$consent,
                    'visibility' => 'private',
                    'publication_restricted' => true,
                    'restriction_reason' => 'Awaiting publication approval.',
                    'is_protected_asset' => false,
                    'uploaded_by' => $request->user()?->id,
                ]);
                $media->variants()->createMany($variants);

                $this->audit(
                    $request,
                    'media.uploaded',
                    $media,
                    'Media uploaded for review: '.$media->original_name,
                    null,
                    $this->binarySnapshot($media),
                );
            });
        } catch (Throwable $exception) {
            $files->deleteStored($stored);

            throw $exception;
        }

        return redirect()
            ->route('admin.media')
            ->with('status', 'Media uploaded for review.');
    }

    public function preview(Media $media): Response
    {
        Gate::authorize('view', $media);

        abort_unless($media->storedFileExists(), 404);

        $headers = [
            'Cache-Control' => 'private, no-store',
            'Content-Type' => $media->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ];
        $disposition = $media->isImage() || $media->mime_type === 'application/pdf'
            ? 'inline'
            : 'attachment';

        if ($media->usesLaravelStorage()) {
            return Storage::disk($media->disk)->response(
                $media->storagePath(),
                $media->original_name,
                $headers,
                $disposition,
            );
        }

        $publicRoot = realpath(public_path());
        $path = realpath(public_path($media->storagePath()));

        abort_unless(
            $publicRoot !== false
                && $path !== false
                && str_starts_with($path, $publicRoot.DIRECTORY_SEPARATOR),
            404,
        );

        return $disposition === 'inline'
            ? response()->file($path, $headers)
            : response()->download($path, $media->original_name, $headers);
    }

    public function updateMetadata(Request $request, Media $media): RedirectResponse
    {
        Gate::authorize('updateMetadata', $media);

        $validated = $request->validate([
            'original_name' => ['required', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:500'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'credit' => ['nullable', 'string', 'max:255'],
            'consent_required' => ['sometimes', 'boolean'],
            'consent_confirmed' => ['sometimes', 'boolean'],
            'consent_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $this->validateAltText($media->mime_type, $validated['alt_text'] ?? null);

        $fields = ['original_name', 'alt_text', 'caption', 'credit'];

        if ($request->has('consent_required')) {
            $fields = [...$fields, 'consent_required', 'consent_confirmed', 'consent_reference'];
        }

        $oldValues = $media->only($fields);

        DB::transaction(function () use ($media, $request, $validated, $oldValues, $fields): void {
            $updates = [
                'original_name' => $validated['original_name'],
                'alt_text' => $this->nullableText($validated['alt_text'] ?? null),
                'caption' => $this->nullableText($validated['caption'] ?? null),
                'credit' => $this->nullableText($validated['credit'] ?? null),
            ];

            if ($request->has('consent_required')) {
                $updates = [...$updates, ...$this->consentValues($request, $validated)];
            }

            $media->forceFill($updates)->save();

            $this->audit(
                $request,
                'media.metadata_updated',
                $media,
                'Media metadata updated: '.$media->original_name,
                $oldValues,
                $media->only($fields),
            );
        });

        return redirect()
            ->route('admin.media')
            ->with('status', 'Media metadata saved.');
    }

    public function updatePublication(Request $request, Media $media, MediaFileManager $files): RedirectResponse
    {
        Gate::authorize('updatePublication', $media);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'restrict'])],
        ]);

        $action = $validated['action'];

        abort_if($media->is_protected_asset && $action === 'restrict', 403);

        if ($action === 'approve' && $media->consent_required && ! $media->consent_confirmed) {
            throw ValidationException::withMessages([
                'action' => 'Confirm and reference consent before publishing this media.',
            ]);
        }

        if ($action === 'approve' && ! $media->storedFileExists()) {
            throw ValidationException::withMessages([
                'action' => 'The stored file is unavailable. Upload a replacement before publishing this media.',
            ]);
        }

        $oldValues = $media->only([
            'disk',
            'visibility',
            'consent_confirmed',
            'publication_restricted',
            'restriction_reason',
        ]);
        $oldDisk = $media->disk;
        // Public visibility is enforced by the MySQL-backed media endpoint.
        // Keeping binaries on the private disk prevents path guessing from
        // bypassing a later consent or publication restriction.
        $targetDisk = 'local';
        $copied = $files->copyToDisk($media, $targetDisk);
        try {
            DB::transaction(function () use ($media, $request, $action, $oldValues, $targetDisk, $copied): void {
                $media->forceFill(match ($action) {
                    'approve' => [
                        'disk' => $copied ? $targetDisk : $media->disk,
                        'visibility' => 'public',
                        'publication_restricted' => false,
                        'restriction_reason' => null,
                    ],
                    'restrict' => [
                        'disk' => $copied ? $targetDisk : $media->disk,
                        'visibility' => 'private',
                        'publication_restricted' => true,
                        'restriction_reason' => 'Restricted from visible admin media workflow.',
                    ],
                })->save();

                $this->audit(
                    $request,
                    'media.publication_updated',
                    $media,
                    'Media publication updated: '.$media->original_name.' to '.str($action)->title(),
                    $oldValues,
                    $media->only([
                        'disk',
                        'visibility',
                        'consent_confirmed',
                        'publication_restricted',
                        'restriction_reason',
                    ]),
                );
            });
        } catch (Throwable $exception) {
            if ($copied) {
                $files->delete($targetDisk, $media->directory, $media->stored_name);
            }

            throw $exception;
        }

        if ($copied) {
            $files->delete($oldDisk, $media->directory, $media->stored_name);
        }

        return redirect()
            ->route('admin.media')
            ->with('status', 'Media publication updated.');
    }

    public function replace(Request $request, Media $media, MediaFileManager $files): RedirectResponse
    {
        Gate::authorize('replace', $media);

        $validated = $request->validate($this->uploadRules());
        $upload = $request->file('file');
        $mimeType = $files->mimeType($upload);
        $this->validateAltText($mimeType, $media->alt_text);
        $stored = $files->store($upload);
        $storedAttributes = $files->attributes($stored);
        $newVariants = $files->variants($stored);
        $oldValues = $this->binarySnapshot($media);
        $oldFile = $media->only(['disk', 'directory', 'stored_name']);
        $oldManaged = $media->isManagedUpload();
        $variantFiles = $media->variants()->get(['disk', 'path']);

        try {
            DB::transaction(function () use ($request, $media, $storedAttributes, $newVariants, $oldValues): void {
                $media->forceFill([
                    ...$storedAttributes,
                    'visibility' => 'private',
                    'consent_confirmed' => $media->consent_required ? false : $media->consent_confirmed,
                    'consent_reference' => $media->consent_required ? null : $media->consent_reference,
                    'publication_restricted' => true,
                    'restriction_reason' => 'Replacement file awaiting publication approval.',
                    'uploaded_by' => $request->user()?->id,
                ])->save();

                $media->variants()->delete();
                $media->variants()->createMany($newVariants);

                $this->audit(
                    $request,
                    'media.replaced',
                    $media,
                    'Media binary replaced for review: '.$media->original_name,
                    $oldValues,
                    $this->binarySnapshot($media),
                );
            });
        } catch (Throwable $exception) {
            $files->deleteStored($stored);

            throw $exception;
        }

        if ($oldManaged) {
            $files->delete($oldFile['disk'], $oldFile['directory'], $oldFile['stored_name']);
        }

        $this->deleteVariantFiles($variantFiles);

        return redirect()
            ->route('admin.media')
            ->with('status', 'Replacement uploaded for review.');
    }

    public function destroy(
        Request $request,
        Media $media,
        MediaFileManager $files,
        MediaUsageInspector $usages,
    ): RedirectResponse {
        Gate::authorize('delete', $media);

        $references = $usages->externalReferences($media);

        if ($references !== []) {
            throw ValidationException::withMessages([
                'media_id' => 'This media is still used as '.implode(', ', $references).'. Remove those links before deleting it.',
            ]);
        }

        $oldValues = $this->binarySnapshot($media);
        $oldDisk = $media->disk;
        $copied = $files->copyToDisk($media, 'local');
        $variantFiles = $media->variants()->get(['disk', 'path']);

        try {
            DB::transaction(function () use ($request, $media, $oldValues, $copied, $usages): void {
                Media::query()->whereKey($media->id)->lockForUpdate()->firstOrFail();
                $references = $usages->externalReferences($media);

                if ($references !== []) {
                    throw ValidationException::withMessages([
                        'media_id' => 'This media is still used as '.implode(', ', $references).'. Remove those links before deleting it.',
                    ]);
                }

                $media->forceFill([
                    'disk' => $copied ? 'local' : $media->disk,
                    'visibility' => 'private',
                    'publication_restricted' => true,
                    'restriction_reason' => 'Deleted from the visible admin media workflow.',
                ])->save();
                $media->variants()->delete();
                $media->delete();

                $this->audit(
                    $request,
                    'media.deleted',
                    $media,
                    'Media deleted: '.$media->original_name,
                    $oldValues,
                    [
                        ...$this->binarySnapshot($media),
                        'deleted_at' => $media->deleted_at?->toISOString(),
                    ],
                );
            });
        } catch (Throwable $exception) {
            if ($copied) {
                $files->delete('local', $media->directory, $media->stored_name);
            }

            throw $exception;
        }

        if ($copied) {
            $files->delete($oldDisk, $media->directory, $media->stored_name);
        }

        $this->deleteVariantFiles($variantFiles);

        return redirect()
            ->route('admin.media')
            ->with('status', 'Media deleted.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function uploadRules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'extensions:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,odt',
                'mimetypes:image/jpeg,image/png,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.oasis.opendocument.text',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function consentValues(Request $request, array $validated): array
    {
        $required = $request->boolean('consent_required');
        $confirmed = $required && $request->boolean('consent_confirmed');
        $reference = $confirmed ? $this->nullableText($validated['consent_reference'] ?? null) : null;

        if ($confirmed && $reference === null) {
            throw ValidationException::withMessages([
                'consent_reference' => 'Enter the consent reference before confirming consent.',
            ]);
        }

        return [
            'consent_required' => $required,
            'consent_confirmed' => $confirmed,
            'consent_reference' => $reference,
        ];
    }

    private function validateAltText(string $mimeType, mixed $altText): void
    {
        if (str_starts_with($mimeType, 'image/') && $this->nullableText($altText) === null) {
            throw ValidationException::withMessages([
                'alt_text' => 'Alternative text is required for uploaded images.',
            ]);
        }
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function binarySnapshot(Media $media): array
    {
        return $media->only([
            'disk',
            'directory',
            'stored_name',
            'original_name',
            'mime_type',
            'extension',
            'size_bytes',
            'width',
            'height',
            'checksum_sha256',
            'visibility',
            'consent_required',
            'consent_confirmed',
            'consent_reference',
            'publication_restricted',
            'restriction_reason',
            'uploaded_by',
        ]);
    }

    private function deleteVariantFiles($variants): void
    {
        $variants->each(function ($variant): void {
            Storage::disk($variant->disk)->delete($variant->path);
        });
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function audit(
        Request $request,
        string $action,
        Media $media,
        string $description,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => Media::class,
            'subject_id' => $media->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);
    }
}
