<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title ?? 'Staff Portal' }} | {{ $settings['identity.school_name'] ?? 'St. Charles Borromeo' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/scb/css/app.css') }}">
    <style>
        .staff-body{background:#F8F5EC;color:#2C2530;min-height:100vh}
        .staff-shell{min-height:100vh;display:grid;grid-template-columns:248px minmax(0,1fr)}
        .staff-sidebar{background:#fff;border-right:1px solid #E5DFD9;padding:22px 16px;position:sticky;top:0;height:100vh;overflow:auto}
        .staff-logo{display:flex;align-items:center;gap:12px;padding-bottom:20px;border-bottom:1px solid #E9E2DC}
        .staff-logo img{width:50px;height:50px;border-radius:50%;border:2px solid #DAAD18}
        .staff-logo b{display:block;color:#3D2D3F;font-family:Georgia,serif;line-height:1.15}
        .staff-logo span{font-size:.68rem;color:#B8101E;font-weight:900;text-transform:uppercase;letter-spacing:.08em}
        .staff-nav{display:grid;gap:5px;margin-top:22px}
        .staff-nav a{display:flex;align-items:center;gap:10px;border-radius:8px;padding:10px 11px;font-size:.84rem;font-weight:800;color:#645A64}
        .staff-nav a.active,.staff-nav a:hover{background:#FFF5D7;color:#3D2D3F}
        .staff-nav i{width:24px;text-align:center;color:#B8101E;font-style:normal}
        .staff-main{min-width:0}
        .staff-topbar{height:72px;background:#3D2D3F;color:#fff;display:flex;align-items:center;justify-content:space-between;padding:0 28px;position:sticky;top:0;z-index:20}
        .staff-search{width:min(430px,44vw);border:1px solid rgba(255,255,255,.18);border-radius:9px;padding:9px 12px;color:rgba(255,255,255,.75)}
        .staff-user{display:flex;align-items:center;gap:12px}
        .staff-avatar{width:38px;height:38px;border-radius:50%;background:#DAAD18;color:#3D2D3F;display:grid;place-items:center;font-weight:900}
        .staff-content{padding:30px}
        .staff-head{display:flex;justify-content:space-between;gap:22px;align-items:center;margin-bottom:24px}
        .staff-head h1{font-size:2rem}
        .staff-actions{display:flex;gap:10px;flex-wrap:wrap}
        .staff-grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
        .staff-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:20px;align-items:start}
        .staff-card{background:#fff;border:1px solid #E5DFD9;border-radius:13px;box-shadow:0 5px 15px rgba(61,45,63,.04);overflow:hidden}
        .staff-card-head{padding:18px 20px;border-bottom:1px solid #ECE6E1;display:flex;justify-content:space-between;gap:12px;align-items:center}
        .staff-card-head h2{font-size:1rem;margin:0}
        .staff-card-body{padding:20px}
        .staff-metric{padding:20px}
        .staff-metric span{font-size:.73rem;color:#746A73;font-weight:900;text-transform:uppercase;letter-spacing:.06em}
        .staff-metric strong{display:block;font-family:"Arial Black",sans-serif;font-size:2rem;color:#3D2D3F;margin-top:8px}
        .staff-list{display:grid;gap:13px}
        .staff-list-item{display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:start;padding-bottom:13px;border-bottom:1px solid #EEE9E4}
        .staff-list-item:last-child{padding-bottom:0;border-bottom:0}
        .staff-dot{width:32px;height:32px;border-radius:50%;background:#FFF5D7;color:#B8101E;display:grid;place-items:center;font-weight:900}
        .staff-status{display:inline-flex;border-radius:99px;padding:5px 9px;background:#EAF7EE;color:#287143;font-size:.66rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em}
        .staff-form{display:grid;gap:12px}
        .staff-field{display:grid;gap:6px}
        .staff-field label{font-size:.72rem;font-weight:900;text-transform:uppercase;letter-spacing:.06em;color:#746A73}
        .staff-field input,.staff-field textarea{width:100%;border:1px solid #E3DBD5;border-radius:8px;padding:10px 11px;background:#fff;color:#2C2530;font:inherit}
        .staff-field textarea{min-height:96px;resize:vertical}
        .staff-alert{margin-bottom:18px;border:1px solid #BEE6C8;background:#F0FAF3;color:#287143;border-radius:9px;padding:12px 14px;font-weight:800}
        .staff-login{min-height:100vh;display:grid;grid-template-columns:1.05fr .95fr;background:#3D2D3F}
        .staff-login-visual{position:relative;overflow:hidden}
        .staff-login-visual img{width:100%;height:100%;object-fit:cover}
        .staff-login-visual:after{content:"";position:absolute;inset:0;background:linear-gradient(0deg,rgba(61,45,63,.88),rgba(61,45,63,.16))}
        .staff-login-caption{position:absolute;z-index:2;left:50px;right:50px;bottom:50px;color:#fff}
        .staff-login-caption h1{color:#fff;font-size:3rem}
        .staff-login-wrap{background:#F8F5EC;display:grid;place-items:center;padding:30px}
        .staff-login-card{width:min(440px,100%);background:#fff;padding:42px;border-radius:18px;box-shadow:0 24px 70px rgba(61,45,63,.18)}
        .staff-login-brand{text-align:center;margin-bottom:28px}
        .staff-login-brand img{width:88px;height:88px;border-radius:50%;border:3px solid #DAAD18;margin:0 auto 12px}
        @media(max-width:1000px){.staff-shell{grid-template-columns:88px minmax(0,1fr)}.staff-logo b,.staff-logo span,.staff-nav a span{display:none}.staff-logo{justify-content:center}.staff-nav a{justify-content:center}.staff-grid-3{grid-template-columns:1fr 1fr}.staff-grid{grid-template-columns:1fr}}
        @media(max-width:760px){.staff-shell,.staff-login{grid-template-columns:1fr}.staff-sidebar{position:relative;height:auto}.staff-topbar{padding:0 16px}.staff-search{display:none}.staff-content{padding:18px}.staff-head{align-items:flex-start;flex-direction:column}.staff-grid-3{grid-template-columns:1fr}.staff-login-visual{min-height:340px}.staff-login-caption{left:24px;right:24px;bottom:24px}.staff-login-caption h1{font-size:2.1rem}}
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/scb-experience.css') }}">
</head>
<body class="staff-body">
    @include('partials.site-loader', ['loaderLabel' => 'Preparing the staff workspace'])
    @yield('body')
    <script src="{{ asset('assets/scb/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/lucide.min.js') }}"></script>
    <script src="{{ asset('assets/js/scb-experience.js') }}"></script>
</body>
</html>
