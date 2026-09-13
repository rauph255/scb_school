<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Choose a new password | St. Charles Borromeo</title>
    <link rel="stylesheet" href="{{ asset('assets/scb/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/scb-experience.css') }}">
</head>
<body class="admin-login">
    <div class="login-card-wrap" style="grid-column:1/-1">
        <form class="login-card" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="login-brand">
                <img src="{{ asset('assets/images/brand/scb-logo-original.jpg') }}" alt="St. Charles Borromeo School crest">
                <h1 style="font-size:1.5rem">Choose a new password</h1>
                <p>Use at least 12 characters with mixed case, a number and a symbol.</p>
            </div>
            <div class="field">
                <label for="reset-account-email">Email address</label>
                <input id="reset-account-email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
                @error('email')<p class="small" style="color:var(--crimson)">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="new-password">New password</label>
                <input id="new-password" type="password" name="password" autocomplete="new-password" required>
                @error('password')<p class="small" style="color:var(--crimson)">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="new-password-confirmation">Confirm new password</label>
                <input id="new-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
            </div>
            <button class="btn btn-primary" style="width:100%;border:0" type="submit">Reset password</button>
        </form>
    </div>
</body>
</html>
