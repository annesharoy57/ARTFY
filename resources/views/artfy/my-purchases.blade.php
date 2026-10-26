@extends('artfy.layout')

@section('title', 'My Purchases | Artfy')

@section('content')
<div class="my-purchases-page" style="max-width: 1000px; margin: 40px auto; padding: 24px;">
    <h1 style="color: #414c2c;">My Purchases</h1>
    <p style="color: #72795f; margin-bottom: 28px;">
        View your purchased artworks and download your digital files.
    </p>

    @forelse ($orders as $order)
        @php
            $artwork = $order->artwork;
            $canDownload = $order->payment_status === 'verified'
                && $artwork
                && $artwork->artwork_type === 'digital'
                && filled($artwork->digital_file_path);
        @endphp

        <div style="border: 1px solid #e3e4d9; border-radius: 12px; padding: 22px; margin-bottom: 18px;">
            <h2 style="color: #414c2c; font-size: 20px; margin: 0 0 12px;">
                {{ $order->artwork_title ?: ($artwork->title ?? 'Artwork') }}
            </h2>

            <p><strong>Order number:</strong> {{ $order->order_number }}</p>
            <p><strong>Total:</strong> ৳{{ number_format((float) $order->total_amount, 2) }}</p>
            <p><strong>Payment:</strong> {{ ucfirst($order->payment_status ?? 'pending') }}</p>
            <p><strong>Order status:</strong> {{ ucfirst($order->order_status ?? 'pending') }}</p>

            @if ($canDownload)
                <a href="{{ route('orders.digital-download', $order) }}"
                   style="display: inline-block; background: #55613a; color: white; padding: 10px 18px; border-radius: 7px; text-decoration: none; margin-top: 8px;">
                    Download Digital Artwork
                </a>
            @elseif ($order->payment_status !== 'verified')
                <p style="color: #72795f; margin-top: 12px;">
                    Your download will be available after payment is confirmed.
                </p>
            @elseif ($artwork && $artwork->artwork_type === 'digital')
                <p style="color: #72795f; margin-top: 12px;">
                    The digital file is not currently available. Please contact the administrator.
                </p>
            @else
                <p style="color: #72795f; margin-top: 12px;">
                    This is not a digital artwork download.
                </p>
            @endif
        </div>
    @empty
        <p>You haven't made any purchases yet.</p>
    @endforelse
</div>
@endsection