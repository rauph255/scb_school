@extends('admin.layout')

@section('title', 'Admission enquiry '.$enquiry->reference_code)

@section('content')
@php
    $statusClass = match ($enquiry->status) {
        'responded' => 'status-published',
        'in_progress' => 'status-review',
        'new' => 'status-new',
        'closed' => 'status-archived',
        default => 'status-draft',
    };
@endphp

<div class="admin-page-head">
    <div>
        <div class="eyebrow">Private admission record</div>
        <h1>{{ $enquiry->reference_code }}</h1>
        <p class="muted">{{ $enquiry->guardian_name }} &middot; received {{ $enquiry->created_at?->format('d M Y H:i') }}</p>
    </div>
    <div class="admin-actions">
        <a class="btn btn-outline btn-sm" href="{{ route('admin.admissions') }}">Back to enquiries</a>
        <button class="btn btn-primary btn-sm" type="button" data-toggle-details="admission-email-reply">Email guardian</button>
    </div>
</div>

@if(session('status'))
    <div class="admin-alert">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="admin-alert admin-alert--error">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<div class="admin-form-grid">
    <div class="admin-detail-stack">
        <section class="admin-panel">
            <div class="panel-head"><h2>Enquiry details</h2><span class="status {{ $statusClass }}">{{ Str::headline($enquiry->status) }}</span></div>
            <div class="panel-body">
                <dl class="detail-pairs">
                    <div class="detail-pair"><dt>Parent or guardian</dt><dd>{{ $enquiry->guardian_name }}</dd></div>
                    <div class="detail-pair"><dt>Email</dt><dd><a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a></dd></div>
                    <div class="detail-pair"><dt>Telephone</dt><dd><a href="tel:{{ $enquiry->telephone }}">{{ $enquiry->telephone }}</a></dd></div>
                    <div class="detail-pair"><dt>Preferred contact</dt><dd>{{ Str::headline($enquiry->preferred_contact_method ?: 'Not specified') }}</dd></div>
                    <div class="detail-pair"><dt>Intended level</dt><dd>{{ $enquiry->intended_level ?: 'Not specified' }}</dd></div>
                    <div class="detail-pair"><dt>Intended term</dt><dd>{{ $enquiry->intended_term ?: 'Not specified' }}</dd></div>
                    <div class="detail-pair"><dt>Intended year</dt><dd>{{ $enquiry->intended_year ?: 'Not specified' }}</dd></div>
                    <div class="detail-pair"><dt>Privacy consent</dt><dd>{{ $enquiry->consent_confirmed ? 'Confirmed' : 'Not confirmed' }}</dd></div>
                </dl>
            </div>
        </section>

        <section class="admin-panel">
            <div class="panel-head"><h2>Guardian message</h2></div>
            <div class="panel-body"><p class="detail-copy">{{ $enquiry->message ?: 'No message supplied.' }}</p></div>
        </section>

        <details class="admin-panel" id="admission-email-reply" @if($errors->has('subject') || $errors->has('body')) open @endif>
            <summary class="panel-head"><h2>Email reply</h2><span class="small muted">Sent through the configured school mailbox</span></summary>
            <div class="panel-body">
                <form method="POST" action="{{ route('admin.admissions.reply', $enquiry) }}" class="admin-fields">
                    @csrf
                    <div class="admin-field"><label for="admission-reply-subject">Subject</label><input id="admission-reply-subject" name="subject" value="{{ old('subject', 'Re: Admission enquiry '.$enquiry->reference_code) }}" maxlength="255" required></div>
                    <div class="admin-field"><label for="admission-reply-body">Message to {{ $enquiry->guardian_name }}</label><textarea id="admission-reply-body" name="body" maxlength="10000" required>{{ old('body') }}</textarea></div>
                    <button class="btn btn-primary btn-sm" type="submit">Queue email reply</button>
                </form>
            </div>
        </details>

        @if($enquiry->emailReplies->isNotEmpty())
            <section class="admin-panel">
                <div class="panel-head"><h2>Email history</h2><span class="small muted">{{ $enquiry->emailReplies->count() }} replies</span></div>
                <div class="panel-body detail-notes">
                    @foreach($enquiry->emailReplies as $reply)
                        <article class="detail-note">
                            <div class="detail-note__mark"><i data-lucide="mail-check" aria-hidden="true"></i></div>
                            <div>
                                <b class="small">{{ $reply->subject }}</b>
                                <span class="status {{ $reply->status === 'sent' ? 'status-published' : ($reply->status === 'failed' ? 'status-new' : 'status-review') }}" style="margin-left:6px">{{ Str::headline($reply->status) }}</span>
                                <p class="small">{{ $reply->body }}</p>
                            </div>
                            <time class="small muted" datetime="{{ ($reply->sent_at ?? $reply->queued_at)?->toAtomString() }}">{{ ($reply->sent_at ?? $reply->queued_at)?->format('d M Y H:i') }}</time>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="admin-panel">
            <div class="panel-head"><h2>Internal notes</h2><span class="small muted">{{ $enquiry->notes->count() }} notes</span></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('admin.admissions.notes.store', $enquiry) }}" class="admin-fields" style="margin-bottom:20px">
                    @csrf
                    <input type="hidden" name="is_sensitive" value="1">
                    <div class="admin-field">
                        <label for="admission-note">Add internal note</label>
                        <textarea id="admission-note" name="note" maxlength="2000" required>{{ old('note') }}</textarea>
                    </div>
                    <button class="btn btn-primary btn-sm" type="submit">Save note</button>
                </form>
                <div class="detail-notes">
                    @forelse($enquiry->notes as $note)
                        <article class="detail-note">
                            <div class="detail-note__mark">{{ Str::upper(Str::substr($note->user?->name ?? 'S', 0, 1)) }}</div>
                            <div>
                                <b class="small">{{ $note->user?->name ?? 'Former staff member' }}</b>
                                <span class="status status-draft" style="margin-left:6px">{{ $note->is_sensitive ? 'Sensitive' : 'Internal' }}</span>
                                <p class="small">{{ $note->note }}</p>
                            </div>
                            <time class="small muted" datetime="{{ $note->created_at?->toAtomString() }}">{{ $note->created_at?->format('d M Y H:i') }}</time>
                        </article>
                    @empty
                        <p class="small muted">No internal notes recorded.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <aside class="admin-detail-stack">
        <section class="admin-panel">
            <div class="panel-head"><h2>Workflow</h2></div>
            <div class="panel-body detail-workflow">
                <form method="POST" action="{{ route('admin.admissions.status', $enquiry) }}">
                    @csrf
                    @method('PATCH')
                    <label for="admission-status">Status</label>
                    <select id="admission-status" name="status">
                        @foreach(['new' => 'New', 'in_progress' => 'In progress', 'responded' => 'Responded', 'closed' => 'Closed'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $enquiry->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary btn-sm" type="submit">Update status</button>
                </form>
                <form method="POST" action="{{ route('admin.admissions.assignment', $enquiry) }}">
                    @csrf
                    @method('PATCH')
                    <label for="admission-assignee">Assigned to</label>
                    <select id="admission-assignee" name="assigned_to">
                        <option value="">Unassigned</option>
                        @foreach($assignees as $assignee)
                            <option value="{{ $assignee->id }}" @selected((int) old('assigned_to', $enquiry->assigned_to) === $assignee->id)>{{ $assignee->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-outline btn-sm" type="submit">Save assignment</button>
                </form>
            </div>
        </section>

        <section class="admin-panel">
            <div class="panel-head"><h2>Response tracking</h2></div>
            <div class="panel-body detail-meta">
                <div class="detail-meta-row"><span>Received</span><b>{{ $enquiry->created_at?->format('d M Y H:i') }}</b></div>
                <div class="detail-meta-row"><span>Assigned to</span><b>{{ $enquiry->assignedTo?->name ?? 'Unassigned' }}</b></div>
                <div class="detail-meta-row"><span>Responded</span><b>{{ $enquiry->responded_at?->format('d M Y H:i') ?? 'Pending' }}</b></div>
                <div class="detail-meta-row"><span>Closed</span><b>{{ $enquiry->closed_at?->format('d M Y H:i') ?? 'Open' }}</b></div>
            </div>
        </section>
    </aside>
</div>
@endsection
