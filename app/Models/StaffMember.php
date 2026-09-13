<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffMember extends Model
{
    use NormalizesEmail, SoftDeletes;

    protected $fillable = ['department_id', 'name', 'slug', 'job_title', 'staff_type', 'approved_biography', 'photo_media_id', 'email', 'telephone', 'sort_order', 'is_leadership', 'is_public', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_leadership' => 'boolean',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('is_public', true)->where('is_active', true);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }
}
