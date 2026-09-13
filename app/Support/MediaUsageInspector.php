<?php

namespace App\Support;

use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Media;
use Illuminate\Support\Facades\DB;

class MediaUsageInspector
{
    /**
     * @return list<string>
     */
    public function externalReferences(
        Media $media,
        ?GalleryItem $allowedGalleryItem = null,
        ?Gallery $allowedCoverGallery = null,
    ): array {
        $references = [];
        $columns = [
            ['pages', 'featured_media_id', 'a page featured image'],
            ['pages', 'og_media_id', 'a page social image'],
            ['page_blocks', 'media_id', 'a page block'],
            ['posts', 'featured_media_id', 'a news featured image'],
            ['posts', 'og_media_id', 'a news social image'],
            ['downloads', 'media_id', 'a download'],
            ['events', 'featured_media_id', 'an event featured image'],
            ['events', 'og_media_id', 'an event social image'],
            ['programmes', 'featured_media_id', 'a programme featured image'],
            ['programmes', 'og_media_id', 'a programme social image'],
            ['staff_members', 'photo_media_id', 'a staff profile'],
        ];

        foreach ($columns as [$table, $column, $label]) {
            if (DB::table($table)->where($column, $media->id)->exists()) {
                $references[] = $label;
            }
        }

        if (DB::table('gallery_items')
            ->where('media_id', $media->id)
            ->when($allowedGalleryItem, fn ($query) => $query->where('id', '!=', $allowedGalleryItem->id))
            ->exists()) {
            $references[] = 'another gallery item';
        }

        if (DB::table('galleries')
            ->where('cover_media_id', $media->id)
            ->when($allowedCoverGallery, fn ($query) => $query->where('id', '!=', $allowedCoverGallery->id))
            ->exists()) {
            $references[] = 'another gallery cover';
        }

        $usageQuery = DB::table('media_usages')->where('media_id', $media->id);

        if ($allowedGalleryItem) {
            $usageQuery->where(function ($query) use ($allowedGalleryItem): void {
                $query->where('usable_type', '!=', GalleryItem::class)
                    ->orWhere('usable_id', '!=', $allowedGalleryItem->id)
                    ->orWhere('field_name', '!=', 'media_id');
            });
        }

        if ($usageQuery->exists()) {
            $references[] = 'another recorded media usage';
        }

        return array_values(array_unique($references));
    }
}
