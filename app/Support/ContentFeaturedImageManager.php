<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\Media;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContentFeaturedImageManager
{
    public function __construct(private readonly MediaFileManager $files) {}

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'featured_media_id' => [
                'nullable',
                'integer',
                Rule::exists('media', 'id')->whereNull('deleted_at'),
            ],
            'featured_image' => [
                'nullable',
                'file',
                'max:10240',
                'extensions:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
            ],
            'featured_image_alt_text' => ['nullable', 'string', 'max:500'],
            'featured_image_caption' => ['nullable', 'string', 'max:1000'],
            'featured_image_credit' => ['nullable', 'string', 'max:255'],
            'featured_image_consent_required' => ['nullable', 'boolean'],
            'featured_image_consent_confirmed' => ['nullable', 'boolean'],
            'featured_image_consent_reference' => ['nullable', 'string', 'max:255'],
            'featured_image_safeguarding_confirmed' => ['nullable', 'boolean'],
        ];
    }

    public function selectedImage(mixed $mediaId): ?Media
    {
        if ($mediaId === null || $mediaId === '') {
            return null;
        }

        $media = Media::query()->findOrFail((int) $mediaId);

        if (! $media->isImage()) {
            throw ValidationException::withMessages([
                'featured_media_id' => 'Choose an image from the media library.',
            ]);
        }

        return $media;
    }

    /**
     * Resolve a library image or create a reviewed upload, then perform the
     * content update in the same database transaction.
     */
    public function run(
        Request $request,
        ?Media $selectedImage,
        Closure $callback,
    ): mixed {
        if (! $request->hasFile('featured_image')) {
            return DB::transaction(function () use ($selectedImage, $callback): mixed {
                $lockedImage = $selectedImage
                    ? Media::query()->whereKey($selectedImage->id)->lockForUpdate()->firstOrFail()
                    : null;

                return $callback($lockedImage, false);
            });
        }

        $this->validateUploadMetadata($request);

        try {
            $stored = $this->files->store($request->file('featured_image'));
        } catch (ValidationException $exception) {
            $this->rethrowForFeaturedImage($exception);
        }

        try {
            return DB::transaction(function () use ($request, $stored, $callback): mixed {
                $media = Media::query()->create([
                    'uuid' => (string) Str::uuid(),
                    ...$this->files->attributes($stored),
                    'alt_text' => $this->nullableText($request->input('featured_image_alt_text')),
                    'caption' => $this->nullableText($request->input('featured_image_caption')),
                    'credit' => $this->nullableText($request->input('featured_image_credit')),
                    ...$this->consentValues($request),
                    'visibility' => 'private',
                    'publication_restricted' => true,
                    'restriction_reason' => 'Uploaded with content and awaiting publication approval.',
                    'is_protected_asset' => false,
                    'uploaded_by' => $request->user()?->id,
                ]);

                $media->variants()->createMany($this->files->variants($stored));
                $this->auditUpload($request, $media);

                return $callback($media, true);
            });
        } catch (Throwable $exception) {
            $this->files->deleteStored($stored);

            throw $exception;
        }
    }

    public function assertCanPublish(?Media $media): void
    {
        if ($media === null) {
            return;
        }

        if (! $media->isImage() || ! $media->isPubliclyAvailable() || ! $media->storedFileExists()) {
            throw ValidationException::withMessages([
                'featured_media_id' => 'Publish the selected image in the media library before publishing this content.',
            ]);
        }
    }

    private function validateUploadMetadata(Request $request): void
    {
        if ($this->nullableText($request->input('featured_image_alt_text')) === null) {
            throw ValidationException::withMessages([
                'featured_image_alt_text' => 'Alternative text is required for a new featured image.',
            ]);
        }

        if (! $request->boolean('featured_image_safeguarding_confirmed')) {
            throw ValidationException::withMessages([
                'featured_image_safeguarding_confirmed' => 'Confirm that the image contains no sensitive identifying information.',
            ]);
        }

        $this->consentValues($request);
    }

    /** @return array<string, mixed> */
    private function consentValues(Request $request): array
    {
        $required = $request->boolean('featured_image_consent_required');
        $confirmed = $required && $request->boolean('featured_image_consent_confirmed');
        $reference = $confirmed
            ? $this->nullableText($request->input('featured_image_consent_reference'))
            : null;

        if ($confirmed && $reference === null) {
            throw ValidationException::withMessages([
                'featured_image_consent_reference' => 'Enter the consent reference before confirming consent.',
            ]);
        }

        return [
            'consent_required' => $required,
            'consent_confirmed' => $confirmed,
            'consent_reference' => $reference,
        ];
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function rethrowForFeaturedImage(ValidationException $exception): never
    {
        $messages = $exception->errors();

        if (isset($messages['file'])) {
            $messages['featured_image'] = $messages['file'];
            unset($messages['file']);
        }

        throw ValidationException::withMessages($messages);
    }

    private function auditUpload(Request $request, Media $media): void
    {
        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => 'media.uploaded',
            'subject_type' => Media::class,
            'subject_id' => $media->id,
            'description' => 'Media uploaded with content for review: '.$media->original_name,
            'old_values' => null,
            'new_values' => $media->only([
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
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);
    }
}
