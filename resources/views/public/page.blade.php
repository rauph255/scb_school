@php
    $mediaUrl = fn ($media) => $media?->publicUrl();
@endphp

@extends('public.layout')

@section('content')
    <section class="page-hero">
        @if($mediaUrl($page->featuredMedia))
            <x-responsive-image :media="$page->featuredMedia" :alt="$page->featuredMedia->alt_text ?? $page->title" :eager="true" />
        @endif
        <div class="container">
            <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>›</span><span>{{ $page->title }}</span></div>
            <h1>{{ $page->title }}</h1>
            <p class="hero-copy">{{ $page->excerpt }}</p>
        </div>
    </section>

    @forelse($page->blocks as $block)
        <section class="section {{ $loop->even ? 'bg-cream' : '' }}" id="block-{{ $block->id }}">
            <div class="container {{ $block->media ? 'grid-2' : '' }}">
                <div>
                    @if($block->subheading)<div class="eyebrow">{{ $block->subheading }}</div>@endif
                    @if($block->heading)<h2>{{ $block->heading }}</h2>@endif
                    @if($block->body)<div class="lead">{!! nl2br(e($block->body)) !!}</div>@endif
                </div>
                @if($block->media)
                    <div class="image-frame">
                        <x-responsive-image :media="$block->media" :alt="$block->media->alt_text ?? $block->heading ?? $page->title" sizes="(max-width: 800px) 100vw, 50vw" />
                    </div>
                @endif
            </div>
        </section>
    @empty
        <section class="section"><div class="container"><div class="empty-state">No information has been published for this section yet.</div></div></section>
    @endforelse
@endsection
