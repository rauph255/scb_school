<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function create(User $user): bool
    {
        return $user->is_active && $user->hasPermission('pages.create');
    }

    public function update(User $user, Page $page): bool
    {
        return $user->is_active && $user->hasPermission('pages.update');
    }

    public function publish(User $user, Page $page): bool
    {
        return $user->is_active && $user->hasPermission('pages.publish');
    }
}
