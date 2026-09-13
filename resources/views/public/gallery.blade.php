@php
    $galleryCategories = $galleries
        ->pluck('category')
        ->filter()
        ->unique('id')
        ->sortBy('name')
        ->values();
    $galleryItemCount = $galleries->sum(fn ($gallery) => $gallery->items->count());
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
                    <div class="eyebrow">School in pictures</div>
                    <h2>Real moments, carefully presented.</h2>
                    <p class="lead">Albums are governed by the school’s child-safeguarding and photo-consent process.</p>
                    @if($galleries->isNotEmpty())
                        <p class="muted">{{ $galleries->pluck('title')->join(' · ') }}</p>
                    @endif
                </div>
            </div>
            @if($galleryItemCount > 0)
                <div class="gallery-controls" aria-label="Gallery filters">
                    <div class="filter-bar" role="group" aria-label="Filter photographs by album">
                        <button class="filter active" type="button" data-gallery-filter="all" aria-pressed="true">All photos</button>
                        @foreach($galleries as $gallery)
                            <button class="filter" type="button" data-gallery-filter="{{ $gallery->slug }}" aria-pressed="false">{{ $gallery->title }}</button>
                        @endforeach
                    </div>
                    @if($galleryCategories->count() > 1)
                        <label class="gallery-category-filter" for="gallery-category">
                            <span>Category</span>
                            <select id="gallery-category" data-gallery-category>
                                <option value="all">All categories</option>
                                @foreach($galleryCategories as $category)
                                    <option value="{{ $category->slug }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <p class="gallery-result-count small muted" data-gallery-count aria-live="polite">{{ trans_choice(':count photograph|:count photographs', $galleryItemCount, ['count' => $galleryItemCount]) }}</p>
                </div>
            @endif

            <div class="gallery-grid" data-gallery-grid>
                @foreach($galleries as $gallery)
                    @foreach($gallery->items as $item)
                        @php($caption = $item->caption ?: $item->media?->caption ?: $gallery->title)
                        <figure
                            class="gallery-item"
                            data-gallery-item
                            data-album="{{ $gallery->slug }}"
                            data-category="{{ $gallery->category?->slug ?? 'school-life' }}"
                            data-label="{{ $caption }}"
                            data-full="{{ $item->media?->publicUrl() }}"
                        >
                            <button class="gallery-item__button" type="button" data-gallery-open aria-label="Open photograph: {{ $caption }}">
                                <x-responsive-image :media="$item->media" :alt="$item->media->alt_text ?? $caption" sizes="(max-width: 640px) 100vw, (max-width: 900px) 50vw, 34vw" />
                                <span class="gallery-item__overlay">
                                    <strong>{{ $caption }}</strong>
                                    <span>{{ $gallery->title }}</span>
                                </span>
                            </button>
                        </figure>
                    @endforeach
                @endforeach
            </div>
            <div class="empty-state gallery-empty" data-gallery-empty hidden>No photographs match the selected filters.</div>
        </div>
    </section>
    <div class="modal gallery-lightbox" data-gallery-modal role="dialog" aria-modal="true" aria-labelledby="gallery-lightbox-title" aria-hidden="true" hidden>
        <h2 class="sr-only" id="gallery-lightbox-title">Expanded gallery photograph</h2>
        <button class="modal-close" type="button" aria-label="Close expanded photograph">×</button>
        <figure class="gallery-lightbox__figure">
            <img src="" alt="">
            <figcaption data-gallery-modal-caption></figcaption>
        </figure>
    </div>
@endsection
