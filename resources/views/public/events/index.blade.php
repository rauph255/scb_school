@php
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

    <section class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <div class="eyebrow">School calendar</div>
                    <h2>Plan ahead with confidence.</h2>
                </div>
            </div>
            <div class="event-list">
                @forelse($events as $event)
                    <div class="event-row">
                        <div class="date-box">
                            <b>{{ $event->starts_at->format('M') }}</b>
                            <strong>{{ $event->starts_at->format('d') }}</strong>
                        </div>
                        <div>
                            @if($event->category)
                                <div class="tag">{{ $event->category->name }}</div>
                            @endif
                            <h3>{{ $event->title }}</h3>
                            <div class="meta">{{ $event->starts_at->format('g:i A') }}@if($event->ends_at)-{{ $event->ends_at->format('g:i A') }}@endif · {{ $event->venue_name }}</div>
                            <p class="muted small">{{ $event->summary }}</p>
                        </div>
                        <a class="btn btn-outline btn-sm" href="{{ route('events.show', ['event' => $event->slug]) }}">View event</a>
                    </div>
                @empty
                    <div class="empty-state">No published events are available yet.</div>
                @endforelse
            </div>
            @include('public.partials.pagination', ['paginator' => $events])
        </div>
    </section>

    <section class="section bg-cream">
        <div class="container grid-2">
            <div class="image-frame">
                <x-responsive-image :media="$page->featuredMedia" :alt="$page->featuredMedia->alt_text ?? $page->title" sizes="(max-width: 800px) 100vw, 50vw" />
            </div>
            <div>
                <div class="eyebrow">Stay organised</div>
                <h2>Download the term calendar.</h2>
                <p class="lead">Calendars and school resources are published through the downloads area.</p>
                <a href="{{ route('downloads') }}" class="btn btn-secondary">Open downloads</a>
            </div>
        </div>
    </section>
@endsection
