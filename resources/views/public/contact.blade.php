@php
    $contactBlock = $blocks['contact_details'] ?? null;
@endphp

@extends('public.layout')

@section('content')
    <section class="page-hero">
        <x-responsive-image :media="$page->featuredMedia" :alt="$page->featuredMedia->alt_text ?? $page->title" :eager="true" />
        <div class="container">
            <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>›</span><span>{{ $page->title }}</span></div>
            <h1>Contact us</h1>
            <p class="hero-copy">{{ $page->excerpt }}</p>
        </div>
    </section>

    <section class="section">
        <div class="container grid-2" style="align-items:start">
            <div>
                <div class="eyebrow">Get in touch</div>
                <h2>{{ $contactBlock->heading ?? 'We will guide you to the right person.' }}</h2>
                <p class="lead">{{ $contactBlock->body ?? 'Use the form to send your enquiry to the school team.' }}</p>
                <div class="contact-info">
                    @if($publicContact['address'])
                        <div class="contact-item"><div class="feature-icon" style="margin:0"><i data-lucide="map-pin"></i></div><div><b>Visit the school</b><span class="muted">{{ $publicContact['address'] }}</span></div></div>
                    @endif
                    @if($publicContact['telephone'])
                        <div class="contact-item"><div class="feature-icon" style="margin:0"><i data-lucide="phone"></i></div><div><b>Call the office</b><a class="muted" href="tel:{{ preg_replace('/\s+/', '', $publicContact['telephone']) }}">{{ $publicContact['telephone'] }}</a></div></div>
                    @endif
                    @if($publicContact['email'])
                        <div class="contact-item"><div class="feature-icon" style="margin:0"><i data-lucide="mail"></i></div><div><b>Email</b><a class="muted" href="mailto:{{ $publicContact['email'] }}">{{ $publicContact['email'] }}</a></div></div>
                    @endif
                    <div class="notice"><b>Safeguarding concern?</b><br><span class="small">Do not include sensitive child information in this general contact form.</span></div>
                </div>
            </div>
            <div class="form-card">
                <h3 style="font-size:1.8rem">Send a message</h3>
                @if(session('status'))
                    <div class="notice" style="margin-bottom:18px">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="notice" style="margin-bottom:18px;border-left-color:var(--crimson)">Please check the highlighted fields and try again.</div>
                @endif
                <form method="POST" action="{{ route('contact.messages.store') }}">
                    @csrf
                    <div class="honeypot-field" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input id="contact-website" name="website" value="" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="full_name">Name</label>
                            <input id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="Full name" required>
                            @error('full_name')<p class="small muted">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="telephone">Phone</label>
                            <input id="telephone" name="telephone" value="{{ old('telephone') }}" placeholder="+255 ...">
                            @error('telephone')<p class="small muted">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@example.com" required>
                        @error('email')<p class="small muted">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject" required>
                            @foreach(['General enquiry', 'Admissions', 'School office', 'Website feedback'] as $subject)
                                <option value="{{ $subject }}" @selected(old('subject') === $subject)>{{ $subject }}</option>
                            @endforeach
                        </select>
                        @error('subject')<p class="small muted">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="How can we help?" required>{{ old('message') }}</textarea>
                        @error('message')<p class="small muted">{{ $message }}</p>@enderror
                    </div>
                    <label class="check"><input type="checkbox" name="privacy_notice" value="1" @checked(old('privacy_notice')) required> I agree to the privacy notice.</label>
                    @error('privacy_notice')<p class="small muted">{{ $message }}</p>@enderror
                    <button type="submit" class="btn btn-primary">Send message</button>
                </form>
            </div>
        </div>
    </section>

    @if($page->featuredMedia?->publicUrl())
        <section class="section bg-cream">
            <div class="container">
                <div class="image-frame"><x-responsive-image :media="$page->featuredMedia" :alt="$page->featuredMedia->alt_text ?? $page->title" sizes="(max-width: 800px) 100vw, 1240px" style="min-height:360px" /></div>
            </div>
        </section>
    @endif
@endsection
