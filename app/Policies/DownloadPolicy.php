<?php

namespace App\Policies;

use App\Models\Download;
use App\Models\User;

class DownloadPolicy
{
    public function create(User $user): bool
    {
        return $user->is_active && $user->hasPermission('downloads.manage');
    }

    public function updateStatus(User $user, Download $download): bool
    {
        return $user->is_active && $user->hasPermission('downloads.manage');
    }

    public function updateMetadata(User $user, Download $download): bool
    {
        return $user->is_active && $user->hasPermission('downloads.manage');
    }

    public function updateFile(User $user, Download $download): bool
    {
        return $user->is_active && $user->hasPermission('downloads.manage');
    }
}
