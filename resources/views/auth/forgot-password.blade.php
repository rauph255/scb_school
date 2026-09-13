<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reset password | St. Charles Borromeo</title>
    <link rel="stylesheet" href="{{ asset('assets/scb/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/scb-experience.css') }}">
</head>
<body class="admin-login">
    <div class="login-card-wrap" style="grid-column:1/-1">
        <form class="login-card" method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="login-brand">
                <img src="{{ asset('assets/images/brand/scb-logo-original.jpg') }}" alt="St. Charles Borromeo School crest">
                <h1 style="font-size:1.5rem">Reset your password</h1>
                <p>Enter the email address for your individual staff account.</p>
            </div>
            @if(session('status'))
                <div class="notice" style="margin-bottom:16px">{{ session('status') }}</div>
            @endif
            <div class="field">
                <label for="reset-email">Email address</label>
                <input id="reset-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                @error('email')<p class="small" style="color:var(--crimson)">{{ $message }}</p>@enderror
            </div>
            <button class="btn btn-primary" style="width:100%;border:0" type="submit">Send reset link</button>
            <div style="text-align:center;margin-top:16px"><a class="small muted" href="{{ route('admin.login') }}">Return to sign in</a></div>
        </form>
    </div>
</body>
</html>
