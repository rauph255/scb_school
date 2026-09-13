@php
    $policy = $blocks['privacy_statement'] ?? null;
    $mediaUrl = fn ($media) => $media?->publicUrl();
@endphp

@extends('public.layout')

@section('content')
    <section class="page-hero">
        @if($mediaUrl($policy?->media))
            <x-responsive-image :media="$policy?->media" :alt="$policy?->media?->alt_text ?? $page->title" :eager="true" />
        @endif
        <div class="container">
            <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>›</span><span>{{ $page->title }}</span></div>
            <h1>Privacy & safeguarding</h1>
            <p class="hero-copy">{{ $page->excerpt }}</p>
        </div>
    </section>

    <section class="section">
        <div class="container article-layout">
            <article class="article-body">
                <p class="lead">{{ $policy->body ?? $page->excerpt }}</p>
                @foreach(($policy->settings['sections'] ?? []) as $section)
                    <h2 id="{{ Str::slug($section['heading']) }}">{{ $section['heading'] }}</h2>
                    <p>{{ $section['body'] }}</p>
                @endforeach
            </article>
            <aside>
                <div class="sidebar-card">
                    <h3>Policy sections</h3>
                    <div class="footer-links">
                        @foreach(($policy->settings['links'] ?? []) as $link)
                            <a href="#{{ Str::slug($link) }}">{{ $link }}</a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection
