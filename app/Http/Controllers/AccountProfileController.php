<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountProfileController extends Controller
{
    public function admin(): View
    {
        return view('admin.profile', [
            'activeNav' => 'profile',
            'user' => auth()->user(),
        ]);
    }

    public function updateAdmin(Request $request): RedirectResponse
    {
        $this->update($request);

        return redirect()->route('admin.profile')->with('status', 'Your account details were updated.');
    }

    public function updateStaff(Request $request): RedirectResponse
    {
        $this->update($request);

        return redirect(route('staff.dashboard').'#staff-profile-form')
            ->with('status', 'Your account details were updated.');
    }

    private function update(Request $request): void
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validateWithBag('profile', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['required', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $oldValues = $user->only(['name', 'email']);
        $passwordChanged = filled($validated['password'] ?? null);

        DB::transaction(function () use ($request, $user, $validated, $oldValues, $passwordChanged): void {
            $emailChanged = $validated['email'] !== $user->email;
            $user->forceFill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
                ...($passwordChanged ? [
                    'password' => $validated['password'],
                    'remember_token' => Str::random(60),
                ] : []),
            ])->save();

            if ($passwordChanged && config('session.driver') === 'database') {
                DB::table((string) config('session.table', 'sessions'))
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $request->session()->getId())
                    ->delete();
            }

            AuditLog::query()->create([
                'actor_id' => $user->id,
                'action' => $passwordChanged ? 'account.profile_and_password_updated' : 'account.profile_updated',
                'subject_type' => User::class,
                'subject_id' => $user->id,
                'description' => 'User updated their own account details.',
                'old_values' => $oldValues,
                'new_values' => [
                    ...$user->only(['name', 'email']),
                    'password_changed' => $passwordChanged,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });
    }
}
