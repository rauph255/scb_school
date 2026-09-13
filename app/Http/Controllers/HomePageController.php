<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsPublicSiteData;
use App\Models\Event;
use App\Models\Media;
use App\Models\Post;
use Illuminate\Contracts\View\View;

class HomePageController extends Controller
{
    use LoadsPublicSiteData;

    public function __invoke(): View
    {
        $page = $this->publicPage('home');
        $heroBlock = $page->blocks->firstWhere('block_type', 'hero');
        $heroMediaIds = collect($heroBlock?->settings['carousel_media_ids'] ?? [])
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();
        $heroMedia = Media::query()
            ->publiclyVisible()
            ->whereKey($heroMediaIds)
            ->get()
            ->keyBy('id');
        $heroSlides = $heroMediaIds
            ->map(fn (int $id) => $heroMedia->get($id))
            ->filter()
            ->values();

        if ($heroSlides->isEmpty() && $heroBlock?->media) {
            $heroSlides = collect([$heroBlock->media]);
        }

        $latestPosts = Post::query()
            ->published()
            ->with([
                'category',
                'featuredMedia' => fn ($query) => $query->publiclyVisible(),
            ])
            ->latest('published_at')
            ->limit(2)
            ->get();

        $upcomingEvents = Event::query()
            ->published()
            ->where('starts_at', '>=', now())
            ->with('category')
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        return view('public.home', [
            ...$this->publicSiteData($page),
            'heroSlides' => $heroSlides,
            'latestPosts' => $latestPosts,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
