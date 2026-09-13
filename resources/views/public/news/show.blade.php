@php
    $mediaUrl = fn ($media) => $media?->publicUrl();
@endphp

@extends('public.layout')

@push('structured-data')
<script type="application/ld+json">{!! json_encode(array_filter([
    '@'.'context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $post->title,
    'description' => $post->excerpt,
    'datePublished' => $post->published_at?->toAtomString(),
    'dateModified' => $post->updated_at?->toAtomString(),
    'mainEntityOfPage' => route('news.show', $post),
    'image' => $mediaUrl($post->featuredMedia) ? url($mediaUrl($post->featuredMedia)) : null,
    'author' => $post->author ? ['@type' => 'Person', 'name' => $post->author->name] : null,
], fn ($value) => $value !== null && $value !== ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('content')
    <section class="page-hero">
        <x-responsive-image :media="$post->featuredMedia" :alt="$post->featuredMedia->alt_text ?? $post->title" :eager="true" />
        <div class="container">
            <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>›</span><a href="{{ route('news.index') }}">News</a><span>›</span><span>{{ $post->title }}</span></div>
            <h1>{{ $post->title }}</h1>
            <p class="hero-copy">{{ $post->category?->name ?? 'School news' }} · {{ $post->published_at->format('j F Y') }}</p>
        </div>
    </section>

    <section class="section">
        <div class="container article-layout">
            <article class="article-body">
                <div class="meta">
                    @if($post->category)
                        <span class="tag">{{ $post->category->name }}</span>
                    @endif
                    <span>{{ $post->published_at->format('j F Y') }}</span>
                    @foreach($post->tags as $tag)
                        <span>{{ $tag->name }}</span>
                    @endforeach
                </div>
                <p class="lead">{{ $post->excerpt }}</p>
                @if($post->featuredMedia)
                    <x-responsive-image :media="$post->featuredMedia" :alt="$post->featuredMedia->alt_text ?? $post->title" sizes="(max-width: 900px) 100vw, 760px" />
                @endif
                <h2>Story</h2>
                @foreach(preg_split('/\R{2,}/', trim($post->body)) ?: [] as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
                @if($post->articleMediaUsages->isNotEmpty())
                    <section class="article-gallery" aria-labelledby="article-gallery-title">
                        <div class="article-gallery__heading">
                            <div>
                                <div class="eyebrow">Graduation in pictures</div>
                                <h2 id="article-gallery-title">Moments from the celebration</h2>
                            </div>
                            <a class="text-link" href="{{ route('gallery') }}">View gallery →</a>
                        </div>
                        <div class="article-gallery__grid">
                            @foreach($post->articleMediaUsages as $usage)
                                <figure>
                                    <x-responsive-image :media="$usage->media" :alt="$usage->media->alt_text" sizes="(max-width: 700px) 100vw, 380px" />
                                    @if($usage->media->caption)
                                        <figcaption>{{ $usage->media->caption }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    </section>
                @endif
            </article>
            <aside>
                <div class="sidebar-card">
                    <h3>Story details</h3>
                    <p class="small muted">Category: {{ $post->category?->name ?? 'Uncategorized' }}<br>Published: {{ $post->published_at->format('j F Y') }}</p>
                </div>
                @if($relatedPosts->isNotEmpty())
                    <div class="sidebar-card">
                        <h3>Related stories</h3>
                        <div class="footer-links">
                            @foreach($relatedPosts as $related)
                                <a href="{{ route('news.show', ['post' => $related->slug]) }}">{{ $related->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </section>
@endsection
