@php
    $siteName = $settings['identity.school_name'] ?? 'St. Charles Borromeo Pre & Primary School';
    $recordTitle = $page->title ?? $page->name ?? $siteName;
    $seoTitle = $page->seo_title ?: ($recordTitle === $siteName ? $siteName : $recordTitle.' | '.$siteName);
    $seoDescription = $page->seo_description ?: ($page->excerpt ?? $page->summary ?? $settings['seo.default_description'] ?? '');
    $canonicalCandidate = $page->canonical_url ?? null;
    $canonicalUrl = is_string($canonicalCandidate) && filter_var($canonicalCandidate, FILTER_VALIDATE_URL)
        ? $canonicalCandidate
        : url()->current();
    $robotsIndex = ! array_key_exists('robots_index', $page->getAttributes()) || (bool) $page->robots_index;
    $robotsFollow = ! array_key_exists('robots_follow', $page->getAttributes()) || (bool) $page->robots_follow;
    $socialTitle = $page->og_title ?: $seoTitle;
    $socialDescription = $page->og_description ?: $seoDescription;
    $socialImagePath = $page->featuredMedia?->publicUrl();
    $socialImage = $socialImagePath ? url($socialImagePath) : null;
    $socialType = $page instanceof App\Models\Post ? 'article' : 'website';
    $analyticsEnabled = filter_var($settings['analytics.enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $analyticsProvider = $settings['analytics.provider'] ?? 'none';
    $analyticsSiteId = trim((string) ($settings['analytics.site_id'] ?? ''));
    $schoolSchema = array_filter([
        '@'.'context' => 'https://schema.org',
        '@type' => 'School',
        'name' => $siteName,
        'url' => route('home'),
        'logo' => $logo,
        'description' => $settings['seo.default_description'] ?? null,
        'email' => $publicContact['email'] ?? null,
        'telephone' => $publicContact['telephone'] ?? null,
        'address' => $publicContact['address'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
    $websiteSchema = [
        '@'.'context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => route('home'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => route('search').'?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ $robotsIndex ? 'index' : 'noindex' }},{{ $robotsFollow ? 'follow' : 'nofollow' }}">
    <meta name="theme-color" content="#3D2D3F">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $socialType }}">
    <meta property="og:title" content="{{ $socialTitle }}">
    <meta property="og:description" content="{{ $socialDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if($socialImage)<meta property="og:image" content="{{ $socialImage }}">@endif
    <meta name="twitter:card" content="{{ $socialImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $socialTitle }}">
    <meta name="twitter:description" content="{{ $socialDescription }}">
    <link rel="stylesheet" href="{{ asset('assets/scb/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/scb-experience.css') }}">
    <script type="application/ld+json">{!! json_encode($schoolSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    <script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @stack('structured-data')
</head>
<body>
<a class="skip-link" href="#main-content">Skip to main content</a>
@include('partials.site-loader', ['loaderLabel' => 'Preparing the school website'])
@php
    $menuHref = function ($item) {
        if ($item->route_name && Route::has($item->route_name)) {
            if ($item->route_name === 'pages.show' && $item->page) {
                return route('pages.show', ['page' => $item->page->slug]);
            }

            return route($item->route_name);
        }

        if ($item->page) {
            return route('pages.show', ['page' => $item->page->slug]);
        }

        return $item->url ?: route('home');
    };
@endphp
<div class="topbar">
    <div class="container">
        <div>{{ $settings['identity.motto'] ?? 'Learning with purpose' }}</div>
        <div class="topbar-links">
            <a href="{{ route('contact') }}">School office</a>
            <a href="{{ route('downloads') }}">School downloads</a>
            <a href="{{ route('staff.login') }}">Staff portal</a>
        </div>
    </div>
</div>
<header class="site-header">
    <div class="container nav-row">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ $logo }}" alt="St. Charles Borromeo School crest" width="64" height="64">
            <div>
                <strong>{{ Str::upper($settings['identity.school_name'] ?? 'St. Charles Borromeo Pre & Primary School') }}</strong>
                <span>{{ $settings['identity.motto'] ?? 'Learning with purpose' }}</span>
            </div>
        </a>
        <button class="nav-toggle" type="button" aria-label="Open menu" aria-controls="primary-navigation">☰</button>
        <nav class="main-nav" id="primary-navigation" aria-label="Primary navigation">
            @foreach(($menus['primary']->items ?? collect()) as $item)
                <a href="{{ $menuHref($item) }}" target="{{ $item->target }}" @if($item->target === '_blank') rel="noopener noreferrer" @endif @class(['active' => $item->route_name && (request()->routeIs($item->route_name) || request()->routeIs(Str::before($item->route_name, '.').'.*'))])>{{ $item->label }}</a>
            @endforeach
            <a href="{{ route('search') }}" aria-label="Search the website"><i data-lucide="search" aria-hidden="true"></i><span class="nav-search-label">Search</span></a>
            <a href="{{ route('admissions') }}" class="btn btn-primary btn-sm nav-apply">
                <span>Apply now</span>
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
        </nav>
    </div>
</header>

@php
    $currentNewsPost = request()->route('post');
    $showLatestNewsAlert = $latestNewsAlert
        && (! $currentNewsPost instanceof App\Models\Post || $currentNewsPost->getKey() !== $latestNewsAlert->getKey());
@endphp
@if($showLatestNewsAlert)
    <aside class="recent-news-alert" data-recent-news-alert data-alert-id="news-{{ $latestNewsAlert->id }}" aria-label="Latest school news">
        <div class="container recent-news-alert__inner">
            <div class="recent-news-alert__message">
                <span class="recent-news-alert__badge">New</span>
                <div>
                    <b>{{ $latestNewsAlert->title }}</b>
                    <span>Published {{ $latestNewsAlert->published_at->diffForHumans() }}</span>
                </div>
            </div>
            <div class="recent-news-alert__actions">
                <a href="{{ route('news.show', ['post' => $latestNewsAlert->slug]) }}">Read the story <span aria-hidden="true">→</span></a>
                <button type="button" data-news-alert-dismiss aria-label="Dismiss latest news alert">×</button>
            </div>
        </div>
    </aside>
@endif

<main id="main-content" tabindex="-1">@yield('content')</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">
                    <img src="{{ $logo }}" alt="School crest" width="64" height="64" loading="lazy" decoding="async">
                    <div>
                        <h3>{{ $settings['identity.school_name'] ?? 'St. Charles Borromeo' }}</h3>
                        <div>{{ $settings['identity.motto'] ?? 'Learning with purpose' }}</div>
                    </div>
                </div>
                <p>{{ $settings['seo.default_description'] ?? 'Learning, school life and admissions information from St. Charles Borromeo.' }}</p>
            </div>
            <div>
                <h3>Explore</h3>
                <div class="footer-links">
                    @foreach(($menus['footer-school']->items ?? collect()) as $item)
                        <a href="{{ $menuHref($item) }}" target="{{ $item->target }}" @if($item->target === '_blank') rel="noopener noreferrer" @endif>{{ $item->label }}</a>
                    @endforeach
                </div>
            </div>
            <div>
                <h3>Resources</h3>
                <div class="footer-links">
                    @foreach(($menus['footer-resources']->items ?? collect()) as $item)
                        <a href="{{ $menuHref($item) }}" target="{{ $item->target }}" @if($item->target === '_blank') rel="noopener noreferrer" @endif>{{ $item->label }}</a>
                    @endforeach
                </div>
            </div>
            <div>
                <h3>Contact</h3>
                <div class="footer-links">
                    @if($publicContact['address'])
                        <span>{{ $publicContact['address'] }}</span>
                    @endif
                    @if($publicContact['telephone'])
                        <a href="tel:{{ preg_replace('/\s+/', '', $publicContact['telephone']) }}">{{ $publicContact['telephone'] }}</a>
                    @endif
                    @if($publicContact['email'])
                        <a href="mailto:{{ $publicContact['email'] }}">{{ $publicContact['email'] }}</a>
                    @endif
                    @if(! array_filter($publicContact))
                        <a href="{{ route('contact') }}">Send an enquiry</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© {{ now()->year }} {{ $settings['identity.school_name'] ?? 'St. Charles Borromeo Pre & Primary School' }}.</span>
            <span>
                @foreach(($menus['footer-legal']->items ?? collect()) as $item)
                    <a href="{{ $menuHref($item) }}" target="{{ $item->target }}" @if($item->target === '_blank') rel="noopener noreferrer" @endif>{{ $item->label }}</a>@if(! $loop->last) · @endif
                @endforeach
            </span>
            <x-site-credit />
            <button class="footer-preferences" type="button" data-cookie-reset>Cookie preferences</button>
        </div>
    </div>
</footer>
<div class="cookie" role="dialog" aria-label="Cookie preferences" aria-hidden="true">
    <b>Respectful analytics</b>
    <p class="small muted">Essential cookies keep forms and secure sessions working. Optional analytics remain off unless accepted.</p>
    <div class="cookie-actions">
        <button class="btn btn-secondary btn-sm" type="button" data-cookie="accepted">Accept</button>
        <button class="btn btn-outline btn-sm" type="button" data-cookie="essential">Essential only</button>
    </div>
</div>
@if($analyticsEnabled && $analyticsProvider === 'plausible' && $analyticsSiteId !== '')
    <script id="analytics-config" type="application/json">{!! json_encode(['provider' => 'plausible', 'siteId' => $analyticsSiteId], JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endif
<script src="{{ asset('assets/scb/js/app.js') }}"></script>
<script src="{{ asset('assets/js/lucide.min.js') }}"></script>
<script src="{{ asset('assets/js/scb-experience.js') }}"></script>
</body>
</html>
