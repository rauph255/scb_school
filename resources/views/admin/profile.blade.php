@extends('admin.layout')

@section('title', 'My profile')

@section('content')
    <div class="admin-page-head">
        <div>
            <div class="eyebrow">Account</div>
            <h1>My profile</h1>
            <p class="muted">Keep your sign-in details current and secure.</p>
        </div>
        <a class="btn btn-outline btn-sm" href="{{ route('admin.dashboard') }}">
            <i data-lucide="arrow-left" aria-hidden="true"></i>
            Dashboard
        </a>
    </div>

    @if(session('status'))
        <div class="admin-alert">{{ session('status') }}</div>
    @endif

    @if($errors->profile->any())
        <div class="admin-alert admin-alert--error">Please check the highlighted account fields.</div>
    @endif

    <div class="admin-form-grid">
        <section class="admin-panel" style="margin-top:0">
            <div class="panel-head">
                <h2>Account details</h2>
                <i data-lucide="user-round-cog" aria-hidden="true"></i>
            </div>
            <div class="panel-body">
                <form class="admin-fields" method="POST" action="{{ route('admin.profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <div class="admin-field">
                        <label for="profile-name">Full name</label>
                        <input id="profile-name" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                        @error('name', 'profile')<p class="small" style="color:var(--crimson)">{{ $message }}</p>@enderror
                    </div>
                    <div class="admin-field">
                        <label for="profile-email">Email address</label>
                        <input id="profile-email" type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                        @error('email', 'profile')<p class="small" style="color:var(--crimson)">{{ $message }}</p>@enderror
                    </div>
                    <div class="admin-field">
                        <label for="profile-current-password">Current password</label>
                        <input id="profile-current-password" type="password" name="current_password" autocomplete="current-password" required>
                        @error('current_password', 'profile')<p class="small" style="color:var(--crimson)">{{ $message }}</p>@enderror
                    </div>
                    <div class="admin-field">
                        <label for="profile-password">New password</label>
                        <input id="profile-password" type="password" name="password" autocomplete="new-password" aria-describedby="profile-password-help">
                        <p id="profile-password-help" class="small muted">Leave blank to keep your current password. New passwords require at least 12 characters, mixed case, a number and a symbol.</p>
                        @error('password', 'profile')<p class="small" style="color:var(--crimson)">{{ $message }}</p>@enderror
                    </div>
                    <div class="admin-field">
                        <label for="profile-password-confirmation">Confirm new password</label>
                        <input id="profile-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password">
                    </div>
                    <button class="btn btn-primary" type="submit">
                        <i data-lucide="save" aria-hidden="true"></i>
                        Save account
                    </button>
                </form>
            </div>
        </section>

        <aside class="admin-panel" style="margin-top:0">
            <div class="panel-head">
                <h2>Security</h2>
                <i data-lucide="shield-check" aria-hidden="true"></i>
            </div>
            <div class="panel-body detail-meta">
                <div class="detail-meta-row"><span>Account status</span><b>{{ $user->is_active ? 'Active' : 'Inactive' }}</b></div>
                <div class="detail-meta-row"><span>Last sign in</span><b>{{ $user->last_login_at?->format('d M Y H:i') ?? 'Current session' }}</b></div>
                <div class="detail-meta-row"><span>Password change</span><b>Signs out other sessions</b></div>
                <p class="small muted">Profile updates are recorded in the audit log. Your password is never included in that record.</p>
            </div>
        </aside>
    </div>
@endsection
