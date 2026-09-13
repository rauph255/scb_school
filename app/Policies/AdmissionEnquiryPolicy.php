<?php

namespace App\Policies;

use App\Models\AdmissionEnquiry;
use App\Models\User;

class AdmissionEnquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->hasPermission('admissions.view');
    }

    public function view(User $user, AdmissionEnquiry $admissionEnquiry): bool
    {
        return $user->is_active && $user->hasPermission('admissions.view');
    }

    public function export(User $user): bool
    {
        return $user->is_active && $user->hasPermission('admissions.export');
    }

    public function assign(User $user, AdmissionEnquiry $admissionEnquiry): bool
    {
        return $user->is_active && $user->hasPermission('admissions.manage');
    }

    public function updateStatus(User $user, AdmissionEnquiry $admissionEnquiry): bool
    {
        return $user->is_active && $user->hasPermission('admissions.manage');
    }

    public function storeNote(User $user, AdmissionEnquiry $admissionEnquiry): bool
    {
        return $user->is_active && $user->hasPermission('admissions.manage');
    }

    public function reply(User $user, AdmissionEnquiry $admissionEnquiry): bool
    {
        return $user->is_active && $user->hasPermission('admissions.manage');
    }
}
