<!doctype html>
<html lang="en">
@php($activeNav = $activeNav ?? null)
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title') | SCB Admin</title>
    <link rel="stylesheet" href="{{ asset('assets/scb/css/app.css') }}">
    <style>
        .admin-alert{margin:0 0 20px;padding:13px 15px;border:1px solid #BEE6C8;border-radius:8px;background:#F0FAF3;color:#287143;font-weight:800}
        .admin-alert--error{border-color:#F0C7CB;background:#FFF3F4;color:#A3222C}
        .admin-detail-stack{display:grid;gap:20px}
        .admin-detail-stack .admin-panel{margin:0}
        .detail-pairs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 26px}
        .detail-pair{padding:13px 0;border-bottom:1px solid #EEE9E4;min-width:0}
        .detail-pair dt{font-size:.68rem;text-transform:uppercase;font-weight:900;color:#756B74;margin-bottom:4px}
        .detail-pair dd{margin:0;overflow-wrap:anywhere}
        .detail-copy{white-space:pre-wrap;overflow-wrap:anywhere;margin:0}
        .detail-notes{display:grid;gap:0}
        .detail-note{display:grid;grid-template-columns:34px minmax(0,1fr) auto;gap:12px;padding:15px 0;border-bottom:1px solid #EEE9E4;align-items:start}
        .detail-note:first-child{padding-top:0}
        .detail-note:last-child{border-bottom:0}
        .detail-note__mark{width:30px;height:30px;border-radius:50%;display:grid;place-items:center;background:var(--cream);color:var(--crimson);font-weight:900}
        .detail-note p{white-space:pre-wrap;overflow-wrap:anywhere;margin:4px 0 0}
        .detail-workflow{display:grid;gap:16px}
        .detail-workflow form{display:grid;gap:10px}
        .detail-workflow label{font-size:.7rem;text-transform:uppercase;font-weight:900;color:var(--plum)}
        .detail-workflow select{width:100%;border:1px solid #DCD5CE;border-radius:8px;padding:10px 11px;background:#fff}
        .detail-meta{display:grid;gap:12px}
        .detail-meta-row{display:flex;justify-content:space-between;gap:16px;padding-bottom:11px;border-bottom:1px solid #EEE9E4;font-size:.82rem}
        .detail-meta-row:last-child{border-bottom:0;padding-bottom:0}
        .detail-meta-row span:first-child{color:#756B74}
        @media(max-width:800px){.detail-pairs{grid-template-columns:1fr}.detail-note{grid-template-columns:34px minmax(0,1fr)}.detail-note time{grid-column:2}}
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/scb-experience.css') }}">
</head>
<body class="admin-body">
@include('partials.site-loader', ['logo' => asset('assets/images/brand/scb-logo-original.jpg'), 'loaderLabel' => 'Preparing administration'])
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-logo" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/images/brand/scb-logo-original.jpg') }}" alt="School crest">
            <div><b>St. Charles<br>Borromeo</b><span>Administration</span></div>
        </a>
        <div class="admin-nav-group">
            <div class="admin-nav-label">Overview</div>
            <nav class="admin-nav">
                <a href="{{ route('admin.dashboard') }}"><span class="nav-ico">&#8962;</span><span>Dashboard</span></a>
                <a href="{{ route('admin.profile') }}" @class(['active' => $activeNav === 'profile'])><span class="nav-ico"><i data-lucide="user-round-cog"></i></span><span>My profile</span></a>
            </nav>
        </div>
        <div class="admin-nav-group">
            <div class="admin-nav-label">Content</div>
            <nav class="admin-nav">
                <a href="{{ route('admin.pages') }}"><span class="nav-ico">&#9636;</span><span>Pages</span></a>
                <a href="{{ route('admin.news') }}"><span class="nav-ico">&#9998;</span><span>News</span></a>
                <a href="{{ route('admin.events') }}"><span class="nav-ico">&#9638;</span><span>Events</span></a>
                <a href="{{ route('admin.gallery') }}"><span class="nav-ico">&#9639;</span><span>Galleries</span></a>
                <a href="{{ route('admin.downloads') }}"><span class="nav-ico">&#8595;</span><span>Downloads</span></a>
                <a href="{{ route('admin.faqs') }}" @class(['active' => $activeNav === 'faqs'])><span class="nav-ico"><i data-lucide="messages-square"></i></span><span>FAQs</span></a>
            </nav>
        </div>
        <div class="admin-nav-group">
            <div class="admin-nav-label">School</div>
            <nav class="admin-nav">
                <a href="{{ route('admin.staff') }}"><span class="nav-ico">&#9823;</span><span>Staff</span></a>
                <a href="{{ route('admin.programmes') }}"><span class="nav-ico">&#9637;</span><span>Programmes</span></a>
                <a href="{{ route('admin.admissions') }}" @class(['active' => $activeNav === 'admissions'])><span class="nav-ico">&#10022;</span><span>Admissions</span></a>
                <a href="{{ route('admin.contact-messages') }}" @class(['active' => $activeNav === 'contact-messages'])><span class="nav-ico">&#9993;</span><span>Messages</span></a>
            </nav>
        </div>
        <div class="admin-nav-group">
            <div class="admin-nav-label">System</div>
            <nav class="admin-nav">
                <a href="{{ route('admin.media') }}"><span class="nav-ico">&#9635;</span><span>Media library</span></a>
                <a href="{{ route('admin.users') }}"><span class="nav-ico">&#9817;</span><span>Users</span></a>
                <a href="{{ route('admin.roles') }}"><span class="nav-ico">&#9911;</span><span>Roles &amp; permissions</span></a>
                <a href="{{ route('admin.settings') }}"><span class="nav-ico">&#9881;</span><span>Site settings</span></a>
                <a href="{{ route('admin.audit-log') }}"><span class="nav-ico">&#9719;</span><span>Audit log</span></a>
            </nav>
        </div>
        <div class="admin-nav-group">
            <nav class="admin-nav">
                <a href="{{ route('home') }}"><span class="nav-ico">&#8599;</span><span>View website</span></a>
                <form class="admin-nav-form" method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"><span class="nav-ico">&#8681;</span><span>Sign out</span></button>
                </form>
            </nav>
        </div>
        <x-site-credit class="sidebar-credit" />
    </aside>
    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px">
                <button class="icon-btn" type="button" data-admin-menu aria-label="Toggle admin navigation"><i data-lucide="menu"></i></button>
                <div class="admin-search"><i data-lucide="search"></i><input placeholder="Search visible records"></div>
            </div>
            <div class="admin-topbar-actions">
                @include('admin.partials.notifications')
                <a class="admin-user admin-user-link" href="{{ route('admin.profile') }}" aria-label="Edit your account details">
                    <div><b class="small">{{ auth()->user()->name }}</b><div class="small muted">{{ auth()->user()->email }}</div></div>
                    <div class="avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</div>
                </a>
            </div>
        </header>
        <div class="admin-content">@yield('content')</div>
    </main>
</div>
<div class="toast"></div>
<script src="{{ asset('assets/scb/js/app.js') }}"></script>
<script src="{{ asset('assets/js/lucide.min.js') }}"></script>
<script src="{{ asset('assets/js/scb-experience.js') }}"></script>
</body>
</html>
