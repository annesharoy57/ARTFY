@extends('admin.layout')

@section('title', 'Exhibition Applications | ARTFY Admin')
@section('page_eyebrow', 'Artfy / Exhibition Management')
@section('page_title', 'Applications & awards')
@section('page_intro', 'Review artist registrations, payment status, submitted artwork, and prize settlement.')

@section('content')
    <div class="application-toolbar">
        <a class="back-link" href="{{ route('admin.exhibitions.index') }}"><span aria-hidden="true">&larr;</span> All exhibitions</a>
        @if ($exhibition->status === 'published')
            <a class="preview-link" href="{{ route('exhibitions.show', $exhibition->slug) }}" target="_blank" rel="noopener">Open public exhibition <span aria-hidden="true">&nearr;</span></a>
        @endif
    </div>

    <section class="exhibition-context">
        <div class="context-art" aria-hidden="true">
            @if ($exhibition->cover_image)
                <img src="{{ asset('storage/' . ltrim($exhibition->cover_image, '/')) }}" alt="">
            @else
                <span>ARTFY<br>EXHIBITION</span>
            @endif
        </div>
        <div class="context-copy">
            <span class="context-kicker">EXHIBITION OVERVIEW</span>
            <h2>{{ $exhibition->title }}</h2>
            <p>{{ $exhibition->starts_at->format('M j, Y') }} <span aria-hidden="true">&rarr;</span> {{ $exhibition->ends_at->format('M j, Y') }}</p>
        </div>
        <span class="status-pill status-{{ str_replace('_', '-', $exhibition->status) }}">{{ ucfirst($exhibition->status) }}</span>
    </section>

    @if (session('success'))
        <p class="exhibition-admin-message success" role="status">{{ session('success') }}</p>
    @endif
    @if (session('error'))
        <p class="exhibition-admin-message error" role="alert">{{ session('error') }}</p>
    @endif

    <section class="application-metrics" aria-label="Exhibition registration summary">
        <article class="metric-card metric-primary">
            <span class="metric-icon" aria-hidden="true">BDT</span>
            <span class="metric-label">Verified registration fees</span>
            <strong>BDT {{ number_format((float) $confirmedIncome, 2) }}</strong>
            <small>Payments verified for this exhibition</small>
        </article>
        <article class="metric-card">
            <span class="metric-icon" aria-hidden="true">01</span>
            <span class="metric-label">Artist places used</span>
            <strong>{{ $enrolledCount }} <small>/ {{ $exhibition->max_artists }}</small></strong>
            <small>{{ max(0, $exhibition->max_artists - $enrolledCount) }} places remaining</small>
        </article>
        <article class="metric-card">
            <span class="metric-icon" aria-hidden="true">&#8230;</span>
            <span class="metric-label">Active checkout payments</span>
            <strong>{{ $pendingPaymentCount }}</strong>
            <small>Not counted until payment is verified</small>
        </article>
    </section>

    <section class="awards-panel">
        <div class="awards-copy">
            <span class="section-kicker">RESULTS & SETTLEMENT</span>
            <h2>Voting and awards</h2>
            <p>Final points combine community votes with all co-admin scores. Tied artists share the prize amounts for their places.</p>
        </div>

        @if ($settlement)
            <div class="settlement-grid">
                <div><span>Verified fees</span><strong>BDT {{ number_format((float) $settlement->total_fees, 2) }}</strong></div>
                <div><span>Artfy commission · 20%</span><strong>BDT {{ number_format((float) $settlement->admin_fund_amount, 2) }}</strong></div>
                <div><span>Artist prizes credited</span><strong>BDT {{ number_format((float) $prizeCreditsTotal, 2) }}</strong></div>
                <div><span>Unallocated prize amount</span><strong>BDT {{ number_format((float) $settlement->unallocated_prize_amount, 2) }}</strong></div>
            </div>
            <p class="settlement-note">Awards finalized {{ $settlement->settled_at->format('M j, Y · g:i A') }}</p>
        @elseif ($exhibition->status === 'published' && $exhibition->visitHasEnded())
            <div class="finalize-box">
                <div><strong>Ready to finalize the results?</strong><p>This credits the three winning artists and records Artfy’s commission.</p></div>
                <form method="POST" action="{{ route('admin.exhibitions.finalize-awards', $exhibition) }}" onsubmit="return confirm('Finalize this exhibition and credit prize money to the winning artists? This cannot be undone.')">
                    @csrf
                    <button type="submit">Finalize winners</button>
                </form>
            </div>
        @else
            <div class="settlement-waiting"><span class="waiting-dot" aria-hidden="true"></span> Prize and commission settlement becomes available after the exhibition closes.</div>
        @endif
    </section>

    <section class="applications-section">
        <div class="applications-heading">
            <div><span class="section-kicker">REGISTRATION REVIEW</span><h2>Artist applications</h2></div>
            <span class="application-count">{{ $applications->total() }} total</span>
        </div>

        <div class="application-list">
            @forelse ($applications as $application)
                @php
                    $artistName = $application->artist?->artistProfile?->artist_name
                        ?: $application->artist?->name
                        ?: 'Artist account';
                    $statusClass = str_replace('_', '-', $application->status);
                    $paymentLabel = $application->payment_status === 'verified'
                        ? 'Paid'
                        : ucfirst(str_replace('_', ' ', $application->payment_status));
                @endphp
                <article class="application-card">
                    <header class="application-card-head">
                        <div class="artist-identity">
                            @if ($application->artist?->artistProfile?->profile_photo)
                                <img src="{{ asset('storage/' . ltrim($application->artist->artistProfile->profile_photo, '/')) }}" alt="{{ $artistName }}" loading="lazy">
                            @else
                                <span class="artist-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($artistName, 0, 1)) }}</span>
                            @endif
                            <div><h3>{{ $artistName }}</h3><a href="mailto:{{ $application->artist?->email }}">{{ $application->artist?->email ?: 'No email available' }}</a></div>
                        </div>
                        <div class="application-badges">
                            <span class="status-pill status-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                            <span class="payment-pill payment-{{ $application->payment_status === 'verified' ? 'verified' : 'pending' }}"><span aria-hidden="true"></span>{{ $paymentLabel }}</span>
                        </div>
                    </header>

                    <div class="application-details">
                        <div class="application-facts">
                            <div><span>Registration fee</span><strong>BDT {{ number_format((float) $application->registration_fee, 2) }}</strong></div>
                            <div><span>Payment reference</span><code>{{ $application->payment_reference ?: 'Not assigned' }}</code></div>
                            @if ($application->payment_verified_at)
                                <div><span>Payment verified</span><strong>{{ $application->payment_verified_at->format('M j, Y · g:i A') }}</strong></div>
                            @endif
                        </div>

                        @if ($application->status === 'pending')
                            <p class="application-note">
                                {{ $application->isActivePaymentReservation()
                                    ? 'Checkout is active. This application does not use an artist place until payment is verified.'
                                    : 'Checkout expired without payment. The artist can retry while registration is open.' }}
                            </p>
                        @endif

                        @if ($application->artwork_title)
                            <div class="submitted-artwork">
                                <div class="submitted-artwork-image">
                                    @if ($application->artwork_image)
                                        <img src="{{ asset('storage/' . ltrim($application->artwork_image, '/')) }}" alt="{{ $application->artwork_title }} by {{ $artistName }}" loading="lazy">
                                    @else
                                        <span>Artwork image not submitted</span>
                                    @endif
                                </div>
                                <div class="submitted-artwork-copy">
                                    <span class="section-kicker">SUBMITTED ARTWORK</span>
                                    <h4>{{ $application->artwork_title }}</h4>
                                    <p>{{ $application->artwork_description }}</p>
                                    <div class="artwork-admin-footer"><span>Asking price <strong>BDT {{ number_format((float) $application->artwork_price, 2) }}</strong></span>
                                        @if ($exhibition->visitHasEnded())
                                            <span>Points <strong>{{ $application->votes_count + (int) ($application->scores_sum_score ?? 0) }}</strong> <small>({{ $application->votes_count }} votes + {{ (int) ($application->scores_sum_score ?? 0) }} co-admin)</small></span>
                                        @endif
                                    </div>
                                    @if ($settlement && $prizeCredits->has($application->id))
                                        <p class="prize-credit">Prize credited: <strong>BDT {{ number_format((float) $prizeCredits->get($application->id)->amount, 2) }}</strong></p>
                                    @endif
                                </div>
                            </div>
                        @elseif ($application->status === 'capacity_review')
                            <p class="application-note note-warning">Payment verified, but no artist place remained when it completed. This registration needs support follow-up.</p>
                        @elseif ($application->payment_status === 'verified')
                            <p class="application-note">Payment is verified. The artist still needs to submit their artwork.</p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="applications-empty"><span aria-hidden="true">&#9675;</span><h3>No applications yet</h3><p>Artist registrations will appear here when they apply to this exhibition.</p></div>
            @endforelse
        </div>

        @if ($applications->hasPages())
            <div class="application-pagination">{{ $applications->links() }}</div>
        @endif
    </section>

    <style>
        .application-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:-8px 0 18px}
        .back-link,.preview-link{display:inline-flex;align-items:center;gap:8px;font-size:.84rem;font-weight:700;text-decoration:none}
        .back-link{color:#687253}.preview-link{padding:10px 14px;border:1px solid #d9ddca;border-radius:999px;background:#fffefa;color:#536142}
        .exhibition-context{display:flex;align-items:center;gap:18px;margin:0 0 22px;padding:17px;border:1px solid #e5e1d3;border-radius:18px;background:linear-gradient(110deg,#fffef9,#f1f0e6);box-shadow:0 10px 28px rgba(65,76,44,.06)}
        .context-art{display:grid;width:70px;height:82px;flex:0 0 70px;place-items:center;overflow:hidden;border:1px solid #e2dfd0;border-radius:10px;background:radial-gradient(circle at 80% 12%,#eee2c3,transparent 40%),linear-gradient(145deg,#d9ddc7,#faf8ed);color:#66714d;text-align:center;font-size:.58rem;font-weight:800;letter-spacing:.12em;line-height:1.6}
        .context-art img{width:100%;height:100%;object-fit:cover}.context-copy{min-width:0;flex:1}.context-kicker,.section-kicker{color:#8a8d6a;font-size:.65rem;font-weight:800;letter-spacing:.17em}
        .context-copy h2{margin:4px 0;color:#3e4b33;font:400 clamp(1.35rem,3vw,2rem)/1.15 Georgia,serif}.context-copy p{margin:0;color:#777b67;font-size:.83rem}
        .status-pill{display:inline-flex;align-items:center;min-height:28px;padding:5px 10px;border:1px solid #d7ddca;border-radius:999px;background:#eef1e6;color:#5d6b47;font-size:.67rem;font-weight:800;letter-spacing:.03em;text-transform:capitalize;white-space:nowrap}
        .status-pill.status-draft,.status-pill.status-pending,.status-pill.status-awaiting-submission{border-color:#eadcb7;background:#fff5dd;color:#82692d}.status-pill.status-capacity-review,.status-pill.status-cancelled{border-color:#edcfca;background:#fff1ef;color:#9c5046}
        .exhibition-admin-message{margin:0 0 16px;padding:13px 16px;border:1px solid;border-radius:12px;font-size:.87rem;line-height:1.55}.exhibition-admin-message.success{border-color:#cfe0c8;background:#f0f7eb;color:#38633a}.exhibition-admin-message.error{border-color:#ebc9c4;background:#fff2f0;color:#99483e}
        .application-metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin:0 0 22px}
        .metric-card{position:relative;display:grid;min-height:154px;align-content:start;gap:7px;padding:20px;border:1px solid #e5e1d3;border-radius:16px;background:#fffefa;box-shadow:0 10px 26px rgba(65,76,44,.05)}
        .metric-card.metric-primary{border-color:#d9dfc8;background:linear-gradient(145deg,#f4f5ea,#fffefa)}.metric-icon{position:absolute;top:18px;right:18px;display:grid;min-width:32px;height:32px;place-items:center;padding:0 6px;border-radius:50%;background:#edf0e4;color:#6b7653;font-size:.58rem;font-weight:900}
        .metric-label{color:#737963;font-size:.78rem;font-weight:600}.metric-card>strong{color:#3e4b33;font:500 clamp(1.45rem,2.3vw,2rem)/1.2 Georgia,serif}.metric-card>strong small{color:#898d77;font:500 1rem Arial,sans-serif}.metric-card>small{color:#939582;font-size:.7rem;line-height:1.5}
        .awards-panel{margin:0 0 34px;padding:clamp(20px,3vw,30px);border:1px solid #e2dece;border-radius:19px;background:radial-gradient(ellipse at 94% 0%,rgba(220,211,179,.3),transparent 38%),linear-gradient(135deg,#f7f5e9,#fffefa 70%);box-shadow:0 14px 34px rgba(65,76,44,.06)}
        .awards-copy h2,.applications-heading h2{margin:6px 0;color:#3e4b33;font:400 clamp(1.55rem,3vw,2.1rem)/1.2 Georgia,serif}.awards-copy p{max-width:720px;margin:8px 0 18px;color:#747967;font-size:.84rem;line-height:1.65}
        .settlement-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.settlement-grid>div{display:grid;gap:8px;padding:14px;border:1px solid #e7e3d6;border-radius:12px;background:rgba(255,255,255,.7)}.settlement-grid span{color:#7b806c;font-size:.7rem;line-height:1.4}.settlement-grid strong{color:#4c5c3d;font-size:.95rem}.settlement-note{margin:14px 0 0;color:#747967;font-size:.75rem}
        .finalize-box{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:16px 18px;border:1px solid #e7dcc0;border-radius:13px;background:rgba(255,255,255,.75)}.finalize-box strong{color:#4e5b3d;font-size:.88rem}.finalize-box p{margin:5px 0 0;color:#7c806c;font-size:.77rem}.finalize-box form{flex:0 0 auto}.finalize-box button{padding:11px 15px;border:0;border-radius:9px;background:linear-gradient(120deg,#6f7b4d,#536440);box-shadow:0 7px 16px rgba(85,97,58,.16);color:white;font:700 .78rem Arial,sans-serif;cursor:pointer}.settlement-waiting{display:flex;align-items:center;gap:10px;padding:13px 15px;border:1px solid #e8e5d9;border-radius:12px;background:rgba(255,255,255,.65);color:#747967;font-size:.78rem}.waiting-dot{width:8px;height:8px;border-radius:50%;background:#b89a5c;box-shadow:0 0 0 5px rgba(184,154,92,.13)}
        .applications-section{margin-top:30px}.applications-heading{display:flex;align-items:end;justify-content:space-between;gap:12px;margin-bottom:16px}.applications-heading h2{margin-bottom:0}.application-count{padding:7px 11px;border:1px solid #e2e2d5;border-radius:999px;background:#fffefa;color:#777c67;font-size:.72rem;font-weight:700;white-space:nowrap}
        .application-list{display:grid;gap:14px}.application-card{overflow:hidden;border:1px solid #e3e0d5;border-radius:17px;background:#fffefa;box-shadow:0 10px 28px rgba(65,76,44,.05)}
        .application-card-head{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:17px 20px;border-bottom:1px solid #eeece4;background:linear-gradient(100deg,#fffefa,#f8f7f0)}
        .artist-identity{display:flex;min-width:0;align-items:center;gap:13px}.artist-identity>img,.artist-avatar{display:grid;width:50px;height:50px;flex:0 0 50px;place-items:center;overflow:hidden;border:2px solid white;border-radius:50%;background:linear-gradient(145deg,#d2d8be,#83906c);box-shadow:0 5px 14px rgba(65,76,44,.12);color:white;object-fit:cover;font-size:1.1rem;font-weight:700}.artist-identity h3{overflow:hidden;margin:0 0 4px;color:#3f4b35;font-size:.95rem;text-overflow:ellipsis;white-space:nowrap}.artist-identity a{overflow-wrap:anywhere;color:#858875;font-size:.75rem;text-decoration:none}.artist-identity a:hover{text-decoration:underline}
        .application-badges{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap}.payment-pill{display:inline-flex;align-items:center;gap:7px;padding:6px 9px;border:1px solid #e8dfc6;border-radius:999px;background:#fff7e5;color:#866e34;font-size:.67rem;font-weight:800;white-space:nowrap}.payment-pill>span{width:7px;height:7px;border-radius:50%;background:#c5a652}.payment-pill.payment-verified{border-color:#d3e1cb;background:#eff5e9;color:#4d7047}.payment-pill.payment-verified>span{background:#6d9a60}
        .application-details{padding:18px 20px 20px}.application-facts{display:flex;flex-wrap:wrap;gap:10px 30px}.application-facts>div{display:grid;gap:5px;min-width:150px}.application-facts span{color:#90927f;font-size:.67rem}.application-facts strong,.application-facts code{color:#596348;font-size:.78rem;font-weight:700}.application-facts code{overflow-wrap:anywhere;font-family:ui-monospace,Consolas,monospace}
        .application-note{margin:16px 0 0;padding:11px 13px;border-left:3px solid #c8ad68;border-radius:0 9px 9px 0;background:#fbf6e8;color:#7a6a42;font-size:.75rem;line-height:1.55}.application-note.note-warning{border-color:#bb7564;background:#fff2ef;color:#895144}
        .submitted-artwork{display:grid;grid-template-columns:minmax(180px,.37fr) minmax(0,.63fr);gap:20px;margin-top:18px;padding-top:18px;border-top:1px solid #ece9de}.submitted-artwork-image{display:grid;min-height:210px;place-items:center;overflow:hidden;border:1px solid #e7e4d8;border-radius:12px;background:radial-gradient(ellipse at 50% 38%,#fffefb,#eeeee3 70%,#e5e8dc);color:#898c79;font-size:.72rem;text-align:center}.submitted-artwork-image img{display:block;width:100%;height:100%;min-height:210px;max-height:340px;object-fit:contain}.submitted-artwork-copy{min-width:0}.submitted-artwork-copy h4{margin:7px 0;color:#3f4b35;font:400 clamp(1.35rem,2.7vw,2rem)/1.15 Georgia,serif}.submitted-artwork-copy>p{display:-webkit-box;max-width:780px;margin:7px 0 14px;overflow:hidden;color:#767a68;font-size:.79rem;line-height:1.65;-webkit-box-orient:vertical;-webkit-line-clamp:4}.artwork-admin-footer{display:flex;flex-wrap:wrap;gap:10px 24px;padding-top:11px;border-top:1px solid #eeece3;color:#858875;font-size:.7rem}.artwork-admin-footer strong{color:#586744}.artwork-admin-footer small{color:#919381}.prize-credit{margin:12px 0 0!important;color:#4a7147!important}.prize-credit strong{color:#3d673d}
        .applications-empty{padding:48px 24px;border:1px dashed #cbd0bd;border-radius:16px;background:#f7f7ef;text-align:center}.applications-empty>span{color:#a8ae91;font-size:2rem}.applications-empty h3{margin:9px 0 5px;color:#4e5b3d;font:400 1.4rem Georgia,serif}.applications-empty p{margin:0;color:#858875;font-size:.82rem}
        .application-pagination{margin-top:20px}.application-pagination nav{display:flex;justify-content:center}.application-pagination a,.application-pagination span{border-color:#e1dfd3!important;color:#596744!important}
        @media(max-width:900px){.application-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.metric-card:first-child{grid-column:1/-1}.settlement-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:640px){.application-toolbar{align-items:flex-start;flex-direction:column}.exhibition-context{align-items:flex-start;flex-wrap:wrap}.exhibition-context>.status-pill{margin-left:88px}.application-metrics{grid-template-columns:1fr}.metric-card:first-child{grid-column:auto}.application-card-head{align-items:flex-start;flex-direction:column;gap:13px}.application-badges{justify-content:flex-start}.submitted-artwork{grid-template-columns:1fr}.submitted-artwork-image{max-height:420px}.submitted-artwork-image img{max-height:420px}.application-facts{gap:13px 20px}.application-facts>div{min-width:calc(50% - 10px)}.settlement-grid{grid-template-columns:1fr 1fr}.finalize-box{align-items:flex-start;flex-direction:column}.finalize-box form,.finalize-box button{width:100%}}
        @media(max-width:420px){.settlement-grid{grid-template-columns:1fr}.context-art{width:58px;height:70px;flex-basis:58px}.exhibition-context>.status-pill{margin-left:76px}.application-details{padding:16px}.application-card-head{padding:15px 16px}}
    </style>
@endsection
