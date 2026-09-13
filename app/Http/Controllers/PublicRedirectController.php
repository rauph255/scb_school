<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $sourcePath = '/'.ltrim($request->path(), '/');
        $redirect = Redirect::query()
            ->where('source_path', $sourcePath)
            ->where('is_active', true)
            ->first();

        abort_unless($redirect, 404);

        Redirect::query()->whereKey($redirect->id)->increment('hit_count');
        Redirect::query()->whereKey($redirect->id)->update(['last_hit_at' => now()]);

        $status = in_array($redirect->http_status, [301, 302, 307, 308], true)
            ? $redirect->http_status
            : 301;

        $target = trim($redirect->target_url);

        if (str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            return redirect($target, $status);
        }

        $scheme = parse_url($target, PHP_URL_SCHEME);
        abort_unless(filter_var($target, FILTER_VALIDATE_URL) && in_array($scheme, ['http', 'https'], true), 404);

        return redirect()->away($target, $status);
    }
}
