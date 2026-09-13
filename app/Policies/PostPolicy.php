<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function create(User $user): bool
    {
        return $user->is_active && $user->hasPermission('news.manage');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->is_active && $user->hasPermission('news.manage');
    }
}
