<details class="admin-notifications" data-admin-alerts data-admin-alert-count="{{ $adminAlerts['count'] }}">
    <summary aria-label="{{ $adminAlerts['count'] > 0 ? $adminAlerts['count'].' unread enquiries and messages' : 'No unread enquiries or messages' }}">
        <i data-lucide="bell" aria-hidden="true"></i>
        @if($adminAlerts['count'] > 0)
            <span class="admin-notifications__badge">{{ $adminAlerts['count'] > 99 ? '99+' : $adminAlerts['count'] }}</span>
        @endif
    </summary>
    <div class="admin-notifications__panel">
        <div class="admin-notifications__head">
            <div>
                <b>Inbox alerts</b>
                <span>{{ $adminAlerts['count'] }} unread</span>
            </div>
            <i data-lucide="inbox" aria-hidden="true"></i>
        </div>
        <div class="admin-notifications__list">
            @forelse($adminAlerts['items'] as $alert)
                <a href="{{ $alert['url'] }}" class="admin-notification" data-admin-alert="{{ $alert['id'] }}">
                    <span class="admin-notification__icon admin-notification__icon--{{ $alert['tone'] }}">
                        <i data-lucide="{{ $alert['icon'] }}" aria-hidden="true"></i>
                    </span>
                    <span>
                        <small>{{ $alert['type'] }} · {{ $alert['created_at']->diffForHumans() }}</small>
                        <b>{{ $alert['title'] }}</b>
                        <span>{{ $alert['detail'] }}</span>
                    </span>
                    <i data-lucide="chevron-right" aria-hidden="true"></i>
                </a>
            @empty
                <div class="admin-notifications__empty">
                    <i data-lucide="check-circle-2" aria-hidden="true"></i>
                    <b>You are all caught up</b>
                    <span>New enquiries and messages will appear here.</span>
                </div>
            @endforelse
        </div>
    </div>
</details>
