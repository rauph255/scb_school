<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Download;
use App\Models\Media;
use App\Support\MediaFileManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdminDownloadController extends Controller
{
    public function store(Request $request, MediaFileManager $files): RedirectResponse
    {
        Gate::authorize('create', Download::class);

        $validated = $request->validate([
            ...$this->documentRules(),
            ...$this->metadataRules(),
        ]);
        $stored = $files->store($request->file('file'));

        try {
            DB::transaction(function () use ($request, $validated, $stored): void {
                $media = $this->createPrivateDocumentMedia($request, $stored);
                $download = Download::query()->create([
                    'download_category_id' => $validated['download_category_id'] ?? null,
                    'title' => $validated['title'],
                    'slug' => $this->uniqueSlug($validated['title']),
                    'description' => $this->nullableText($validated['description'] ?? null),
                    'media_id' => $media->id,
                    'version' => $this->nullableText($validated['version'] ?? null),
                    'publication_date' => $validated['publication_date'] ?? null,
                    'status' => 'draft',
                    'published_at' => null,
                    'download_count' => 0,
                    'created_by' => $request->user()?->id,
                    'updated_by' => $request->user()?->id,
                ]);

                $this->audit(
                    $request,
                    'download.created',
                    $download,
                    'Download created with private file: '.$download->title,
                    null,
                    $this->downloadSnapshot($download),
                );
            });
        } catch (Throwable $exception) {
            $files->deleteStored($stored);

            throw $exception;
        }

        return redirect()
            ->route('admin.downloads')
            ->with('status', 'Download uploaded as draft.');
    }

    public function updateStatus(Request $request, Download $download, MediaFileManager $files): RedirectResponse
    {
        Gate::authorize('updateStatus', $download);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'review', 'published', 'archived'])],
        ]);

        $oldValues = $download->only(['status', 'published_at', 'publication_date', 'updated_by']);
        $status = $validated['status'];
        $media = $download->media;
        $oldMediaValues = $media?->only(['disk', 'visibility', 'publication_restricted', 'restriction_reason']);
        $oldDisk = $media?->disk;
        $targetDisk = null;
        $restrictMedia = false;
        $copied = false;

        if ($status === 'published') {
            if (! $media || ! $this->isDocumentMime($media->mime_type)) {
                throw ValidationException::withMessages([
                    'status' => 'Attach a valid document before publishing this download.',
                ]);
            }

            if (! $media->storedFileExists()) {
                throw ValidationException::withMessages([
                    'status' => 'Upload the document before publishing this download.',
                ]);
            }

            if ($media->consent_required && ! $media->consent_confirmed) {
                throw ValidationException::withMessages([
                    'status' => 'Confirm required consent before publishing this download.',
                ]);
            }

            // Published documents remain on private storage and are streamed
            // through the MySQL-authorized public download endpoint.
            $targetDisk = 'local';
        } elseif ($media && ! Download::query()
            ->whereKeyNot($download->id)
            ->where('media_id', $media->id)
            ->published()
            ->exists()) {
            $targetDisk = 'local';
            $restrictMedia = true;
        }

        try {
            if ($targetDisk && $media) {
                $copied = $files->copyToDisk($media, $targetDisk);
            }

            DB::transaction(function () use ($download, $request, $status, $oldValues, $media, $oldMediaValues, $copied, $restrictMedia): void {
                if ($status === 'published' && $media) {
                    $media->forceFill([
                        'disk' => $copied ? 'local' : $media->disk,
                        'visibility' => 'public',
                        'publication_restricted' => false,
                        'restriction_reason' => null,
                    ])->save();

                    if ($oldMediaValues !== $media->only(['disk', 'visibility', 'publication_restricted', 'restriction_reason'])) {
                        $this->auditMediaPublication($request, $media, $oldMediaValues);
                    }
                } elseif ($restrictMedia && $media) {
                    $media->forceFill([
                        'disk' => $copied ? 'local' : $media->disk,
                        'visibility' => 'private',
                        'publication_restricted' => true,
                        'restriction_reason' => 'Attached download is not published.',
                    ])->save();

                    if ($oldMediaValues !== $media->only(['disk', 'visibility', 'publication_restricted', 'restriction_reason'])) {
                        $this->auditMediaPublication($request, $media, $oldMediaValues);
                    }
                }

                $download->forceFill([
                    'status' => $status,
                    'published_at' => $status === 'published' ? ($download->published_at ?? now()) : null,
                    'publication_date' => $status === 'published' ? ($download->publication_date ?? today()) : $download->publication_date,
                    'updated_by' => $request->user()?->id,
                ])->save();

                $this->audit(
                    $request,
                    'download.status_updated',
                    $download,
                    'Download status updated: '.$download->title.' to '.str($status)->replace('_', ' ')->title(),
                    $oldValues,
                    $download->only(['status', 'published_at', 'publication_date', 'updated_by']),
                );
            });
        } catch (Throwable $exception) {
            if ($copied && $media && $targetDisk) {
                $files->delete($targetDisk, $media->directory, $media->stored_name);
            }

            throw $exception;
        }

        if ($copied && $media && $oldDisk) {
            $files->delete($oldDisk, $media->directory, $media->stored_name);
        }

        return redirect()
            ->route('admin.downloads')
            ->with('status', 'Download updated.');
    }

    public function updateMetadata(Request $request, Download $download): RedirectResponse
    {
        Gate::authorize('updateMetadata', $download);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'download_category_id' => ['nullable', 'integer', Rule::exists('download_categories', 'id')],
            'version' => ['nullable', 'string', 'max:100'],
            'publication_date' => ['sometimes', 'nullable', 'date'],
        ]);

        $oldValues = $download->only([
            'download_category_id',
            'title',
            'slug',
            'description',
            'version',
            'publication_date',
            'updated_by',
        ]);

        DB::transaction(function () use ($download, $request, $validated, $oldValues): void {
            $download->forceFill([
                'download_category_id' => $validated['download_category_id'] ?? null,
                'title' => $validated['title'],
                'slug' => $this->uniqueSlug($validated['title'], $download),
                'description' => $this->nullableText($validated['description'] ?? null),
                'version' => $this->nullableText($validated['version'] ?? null),
                'publication_date' => $validated['publication_date'] ?? $download->publication_date,
                'updated_by' => $request->user()?->id,
            ])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'download.metadata_updated',
                'subject_type' => Download::class,
                'subject_id' => $download->id,
                'description' => 'Download metadata updated: '.$download->title,
                'old_values' => $oldValues,
                'new_values' => $download->only([
                    'download_category_id',
                    'title',
                    'slug',
                    'description',
                    'version',
                    'publication_date',
                    'updated_by',
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.downloads')
            ->with('status', 'Download saved.');
    }

    public function replaceFile(Request $request, Download $download, MediaFileManager $files): RedirectResponse
    {
        Gate::authorize('updateFile', $download);

        $request->validate($this->documentRules());
        $stored = $files->store($request->file('file'));
        $oldDownloadValues = $this->downloadSnapshot($download);
        $oldMedia = Media::withTrashed()->findOrFail($download->media_id);
        $oldMediaValues = $oldMedia->only(['disk', 'visibility', 'publication_restricted', 'restriction_reason']);
        $oldDisk = $oldMedia->disk;
        $usedByOtherPublishedDownload = Download::query()
            ->where('media_id', $oldMedia->id)
            ->whereKeyNot($download->id)
            ->published()
            ->exists();
        $copied = false;

        try {
            $copied = ! $usedByOtherPublishedDownload && $files->copyToDisk($oldMedia, 'local');

            DB::transaction(function () use ($request, $download, $stored, $oldDownloadValues, $oldMedia, $oldMediaValues, $usedByOtherPublishedDownload, $copied): void {
                $newMedia = $this->createPrivateDocumentMedia($request, $stored);

                $download->forceFill([
                    'media_id' => $newMedia->id,
                    'status' => 'draft',
                    'published_at' => null,
                    'updated_by' => $request->user()?->id,
                ])->save();

                if (! $usedByOtherPublishedDownload) {
                    $oldMedia->forceFill([
                        'disk' => $copied ? 'local' : $oldMedia->disk,
                        'visibility' => 'private',
                        'publication_restricted' => true,
                        'restriction_reason' => 'Superseded by a replacement download file.',
                    ])->save();

                    $this->auditMediaPublication($request, $oldMedia, $oldMediaValues);
                }

                $this->audit(
                    $request,
                    'download.file_replaced',
                    $download,
                    'Download file replaced and returned to draft: '.$download->title,
                    $oldDownloadValues,
                    $this->downloadSnapshot($download),
                );
            });
        } catch (Throwable $exception) {
            $files->deleteStored($stored);

            if ($copied) {
                $files->delete('local', $oldMedia->directory, $oldMedia->stored_name);
            }

            throw $exception;
        }

        if ($copied) {
            $files->delete($oldDisk, $oldMedia->directory, $oldMedia->stored_name);
        }

        return redirect()
            ->route('admin.downloads')
            ->with('status', 'Replacement uploaded; download returned to draft.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function documentRules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:20480',
                'extensions:pdf,doc,docx,xls,xlsx,odt',
                'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.oasis.opendocument.text',
            ],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function metadataRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'download_category_id' => ['nullable', 'integer', Rule::exists('download_categories', 'id')],
            'version' => ['nullable', 'string', 'max:100'],
            'publication_date' => ['nullable', 'date'],
        ];
    }

    /**
     * @param  array<string, mixed>  $stored
     */
    private function createPrivateDocumentMedia(Request $request, array $stored): Media
    {
        $media = Media::query()->create([
            'uuid' => (string) Str::uuid(),
            ...$stored,
            'alt_text' => null,
            'caption' => 'Download document awaiting publication approval.',
            'visibility' => 'private',
            'consent_required' => false,
            'consent_confirmed' => false,
            'consent_reference' => null,
            'publication_restricted' => true,
            'restriction_reason' => 'Attached download awaiting publication.',
            'is_protected_asset' => false,
            'uploaded_by' => $request->user()?->id,
        ]);

        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => 'media.uploaded',
            'subject_type' => Media::class,
            'subject_id' => $media->id,
            'description' => 'Document uploaded for download review: '.$media->original_name,
            'old_values' => null,
            'new_values' => $media->only([
                'disk',
                'directory',
                'stored_name',
                'original_name',
                'mime_type',
                'extension',
                'size_bytes',
                'checksum_sha256',
                'visibility',
                'publication_restricted',
                'uploaded_by',
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);

        return $media;
    }

    private function isDocumentMime(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.oasis.opendocument.text',
        ], true);
    }

    /**
     * @return array<string, mixed>
     */
    private function downloadSnapshot(Download $download): array
    {
        return $download->only([
            'download_category_id',
            'title',
            'slug',
            'description',
            'media_id',
            'version',
            'publication_date',
            'status',
            'published_at',
            'created_by',
            'updated_by',
        ]);
    }

    private function auditMediaPublication(Request $request, Media $media, ?array $oldValues): void
    {
        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => 'media.publication_updated',
            'subject_type' => Media::class,
            'subject_id' => $media->id,
            'description' => 'Media publication updated by download workflow: '.$media->original_name,
            'old_values' => $oldValues,
            'new_values' => $media->only(['disk', 'visibility', 'publication_restricted', 'restriction_reason']),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function audit(
        Request $request,
        string $action,
        Download $download,
        string $description,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => Download::class,
            'subject_id' => $download->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function uniqueSlug(string $title, ?Download $download = null): string
    {
        $baseSlug = Str::slug($title) ?: 'download';
        $slug = $baseSlug;
        $suffix = 2;

        while (Download::query()
            ->where('slug', $slug)
            ->when($download, fn ($query) => $query->whereKeyNot($download->id))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
