<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\StaffMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminStaffMemberController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', StaffMember::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'job_title' => ['required', 'string', 'max:190'],
            'department_id' => ['nullable', Rule::exists('departments', 'id')],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $member = StaffMember::query()->create([
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug($validated['name']),
                'job_title' => $validated['job_title'],
                'department_id' => $validated['department_id'] ?? null,
                'sort_order' => ((int) StaffMember::query()->max('sort_order')) + 1,
                'is_public' => false,
                'is_active' => true,
            ]);

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'staff_member.created',
                'subject_type' => StaffMember::class,
                'subject_id' => $member->id,
                'description' => 'Private staff profile created: '.$member->name,
                'new_values' => $member->only(['name', 'slug', 'job_title', 'department_id', 'sort_order', 'is_public', 'is_active']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.staff')
            ->with('status', 'Private staff profile created. Review it before making it public.');
    }

    public function updateVisibility(Request $request, StaffMember $staffMember): RedirectResponse
    {
        Gate::authorize('updateVisibility', $staffMember);

        $validated = $request->validate([
            'visibility' => ['required', Rule::in(['public', 'private', 'inactive'])],
        ]);

        $oldValues = $staffMember->only(['is_public', 'is_active']);

        DB::transaction(function () use ($staffMember, $request, $validated, $oldValues): void {
            $visibility = $validated['visibility'];

            $staffMember->forceFill([
                'is_public' => $visibility === 'public',
                'is_active' => $visibility !== 'inactive',
            ])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'staff_member.visibility_updated',
                'subject_type' => StaffMember::class,
                'subject_id' => $staffMember->id,
                'description' => 'Staff visibility updated: '.$staffMember->name.' to '.str($visibility)->title(),
                'old_values' => $oldValues,
                'new_values' => $staffMember->only(['is_public', 'is_active']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.staff')
            ->with('status', 'Staff visibility updated.');
    }

    public function updateMetadata(Request $request, StaffMember $staffMember): RedirectResponse
    {
        Gate::authorize('updateMetadata', $staffMember);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'job_title' => ['nullable', 'string', 'max:190'],
            'department_id' => ['nullable', Rule::exists('departments', 'id')],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $oldValues = $staffMember->only(['name', 'job_title', 'department_id', 'sort_order']);

        DB::transaction(function () use ($staffMember, $request, $validated, $oldValues): void {
            $staffMember->forceFill([
                'name' => $validated['name'],
                'job_title' => $validated['job_title'] ?: null,
                'department_id' => $validated['department_id'] ?? null,
                'sort_order' => $validated['sort_order'],
            ])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'staff_member.metadata_updated',
                'subject_type' => StaffMember::class,
                'subject_id' => $staffMember->id,
                'description' => 'Staff metadata updated: '.$staffMember->name,
                'old_values' => $oldValues,
                'new_values' => $staffMember->only(['name', 'job_title', 'department_id', 'sort_order']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.staff')
            ->with('status', 'Staff profile saved.');
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'staff-member';
        $slug = $baseSlug;
        $suffix = 2;

        while (StaffMember::query()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
