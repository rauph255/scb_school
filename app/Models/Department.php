<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use NormalizesEmail, SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'email', 'telephone', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }

    public function staffMembers(): HasMany
    {
        return $this->hasMany(StaffMember::class);
    }
}
