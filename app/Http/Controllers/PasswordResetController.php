<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function requestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        Password::sendResetLink(['email' => mb_strtolower(trim($validated['email']))]);

        return back()->with(
            'status',
            'If an active account matches that address, a password reset link has been sent.',
        );
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $status = Password::reset(
            [
                'email' => mb_strtolower(trim($validated['email'])),
                'password' => $validated['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $validated['token'],
            ],
            function (User $user, string $password) use ($request): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                AuditLog::query()->create([
                    'actor_id' => $user->id,
                    'action' => 'account.password_reset',
                    'subject_type' => User::class,
                    'subject_id' => $user->id,
                    'description' => 'Account password reset through a verified token.',
                    'old_values' => null,
                    'new_values' => ['password_changed' => true],
                    'ip_address' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                    'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
                ]);

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => trans($status)]);
        }

        return redirect()
            ->route('admin.login')
            ->with('status', 'Your password has been reset. You can now sign in.');
    }
}
