<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaVariant extends Model
{
    protected $fillable = ['media_id', 'variant_key', 'disk', 'path', 'mime_type', 'width', 'height', 'size_bytes', 'checksum_sha256'];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
