<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'role_ids' => ['sometimes', 'array'],
            'role_ids.*' => ['integer', Rule::exists('roles', 'id')],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            $pivotRows = $this->rolePivotRows($validated['role_ids'] ?? [], $request->user()?->id);

            $user->roles()->sync($pivotRows);
            $user->load('roles');

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'user.created',
                'subject_type' => User::class,
                'subject_id' => $user->id,
                'description' => 'User created from visible admin users screen: '.$user->email,
                'old_values' => null,
                'new_values' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->sortBy('slug')->pluck('slug')->values()->all(),
                    'is_active' => $user->is_active,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.users')
            ->with('status', 'User invited.');
    }

    public function updateProfile(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('updateProfile', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $oldValues = $user->only(['name', 'email']);

        DB::transaction(function () use ($request, $user, $validated, $oldValues): void {
            $user->forceFill([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'user.profile_updated',
                'subject_type' => User::class,
                'subject_id' => $user->id,
                'description' => 'User profile updated: '.$user->email,
                'old_values' => $oldValues,
                'new_values' => $user->only(['name', 'email']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.users')
            ->with('status', 'User profile saved.');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('updateStatus', $user);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'disabled'])],
        ]);

        $oldValues = $user->only(['is_active']);

        DB::transaction(function () use ($request, $user, $validated, $oldValues): void {
            $user->forceFill([
                'is_active' => $validated['status'] === 'active',
            ])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'user.status_updated',
                'subject_type' => User::class,
                'subject_id' => $user->id,
                'description' => 'User status updated: '.$user->email.' to '.str($validated['status'])->title(),
                'old_values' => $oldValues,
                'new_values' => $user->only(['is_active']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.users')
            ->with('status', 'User updated.');
    }

    public function updateRoles(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('updateRoles', $user);

        $validated = $request->validate([
            'role_ids' => ['sometimes', 'array'],
            'role_ids.*' => ['integer', Rule::exists('roles', 'id')],
        ]);

        $oldRoles = $user->roles()->orderBy('slug')->pluck('slug')->all();
        $roleIds = collect($validated['role_ids'] ?? [])
            ->map(fn (int|string $roleId): int => (int) $roleId)
            ->unique()
            ->values();

        DB::transaction(function () use ($request, $roleIds, $user, $oldRoles): void {
            $pivotRows = $this->rolePivotRows($roleIds->all(), $request->user()?->id);

            $user->roles()->sync($pivotRows);
            $user->load('roles');

            $newRoles = $user->roles
                ->sortBy('slug')
                ->pluck('slug')
                ->values()
                ->all();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'user.roles_updated',
                'subject_type' => User::class,
                'subject_id' => $user->id,
                'description' => 'User roles updated: '.$user->email,
                'old_values' => ['roles' => $oldRoles],
                'new_values' => ['roles' => $newRoles],
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.users')
            ->with('status', 'User roles saved.');
    }

    /**
     * @param  array<int, int|string>  $roleIds
     * @return array<int, array{assigned_by: int|null, created_at: Carbon}>
     */
    private function rolePivotRows(array $roleIds, ?int $assignedBy): array
    {
        return Role::query()
            ->whereIn('id', collect($roleIds)->map(fn (int|string $roleId): int => (int) $roleId)->unique())
            ->pluck('id')
            ->mapWithKeys(fn (int $roleId): array => [
                $roleId => [
                    'assigned_by' => $assignedBy,
                    'created_at' => now(),
                ],
            ])
            ->all();
    }
}
