<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function create(User $user): bool
    {
        return $user->is_active && $user->hasPermission('roles.manage');
    }

    public function updatePermissions(User $user, Role $role): bool
    {
        return $user->is_active && $user->hasPermission('roles.manage');
    }
}
