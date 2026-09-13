<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->hasPermission('pages.view');
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->hasPermission('pages.create');
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->is_active && $user->hasPermission('pages.update');
    }

    public function verify(User $user, Faq $faq): bool
    {
        return $user->is_active && $user->hasPermission('pages.publish');
    }
}
