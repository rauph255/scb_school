<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasPublicationScope
{
    public function scopePublished(Builder $query): Builder
    {
        $column = $this->getPublicationStatusColumn();

        return $query
            ->where($column, 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    protected function getPublicationStatusColumn(): string
    {
        return property_exists($this, 'publicationStatusColumn')
            ? $this->publicationStatusColumn
            : 'status';
    }
}
