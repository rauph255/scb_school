<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaUsage extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['media_id', 'usable_type', 'usable_id', 'field_name'];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
