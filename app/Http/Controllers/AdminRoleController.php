<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class AdminRoleController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Role::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $role = Role::query()->create([
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug($validated['name']),
                'description' => $validated['description'] ?? null,
                'is_system' => false,
            ]);

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'role.created',
                'subject_type' => Role::class,
                'subject_id' => $role->id,
                'description' => 'Restricted role created: '.$role->name,
                'new_values' => $role->only(['name', 'slug', 'description', 'is_system']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.roles')
            ->with('status', 'Restricted role created. Assign permissions before use.');
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('updatePermissions', $role);

        $validated = $request->validate([
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $oldValues = [
            'permissions' => $role->permissions()->orderBy('slug')->pluck('slug')->all(),
        ];

        DB::transaction(function () use ($request, $role, $validated, $oldValues): void {
            $permissionIds = collect($validated['permission_ids'] ?? [])
                ->map(fn (int|string $permissionId): int => (int) $permissionId)
                ->unique()
                ->values()
                ->all();

            $role->permissions()->sync($permissionIds);

            $newPermissions = Permission::query()
                ->whereIn('id', $permissionIds)
                ->orderBy('slug')
                ->pluck('slug')
                ->all();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'role.permissions_updated',
                'subject_type' => Role::class,
                'subject_id' => $role->id,
                'description' => 'Role permissions updated: '.$role->name,
                'old_values' => $oldValues,
                'new_values' => ['permissions' => $newPermissions],
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.roles')
            ->with('status', 'Role permissions saved.');
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'custom-role';
        $slug = $baseSlug;
        $suffix = 2;

        while (Role::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
