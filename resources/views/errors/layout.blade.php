<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') | St. Charles Borromeo</title>
    <link rel="stylesheet" href="/assets/scb/css/app.css">
    <link rel="stylesheet" href="/assets/css/scb-experience.css">
</head>
<body>
    <main id="main-content">
        <section class="section bg-plum" style="min-height:100vh;display:grid;align-items:center">
            <div class="container grid-2">
                <div>
                    <div class="eyebrow" style="color:var(--gold)">@yield('code')</div>
                    <h1 style="color:#fff">@yield('heading')</h1>
                    <p class="lead">@yield('message')</p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="/">Go home</a>
                        <a class="btn btn-light" href="/contact">Contact school</a>
                    </div>
                    @if(request()->header('X-Request-Id'))
                        <p class="small" style="margin-top:24px;color:rgba(255,255,255,.68)">Reference: {{ request()->header('X-Request-Id') }}</p>
                    @endif
                </div>
                <div style="display:grid;place-items:center">
                    <img src="/assets/images/brand/scb-logo-original.jpg" alt="St. Charles Borromeo School crest" width="280" height="280" style="width:min(280px,70vw);border-radius:50%;border:8px solid var(--gold);box-shadow:var(--shadow)">
                </div>
            </div>
        </section>
    </main>
    <a class="site-credit error-credit" href="https://www.falconode.net" target="_blank" rel="noopener noreferrer">Powered by Falconode (T) Ltd</a>
</body>
</html>
