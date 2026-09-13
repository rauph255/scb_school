<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationScope;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use HasPublicationScope;
    use SoftDeletes;

    protected $fillable = ['post_category_id', 'author_id', 'title', 'slug', 'excerpt', 'body', 'featured_media_id', 'status', 'is_featured', 'published_at', 'scheduled_at', 'seo_title', 'seo_description', 'canonical_url', 'og_title', 'og_description', 'og_media_id', 'robots_index', 'robots_follow', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function articleMediaUsages(): HasMany
    {
        return $this->hasMany(MediaUsage::class, 'usable_id')
            ->where('usable_type', self::class)
            ->where('field_name', 'like', 'article_gallery:%')
            ->orderBy('field_name');
    }
}
