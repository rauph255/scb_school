@extends('admin.layout')

@section('title', 'Audit entry '.$auditLog->id)

@section('content')
<div class="admin-page-head">
    <div>
        <div class="eyebrow">Privileged activity record</div>
        <h1>{{ Str::headline($auditLog->action) }}</h1>
        <p class="muted">Entry #{{ $auditLog->id }} &middot; {{ $auditLog->created_at?->format('d M Y H:i:s') }}</p>
    </div>
    <div class="admin-actions">
        <a class="btn btn-outline btn-sm" href="{{ route('admin.audit-log') }}">Back to audit log</a>
        <a class="btn btn-primary btn-sm" href="{{ route('admin.audit-log.export') }}">Download CSV</a>
    </div>
</div>

<div class="admin-form-grid">
    <div class="admin-detail-stack">
        <section class="admin-panel">
            <div class="panel-head"><h2>Activity</h2><span class="status status-review">Immutable log</span></div>
            <div class="panel-body">
                <p class="detail-copy">{{ $auditLog->description ?: 'No description was recorded.' }}</p>
                <dl class="detail-pairs" style="margin-top:18px">
                    <div class="detail-pair"><dt>Action</dt><dd>{{ $auditLog->action }}</dd></div>
                    <div class="detail-pair"><dt>Actor</dt><dd>{{ $auditLog->actor?->name ?? 'System process' }}</dd></div>
                    <div class="detail-pair"><dt>Subject type</dt><dd>{{ class_basename((string) ($auditLog->subject_type ?? 'System')) }}</dd></div>
                    <div class="detail-pair"><dt>Subject ID</dt><dd>{{ $auditLog->subject_id ?? 'Not applicable' }}</dd></div>
                    <div class="detail-pair"><dt>IP address</dt><dd>{{ $auditLog->ip_address ?? 'Not recorded' }}</dd></div>
                    <div class="detail-pair"><dt>Request ID</dt><dd>{{ $auditLog->request_id ?? 'Not recorded' }}</dd></div>
                </dl>
            </div>
        </section>

        <section class="admin-panel">
            <div class="panel-head"><h2>Recorded changes</h2></div>
            <div class="panel-body admin-fields">
                <div class="admin-field">
                    <label>Before</label>
                    <pre style="white-space:pre-wrap;overflow-wrap:anywhere;margin:0;padding:14px;border:1px solid #E5DFD9;border-radius:8px;background:#FAF8F6">{{ json_encode($auditLog->old_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
                <div class="admin-field">
                    <label>After</label>
                    <pre style="white-space:pre-wrap;overflow-wrap:anywhere;margin:0;padding:14px;border:1px solid #E5DFD9;border-radius:8px;background:#FAF8F6">{{ json_encode($auditLog->new_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </section>
    </div>

    <aside class="admin-detail-stack">
        <section class="admin-panel">
            <div class="panel-head"><h2>Request context</h2></div>
            <div class="panel-body detail-meta">
                <div class="detail-meta-row"><span>Recorded</span><b>{{ $auditLog->created_at?->format('d M Y H:i:s') }}</b></div>
                <div class="detail-meta-row"><span>Actor email</span><b>{{ $auditLog->actor?->email ?? 'System' }}</b></div>
                <div class="detail-meta-row"><span>User agent</span><b style="overflow-wrap:anywhere;text-align:right">{{ $auditLog->user_agent ?? 'Not recorded' }}</b></div>
            </div>
        </section>
    </aside>
</div>
@endsection
