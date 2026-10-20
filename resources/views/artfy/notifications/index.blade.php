@extends('artfy.layout')

@section('content')
<div class="notifications-page">
    <div class="notifications-heading">
        <span class="notifications-eyebrow">YOUR ART COMMUNITY</span>
        <h1>Notifications</h1>
        <p>Stay up to date with your Artfy activity.</p>
    </div>

    @if ($notifications->count() > 0)
        <div class="notifications-list">
            @foreach ($notifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $isUnread = $notification->read_at === null;
                @endphp

                <a href="{{ route('notifications.open', ['notification' => $notification->id]) }}"
                   @class([
                       'notification-item',
                       'notification-unread' => $isUnread,
                       'notification-read' => !$isUnread,
                   ])>

                    <span class="notification-status-dot"
                          aria-hidden="true"></span>

                    <span class="notification-item-content">
                        <span class="notification-item-title">
                            {{ $data['title'] ?? 'Artfy notification' }}
                        </span>

                        <span class="notification-item-message">
                            {{ $data['message'] ?? 'You have an update from Artfy.' }}
                        </span>

                        <span class="notification-item-time">
                            {{ $notification->created_at?->diffForHumans() ?? '' }}
                        </span>
                    </span>

                    <span class="notification-item-arrow" aria-hidden="true">
                        →
                    </span>
                </a>
            @endforeach
        </div>

        <div class="notifications-pagination">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="notifications-empty">
            <div class="notifications-empty-icon" aria-hidden="true">♧</div>
            <h2>You're all caught up</h2>
            <p>When there's something new, you'll find it here.</p>
            <a href="{{ route('home') }}" class="button button-dark">
                Explore Art
            </a>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .notifications-page {
        width: min(900px, calc(100% - 40px));
        margin: 65px auto 90px;
        color: #414c2c;
    }

    .notifications-heading {
        margin-bottom: 32px;
    }

    .notifications-eyebrow {
        color: #72795f;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 2px;
    }

    .notifications-heading h1 {
        margin: 12px 0;
        font-family: "Playfair Display", serif;
        font-size: clamp(32px, 5vw, 46px);
        font-weight: 600;
    }

    .notifications-heading p {
        margin: 0;
        color: #72795f;
        font-size: 15px;
    }

    .notifications-list {
        overflow: hidden;
        border: 1px solid #e7e4d9;
        border-radius: 14px;
        background: #fffefa;
    }

    .notification-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 22px 24px;
        color: inherit;
        text-decoration: none;
        border-bottom: 1px solid #ece9df;
        transition: background-color 0.2s ease;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item:hover {
        background: #f5f3e9;
    }

    .notification-status-dot {
        width: 9px;
        height: 9px;
        flex: 0 0 9px;
        border-radius: 50%;
        background: transparent;
    }

    .notification-unread {
        background: #f6f4e9;
    }

    .notification-unread .notification-status-dot {
        background: #b74f37;
    }

    .notification-unread .notification-item-title {
        color: #303923;
        font-weight: 700;
    }

    .notification-read .notification-status-dot {
        border: 1px solid #c9c8bc;
    }

    .notification-read .notification-item-title {
        color: #656a5b;
        font-weight: 500;
    }

    .notification-item-content {
        display: flex;
        flex: 1;
        min-width: 0;
        flex-direction: column;
        gap: 6px;
    }

    .notification-item-title {
        font-size: 15px;
        line-height: 1.5;
    }

    .notification-item-message {
        color: #727568;
        font-size: 13px;
        font-weight: 400;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .notification-item-time {
        color: #929386;
        font-size: 11px;
    }

    .notification-item-arrow {
        flex-shrink: 0;
        color: #72795f;
        font-size: 22px;
    }

    .notifications-empty {
        padding: 65px 20px;
        text-align: center;
        border: 1px solid #e7e4d9;
        border-radius: 14px;
        background: #fffefa;
    }

    .notifications-empty-icon {
        margin-bottom: 14px;
        color: #72795f;
        font-size: 36px;
    }

    .notifications-empty h2 {
        margin: 0 0 10px;
        font-family: "Playfair Display", serif;
        font-size: 25px;
    }

    .notifications-empty p {
        margin: 0 0 24px;
        color: #727568;
        font-size: 14px;
    }

    .notifications-pagination {
        margin-top: 24px;
    }

    @media (max-width: 600px) {
        .notifications-page {
            margin-top: 38px;
        }

        .notification-item {
            gap: 11px;
            padding: 18px 14px;
        }

        .notification-item-title {
            font-size: 14px;
        }

        .notification-item-message {
            font-size: 12px;
        }
    }
</style>
@endpush
