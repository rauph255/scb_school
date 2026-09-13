<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $portal): Response
    {
        if (! $request->user()) {
            return redirect()->route($portal === 'admin' ? 'admin.login' : 'staff.login');
        }

        if (! $request->user()->is_active) {
            abort(403);
        }

        $allowedRoles = match ($portal) {
            'admin' => ['super-administrator', 'school-administrator', 'content-editor', 'admissions-officer'],
            'staff' => ['teacher-contributor'],
            default => [],
        };

        abort_unless($request->user()->hasAnyRole($allowedRoles), 403);

        return $next($request);
    }
}
