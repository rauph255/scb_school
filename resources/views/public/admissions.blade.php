@php
    $journey = $blocks['journey'] ?? null;
    $prepare = $blocks['prepare'] ?? null;
    $visit = $blocks['visit'] ?? null;

    $mediaUrl = fn ($media) => $media?->publicUrl();
@endphp

@extends('public.layout')

@section('content')
    <section class="page-hero">
        <x-responsive-image :media="$page->featuredMedia" :alt="$page->featuredMedia->alt_text ?? $page->title" :eager="true" />
        <div class="container">
            <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>›</span><span>{{ $page->title }}</span></div>
            <h1>{{ $page->title }}</h1>
            <p class="hero-copy">{{ $page->excerpt }}</p>
        </div>
    </section>

    <section class="section">
        <div class="container grid-2">
            <div>
                <div class="eyebrow">{{ $journey->settings['eyebrow'] ?? 'Join the school' }}</div>
                <h2>{{ $journey->heading ?? 'Admissions journey' }}</h2>
                <p class="lead">{{ $journey->body }}</p>
                <div class="event-list">
                    @foreach(($journey->settings['steps'] ?? []) as $index => $step)
                        <div class="event-row">
                            <div class="date-box"><b>STEP</b><strong>{{ $index + 1 }}</strong></div>
                            <div>
                                <h3>{{ $step['label'] }}</h3>
                                <p class="muted small">{{ $step['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="form-card">
                <div class="eyebrow">Admission enquiry</div>
                <h3 style="font-size:1.8rem">Start a conversation</h3>

                @if(session('status'))
                    <div class="notice" style="margin-bottom:18px">{{ session('status') }}</div>
                @endif

                @if($errors->any())
                    <div class="notice" style="margin-bottom:18px;border-left-color:var(--crimson)">
                        Please check the highlighted fields and try again.
                    </div>
                @endif

                <form method="POST" action="{{ route('admissions.enquiries.store') }}">
                    @csrf
                    <div class="honeypot-field" aria-hidden="true">
                        <label for="admissions-website">Website</label>
                        <input id="admissions-website" name="website" value="" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="guardian_name">Parent or guardian</label>
                            <input id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}" placeholder="Full name" required>
                            @error('guardian_name')<p class="small muted">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="telephone">Phone number</label>
                            <input id="telephone" name="telephone" value="{{ old('telephone') }}" placeholder="+255 ..." required>
                            @error('telephone')<p class="small muted">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@example.com" required>
                            @error('email')<p class="small muted">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="intended_level">Intended level</label>
                            <select id="intended_level" name="intended_level">
                                <option value="">Select level</option>
                                @foreach(['Pre-primary', 'Standard 1-3', 'Standard 4-7'] as $level)
                                    <option value="{{ $level }}" @selected(old('intended_level') === $level)>{{ $level }}</option>
                                @endforeach
                            </select>
                            @error('intended_level')<p class="small muted">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="intended_term">Intended term</label>
                            <input id="intended_term" name="intended_term" value="{{ old('intended_term') }}" placeholder="Preferred admission period">
                            @error('intended_term')<p class="small muted">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="intended_year">Intended year</label>
                            <input id="intended_year" name="intended_year" type="number" min="{{ now()->year }}" max="{{ now()->addYears(3)->year }}" value="{{ old('intended_year') }}" placeholder="{{ now()->year }}">
                            @error('intended_year')<p class="small muted">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="preferred_contact_method">Preferred contact</label>
                            <select id="preferred_contact_method" name="preferred_contact_method">
                                <option value="">No preference</option>
                                <option value="telephone" @selected(old('preferred_contact_method') === 'telephone')>Telephone</option>
                                <option value="email" @selected(old('preferred_contact_method') === 'email')>Email</option>
                            </select>
                            @error('preferred_contact_method')<p class="small muted">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="field">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="Tell us how we can help">{{ old('message') }}</textarea>
                        @error('message')<p class="small muted">{{ $message }}</p>@enderror
                    </div>
                    <label class="check">
                        <input type="checkbox" name="privacy_notice" value="1" @checked(old('privacy_notice')) required>
                        I agree to the school privacy notice.
                    </label>
                    @error('privacy_notice')<p class="small muted">{{ $message }}</p>@enderror
                    <button type="submit" class="btn btn-primary">Send enquiry</button>
                </form>
            </div>
        </div>
    </section>

    @if($prepare)
        <section class="section bg-cream">
            <div class="container">
                <div class="section-head">
                    <div>
                        <div class="eyebrow">{{ $prepare->settings['eyebrow'] ?? 'What to prepare' }}</div>
                        <h2>{{ $prepare->heading }}</h2>
                    </div>
                </div>
                <div class="grid-3">
                    @foreach(($prepare->settings['cards'] ?? []) as $card)
                        <div class="feature-card">
                            <div class="feature-icon">{{ $card['icon'] }}</div>
                            <h3>{{ $card['title'] }}</h3>
                            <p class="muted">{{ $card['description'] }}</p>
                            @if($card['route'] ?? null)
                                <a href="{{ route($card['route']) }}" class="text-link">Open resources →</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($downloads->isNotEmpty() || $faqs->isNotEmpty())
        <section class="section">
            <div @class(['container', 'grid-2' => $downloads->isNotEmpty() && $faqs->isNotEmpty()])>
                @if($downloads->isNotEmpty())
                    <div>
                        <div class="eyebrow">Resources</div>
                        <h2>Useful downloads.</h2>
                        <div class="event-list">
                            @foreach($downloads as $download)
                                <div class="event-row">
                                    <div class="download-icon"><i data-lucide="download" aria-hidden="true"></i></div>
                                    <div>
                                        <h3>{{ $download->title }}</h3>
                                        <p class="muted small">{{ $download->description }}</p>
                                    </div>
                                    <a class="btn btn-outline btn-sm" href="{{ route('downloads.show', $download) }}" download>Download</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($faqs->isNotEmpty())
                    <div>
                        <div class="eyebrow">Questions</div>
                        <h2>Common admissions questions.</h2>
                        @foreach($faqs as $faq)
                            <div class="accordion {{ $loop->first ? 'open' : '' }}">
                                <button type="button">{{ $faq->question }}<span>＋</span></button>
                                <div class="accordion-content">{{ $faq->answer }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if($visit)
        <section class="section bg-cream">
            <div class="container grid-2">
                <div class="image-frame">
                    <x-responsive-image :media="$visit->media" :alt="$visit->media->alt_text ?? $visit->heading" sizes="(max-width: 800px) 100vw, 50vw" />
                </div>
                <div>
                    <div class="eyebrow">{{ $visit->settings['eyebrow'] ?? 'Visit the campus' }}</div>
                    <h2>{{ $visit->heading }}</h2>
                    <p class="lead">{{ $visit->body }}</p>
                    @if($visit->settings['action'] ?? null)
                        <a href="{{ route($visit->settings['action']['route']) }}" class="btn btn-secondary">{{ $visit->settings['action']['label'] }}</a>
                    @endif
                </div>
            </div>
        </section>
    @endif
@endsection
