<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use HasPublicationScope;
    use SoftDeletes;

    protected $fillable = ['gallery_category_id', 'title', 'slug', 'description', 'cover_media_id', 'event_date', 'status', 'published_at', 'sort_order', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order');
    }
}
