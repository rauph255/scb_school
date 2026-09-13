@extends('staff-portal.layout', ['title' => 'Staff Dashboard'])

@section('body')
<div class="staff-shell">
    <aside class="staff-sidebar">
        <a class="staff-logo" href="{{ route('staff.dashboard') }}">
            <img src="{{ $logo }}" alt="St. Charles Borromeo School crest">
            <div>
                <b>St. Charles<br>Borromeo</b>
                <span>Staff portal</span>
            </div>
        </a>
        <nav class="staff-nav">
            <a class="active" href="{{ route('staff.dashboard') }}"><i>⌂</i><span>Dashboard</span></a>
            <a href="{{ route('events.index') }}"><i>▦</i><span>School calendar</span></a>
            <a href="{{ route('downloads') }}"><i>↓</i><span>Staff resources</span></a>
            <a href="{{ route('news.index') }}"><i>✎</i><span>News contributions</span></a>
            <a href="{{ route('about') }}"><i>♙</i><span>Staff directory</span></a>
            <a href="{{ route('home') }}"><i>↗</i><span>View website</span></a>
            <form class="staff-nav-form" method="POST" action="{{ route('staff.logout') }}">
                @csrf
                <button type="submit"><i>⇥</i><span>Sign out</span></button>
            </form>
        </nav>
    </aside>
    <main class="staff-main">
        <header class="staff-topbar">
            <label class="staff-search"><input data-staff-search type="search" placeholder="Search visible notices, resources or calendar items" aria-label="Search staff dashboard"></label>
            <a class="staff-user staff-user-link" href="#staff-profile-form" aria-label="Edit your account details">
                <div>
                    <b class="small">{{ auth()->user()->name }}</b>
                    <div class="small" style="color:rgba(255,255,255,.68)">{{ auth()->user()->email }}</div>
                </div>
                <div class="staff-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</div>
            </a>
        </header>
        <div class="staff-content">
            <div class="staff-head">
                <div>
                    <div class="eyebrow">Staff workspace</div>
                    <h1>Staff portal</h1>
                    <p class="muted">Calendar, resources and contribution queues for teaching and school staff.</p>
                </div>
                <div class="staff-actions">
                    <a class="btn btn-outline btn-sm" href="{{ route('downloads') }}">Resources</a>
                    <a class="btn btn-outline btn-sm" href="#staff-profile-form">My account</a>
                    <a class="btn btn-primary btn-sm" href="#staff-contribution-form">Draft contribution</a>
                </div>
            </div>

            @if(session('status'))
                <div class="staff-alert">{{ session('status') }}</div>
            @endif

            <div class="staff-grid-3">
                <div class="staff-card staff-metric">
                    <span>Upcoming events</span>
                    <strong>{{ $upcomingEvents->count() }}</strong>
                    <p class="small muted">Published calendar items</p>
                </div>
                <div class="staff-card staff-metric">
                    <span>Resource files</span>
                    <strong>{{ $staffDownloads->count() }}</strong>
                    <p class="small muted">Published downloads</p>
                </div>
                <div class="staff-card staff-metric">
                    <span>Staff directory</span>
                    <strong>{{ $staffMembers->count() }}</strong>
                    <p class="small muted">Public staff records</p>
                </div>
            </div>

            <div class="staff-grid" style="margin-top:20px">
                <div>
                    <section class="staff-card">
                        <div class="staff-card-head">
                            <h2>School calendar</h2>
                            <a class="text-link" href="{{ route('events.index') }}">View all</a>
                        </div>
                        <div class="staff-card-body staff-list">
                            @forelse($upcomingEvents as $event)
                                <a class="staff-list-item" href="{{ route('events.show', ['event' => $event->slug]) }}">
                                    <div class="staff-dot">▦</div>
                                    <div>
                                        <b>{{ $event->title }}</b>
                                        <div class="small muted">{{ $event->starts_at->format('M j, Y H:i') }} · {{ $event->venue_name ?? 'School campus' }}</div>
                                    </div>
                                    <span class="staff-status">{{ $event->event_state }}</span>
                                </a>
                            @empty
                                <div class="staff-list-item"><div><b>No upcoming events</b><div class="small muted">Published events will appear here.</div></div></div>
                            @endforelse
                        </div>
                    </section>

                    <section class="staff-card" style="margin-top:20px">
                        <div class="staff-card-head">
                            <h2>News contribution queue</h2>
                            <a class="text-link" href="{{ route('news.index') }}">Open news</a>
                        </div>
                        <div class="staff-card-body staff-list">
                            @forelse($myContributions as $post)
                                <div class="staff-list-item">
                                    <div class="staff-dot">✎</div>
                                    <div>
                                        <b>{{ $post->title }}</b>
                                        <div class="small muted">{{ $post->category?->name ?? 'School news' }} · {{ $post->updated_at?->format('M j, Y') }}</div>
                                    </div>
                                    <span class="staff-status">{{ $post->status }}</span>
                                </div>
                            @empty
                                @foreach($recentPosts as $post)
                                    <div class="staff-list-item">
                                        <div class="staff-dot">✎</div>
                                        <div>
                                            <b>{{ $post->title }}</b>
                                            <div class="small muted">{{ $post->category?->name ?? 'School news' }} · {{ $post->published_at?->format('M j, Y') }}</div>
                                        </div>
                                        <span class="staff-status">Published</span>
                                    </div>
                                @endforeach
                            @endforelse
                        </div>
                    </section>

                    <section class="staff-card" id="staff-contribution-form" style="margin-top:20px">
                        <div class="staff-card-head">
                            <h2>Draft contribution</h2>
                        </div>
                        <div class="staff-card-body">
                            <form class="staff-form" method="POST" action="{{ route('staff.contributions.store') }}">
                                @csrf
                                <div class="staff-field">
                                    <label for="contribution-title">Title</label>
                                    <input id="contribution-title" name="title" value="{{ old('title') }}" required>
                                </div>
                                <div class="staff-field">
                                    <label for="contribution-excerpt">Summary</label>
                                    <textarea id="contribution-excerpt" name="excerpt" required>{{ old('excerpt') }}</textarea>
                                </div>
                                <div class="staff-field">
                                    <label for="contribution-body">Details</label>
                                    <textarea id="contribution-body" name="body">{{ old('body') }}</textarea>
                                </div>
                                <button class="btn btn-primary btn-sm" type="submit">Submit for review</button>
                            </form>
                        </div>
                    </section>
                </div>

                <aside>
                    <section class="staff-card">
                        <div class="staff-card-head">
                            <h2>Staff resources</h2>
                            <a class="text-link" href="{{ route('downloads') }}">Downloads</a>
                        </div>
                        <div class="staff-card-body staff-list">
                            @forelse($staffDownloads as $download)
                                <a class="staff-list-item" href="{{ route('downloads.show', $download) }}" download>
                                    <div class="staff-dot">↓</div>
                                    <div>
                                        <b>{{ $download->title }}</b>
                                        <div class="small muted">{{ $download->category?->name ?? 'Resource' }}</div>
                                    </div>
                                </a>
                            @empty
                                <div class="staff-list-item"><div><b>No available resources</b><div class="small muted">Approved files will appear here.</div></div></div>
                            @endforelse
                        </div>
                    </section>

                    <section class="staff-card" style="margin-top:20px">
                        <div class="staff-card-head">
                            <h2>Directory</h2>
                        </div>
                        <div class="staff-card-body staff-list">
                            @foreach($staffMembers as $member)
                                <div class="staff-list-item">
                                    <div class="staff-dot">♙</div>
                                    <div>
                                        <b>{{ $member->name }}</b>
                                        <div class="small muted">{{ $member->job_title }} · {{ $member->department?->name ?? 'School' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="staff-card" id="staff-profile-form" style="margin-top:20px">
                        <div class="staff-card-head">
                            <h2>My account</h2>
                            <i data-lucide="user-round-cog" aria-hidden="true"></i>
                        </div>
                        <div class="staff-card-body">
                            @if($errors->profile->any())
                                <div class="staff-alert" style="border-color:#F0C7CB;background:#FFF3F4;color:#A3222C">Please check the highlighted account fields.</div>
                            @endif
                            <form class="staff-form" method="POST" action="{{ route('staff.profile.update') }}">
                                @csrf
                                @method('PATCH')
                                <div class="staff-field">
                                    <label for="staff-profile-name">Full name</label>
                                    <input id="staff-profile-name" name="name" value="{{ old('name', auth()->user()->name) }}" autocomplete="name" required>
                                    @error('name', 'profile')<p class="small" style="color:#A3222C">{{ $message }}</p>@enderror
                                </div>
                                <div class="staff-field">
                                    <label for="staff-profile-email">Email address</label>
                                    <input id="staff-profile-email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" autocomplete="email" required>
                                    @error('email', 'profile')<p class="small" style="color:#A3222C">{{ $message }}</p>@enderror
                                </div>
                                <div class="staff-field">
                                    <label for="staff-current-password">Current password</label>
                                    <input id="staff-current-password" type="password" name="current_password" autocomplete="current-password" required>
                                    @error('current_password', 'profile')<p class="small" style="color:#A3222C">{{ $message }}</p>@enderror
                                </div>
                                <div class="staff-field">
                                    <label for="staff-new-password">New password</label>
                                    <input id="staff-new-password" type="password" name="password" autocomplete="new-password" aria-describedby="staff-password-help">
                                    <p id="staff-password-help" class="small muted">Optional. Use at least 12 characters with mixed case, a number and a symbol.</p>
                                    @error('password', 'profile')<p class="small" style="color:#A3222C">{{ $message }}</p>@enderror
                                </div>
                                <div class="staff-field">
                                    <label for="staff-password-confirmation">Confirm new password</label>
                                    <input id="staff-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password">
                                </div>
                                <button class="btn btn-primary btn-sm" type="submit">
                                    <i data-lucide="save" aria-hidden="true"></i>
                                    Save account
                                </button>
                            </form>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
        <x-site-credit class="portal-credit" :light="true" />
    </main>
</div>
@endsection
