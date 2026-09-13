<?php

namespace App\Policies;

use App\Models\User;

class SiteSettingPolicy
{
    public function update(User $user): bool
    {
        return $user->is_active && $user->hasPermission('settings.manage');
    }
}
