<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasPublicationScope;
    use SoftDeletes;

    protected string $publicationStatusColumn = 'publication_status';

    protected $fillable = ['event_category_id', 'title', 'slug', 'summary', 'body', 'featured_media_id', 'starts_at', 'ends_at', 'timezone', 'venue_name', 'venue_address', 'map_url', 'registration_url', 'programme_download_id', 'event_state', 'publication_status', 'published_at', 'seo_title', 'seo_description', 'canonical_url', 'og_title', 'og_description', 'og_media_id', 'robots_index', 'robots_follow', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function programmeDownload(): BelongsTo
    {
        return $this->belongsTo(Download::class, 'programme_download_id');
    }
}
