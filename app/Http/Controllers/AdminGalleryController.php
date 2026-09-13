<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\MediaUsage;
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
use Throwable;

class AdminGalleryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Gallery::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $gallery = DB::transaction(function () use ($request, $validated): Gallery {
            $gallery = Gallery::query()->create([
                'title' => $validated['title'],
                'slug' => $this->uniqueSlugForCreate($validated['title']),
                'description' => $validated['description'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);

            $this->audit(
                $request,
                'gallery.created',
                Gallery::class,
                $gallery->id,
                'Gallery draft created: '.$gallery->title,
                null,
                $gallery->only(['title', 'slug', 'description', 'status', 'created_by', 'updated_by']),
            );

            return $gallery;
        });

        return redirect()
            ->route('admin.gallery.editor', ['gallery' => $gallery->slug])
            ->with('status', 'Gallery draft created.');
    }

    public function update(Request $request, Gallery $gallery): RedirectResponse
    {
        Gate::authorize('update', $gallery);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'gallery_category_id' => ['nullable', 'integer', Rule::exists('gallery_categories', 'id')],
            'event_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['draft', 'review', 'published', 'archived'])],
            'items' => ['sometimes', 'array'],
            'items.*.id' => ['required_with:items', 'integer', Rule::exists('gallery_items', 'id')->where('gallery_id', $gallery->id)],
            'items.*.caption' => ['nullable', 'string', 'max:500'],
            'items.*.sort_order' => ['required_with:items', 'integer', 'min:0', 'max:9999'],
            'items.*.is_featured' => ['sometimes', 'boolean'],
            'featured_item_id' => [
                'nullable',
                'integer',
                Rule::exists('gallery_items', 'id')->where('gallery_id', $gallery->id),
            ],
        ]);

        $featuredItemId = $this->featuredItemId($validated);

        $oldValues = $gallery->only([
            'gallery_category_id',
            'title',
            'slug',
            'description',
            'cover_media_id',
            'event_date',
            'status',
            'published_at',
        ]);
        $oldItems = $gallery->items()
            ->orderBy('sort_order')
            ->get(['id', 'caption', 'sort_order', 'is_featured'])
            ->toArray();

        DB::transaction(function () use ($featuredItemId, $gallery, $request, $validated, $oldItems, $oldValues): void {
            Gallery::query()->whereKey($gallery->id)->lockForUpdate()->firstOrFail();

            if ($validated['status'] === 'published') {
                $this->assertGalleryCanBePublished($gallery);
            }

            $gallery->forceFill([
                'gallery_category_id' => $validated['gallery_category_id'] ?? null,
                'title' => $validated['title'],
                'slug' => $this->uniqueSlug($validated['title'], $gallery),
                'description' => $validated['description'] ?? null,
                'event_date' => $validated['event_date'] ?? null,
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published'
                    ? ($gallery->published_at ?? now())
                    : null,
                'updated_by' => $request->user()?->id,
            ])->save();

            foreach ($validated['items'] ?? [] as $itemData) {
                $gallery->items()
                    ->whereKey($itemData['id'])
                    ->update([
                        'caption' => $itemData['caption'] ?: null,
                        'sort_order' => $itemData['sort_order'],
                    ]);
            }

            $this->normalizeCover($gallery, $featuredItemId);

            $newItems = $gallery->items()
                ->orderBy('sort_order')
                ->get(['id', 'caption', 'sort_order', 'is_featured'])
                ->toArray();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'gallery.updated',
                'subject_type' => Gallery::class,
                'subject_id' => $gallery->id,
                'description' => 'Gallery updated from visible admin editor: '.$gallery->title,
                'old_values' => $oldValues + ['items' => $oldItems],
                'new_values' => $gallery->only([
                    'gallery_category_id',
                    'title',
                    'slug',
                    'description',
                    'cover_media_id',
                    'event_date',
                    'status',
                    'published_at',
                ]) + ['items' => $newItems],
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.gallery')
            ->with('status', 'Gallery saved.');
    }

    public function storeItem(Request $request, Gallery $gallery): RedirectResponse
    {
        Gate::authorize('update', $gallery);

        $validated = $request->validate([
            'media_id' => [
                'required',
                'integer',
                Rule::exists('media', 'id')->whereNull('deleted_at'),
                Rule::unique('gallery_items', 'media_id')->where('gallery_id', $gallery->id),
            ],
            'caption' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        $media = Media::query()->findOrFail($validated['media_id']);

        if (! str_starts_with((string) $media->mime_type, 'image/')) {
            throw ValidationException::withMessages([
                'media_id' => 'Only image media can be added to a gallery.',
            ]);
        }

        if (! $media->storedFileExists()) {
            throw ValidationException::withMessages([
                'media_id' => 'The stored image file is unavailable. Replace it in the media library before adding it.',
            ]);
        }

        if ($gallery->status === 'published') {
            $this->assertMediaCanBePublished($media);
        }

        DB::transaction(function () use ($gallery, $media, $request, $validated): void {
            Gallery::query()->whereKey($gallery->id)->lockForUpdate()->firstOrFail();
            $lockedMedia = Media::query()->whereKey($media->id)->lockForUpdate()->firstOrFail();
            $gallery->refresh();

            if (! $lockedMedia->storedFileExists()) {
                throw ValidationException::withMessages([
                    'media_id' => 'The stored image file is unavailable. Replace it in the media library before adding it.',
                ]);
            }

            if ($gallery->status === 'published') {
                $this->assertMediaCanBePublished($lockedMedia);
            }

            if ($gallery->items()->where('media_id', $media->id)->exists()) {
                throw ValidationException::withMessages([
                    'media_id' => 'This image is already attached to the gallery.',
                ]);
            }

            $isFirstItem = ! $gallery->items()->exists();
            $isFeatured = $isFirstItem || $request->boolean('is_featured');
            $sortOrder = ((int) $gallery->items()->lockForUpdate()->max('sort_order')) + 1;

            if ($isFeatured) {
                $gallery->items()->update(['is_featured' => false]);
            }

            $item = $gallery->items()->create([
                'media_id' => $lockedMedia->id,
                'caption' => $this->nullableText($validated['caption'] ?? null) ?? $lockedMedia->caption,
                'sort_order' => $sortOrder,
                'is_featured' => $isFeatured,
            ]);

            MediaUsage::query()->create([
                'media_id' => $lockedMedia->id,
                'usable_type' => GalleryItem::class,
                'usable_id' => $item->id,
                'field_name' => 'media_id',
            ]);

            if ($isFeatured) {
                $gallery->forceFill([
                    'cover_media_id' => $lockedMedia->id,
                    'updated_by' => $request->user()?->id,
                ])->save();
            } else {
                $gallery->forceFill(['updated_by' => $request->user()?->id])->save();
            }

            $this->audit(
                $request,
                'gallery.item_added',
                GalleryItem::class,
                $item->id,
                'Image added to gallery: '.$gallery->title,
                null,
                $item->only(['gallery_id', 'media_id', 'caption', 'sort_order', 'is_featured']),
            );
        });

        return $this->redirectToEditor($gallery, 'Image added to gallery.');
    }

    public function destroyItem(Request $request, Gallery $gallery, GalleryItem $galleryItem): RedirectResponse
    {
        Gate::authorize('update', $gallery);
        $this->assertItemBelongsToGallery($galleryItem, $gallery);

        DB::transaction(function () use ($gallery, $galleryItem, $request): void {
            Gallery::query()->whereKey($gallery->id)->lockForUpdate()->firstOrFail();
            $oldValues = $galleryItem->only(['gallery_id', 'media_id', 'caption', 'sort_order', 'is_featured']);

            $this->removeItemRecord($gallery, $galleryItem, $request->user()?->id);

            $this->audit(
                $request,
                'gallery.item_removed',
                GalleryItem::class,
                $galleryItem->id,
                'Image removed from gallery: '.$gallery->title,
                $oldValues,
                null,
            );
        });

        return $this->redirectToEditor($gallery, 'Image removed from gallery.');
    }

    public function destroyItemMedia(
        Request $request,
        Gallery $gallery,
        GalleryItem $galleryItem,
        MediaFileManager $files,
        MediaUsageInspector $usages,
    ): RedirectResponse {
        Gate::authorize('update', $gallery);
        $this->assertItemBelongsToGallery($galleryItem, $gallery);

        $media = $galleryItem->media()->firstOrFail();
        Gate::authorize('delete', $media);

        $references = $usages->externalReferences($media, $galleryItem, $gallery);

        if ($references !== []) {
            throw ValidationException::withMessages([
                'media_id' => 'This image is still used as '.implode(', ', $references).'. Remove those links before deleting it.',
            ]);
        }

        $oldValues = $media->only([
            'disk',
            'directory',
            'stored_name',
            'original_name',
            'mime_type',
            'visibility',
            'consent_required',
            'consent_confirmed',
            'publication_restricted',
        ]);
        $oldDisk = $media->disk;
        $copied = $files->copyToDisk($media, 'local');
        $variantFiles = $media->variants()->get(['disk', 'path']);

        try {
            DB::transaction(function () use ($copied, $gallery, $galleryItem, $media, $oldValues, $request, $usages): void {
                Gallery::query()->whereKey($gallery->id)->lockForUpdate()->firstOrFail();
                Media::query()->whereKey($media->id)->lockForUpdate()->firstOrFail();

                $references = $usages->externalReferences($media, $galleryItem, $gallery);

                if ($references !== []) {
                    throw ValidationException::withMessages([
                        'media_id' => 'This image is still used as '.implode(', ', $references).'. Remove those links before deleting it.',
                    ]);
                }

                $this->removeItemRecord($gallery, $galleryItem, $request->user()?->id);

                $media->forceFill([
                    'disk' => $copied ? 'local' : $media->disk,
                    'visibility' => 'private',
                    'publication_restricted' => true,
                    'restriction_reason' => 'Deleted from the gallery editor.',
                ])->save();
                $media->variants()->delete();
                $media->delete();

                $this->audit(
                    $request,
                    'gallery.media_deleted',
                    Media::class,
                    $media->id,
                    'Media deleted from gallery: '.$gallery->title,
                    $oldValues + ['gallery_item_id' => $galleryItem->id],
                    ['deleted_at' => $media->deleted_at?->toISOString()],
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

        $variantFiles->each(fn ($variant) => Storage::disk($variant->disk)->delete($variant->path));

        return $this->redirectToEditor($gallery, 'Image and media record deleted.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function featuredItemId(array $validated): ?int
    {
        if (isset($validated['featured_item_id'])) {
            return (int) $validated['featured_item_id'];
        }

        $featured = collect($validated['items'] ?? [])
            ->filter(fn (array $item): bool => (bool) ($item['is_featured'] ?? false));

        if ($featured->count() > 1) {
            throw ValidationException::withMessages([
                'featured_item_id' => 'Choose only one gallery cover image.',
            ]);
        }

        return $featured->isEmpty() ? null : (int) $featured->first()['id'];
    }

    private function assertGalleryCanBePublished(Gallery $gallery): void
    {
        $items = $gallery->items()->with('media')->get();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'status' => 'Add at least one approved image before publishing this gallery.',
            ]);
        }

        foreach ($items as $item) {
            $this->assertMediaCanBePublished($item->media);
        }
    }

    private function assertMediaCanBePublished(?Media $media): void
    {
        $name = $media?->original_name ?? 'An attached image';

        if (! $media || ! str_starts_with((string) $media->mime_type, 'image/')) {
            throw ValidationException::withMessages([
                'status' => $name.' is not a valid gallery image.',
            ]);
        }

        if ($this->nullableText($media->alt_text) === null) {
            throw ValidationException::withMessages([
                'status' => $name.' needs alternative text before the gallery can be published.',
            ]);
        }

        if (! $media->storedFileExists()) {
            throw ValidationException::withMessages([
                'status' => $name.' has no readable stored file. Replace it in the media library before publication.',
            ]);
        }

        if (! $media->isPubliclyAvailable()) {
            throw ValidationException::withMessages([
                'status' => $name.' must be approved, public, unrestricted, and have confirmed consent before publication.',
            ]);
        }
    }

    private function normalizeCover(Gallery $gallery, ?int $preferredItemId = null): void
    {
        $items = $gallery->items()->orderBy('sort_order')->orderBy('id')->get();

        if ($items->isEmpty()) {
            $gallery->forceFill(['cover_media_id' => null])->save();

            return;
        }

        $featured = $preferredItemId
            ? $items->firstWhere('id', $preferredItemId)
            : $items->firstWhere('is_featured', true);
        $featured ??= $items->first();

        $gallery->items()->update(['is_featured' => false]);
        $gallery->items()->whereKey($featured->id)->update(['is_featured' => true]);
        $gallery->forceFill(['cover_media_id' => $featured->media_id])->save();
    }

    private function removeItemRecord(Gallery $gallery, GalleryItem $galleryItem, ?int $actorId): void
    {
        MediaUsage::query()
            ->where('media_id', $galleryItem->media_id)
            ->where('usable_type', GalleryItem::class)
            ->where('usable_id', $galleryItem->id)
            ->where('field_name', 'media_id')
            ->delete();

        $galleryItem->delete();

        $gallery->items()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->each(fn (GalleryItem $item, int $index) => $item->update(['sort_order' => $index + 1]));

        $gallery->forceFill(['updated_by' => $actorId])->save();
        $this->normalizeCover($gallery);
    }

    private function assertItemBelongsToGallery(GalleryItem $galleryItem, Gallery $gallery): void
    {
        abort_unless($galleryItem->gallery_id === $gallery->id, 404);
    }

    private function redirectToEditor(Gallery $gallery, string $status): RedirectResponse
    {
        return redirect()
            ->route('admin.gallery.editor', ['gallery' => $gallery->slug])
            ->with('status', $status);
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function audit(
        Request $request,
        string $action,
        string $subjectType,
        int $subjectId,
        string $description,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);
    }

    private function uniqueSlug(string $title, Gallery $gallery): string
    {
        $baseSlug = Str::slug($title) ?: 'school-gallery';
        $slug = $baseSlug;
        $suffix = 2;

        while (Gallery::query()->withTrashed()->where('slug', $slug)->whereKeyNot($gallery->id)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function uniqueSlugForCreate(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'school-gallery';
        $slug = $baseSlug;
        $suffix = 2;

        while (Gallery::query()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
