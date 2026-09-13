@php
    $identity = $blocks['identity'] ?? null;
    $values = $blocks['values'] ?? null;
    $catholicCharacter = $blocks['catholic_character'] ?? null;
    $leadership = $blocks['leadership_staff'] ?? null;

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

    @if($identity)
        <section class="section">
            <div class="container grid-2">
                <div>
                    <div class="eyebrow">{{ $identity->settings['eyebrow'] ?? 'Our identity' }}</div>
                    <h2>{{ $identity->heading }}</h2>
                    <p class="lead">{{ $identity->body }}</p>
                    @if($identity->settings['body_secondary'] ?? null)
                        <p>{{ $identity->settings['body_secondary'] }}</p>
                    @endif
                    @if($identity->settings['quote'] ?? null)
                        <div class="quote">“{{ $identity->settings['quote'] }}”</div>
                    @endif
                </div>
                <div class="image-frame">
                    <x-responsive-image :media="$identity->media" :alt="$identity->media->alt_text ?? $identity->heading" sizes="(max-width: 800px) 100vw, 50vw" />
                </div>
            </div>
        </section>
    @endif

    @if($values)
        <section class="section bg-cream">
            <div class="container">
                <div class="values-strip">
                    @foreach(($values->settings['items'] ?? []) as $item)
                        <div class="value">
                            <div class="feature-icon" style="margin:0 auto 14px">{{ $item['icon'] }}</div>
                            <b>{{ $item['label'] }}</b>
                            <span>{{ $item['description'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($catholicCharacter)
        <section class="section">
            <div class="container grid-2">
                <div class="image-frame">
                    <x-responsive-image :media="$catholicCharacter->media" :alt="$catholicCharacter->media->alt_text ?? $catholicCharacter->heading" sizes="(max-width: 800px) 100vw, 50vw" />
                </div>
                <div>
                    <div class="eyebrow">{{ $catholicCharacter->settings['eyebrow'] ?? 'Catholic character' }}</div>
                    <h2>{{ $catholicCharacter->heading }}</h2>
                    <p class="lead">{{ $catholicCharacter->body }}</p>
                    @if($catholicCharacter->settings['body_secondary'] ?? null)
                        <p>{{ $catholicCharacter->settings['body_secondary'] }}</p>
                    @endif
                    @if($catholicCharacter->settings['action'] ?? null)
                        <a href="{{ route($catholicCharacter->settings['action']['route']) }}" class="btn btn-secondary">{{ $catholicCharacter->settings['action']['label'] }}</a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <section class="section bg-cream">
        <div class="container">
            <div class="section-head">
                <div>
                    <div class="eyebrow">{{ $leadership->settings['eyebrow'] ?? 'Leadership and staff' }}</div>
                    <h2>{{ $leadership->heading ?? 'People who guide learning with clarity and care.' }}</h2>
                </div>
            </div>
            <div class="grid-3">
                @foreach($staffMembers as $member)
                    <div class="card">
                        <div class="card-media">
                            <x-responsive-image :media="$member->photo" :alt="$member->photo->alt_text ?? $member->name" sizes="(max-width: 800px) 100vw, 33vw" />
                        </div>
                        <div class="card-body">
                            <h3>{{ $member->name }}</h3>
                            <p class="muted">{{ $member->job_title }}@if($member->department) · {{ $member->department->name }}@endif</p>
                            <p class="muted">{{ $member->approved_biography }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
