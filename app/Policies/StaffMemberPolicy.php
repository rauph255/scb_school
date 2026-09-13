<?php

namespace App\Policies;

use App\Models\StaffMember;
use App\Models\User;

class StaffMemberPolicy
{
    public function create(User $user): bool
    {
        return $user->is_active && $user->hasPermission('staff.manage');
    }

    public function updateVisibility(User $user, StaffMember $staffMember): bool
    {
        return $user->is_active && $user->hasPermission('staff.manage');
    }

    public function updateMetadata(User $user, StaffMember $staffMember): bool
    {
        return $user->is_active && $user->hasPermission('staff.manage');
    }
}
