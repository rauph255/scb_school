<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->hasPermission('contacts.view');
    }

    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $user->is_active && $user->hasPermission('contacts.view');
    }

    public function export(User $user): bool
    {
        return $user->is_active && $user->hasPermission('contacts.export');
    }

    public function assign(User $user, ContactMessage $contactMessage): bool
    {
        return $user->is_active && $user->hasPermission('contacts.manage');
    }

    public function updateStatus(User $user, ContactMessage $contactMessage): bool
    {
        return $user->is_active && $user->hasPermission('contacts.manage');
    }

    public function storeNote(User $user, ContactMessage $contactMessage): bool
    {
        return $user->is_active && $user->hasPermission('contacts.manage');
    }

    public function reply(User $user, ContactMessage $contactMessage): bool
    {
        return $user->is_active && $user->hasPermission('contacts.manage');
    }
}
