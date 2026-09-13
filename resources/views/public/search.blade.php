@extends('public.layout')

@section('content')
<section class="page-hero page-hero--compact">
    <div class="container">
        <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>›</span><span>Search</span></div>
        <h1>Search the website</h1>
        <p class="hero-copy">Find published school information, news, events, programmes, answers and documents.</p>
    </div>
</section>

<section class="section" id="search-results">
    <div class="container" style="max-width:960px">
        <form class="site-search site-search--page" method="GET" action="{{ route('search') }}" role="search">
            <label for="site-search-query">What are you looking for?</label>
            <div class="search-box">
                <input id="site-search-query" name="q" type="search" value="{{ $query }}" minlength="2" maxlength="100" autocomplete="off" required>
                <button type="submit" aria-label="Search"><i data-lucide="search" aria-hidden="true"></i><span>Search</span></button>
            </div>
        </form>

        @if(mb_strlen($query) === 1)
            <div class="notice" role="status">Enter at least two characters to search.</div>
        @elseif($query !== '')
            <div class="section-head" style="margin-top:34px">
                <div><div class="eyebrow">{{ $results->count() }} {{ Str::plural('result', $results->count()) }}</div><h2>Results for “{{ $query }}”</h2></div>
            </div>
            <div class="search-results" aria-live="polite">
                @forelse($results as $result)
                    <article class="search-result">
                        <span class="tag">{{ $result['type'] }}</span>
                        <h3><a href="{{ $result['url'] }}">{{ $result['title'] }}</a></h3>
                        @if($result['summary'])<p class="muted">{{ $result['summary'] }}</p>@endif
                        <a class="text-link" href="{{ $result['url'] }}">Open {{ Str::lower($result['type']) }} →</a>
                    </article>
                @empty
                    <div class="empty-state">No published content matches this search.</div>
                @endforelse
            </div>
        @endif
    </div>
</section>
@endsection
