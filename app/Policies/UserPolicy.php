<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function create(User $actor): bool
    {
        return $actor->is_active && $actor->hasPermission('users.manage');
    }

    public function updateProfile(User $actor, User $user): bool
    {
        return $actor->is_active && $actor->hasPermission('users.manage');
    }

    public function updateStatus(User $actor, User $user): bool
    {
        return $actor->is_active
            && $actor->id !== $user->id
            && $actor->hasPermission('users.manage');
    }

    public function updateRoles(User $actor, User $user): bool
    {
        return $actor->is_active
            && $actor->id !== $user->id
            && $actor->hasPermission('users.manage');
    }
}
