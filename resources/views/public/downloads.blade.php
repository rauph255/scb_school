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
                    <div class="eyebrow">School downloads</div>
                    <h2>Find the right document quickly.</h2>
                </div>
                <form class="search-box" method="GET" action="{{ route('downloads') }}">
                    <input name="q" value="{{ request('q') }}" placeholder="Search downloads">
                    <select name="category" aria-label="Download category">
                        <option value="">All categories</option>
                        @foreach($downloadCategories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" aria-label="Search downloads"><i data-lucide="search" aria-hidden="true"></i></button>
                </form>
            </div>
            <div class="table-wrap">
                <table class="public-table">
                    <thead>
                        <tr><th>Document</th><th>Category</th><th>Updated</th><th>Format</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($downloads as $download)
                            <tr id="download-{{ $download->slug }}">
                                <td>
                                    <div style="display:flex;gap:12px;align-items:center">
                                        <div class="download-icon">{{ Str::upper($download->media->extension ?? 'DOC') }}</div>
                                        <div>
                                            <b>{{ $download->title }}</b>
                                            <div class="small muted">{{ $download->description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $download->category?->name ?? 'General' }}</td>
                                <td>{{ optional($download->publication_date)->format('j M Y') }}</td>
                                <td>{{ Str::upper($download->media->extension ?? 'file') }} · {{ number_format(($download->media->size_bytes ?? 0) / 1024, 0) }} KB</td>
                                <td>
                                    @if($mediaUrl($download->media))
                                        <a class="btn btn-outline btn-sm" href="{{ route('downloads.show', $download) }}" download>
                                            <i data-lucide="download" aria-hidden="true"></i>
                                            Download
                                        </a>
                                    @else
                                        <span class="small muted">Unavailable</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state">No published downloads match your search.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('public.partials.pagination', ['paginator' => $downloads])
        </div>
    </section>
@endsection
