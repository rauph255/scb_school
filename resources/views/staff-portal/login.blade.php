@extends('staff-portal.layout', ['title' => 'Staff Login'])

@section('body')
<div class="staff-login">
    <div class="staff-login-visual">
        <x-responsive-image :media="$loginMedia" :alt="$loginMedia?->alt_text ?? 'Aerial view of the school campus'" :eager="true" sizes="(max-width: 760px) 100vw, 55vw" />
        <div class="staff-login-caption">
            <div class="eyebrow" style="color:#DAAD18">Staff workspace</div>
            <h1>Teaching work, notices and resources in one place.</h1>
            <p>Separate from administration so staff can focus on school communication and classroom support.</p>
        </div>
    </div>
    <div class="staff-login-wrap">
        <form class="staff-login-card" method="POST" action="{{ route('staff.login.store') }}">
            @csrf
            <div class="staff-login-brand">
                <img src="{{ $logo }}" alt="St. Charles Borromeo School crest">
                <h2>Staff portal</h2>
                <p>Teachers and school staff</p>
            </div>
            @if($errors->any())
                <div class="status status-new" style="margin-bottom:14px">{{ $errors->first() }}</div>
            @endif
            <div class="field">
                <label>Email address</label>
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" autocomplete="current-password" required>
            </div>
            <label class="check"><input type="checkbox" name="remember" value="1"> Remember this device</label>
            <button class="btn btn-primary" style="width:100%;border:0" type="submit">Sign in to staff portal</button>
            <div style="text-align:center;margin-top:16px">
                <a class="small muted" href="{{ route('password.request') }}">Forgot password?</a>
                <span aria-hidden="true"> · </span>
                <a class="small muted" href="{{ route('admin.login') }}">Administrator access</a>
            </div>
        </form>
        <x-site-credit class="login-credit" :light="true" />
    </div>
</div>
@endsection
