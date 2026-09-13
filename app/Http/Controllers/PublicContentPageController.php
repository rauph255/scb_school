<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsPublicSiteData;
use App\Models\AdmissionEnquiry;
use App\Models\ContactMessage;
use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Page;
use App\Models\Post;
use App\Models\Programme;
use App\Models\StaffMember;
use App\Support\PublicEnquiryNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicContentPageController extends Controller
{
    use LoadsPublicSiteData;

    public function about(): View
    {
        $page = $this->publicPage('about');

        $staffMembers = StaffMember::query()
            ->publiclyVisible()
            ->with([
                'department',
                'photo' => fn ($query) => $query->publiclyVisible(),
            ])
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        return view('public.about', [
            ...$this->publicSiteData($page),
            'staffMembers' => $staffMembers,
        ]);
    }

    public function academics(): View
    {
        $page = $this->publicPage('academics');

        $programmes = Programme::query()
            ->published()
            ->with([
                'department',
                'featuredMedia' => fn ($query) => $query->publiclyVisible(),
            ])
            ->orderBy('sort_order')
            ->get();

        return view('public.academics', [
            ...$this->publicSiteData($page),
            'programmes' => $programmes,
        ]);
    }

    public function admissions(): View
    {
        $page = $this->publicPage('admissions');

        $downloads = Download::query()
            ->published()
            ->whereHas('media', fn ($query) => $query->publiclyVisible())
            ->with([
                'category',
                'media' => fn ($query) => $query->publiclyVisible(),
            ])
            ->orderByDesc('publication_date')
            ->limit(3)
            ->get();

        $faqs = Faq::query()
            ->published()
            ->with('category')
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        return view('public.admissions', [
            ...$this->publicSiteData($page),
            'downloads' => $downloads,
            'faqs' => $faqs,
        ]);
    }

    public function storeAdmissionEnquiry(Request $request, PublicEnquiryNotifier $notifier): RedirectResponse
    {
        $validated = $request->validate([
            'website' => ['prohibited'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:255'],
            'intended_level' => ['nullable', 'string', 'max:150'],
            'intended_term' => ['nullable', 'string', 'max:100'],
            'intended_year' => ['nullable', 'integer', 'between:'.now()->year.','.now()->addYears(3)->year],
            'preferred_contact_method' => ['nullable', Rule::in(['email', 'telephone'])],
            'message' => ['nullable', 'string', 'max:5000'],
            'privacy_notice' => ['accepted'],
        ]);

        $enquiry = AdmissionEnquiry::query()->create([
            'reference_code' => $this->nextAdmissionReference(),
            'guardian_name' => $validated['guardian_name'],
            'telephone' => $validated['telephone'],
            'email' => $validated['email'],
            'intended_level' => $validated['intended_level'] ?? null,
            'intended_term' => $validated['intended_term'] ?? null,
            'intended_year' => $validated['intended_year'] ?? null,
            'preferred_contact_method' => $validated['preferred_contact_method'] ?? null,
            'message' => $validated['message'] ?? null,
            'consent_confirmed' => true,
            'status' => 'new',
            'source_ip_hash' => $request->ip() ? hash('sha256', $request->ip()) : null,
            'user_agent_hash' => $request->userAgent() ? hash('sha256', $request->userAgent()) : null,
        ]);

        $notifier->admission($enquiry);

        return redirect()
            ->route('admissions')
            ->with('status', 'Admission enquiry submitted.');
    }

    private function nextAdmissionReference(): string
    {
        do {
            $reference = 'ADM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (AdmissionEnquiry::query()->where('reference_code', $reference)->exists());

        return $reference;
    }

    public function news(Request $request): View
    {
        $page = $this->publicPage('news');

        $posts = Post::query()
            ->published()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = trim((string) $request->query('q'));
                $query->where(function ($query) use ($term): void {
                    $query->where('title', 'like', '%'.$term.'%')
                        ->orWhere('excerpt', 'like', '%'.$term.'%')
                        ->orWhere('body', 'like', '%'.$term.'%');
                });
            })
            ->with([
                'category',
                'featuredMedia' => fn ($query) => $query->publiclyVisible(),
            ])
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('public.news.index', [
            ...$this->publicSiteData($page),
            'posts' => $posts,
        ]);
    }

    public function newsShow(Post $post): View
    {
        abort_unless(Post::query()->published()->whereKey($post->id)->exists(), 404);

        $post->load([
            'category',
            'featuredMedia' => fn ($query) => $query->publiclyVisible(),
            'tags',
            'articleMediaUsages' => fn ($query) => $query
                ->whereHas('media', fn ($query) => $query->publiclyVisible())
                ->with(['media' => fn ($query) => $query->publiclyVisible()]),
        ]);

        $relatedPosts = Post::query()
            ->published()
            ->whereKeyNot($post->id)
            ->with('category')
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('public.news.show', [
            ...$this->publicChromeData(),
            'page' => $post,
            'blocks' => collect(),
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }

    public function events(): View
    {
        $page = $this->publicPage('events');

        $events = Event::query()
            ->published()
            ->with([
                'category',
                'featuredMedia' => fn ($query) => $query->publiclyVisible(),
            ])
            ->orderBy('starts_at')
            ->paginate(10);

        return view('public.events.index', [
            ...$this->publicSiteData($page),
            'events' => $events,
        ]);
    }

    public function eventShow(Event $event): View
    {
        abort_unless(Event::query()->published()->whereKey($event->id)->exists(), 404);

        $event->load([
            'category',
            'featuredMedia' => fn ($query) => $query->publiclyVisible(),
            'programmeDownload' => fn ($query) => $query
                ->published()
                ->whereHas('media', fn ($query) => $query->publiclyVisible())
                ->with(['media' => fn ($query) => $query->publiclyVisible()]),
        ]);

        return view('public.events.show', [
            ...$this->publicChromeData(),
            'page' => $event,
            'blocks' => collect(),
            'event' => $event,
        ]);
    }

    public function eventCalendar(Event $event): Response
    {
        $isPublished = Event::query()->published()->whereKey($event->id)->exists();
        $canPreview = auth()->user()?->hasAnyRole([
            'super-administrator',
            'school-administrator',
            'content-editor',
            'admissions-officer',
        ]) ?? false;

        abort_unless($isPublished || $canPreview, 404);

        $event->load('category');
        $endsAt = $event->ends_at ?? $event->starts_at->copy()->addHour();
        $description = $this->escapeCalendarValue(strip_tags((string) ($event->summary ?: $event->body)));
        $venue = $this->escapeCalendarValue(collect([$event->venue_name, $event->venue_address])->filter()->implode(', '));
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'scb-school.local';

        $calendar = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//St. Charles Borromeo School//Events//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:event-'.$event->id.'@'.$host,
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$event->starts_at->utc()->format('Ymd\THis\Z'),
            'DTEND:'.$endsAt->utc()->format('Ymd\THis\Z'),
            'SUMMARY:'.$this->escapeCalendarValue($event->title),
            'DESCRIPTION:'.$description,
            'LOCATION:'.$venue,
            'URL:'.route('events.show', ['event' => $event->slug]),
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        return response($calendar, 200, [
            'Content-Disposition' => 'attachment; filename="'.Str::slug($event->title).'.ics"',
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function gallery(): View
    {
        $page = $this->publicPage('gallery');

        $galleries = Gallery::query()
            ->published()
            ->with([
                'category',
                'coverMedia' => fn ($query) => $query->publiclyVisible(),
                'items' => fn ($query) => $query
                    ->whereHas('media', fn ($query) => $query->publiclyVisible())
                    ->with(['media' => fn ($query) => $query->publiclyVisible()])
                    ->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->get()
            ->each(function (Gallery $gallery): void {
                $gallery->setRelation(
                    'items',
                    $gallery->items
                        ->filter(fn ($item): bool => $item->media?->storedFileExists() ?? false)
                        ->values(),
                );
            })
            ->filter(fn (Gallery $gallery): bool => $gallery->items->isNotEmpty())
            ->values();

        return view('public.gallery', [
            ...$this->publicSiteData($page),
            'galleries' => $galleries,
        ]);
    }

    public function downloads(Request $request): View
    {
        $page = $this->publicPage('downloads');

        $downloads = Download::query()
            ->published()
            ->whereHas('media', fn ($query) => $query->publiclyVisible())
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = trim((string) $request->query('q'));
                $query->where(function ($query) use ($term): void {
                    $query->where('title', 'like', '%'.$term.'%')
                        ->orWhere('description', 'like', '%'.$term.'%');
                });
            })
            ->when($request->filled('category'), fn ($query) => $query
                ->whereHas('category', fn ($query) => $query->where('slug', $request->query('category'))))
            ->with([
                'category',
                'media' => fn ($query) => $query->publiclyVisible(),
            ])
            ->orderByDesc('publication_date')
            ->paginate(20)
            ->withQueryString();

        return view('public.downloads', [
            ...$this->publicSiteData($page),
            'downloads' => $downloads,
            'downloadCategories' => DownloadCategory::query()
                ->where('is_active', true)
                ->whereHas('downloads', fn ($query) => $query->published())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function page(Page $page): View
    {
        abort_unless(Page::query()->published()->whereKey($page->id)->exists(), 404);

        $page->load([
            'featuredMedia' => fn ($query) => $query->publiclyVisible(),
            'blocks' => fn ($query) => $query
                ->visible()
                ->with(['media' => fn ($query) => $query->publiclyVisible()]),
        ]);

        return view('public.page', [
            ...$this->publicChromeData(),
            'page' => $page,
            'blocks' => $page->blocks->keyBy('block_type'),
        ]);
    }

    public function contact(): View
    {
        $page = $this->publicPage('contact');

        return view('public.contact', $this->publicSiteData($page));
    }

    public function storeContactMessage(Request $request, PublicEnquiryNotifier $notifier): RedirectResponse
    {
        $validated = $request->validate([
            'website' => ['prohibited'],
            'full_name' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'privacy_notice' => ['accepted'],
        ]);

        $message = ContactMessage::query()->create([
            'reference_code' => $this->nextContactReference(),
            'full_name' => $validated['full_name'],
            'telephone' => $validated['telephone'] ?? null,
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'consent_confirmed' => true,
            'status' => 'new',
            'source_ip_hash' => $request->ip() ? hash('sha256', $request->ip()) : null,
            'user_agent_hash' => $request->userAgent() ? hash('sha256', $request->userAgent()) : null,
        ]);

        $notifier->contact($message);

        return redirect()
            ->route('contact')
            ->with('status', 'Message sent to the school office.');
    }

    public function faq(): View
    {
        $page = $this->publicPage('faq');

        $faqs = Faq::query()
            ->published()
            ->with('category')
            ->orderBy('sort_order')
            ->get();

        return view('public.faq', [
            ...$this->publicSiteData($page),
            'faqs' => $faqs,
        ]);
    }

    public function privacy(): View
    {
        return view('public.privacy', $this->publicSiteData($this->publicPage('privacy')));
    }

    private function nextContactReference(): string
    {
        do {
            $reference = 'CON-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (ContactMessage::query()->where('reference_code', $reference)->exists());

        return $reference;
    }

    private function escapeCalendarValue(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\r", "\n"],
            ['\\\\', '\\;', '\\,', '\\n', '\\n', '\\n'],
            $value,
        );
    }
}
