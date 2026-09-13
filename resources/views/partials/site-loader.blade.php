<div class="site-loader @if(($loaderTheme ?? null) === 'admin') site-loader--admin @endif" data-site-loader role="status" aria-live="polite" aria-label="Loading St. Charles Borromeo">
    <div class="site-loader__content">
        <div class="site-loader__crest">
            <img src="{{ $logo ?? asset('assets/images/brand/scb-logo-original.jpg') }}" alt="">
        </div>
        <b>St. Charles Borromeo</b>
        <span>{{ $loaderLabel ?? 'Preparing the school website' }}</span>
    </div>
</div>
<noscript><style>.site-loader{display:none!important}</style></noscript>
