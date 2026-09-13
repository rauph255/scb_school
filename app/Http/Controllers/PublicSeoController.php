<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class PublicSeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect()
            ->concat(Page::query()->published()->where('robots_index', true)->get(['slug', 'updated_at'])->map(fn (Page $page): array => [
                'location' => $this->pageUrl($page),
                'last_modified' => $page->updated_at,
            ]))
            ->concat(Post::query()->published()->where('robots_index', true)->get(['slug', 'updated_at'])->map(fn (Post $post): array => [
                'location' => route('news.show', $post),
                'last_modified' => $post->updated_at,
            ]))
            ->concat(Event::query()->published()->where('robots_index', true)->get(['slug', 'updated_at'])->map(fn (Event $event): array => [
                'location' => route('events.show', $event),
                'last_modified' => $event->updated_at,
            ]))
            ->unique('location')
            ->values();

        return response()
            ->view('public.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function robots(): Response
    {
        $lines = app()->environment('production')
            ? ['User-agent: *', 'Allow: /', 'Disallow: /admin', 'Disallow: /staff-portal', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function pageUrl(Page $page): string
    {
        /** @var Collection<string, string> $routes */
        $routes = collect([
            'home' => 'home',
            'about' => 'about',
            'academics' => 'academics',
            'admissions' => 'admissions',
            'news' => 'news.index',
            'events' => 'events.index',
            'gallery' => 'gallery',
            'downloads' => 'downloads',
            'contact' => 'contact',
            'faq' => 'faq',
            'privacy' => 'privacy',
            'search' => 'search',
        ]);

        return $routes->has($page->slug)
            ? route($routes->get($page->slug))
            : route('pages.show', $page);
    }
}
