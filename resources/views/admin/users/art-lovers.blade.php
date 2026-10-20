@extends('admin.layout')

@section('title', 'All Art Lovers | Artfy Admin')

@section('page_eyebrow', 'Artfy / User Management')

@section('page_title', 'All Art Lovers')

@section('page_intro', 'View registered art lovers and their account information. Open a user’s details to inspect their account.')

@push('styles')
<style>
    .art-lovers-page {
        width: 100%;
    }

    .art-lovers-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        padding: 22px 24px;
        margin-bottom: 26px;
        background: var(--cream, #efede4);
        border: 1px solid var(--border, #e5e1d7);
        border-radius: 14px;
    }

    .summary-label {
        color: var(--muted, #77786e);
        font-size: 13px;
        line-height: 1.6;
    }

    .summary-count {
        display: block;
        margin: 4px 0;
        color: var(--dark-olive, #414c2c);
        font-family: Georgia, serif;
        font-size: 32px;
        line-height: 1.2;
    }

    .summary-note {
        color: var(--muted, #77786e);
        font-size: 12px;
        line-height: 1.6;
    }

    .art-lovers-table-wrap {
        width: 100%;
        overflow-x: auto;
        background: var(--white, #fff);
        border: 1px solid var(--border, #e5e1d7);
        border-radius: 14px;
    }

    .art-lovers-table {
        width: 100%;
        min-width: 680px;
        border-collapse: collapse;
    }

    .art-lovers-table th,
    .art-lovers-table td {
        padding: 16px 18px;
        text-align: left;
        vertical-align: middle;
        border-bottom: 1px solid var(--border, #e5e1d7);
        font-size: 13px;
    }

    .art-lovers-table th {
        color: var(--dark-olive, #414c2c);
        background: #eeeddf;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .7px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .art-lovers-table tbody tr {
        transition: background .18s ease;
    }

    .art-lovers-table tbody tr:hover {
        background: #fbfaf7;
    }

    .art-lovers-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .user-number {
        color: var(--muted, #77786e);
        font-variant-numeric: tabular-nums;
    }

    .user-name {
        margin-bottom: 5px;
        color: var(--text, #28291f);
        font-size: 14px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .user-id {
        color: var(--muted, #77786e);
        font-size: 11px;
    }

    .user-email {
        color: var(--text, #28291f);
        overflow-wrap: anywhere;
        line-height: 1.6;
    }

    .registration-date {
        color: var(--muted, #77786e);
        white-space: nowrap;
    }

    .view-details-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 14px;
        color: white;
        background: var(--olive, #55613a);
        border: 1px solid var(--olive, #55613a);
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: background .2s ease, border-color .2s ease;
    }

    .view-details-button:hover {
        color: white;
        background: var(--dark-olive, #414c2c);
        border-color: var(--dark-olive, #414c2c);
    }

    .view-details-button:focus-visible {
        outline: 3px solid #a9b18f;
        outline-offset: 3px;
    }

    .art-lovers-empty {
        padding: 48px 24px;
        text-align: center;
    }

    .empty-icon {
        display: grid;
        place-items: center;
        width: 58px;
        height: 58px;
        margin: 0 auto 16px;
        color: var(--olive, #55613a);
        background: var(--cream, #efede4);
        border-radius: 50%;
        font-size: 26px;
    }

    .art-lovers-empty h2 {
        margin: 0 0 10px;
        color: var(--dark-olive, #414c2c);
        font-family: Georgia, serif;
        font-size: 25px;
        font-weight: normal;
    }

    .art-lovers-empty p {
        max-width: 430px;
        margin: 0 auto;
        color: var(--muted, #77786e);
        font-size: 13px;
        line-height: 1.8;
    }

    .art-lovers-pagination {
        margin-top: 26px;
        overflow-x: auto;
    }

    .art-lovers-pagination nav {
        display: flex;
        justify-content: center;
    }

    .art-lovers-footer {
        margin-top: 34px;
        padding-top: 18px;
        color: var(--muted, #77786e);
        border-top: 1px solid var(--border, #e5e1d7);
        font-size: 12px;
        line-height: 1.7;
    }

    @media (max-width: 640px) {
        .art-lovers-summary {
            padding: 18px;
        }

        .art-lovers-table th,
        .art-lovers-table td {
            padding: 13px;
        }
    }
</style>
@endpush

@section('content')
<div class="art-lovers-page">

    {{-- Registration summary --}}
    <section class="art-lovers-summary">
        <div>
            <div class="summary-label">
                Total registered art lovers
            </div>

            <strong class="summary-count">
                {{ $artLovers->total() }}
            </strong>

            <div class="summary-note">
                {{ $artLovers->total() === 1
                    ? 'Art lover account registered'
                    : 'Art lover accounts registered' }}
            </div>
        </div>

        <div class="summary-note">
            Showing {{ $artLovers->count() }}
            {{ $artLovers->count() === 1 ? 'account' : 'accounts' }}
            on this page
        </div>
    </section>

    {{-- Art lovers table --}}
    <div class="art-lovers-table-wrap">
        @if ($artLovers->isNotEmpty())
            <table class="art-lovers-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Art Lover</th>
                        <th>Email Address</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($artLovers as $artLover)
                        <tr>
                            <td class="user-number">
                                {{ $artLovers->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="user-name">
                                    {{ $artLover->name }}
                                </div>

                                <div class="user-id">
                                    User ID: {{ $artLover->id }}
                                </div>
                            </td>

                            <td class="user-email">
                                {{ $artLover->email }}
                            </td>

                            <td class="registration-date">
                                {{ $artLover->created_at
                                    ? $artLover->created_at->format('d M Y')
                                    : 'Unavailable' }}
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin.art-lovers.show', $artLover) }}"
                                    class="view-details-button"
                                >
                                    View Details
                                    <span aria-hidden="true">→</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="art-lovers-empty">
                <div class="empty-icon" aria-hidden="true">♡</div>

                <h2>No Art Lovers Found</h2>

                <p>
                    There are currently no registered art-lover accounts.
                    Once users register as art lovers, their accounts will
                    appear in this directory.
                </p>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if ($artLovers->hasPages())
        <div class="art-lovers-pagination">
            {{ $artLovers->links() }}
        </div>
    @endif

    <footer class="art-lovers-footer">
        ARTFY Administration · Art Lover Directory
    </footer>
</div>
@endsection
