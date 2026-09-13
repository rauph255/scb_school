<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function view(User $user, Media $media): bool
    {
        return $user->is_active
            && ($user->hasPermission('media.view') || $user->hasPermission('media.approve'));
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->hasPermission('media.approve');
    }

    public function updateMetadata(User $user, Media $media): bool
    {
        return $user->is_active && $user->hasPermission('media.approve');
    }

    public function updatePublication(User $user, Media $media): bool
    {
        return $user->is_active && $user->hasPermission('media.approve');
    }

    public function replace(User $user, Media $media): bool
    {
        return $user->is_active
            && ! $media->is_protected_asset
            && $user->hasPermission('media.approve');
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->is_active
            && ! $media->is_protected_asset
            && $user->hasPermission('media.approve');
    }
}
