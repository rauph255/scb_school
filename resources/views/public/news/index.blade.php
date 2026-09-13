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
                    <div class="eyebrow">Latest updates</div>
                    <h2>News that keeps families connected.</h2>
                </div>
                <form class="search-box" method="GET" action="{{ route('news.index') }}">
                    <input name="q" value="{{ request('q') }}" placeholder="Search news">
                    <button type="submit" aria-label="Search news">⌕</button>
                </form>
            </div>
            <div class="grid-3">
                @forelse($posts as $post)
                    <article class="card news-card">
                        <div class="card-media">
                            <x-responsive-image :media="$post->featuredMedia" :alt="$post->featuredMedia->alt_text ?? $post->title" sizes="(max-width: 800px) 100vw, 33vw" />
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
                @empty
                    <div class="empty-state">No published news matches your search.</div>
                @endforelse
            </div>
            @include('public.partials.pagination', ['paginator' => $posts])
        </div>
    </section>
@endsection
