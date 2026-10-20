
@extends('artfy.layout')

@section('title', 'Purchase artwork | Artfy')

@section('content')
<section class="detail-page">

    <a
        class="back-link"
        href="{{ route('artworks.show', 'uploaded-' . $artwork->id) }}"
    >
        ← Back to artwork
    </a>

    <div class="detail-layout">

        {{-- Artwork image --}}
        <div class="detail-art artwork-art green">
            <img
                class="detail-uploaded-image"
                src="{{ asset('storage/' . $artwork->image) }}"
                alt="{{ $artwork->title }}"
            >
        </div>

        {{-- Purchase information --}}
        <div class="detail-copy">

            <span class="eyebrow">Secure your artwork</span>

            <h1>Complete your order</h1>

            <p class="detail-description">
                You are purchasing "{{ $artwork->title }}"
                by {{ $artistName }}.
            </p>

            <div class="detail-facts">

                <span>
                    Artwork
                    <strong>{{ $artwork->title }}</strong>
                </span>

                <span>
                    Artist
                    <strong>{{ $artistName }}</strong>
                </span>

                <span>
                    Total to pay
                    <strong>
                        ৳{{ number_format((float) $artwork->price, 2) }}
                    </strong>
                </span>

            </div>

            {{-- Payment error message --}}
            @if (session('error'))
                <div role="alert" style="margin: 16px 0; color: #a52a2a;">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Payment success message --}}
            @if (session('success'))
                <div role="status" style="margin: 16px 0; color: #386641;">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div role="alert" style="margin: 16px 0;">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p>
                Complete your payment through SSLCOMMERZ's secure checkout.
                Available payment options will be displayed by the payment
                gateway.
            </p>

            <p>
                <strong>Important:</strong>
                Your order will be confirmed only after Artfy verifies the
                payment with SSLCOMMERZ. Do not pay again if a payment
                appears successful but Artfy has not yet confirmed your order.
            </p>

            {{-- Start secure checkout --}}
            <form
                method="POST"
                action="{{ route('artworks.purchase.store', 'uploaded-' . $artwork->id) }}"
            >
                @csrf

                <button
                    class="button button-dark"
                    type="submit"
                >
                    Pay securely with SSLCOMMERZ →
                </button>
            </form>

            <p style="margin-top: 14px; font-size: 0.9rem;">
                You will be redirected to the payment gateway to complete
                your transaction.
            </p>

        </div>
    </div>
</section>
@endsection
