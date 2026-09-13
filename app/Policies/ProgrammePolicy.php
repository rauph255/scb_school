<?php

namespace App\Policies;

use App\Models\Programme;
use App\Models\User;

class ProgrammePolicy
{
    public function create(User $user): bool
    {
        return $user->is_active && $user->hasPermission('programmes.manage');
    }

    public function updateStatus(User $user, Programme $programme): bool
    {
        return $user->is_active && $user->hasPermission('programmes.manage');
    }

    public function updateMetadata(User $user, Programme $programme): bool
    {
        return $user->is_active && $user->hasPermission('programmes.manage');
    }
}
