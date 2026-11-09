@extends('artfy.layout')

@section('title', 'Exhibition Prize Wallet | Artfy')

@section('content')
    <main style="width:min(980px,100%);margin:auto;padding:42px 24px 70px">
        <p style="color:#72795f;letter-spacing:2px;font-size:12px;font-weight:700">ARTIST ACCOUNT</p>
        <h1 style="color:#414c2c">Exhibition prize wallet</h1>
        <p>Prize credits from finalized online exhibitions are recorded here.</p>
        <section style="margin:22px 0;padding:22px;border:1px solid #e5e2d6;border-radius:12px;background:#fff">
            <span>Available credited balance</span>
            <strong style="display:block;margin-top:8px;font-size:30px;color:#414c2c">৳{{ number_format($balance, 2) }}</strong>
        </section>
        <h2>Prize history</h2>
        @forelse ($transactions as $transaction)
            <article style="margin:12px 0;padding:18px;border:1px solid #e5e2d6;border-radius:10px;background:#fff">
                <strong>{{ $transaction->exhibition?->title ?: 'Online exhibition' }}</strong>
                <span style="float:right;color:#285b31">+৳{{ number_format((float) $transaction->amount, 2) }}</span>
                <p>{{ $transaction->description }} · {{ $transaction->created_at->format('M j, Y') }}</p>
            </article>
        @empty
            <p>No exhibition prize credits have been added to your account yet.</p>
        @endforelse
    </main>
@endsection
