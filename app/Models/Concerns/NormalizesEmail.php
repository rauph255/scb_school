<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait NormalizesEmail
{
    protected function email(): Attribute
    {
        return Attribute::set(
            fn (mixed $value): ?string => is_string($value) && trim($value) !== ''
                ? mb_strtolower(trim($value))
                : null,
        );
    }
}
