@extends('admin.layout')

@section('title', 'Notifications | Artfy Admin')

@section('page_eyebrow', 'Artfy / Administration')

@section('page_title', 'Notifications')

@section('page_intro', 'Review verified artwork sales and important platform activity.')

@push('styles')
<style>
    .notification-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 20px;
    }

    .notification-section-title {
        margin: 0;
        color: var(--text);
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 23px;
        font-weight: 600;
    }

    .notification-unread-count {
        padding: 9px 13px;
        border-radius: 30px;
        background: #e5e8d7;
        color: var(--dark-olive);
        font-size: 12px;
        font-weight: 700;
    }

    .notification-list {
        display: grid;
        gap: 14px;
    }

    .notification-card {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 22px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--white);
        color: inherit;
        text-decoration: none;
        transition: border-color .2s, background .2s;
    }

    .notification-card:hover {
        border-color: #aeb69a;
        background: #faf9f5;
    }

    .notification-card.unread {
        border-left: 4px solid var(--olive);
        background: #fffefa;
    }

    .notification-card.unread:hover {
        background: #faf9f1;
    }

    .notification-icon {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: var(--cream);
        color: var(--olive);
        font-size: 22px;
        font-weight: 600;
    }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-title {
        margin: 1px 0 8px;
        color: var(--text);
        font-size: 15px;
        font-weight: 700;
        line-height: 1.5;
    }

    .notification-message {
        margin: 0;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.8;
        overflow-wrap: anywhere;
    }

    .notification-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 9px;
        margin-top: 13px;
        color: var(--muted);
        font-size: 11px;
    }

    .notification-status {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        background: #efede4;
        color: var(--muted);
        font-weight: 700;
    }

    .notification-status.unread {
        background: #e5e8d7;
        color: var(--dark-olive);
    }

    .notification-open-hint {
        align-self: center;
        flex-shrink: 0;
        color: var(--olive);
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .notification-empty {
        padding: 48px 24px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--white);
        text-align: center;
    }

    .notification-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 62px;
        height: 62px;
        margin: 0 auto 16px;
        border-radius: 50%;
        background: var(--cream);
        color: var(--olive);
        font-size: 29px;
    }

    .notification-empty h2 {
        margin: 0 0 10px;
        color: var(--text);
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 23px;
        font-weight: 600;
    }

    .notification-empty p {
        max-width: 460px;
        margin: 0 auto;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.8;
    }

    .notification-pagination {
        margin-top: 20px;
        padding: 16px;
        overflow-x: auto;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: var(--white);
    }

    @media (max-width: 600px) {
        .notification-card {
            gap: 12px;
            padding: 16px;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
        }

        .notification-open-hint {
            display: none;
        }

        .notification-empty {
            padding: 36px 18px;
        }
    }
</style>
@endpush

@section('content')

    @php
        $unreadCount = auth()->user()
            ->unreadNotifications()
            ->count();
    @endphp

    <div class="notification-section-header">
        <h2 class="notification-section-title">Your Notifications</h2>

        <span class="notification-unread-count">
            {{ $unreadCount }} unread
        </span>
    </div>

    @if ($notifications->count())

        <div class="notification-list">

            @foreach ($notifications as $notification)

                @php
                    $data = $notification->data ?? [];

                    $isCommissionNotification =
                        $notification->type === 'artfy.commission_earned';
                @endphp

                <a
                    href="{{ route('notifications.open', $notification->id) }}"
                    class="notification-card {{ $notification->read_at ? '' : 'unread' }}"
                >
                    <div class="notification-icon" aria-hidden="true">
                        {{ $isCommissionNotification ? '৳' : '♧' }}
                    </div>

                    <div class="notification-content">

                        <h3 class="notification-title">
                            {{ $data['title'] ?? 'Artfy notification' }}
                        </h3>

                        <p class="notification-message">
                            {{ $data['message'] ?? 'You have a new notification.' }}
                        </p>

                        <div class="notification-meta">

                            <span>
                                {{ $notification->created_at
                                    ? $notification->created_at->format('d M Y, h:i A')
                                    : 'Date unavailable' }}
                            </span>

                            <span class="notification-status {{ $notification->read_at ? '' : 'unread' }}">
                                {{ $notification->read_at ? 'Read' : 'Unread' }}
                            </span>

                            @if (!empty($data['order_number']))
                                <span>
                                    Order: {{ $data['order_number'] }}
                                </span>
                            @endif

                        </div>
                    </div>

                    <span class="notification-open-hint">Open →</span>
                </a>

            @endforeach

        </div>

        @if ($notifications->hasPages())
            <div class="notification-pagination">
                {{ $notifications->links() }}
            </div>
        @endif

    @else

        <div class="notification-empty">
            <div class="notification-empty-icon" aria-hidden="true">♧</div>

            <h2>You're all caught up</h2>

            <p>
                There are no notifications yet. When an artwork purchase
                is successfully verified, its notification will appear here.
            </p>
        </div>

    @endif

@endsection
