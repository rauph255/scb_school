<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'uuid',
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
        'alt_text',
        'caption',
        'credit',
        'focal_x',
        'focal_y',
        'visibility',
        'consent_required',
        'consent_confirmed',
        'consent_reference',
        'publication_restricted',
        'restriction_reason',
        'is_protected_asset',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'consent_required' => 'boolean',
            'consent_confirmed' => 'boolean',
            'publication_restricted' => 'boolean',
            'is_protected_asset' => 'boolean',
            'focal_x' => 'decimal:2',
            'focal_y' => 'decimal:2',
        ];
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('visibility', 'public')
            ->where('publication_restricted', false)
            ->where(function (Builder $query): void {
                $query->where('consent_required', false)
                    ->orWhere('consent_confirmed', true);
            });
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
