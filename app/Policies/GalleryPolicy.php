<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;

class GalleryPolicy
{
    public function create(User $user): bool
    {
        return $user->is_active && $user->hasPermission('galleries.manage');
    }

    public function update(User $user, Gallery $gallery): bool
    {
        return $user->is_active && $user->hasPermission('galleries.manage');
    }
}
