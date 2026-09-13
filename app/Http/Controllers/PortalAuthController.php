<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PortalAuthController extends Controller
{
    public function adminLogin(Request $request): RedirectResponse
    {
        return $this->login(
            request: $request,
            roles: ['super-administrator', 'school-administrator', 'content-editor', 'admissions-officer'],
            redirectRoute: 'admin.dashboard',
            label: 'administrator'
        );
    }

    public function staffLogin(Request $request): RedirectResponse
    {
        return $this->login(
            request: $request,
            roles: ['teacher-contributor'],
            redirectRoute: 'staff.dashboard',
            label: 'staff'
        );
    }

    public function adminLogout(Request $request): RedirectResponse
    {
        return $this->logout($request, 'admin.login');
    }

    public function staffLogout(Request $request): RedirectResponse
    {
        return $this->logout($request, 'staff.login');
    }

    public function rejectGetLogout(): never
    {
        abort(405);
    }

    /**
     * @param  array<int, string>  $roles
     */
    private function login(Request $request, array $roles, string $redirectRoute, string $label): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an active '.$label.' account.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user || ! $user->is_active || ! $user->hasAnyRole($roles)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an active '.$label.' account.',
            ]);
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return redirect()->intended(route($redirectRoute));
    }

    private function logout(Request $request, string $redirectRoute): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($redirectRoute);
    }
}
