<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBlock extends Model
{
    protected $fillable = ['page_id', 'block_type', 'heading', 'subheading', 'body', 'media_id', 'settings', 'sort_order', 'is_enabled', 'visible_from', 'visible_until'];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_enabled' => 'boolean',
            'visible_from' => 'datetime',
            'visible_until' => 'datetime',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('is_enabled', true)
            ->where(function (Builder $query): void {
                $query->whereNull('visible_from')->orWhere('visible_from', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('visible_until')->orWhere('visible_until', '>=', now());
            });
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
