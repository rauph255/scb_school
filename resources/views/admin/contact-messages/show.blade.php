@extends('admin.layout')

@section('title', 'Contact message '.$message->reference_code)

@section('content')
@php
    $statusClass = match ($message->status) {
        'responded' => 'status-published',
        'assigned' => 'status-review',
        'new' => 'status-new',
        'closed', 'spam' => 'status-archived',
        default => 'status-draft',
    };
@endphp

<div class="admin-page-head">
    <div>
        <div class="eyebrow">Private contact record</div>
        <h1>{{ $message->subject }}</h1>
        <p class="muted">{{ $message->reference_code }} &middot; received {{ $message->created_at?->format('d M Y H:i') }}</p>
    </div>
    <div class="admin-actions">
        <a class="btn btn-outline btn-sm" href="{{ route('admin.contact-messages') }}">Back to messages</a>
        <button class="btn btn-primary btn-sm" type="button" data-toggle-details="contact-email-reply">Reply by email</button>
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
            <div class="panel-head"><h2>Sender details</h2><span class="status {{ $statusClass }}">{{ Str::headline($message->status) }}</span></div>
            <div class="panel-body">
                <dl class="detail-pairs">
                    <div class="detail-pair"><dt>Full name</dt><dd>{{ $message->full_name }}</dd></div>
                    <div class="detail-pair"><dt>Reference</dt><dd>{{ $message->reference_code }}</dd></div>
                    <div class="detail-pair"><dt>Email</dt><dd><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></dd></div>
                    <div class="detail-pair"><dt>Telephone</dt><dd>@if($message->telephone)<a href="tel:{{ $message->telephone }}">{{ $message->telephone }}</a>@else Not supplied @endif</dd></div>
                    <div class="detail-pair"><dt>Subject</dt><dd>{{ $message->subject }}</dd></div>
                    <div class="detail-pair"><dt>Privacy consent</dt><dd>{{ $message->consent_confirmed ? 'Confirmed' : 'Not confirmed' }}</dd></div>
                </dl>
            </div>
        </section>

        <section class="admin-panel">
            <div class="panel-head"><h2>Message</h2></div>
            <div class="panel-body"><p class="detail-copy">{{ $message->message }}</p></div>
        </section>

        <details class="admin-panel" id="contact-email-reply" @if($errors->has('subject') || $errors->has('body')) open @endif>
            <summary class="panel-head"><h2>Email reply</h2><span class="small muted">Sent through the configured school mailbox</span></summary>
            <div class="panel-body">
                <form method="POST" action="{{ route('admin.contact-messages.reply', $message) }}" class="admin-fields">
                    @csrf
                    <div class="admin-field"><label for="contact-reply-subject">Subject</label><input id="contact-reply-subject" name="subject" value="{{ old('subject', 'Re: '.$message->subject) }}" maxlength="255" required></div>
                    <div class="admin-field"><label for="contact-reply-body">Message to {{ $message->full_name }}</label><textarea id="contact-reply-body" name="body" maxlength="10000" required>{{ old('body') }}</textarea></div>
                    <button class="btn btn-primary btn-sm" type="submit">Queue email reply</button>
                </form>
            </div>
        </details>

        @if($message->emailReplies->isNotEmpty())
            <section class="admin-panel">
                <div class="panel-head"><h2>Email history</h2><span class="small muted">{{ $message->emailReplies->count() }} replies</span></div>
                <div class="panel-body detail-notes">
                    @foreach($message->emailReplies as $reply)
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
            <div class="panel-head"><h2>Internal notes</h2><span class="small muted">{{ $message->notes->count() }} notes</span></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('admin.contact-messages.notes.store', $message) }}" class="admin-fields" style="margin-bottom:20px">
                    @csrf
                    <input type="hidden" name="is_sensitive" value="1">
                    <div class="admin-field">
                        <label for="contact-note">Add internal note</label>
                        <textarea id="contact-note" name="note" maxlength="2000" required>{{ old('note') }}</textarea>
                    </div>
                    <button class="btn btn-primary btn-sm" type="submit">Save note</button>
                </form>
                <div class="detail-notes">
                    @forelse($message->notes as $note)
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
                <form method="POST" action="{{ route('admin.contact-messages.status', $message) }}">
                    @csrf
                    @method('PATCH')
                    <label for="contact-status">Status</label>
                    <select id="contact-status" name="status">
                        @foreach(['new' => 'New', 'assigned' => 'Assigned', 'responded' => 'Responded', 'closed' => 'Closed', 'spam' => 'Spam'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $message->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary btn-sm" type="submit">Update status</button>
                </form>
                <form method="POST" action="{{ route('admin.contact-messages.assignment', $message) }}">
                    @csrf
                    @method('PATCH')
                    <label for="contact-assignee">Assigned to</label>
                    <select id="contact-assignee" name="assigned_to">
                        <option value="">Unassigned</option>
                        @foreach($assignees as $assignee)
                            <option value="{{ $assignee->id }}" @selected((int) old('assigned_to', $message->assigned_to) === $assignee->id)>{{ $assignee->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-outline btn-sm" type="submit">Save assignment</button>
                </form>
            </div>
        </section>

        <section class="admin-panel">
            <div class="panel-head"><h2>Response tracking</h2></div>
            <div class="panel-body detail-meta">
                <div class="detail-meta-row"><span>Received</span><b>{{ $message->created_at?->format('d M Y H:i') }}</b></div>
                <div class="detail-meta-row"><span>Assigned to</span><b>{{ $message->assignedTo?->name ?? 'Unassigned' }}</b></div>
                <div class="detail-meta-row"><span>Responded</span><b>{{ $message->responded_at?->format('d M Y H:i') ?? 'Pending' }}</b></div>
                <div class="detail-meta-row"><span>Closed</span><b>{{ $message->closed_at?->format('d M Y H:i') ?? 'Open' }}</b></div>
            </div>
        </section>
    </aside>
</div>
@endsection
