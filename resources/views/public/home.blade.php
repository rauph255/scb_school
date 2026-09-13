@php
    $hero = $blocks['hero'] ?? null;
    $welcome = $blocks['welcome'] ?? null;
    $programmeHighlights = $blocks['programme_highlights'] ?? null;
    $academicLife = $blocks['academic_life'] ?? null;
    $latestNews = $blocks['latest_news'] ?? null;
    $callToAction = $blocks['call_to_action'] ?? null;

    $mediaUrl = fn ($media) => $media?->publicUrl();
@endphp

@extends('public.layout')

@section('content')
    @if($hero)
        <section class="hero" data-hero-carousel aria-roledescription="carousel" aria-label="School highlights">
            <div class="hero-media" aria-live="off">
                @forelse($heroSlides as $slide)
                    <div class="hero-slide @if($loop->first) is-active @endif" data-carousel-slide aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                        <x-responsive-image :media="$slide" :alt="$slide->alt_text" :eager="$loop->first" sizes="100vw" />
                    </div>
                @empty
                    <div class="hero-slide is-active" data-carousel-slide aria-hidden="false">
                        <x-responsive-image :media="$hero->media ?? $page->featuredMedia" :alt="$hero->media->alt_text ?? $page->featuredMedia->alt_text ?? $page->title" eager sizes="100vw" />
                    </div>
                @endforelse
            </div>
            <div class="container hero-content">
                <div class="hero-badge">{{ $hero->settings['badge'] ?? $settings['identity.school_name'] ?? $hero->heading }}</div>
                <h1>{{ $hero->heading }}</h1>
                <p class="hero-copy">{{ $hero->body }}</p>
                <div class="hero-actions">
                    @if($hero->settings['primary_action'] ?? null)
                        <a class="btn btn-primary" href="{{ route($hero->settings['primary_action']['route']) }}">{{ $hero->settings['primary_action']['label'] }}</a>
                    @endif
                    @if($hero->settings['secondary_action'] ?? null)
                        <a class="btn btn-light" href="{{ route($hero->settings['secondary_action']['route']) }}">{{ $hero->settings['secondary_action']['label'] }}</a>
                    @endif
                </div>
            </div>
            @if($heroSlides->count() > 1)
                <div class="hero-carousel-controls" aria-label="Choose a school highlight">
                    <button class="hero-carousel-arrow" type="button" data-carousel-previous aria-label="Previous image">
                        <i data-lucide="chevron-left" aria-hidden="true"></i>
                    </button>
                    <div class="hero-carousel-dots">
                        @foreach($heroSlides as $slide)
                            <button
                                type="button"
                                data-carousel-dot="{{ $loop->index }}"
                                @class(['is-active' => $loop->first])
                                aria-label="Show image {{ $loop->iteration }}"
                                aria-current="{{ $loop->first ? 'true' : 'false' }}"
                            ></button>
                        @endforeach
                    </div>
                    <button class="hero-carousel-arrow" type="button" data-carousel-next aria-label="Next image">
                        <i data-lucide="chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            @endif
        </section>
        <div class="hero-stats">
            <div class="container hero-stats-grid">
                @foreach(($hero->settings['stats'] ?? []) as $stat)
                    <div class="hero-stat">
                        <b>{{ $stat['label'] }}</b>
                        <span>{{ $stat['description'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($welcome)
        <section class="section">
            <div class="container grid-2">
                <div>
                    <div class="eyebrow">{{ $welcome->settings['eyebrow'] ?? 'Welcome' }}</div>
                    <h2>{{ $welcome->heading }}</h2>
                    <p class="lead">{{ $welcome->body }}</p>
                    @if($welcome->settings['body_secondary'] ?? null)
                        <p>{{ $welcome->settings['body_secondary'] }}</p>
                    @endif
                    @if($welcome->settings['action'] ?? null)
                        <a class="btn btn-secondary" href="{{ route($welcome->settings['action']['route']) }}">{{ $welcome->settings['action']['label'] }}</a>
                    @endif
                </div>
                <div class="image-frame">
                    <x-responsive-image :media="$welcome->media" :alt="$welcome->media->alt_text ?? $welcome->heading" sizes="(max-width: 800px) 100vw, 50vw" />
                </div>
            </div>
        </section>
    @endif

    @if($programmeHighlights)
        <section class="section bg-cream">
            <div class="container">
                <div class="section-head">
                    <div>
                        <div class="eyebrow">{{ $programmeHighlights->settings['eyebrow'] ?? 'Learning' }}</div>
                        <h2>{{ $programmeHighlights->heading }}</h2>
                    </div>
                    @if($programmeHighlights->settings['action'] ?? null)
                        <a class="text-link" href="{{ route($programmeHighlights->settings['action']['route']) }}">{{ $programmeHighlights->settings['action']['label'] }} →</a>
                    @endif
                </div>
                <div class="grid-4">
                    @foreach(($programmeHighlights->settings['cards'] ?? []) as $card)
                        <div class="feature-card">
                            <div class="feature-icon">{{ $card['icon'] }}</div>
                            <h3>{{ $card['title'] }}</h3>
                            <p class="muted">{{ $card['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($academicLife)
        <section class="section">
            <div class="container">
                <div class="split-feature">
                    <x-responsive-image :media="$academicLife->media" :alt="$academicLife->media->alt_text ?? $academicLife->heading" sizes="(max-width: 800px) 100vw, 50vw" />
                    <div class="split-copy">
                        <div class="eyebrow" style="color:var(--gold)">{{ $academicLife->settings['eyebrow'] ?? 'Academic life' }}</div>
                        <h2>{{ $academicLife->heading }}</h2>
                        <p class="lead">{{ $academicLife->body }}</p>
                        @if($academicLife->settings['action'] ?? null)
                            <a class="btn btn-primary" href="{{ route($academicLife->settings['action']['route']) }}">{{ $academicLife->settings['action']['label'] }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="section bg-cream">
        <div class="container">
            <div class="section-head">
                <div>
                    <div class="eyebrow">{{ $latestNews->settings['eyebrow'] ?? 'School life' }}</div>
                    <h2>{{ $latestNews->body ?? 'Latest stories and upcoming moments.' }}</h2>
                </div>
                @if($latestNews?->settings['action'] ?? null)
                    <a class="text-link" href="{{ route($latestNews->settings['action']['route']) }}">{{ $latestNews->settings['action']['label'] }} →</a>
                @endif
            </div>
            <div class="grid-3">
                @foreach($latestPosts as $post)
                    <article class="card news-card">
                        <div class="card-media">
                            <x-responsive-image :media="$post->featuredMedia" :alt="$post->featuredMedia->alt_text ?? $post->title" sizes="(max-width: 800px) 100vw, 34vw" />
                        </div>
                        <div class="card-body">
                            <div class="meta">
                                @if($post->category)
                                    <span class="tag">{{ $post->category->name }}</span>
                                @endif
                                <span>{{ $post->published_at->format('j F Y') }}</span>
                            </div>
                            <h3>{{ $post->title }}</h3>
                            <p class="muted">{{ $post->excerpt }}</p>
                            <a class="text-link" href="{{ route('news.show', ['post' => $post->slug]) }}">Read story →</a>
                        </div>
                    </article>
                @endforeach
                @if($latestPosts->isEmpty())
                    <div class="empty-state">
                        <b>School stories are being prepared.</b>
                        <p class="small muted">Published news will appear here.</p>
                    </div>
                @endif
                <div>
                    <div class="event-list">
                        @forelse($upcomingEvents as $event)
                            <div class="event-row">
                                <div class="date-box">
                                    <b>{{ $event->starts_at->format('M') }}</b>
                                    <strong>{{ $event->starts_at->format('d') }}</strong>
                                </div>
                                <div>
                                    <h3>{{ $event->title }}</h3>
                                    <div class="meta">{{ $event->venue_name }} · {{ $event->starts_at->format('g:i A') }}</div>
                                </div>
                                <a class="btn btn-outline btn-sm" href="{{ route('events.show', ['event' => $event->slug]) }}">Details</a>
                            </div>
                        @empty
                            <div class="empty-state">
                                <b>No upcoming dates are published.</b>
                                <p class="small muted">Confirmed events will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($callToAction)
        <section class="section bg-plum">
            <div class="container grid-2">
                <div>
                    <div class="eyebrow" style="color:var(--gold)">{{ $callToAction->settings['eyebrow'] ?? 'Admissions' }}</div>
                    <h2>{{ $callToAction->heading }}</h2>
                    <p class="lead">{{ $callToAction->body }}</p>
                </div>
                <div style="display:flex;gap:14px;justify-content:flex-end;flex-wrap:wrap">
                    @if($callToAction->settings['primary_action'] ?? null)
                        <a class="btn btn-primary" href="{{ route($callToAction->settings['primary_action']['route']) }}">{{ $callToAction->settings['primary_action']['label'] }}</a>
                    @endif
                    @if($callToAction->settings['secondary_action'] ?? null)
                        <a class="btn btn-light" href="{{ route($callToAction->settings['secondary_action']['route']) }}">{{ $callToAction->settings['secondary_action']['label'] }}</a>
                    @endif
                </div>
            </div>
        </section>
    @endif
@endsection
