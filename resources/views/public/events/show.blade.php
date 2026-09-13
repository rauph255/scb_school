@php
    $mediaUrl = fn ($media) => $media?->publicUrl();
@endphp

@extends('public.layout')

@push('structured-data')
<script type="application/ld+json">{!! json_encode(array_filter([
    '@'.'context' => 'https://schema.org',
    '@type' => 'Event',
    'name' => $event->title,
    'description' => $event->summary,
    'startDate' => $event->starts_at?->toAtomString(),
    'endDate' => $event->ends_at?->toAtomString(),
    'eventStatus' => $event->event_state === 'cancelled' ? 'https://schema.org/EventCancelled' : 'https://schema.org/EventScheduled',
    'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
    'url' => route('events.show', $event),
    'image' => $mediaUrl($event->featuredMedia) ? url($mediaUrl($event->featuredMedia)) : null,
    'location' => $event->venue_name ? array_filter([
        '@type' => 'Place',
        'name' => $event->venue_name,
        'address' => $event->venue_address,
    ], fn ($value) => $value !== null && $value !== '') : null,
], fn ($value) => $value !== null && $value !== ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('content')
    <section class="page-hero">
        <x-responsive-image :media="$event->featuredMedia" :alt="$event->featuredMedia->alt_text ?? $event->title" :eager="true" />
        <div class="container">
            <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>›</span><a href="{{ route('events.index') }}">Events</a><span>›</span><span>{{ $event->title }}</span></div>
            <h1>{{ $event->title }}</h1>
            <p class="hero-copy">{{ $event->starts_at->format('j F Y') }} · {{ $event->starts_at->format('g:i A') }} · {{ $event->venue_name }}</p>
        </div>
    </section>

    <section class="section">
        <div class="container article-layout">
            <article class="article-body">
                <p class="lead">{{ $event->summary }}</p>
                <h2>Event details</h2>
                <p>{{ $event->body }}</p>
                @if($event->featuredMedia)
                    <x-responsive-image :media="$event->featuredMedia" :alt="$event->featuredMedia->alt_text ?? $event->title" sizes="(max-width: 900px) 100vw, 760px" />
                @endif
                <div class="notice">
                    <b>{{ $event->starts_at->format('l, j F Y') }}</b><br>
                    <span class="small">{{ $event->starts_at->format('g:i A') }}@if($event->ends_at)-{{ $event->ends_at->format('g:i A') }}@endif at {{ $event->venue_name }}</span>
                </div>
            </article>
            <aside>
                <div class="sidebar-card">
                    <h3>Event information</h3>
                    <p class="small">
                        <b>Date</b><br>{{ $event->starts_at->format('j F Y') }}<br><br>
                        <b>Time</b><br>{{ $event->starts_at->format('g:i A') }}@if($event->ends_at)-{{ $event->ends_at->format('g:i A') }}@endif<br><br>
                        <b>Venue</b><br>{{ $event->venue_name }}
                    </p>
                    <a class="btn btn-outline" style="width:100%;margin-bottom:10px" href="{{ route('events.calendar', ['event' => $event->slug]) }}" download>Add to calendar</a>
                    @if($event->programmeDownload)
                        <a class="btn btn-primary" style="width:100%" href="{{ route('downloads.show', $event->programmeDownload) }}" download>Download programme</a>
                    @endif
                </div>
                <div class="sidebar-card">
                    <h3>Category</h3>
                    <p class="small muted">{{ $event->category?->name ?? 'School event' }}</p>
                </div>
            </aside>
        </div>
    </section>
@endsection
