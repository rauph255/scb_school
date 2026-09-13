<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsPublicSiteData;
use App\Models\Download;
use App\Models\Event;
use App\Models\Media;
use App\Models\Post;
use App\Models\StaffMember;
use Illuminate\Contracts\View\View;

class StaffPortalController extends Controller
{
    use LoadsPublicSiteData;

    public function login(): View
    {
        return view('staff-portal.login', [
            ...$this->publicChromeData(),
            'loginMedia' => Media::query()
                ->publiclyVisible()
                ->where('stored_name', 'campus-aerial.webp')
                ->first(),
        ]);
    }

    public function dashboard(): View
    {
        return view('staff-portal.dashboard', [
            ...$this->publicChromeData(),
            'upcomingEvents' => Event::query()
                ->published()
                ->with('category')
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(4)
                ->get(),
            'staffDownloads' => Download::query()
                ->published()
                ->whereHas('media', fn ($query) => $query->publiclyVisible())
                ->with([
                    'category',
                    'media' => fn ($query) => $query->publiclyVisible(),
                ])
                ->latest('publication_date')
                ->limit(5)
                ->get(),
            'recentPosts' => Post::query()
                ->published()
                ->with('category')
                ->latest('published_at')
                ->limit(3)
                ->get(),
            'myContributions' => Post::query()
                ->with('category')
                ->where('author_id', auth()->id())
                ->whereIn('status', ['draft', 'review'])
                ->latest('updated_at')
                ->limit(4)
                ->get(),
            'staffMembers' => StaffMember::query()
                ->publiclyVisible()
                ->with('department')
                ->orderBy('sort_order')
                ->limit(6)
                ->get(),
        ]);
    }
}
