<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function create(User $user): bool
    {
        return $user->is_active && $user->hasPermission('events.manage');
    }

    public function update(User $user, Event $event): bool
    {
        return $user->is_active && $user->hasPermission('events.manage');
    }
}
