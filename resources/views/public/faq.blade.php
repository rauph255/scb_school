@extends('public.layout')

@push('structured-data')
<script type="application/ld+json">{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $faqs->map(fn ($faq) => [
        '@type' => 'Question',
        'name' => $faq->question,
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
    ])->values(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('content')
    <section class="page-hero">
        <x-responsive-image :media="$page->featuredMedia" :alt="$page->featuredMedia->alt_text ?? $page->title" :eager="true" />
        <div class="container">
            <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>›</span><span>{{ $page->title }}</span></div>
            <h1>Frequently asked questions</h1>
            <p class="hero-copy">{{ $page->excerpt }}</p>
        </div>
    </section>

    <section class="section">
        <div class="container" style="max-width:900px">
            <div class="eyebrow">Help centre</div>
            <h2>Common questions</h2>
            @foreach($faqs as $faq)
                <div class="accordion {{ $loop->first ? 'open' : '' }}" id="faq-{{ $faq->id }}">
                    <button type="button">{{ $faq->question }}<span>＋</span></button>
                    <div class="accordion-content">{{ $faq->answer }}</div>
                </div>
            @endforeach
            @if($faqs->isEmpty())
                <div class="empty-state">Verified answers will appear here after review by authorised school staff.</div>
            @endif
        </div>
    </section>
@endsection
