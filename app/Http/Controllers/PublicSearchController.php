<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsPublicSiteData;
use App\Models\Announcement;
use App\Models\Download;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\Programme;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicSearchController extends Controller
{
    use LoadsPublicSiteData;

    public function __invoke(Request $request): View
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $term = trim((string) ($validated['q'] ?? ''));
        $results = mb_strlen($term) >= 2 ? $this->results($term) : collect();
        $page = Page::query()->published()->where('slug', 'search')->firstOrFail();
        $page->setAttribute('robots_index', false);

        return view('public.search', [
            ...$this->publicChromeData(),
            'page' => $page,
            'blocks' => collect(),
            'query' => $term,
            'results' => $results,
        ]);
    }

    /** @return Collection<int, array{type: string, title: string, summary: string, url: string}> */
    private function results(string $term): Collection
    {
        $like = '%'.addcslashes($term, '\\%_').'%';
        $matches = fn (Builder $query, array $columns): Builder => $query->where(function (Builder $query) use ($columns, $like): void {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', $like);
            }
        });

        $pages = Page::query()
            ->published()
            ->where('robots_index', true)
            ->where(fn (Builder $query) => $matches($query, ['title', 'excerpt']))
            ->limit(8)
            ->get(['title', 'slug', 'excerpt'])
            ->map(fn (Page $page): array => $this->result('Page', $page->title, $page->excerpt, $this->pageUrl($page)));

        $posts = Post::query()
            ->published()
            ->where('robots_index', true)
            ->where(fn (Builder $query) => $matches($query, ['title', 'excerpt', 'body']))
            ->limit(8)
            ->get(['title', 'slug', 'excerpt'])
            ->map(fn (Post $post): array => $this->result('News', $post->title, $post->excerpt, route('news.show', $post)));

        $events = Event::query()
            ->published()
            ->where('robots_index', true)
            ->where(fn (Builder $query) => $matches($query, ['title', 'summary', 'body']))
            ->limit(8)
            ->get(['title', 'slug', 'summary'])
            ->map(fn (Event $event): array => $this->result('Event', $event->title, $event->summary, route('events.show', $event)));

        $programmes = Programme::query()
            ->published()
            ->where('robots_index', true)
            ->where(fn (Builder $query) => $matches($query, ['name', 'summary', 'body']))
            ->limit(8)
            ->get(['name', 'slug', 'summary'])
            ->map(fn (Programme $programme): array => $this->result('Programme', $programme->name, $programme->summary, route('academics').'#programme-'.$programme->slug));

        $faqs = Faq::query()
            ->published()
            ->where(fn (Builder $query) => $matches($query, ['question', 'answer']))
            ->limit(8)
            ->get(['id', 'question', 'answer'])
            ->map(fn (Faq $faq): array => $this->result('FAQ', $faq->question, $faq->answer, route('faq').'#faq-'.$faq->id));

        $downloads = Download::query()
            ->published()
            ->whereHas('media', fn (Builder $query) => $query->publiclyVisible())
            ->where(fn (Builder $query) => $matches($query, ['title', 'description']))
            ->limit(8)
            ->get(['title', 'slug', 'description'])
            ->map(fn (Download $download): array => $this->result('Download', $download->title, $download->description, route('downloads').'#download-'.$download->slug));

        $announcements = Announcement::query()
            ->active()
            ->where(fn (Builder $query) => $matches($query, ['title', 'message']))
            ->limit(8)
            ->get(['title', 'message', 'link_url'])
            ->map(fn (Announcement $announcement): array => $this->result('Notice', $announcement->title, $announcement->message, $announcement->link_url ?: route('home')));

        return collect()
            ->concat($pages)
            ->concat($posts)
            ->concat($events)
            ->concat($programmes)
            ->concat($faqs)
            ->concat($downloads)
            ->concat($announcements)
            ->take(40)
            ->values();
    }

    /** @return array{type: string, title: string, summary: string, url: string} */
    private function result(string $type, string $title, ?string $summary, string $url): array
    {
        return [
            'type' => $type,
            'title' => $title,
            'summary' => Str::limit(trim(strip_tags((string) $summary)), 220),
            'url' => $url,
        ];
    }

    private function pageUrl(Page $page): string
    {
        $routes = [
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
        ];

        return isset($routes[$page->slug])
            ? route($routes[$page->slug])
            : route('pages.show', $page);
    }
}
