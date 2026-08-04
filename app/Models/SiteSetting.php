<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSetting extends Model
{
    protected $fillable = ['group_name', 'setting_key', 'value_json', 'value_type', 'is_public', 'updated_by'];

    protected function casts(): array
    {
        return [
            'value_json' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
