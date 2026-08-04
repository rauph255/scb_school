<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Programme extends Model
{
    use HasPublicationScope;
    use SoftDeletes;

    protected $fillable = ['department_id', 'name', 'slug', 'programme_type', 'level', 'summary', 'body', 'featured_media_id', 'sort_order', 'status', 'published_at', 'seo_title', 'seo_description', 'canonical_url', 'og_title', 'og_description', 'og_media_id', 'robots_index', 'robots_follow', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
