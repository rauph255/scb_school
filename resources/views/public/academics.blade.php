@php
    $learningJourney = $blocks['learning_journey'] ?? null;
    $prePrimary = $blocks['pre_primary'] ?? null;
    $primarySchool = $blocks['primary_school'] ?? null;
    $beyondLessons = $blocks['beyond_lessons'] ?? null;
    $programmeList = $blocks['programme_list'] ?? null;

    $mediaUrl = fn ($media) => $media?->publicUrl();
@endphp

@extends('public.layout')

@section('content')
    <section class="page-hero">
        <x-responsive-image :media="$page->featuredMedia" :alt="$page->featuredMedia->alt_text ?? $page->title" :eager="true" />
        <div class="container">
            <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>›</span><span>{{ $page->title }}</span></div>
            <h1>{{ $page->title }}</h1>
            <p class="hero-copy">{{ $page->excerpt }}</p>
        </div>
    </section>

    @if($learningJourney)
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div>
                        <div class="eyebrow">{{ $learningJourney->settings['eyebrow'] ?? 'Learning journey' }}</div>
                        <h2>{{ $learningJourney->heading }}</h2>
                        <p class="lead">{{ $learningJourney->body }}</p>
                    </div>
                </div>
                <div class="grid-3">
                    @foreach(($learningJourney->settings['cards'] ?? []) as $card)
                        <div class="feature-card">
                            <div class="feature-icon">{{ $card['number'] }}</div>
                            <h3>{{ $card['title'] }}</h3>
                            <p class="muted">{{ $card['description'] }}</p>
                            <a class="text-link" href="#{{ $card['target'] }}">Explore →</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($prePrimary)
        <section class="section bg-cream" id="preprimary">
            <div class="container grid-2">
                <div>
                    <div class="eyebrow">{{ $prePrimary->settings['eyebrow'] ?? 'Pre-primary' }}</div>
                    <h2>{{ $prePrimary->heading }}</h2>
                    <p class="lead">{{ $prePrimary->body }}</p>
                    <div class="grid-2" style="gap:14px;align-items:stretch">
                        @foreach(($prePrimary->settings['items'] ?? []) as $item)
                            <div class="feature-card">
                                <h3>{{ $item['label'] }}</h3>
                                <p class="muted">{{ $item['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="image-frame">
                    <x-responsive-image :media="$prePrimary->media" :alt="$prePrimary->media->alt_text ?? $prePrimary->heading" sizes="(max-width: 800px) 100vw, 50vw" />
                </div>
            </div>
        </section>
    @endif

    @if($primarySchool)
        <section class="section" id="primary">
            <div class="container grid-2">
                <div class="image-frame">
                    <x-responsive-image :media="$primarySchool->media" :alt="$primarySchool->media->alt_text ?? $primarySchool->heading" sizes="(max-width: 800px) 100vw, 50vw" />
                </div>
                <div>
                    <div class="eyebrow">{{ $primarySchool->settings['eyebrow'] ?? 'Primary school' }}</div>
                    <h2>{{ $primarySchool->heading }}</h2>
                    <p class="lead">{{ $primarySchool->body }}</p>
                    <div class="values-strip" style="grid-template-columns:1fr 1fr">
                        @foreach(($primarySchool->settings['items'] ?? []) as $item)
                            <div class="value">
                                <b>{{ $item['label'] }}</b>
                                <span>{{ $item['description'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($programmeList)
        <section class="section bg-cream">
            <div class="container">
                <div class="section-head">
                    <div>
                        <div class="eyebrow">Programmes</div>
                        <h2>{{ $programmeList->heading }}</h2>
                        <p class="lead">{{ $programmeList->body }}</p>
                    </div>
                </div>
                <div class="grid-3">
                    @foreach($programmes as $programme)
                        <article class="card news-card" id="programme-{{ $programme->slug }}">
                            <div class="card-media">
                                <x-responsive-image :media="$programme->featuredMedia" :alt="$programme->featuredMedia->alt_text ?? $programme->name" sizes="(max-width: 800px) 100vw, 33vw" />
                            </div>
                            <div class="card-body">
                                <div class="meta">
                                    <span class="tag">{{ $programme->programme_type }}</span>
                                    @if($programme->department)
                                        <span>{{ $programme->department->name }}</span>
                                    @endif
                                </div>
                                <h3>{{ $programme->name }}</h3>
                                <p class="muted">{{ $programme->summary }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($beyondLessons)
        <section class="section bg-plum">
            <div class="container grid-2">
                <div>
                    <div class="eyebrow" style="color:var(--gold)">{{ $beyondLessons->settings['eyebrow'] ?? 'Beyond lessons' }}</div>
                    <h2>{{ $beyondLessons->heading }}</h2>
                    <p class="lead">{{ $beyondLessons->body }}</p>
                </div>
                <div class="grid-2" style="gap:14px">
                    @foreach(($beyondLessons->settings['items'] ?? []) as $item)
                        <div class="feature-card" style="background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.15)">
                            <h3 style="color:#fff">{{ $item['label'] }}</h3>
                            <p style="color:rgba(255,255,255,.72)">{{ $item['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
