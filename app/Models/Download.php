<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Download extends Model
{
    use HasPublicationScope;
    use SoftDeletes;

    protected $fillable = ['download_category_id', 'title', 'slug', 'description', 'media_id', 'version', 'publication_date', 'status', 'published_at', 'download_count', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DownloadCategory::class, 'download_category_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
