@extends('coadmin.layout')

@section('title', $exhibition->title . ' | Artfy Co-admin')

@section('content')
    <a href="{{ route('co-admin.exhibitions.index') }}">← Online exhibitions</a>
    <h1>{{ $exhibition->title }}</h1>
    <p>{{ $exhibition->description }}</p>
    <p>
        @if ($exhibition->visitIsOpen())
            Scoring is open until {{ $exhibition->ends_at->format('M j, Y g:i A') }}.
        @elseif (! $exhibition->visitHasStarted())
            Scoring opens when the exhibition opens for visits.
        @else
            This exhibition is closed. Scores can no longer be changed.
        @endif
    </p>

    <div style="display:grid;gap:18px;margin-top:24px">
        @forelse ($applications as $application)
            <article style="padding:20px;border:1px solid #e5e2d6;border-radius:12px;background:#fff">
                <h2>{{ $application->artist?->artistProfile?->artist_name ?: $application->artist?->name }}</h2>
                @if ($application->artwork_image)
                    <img src="{{ asset('storage/' . $application->artwork_image) }}" alt="{{ $application->artwork_title }}" style="display:block;width:min(100%,520px);max-height:420px;object-fit:contain;background:#f5f3e8">
                @endif
                <h3>{{ $application->artwork_title }}</h3>
                <p>{{ $application->artwork_description }}</p>
                <p>Asking price: ৳{{ number_format((float) $application->artwork_price, 2) }}</p>
                <p>{{ $application->votes_count }} art-lover vote(s)</p>

                @php($myScore = $application->scores->first()?->score)
                @if ($exhibition->visitIsOpen())
                    <form method="POST" action="{{ route('co-admin.exhibitions.score', [$exhibition, $application]) }}" style="display:flex;align-items:end;gap:10px;flex-wrap:wrap">
                        @csrf
                        <label>
                            Your score (1–10)
                            <select name="score" required>
                                <option value="">Select a score</option>
                                @for ($score = 1; $score <= 10; $score++)
                                    <option value="{{ $score }}" @selected((string) $myScore === (string) $score)>{{ $score }}</option>
                                @endfor
                            </select>
                        </label>
                        <button type="submit">{{ $myScore ? 'Update score' : 'Save score' }}</button>
                    </form>
                @elseif ($myScore)
                    <p>Your final score: <strong>{{ $myScore }} / 10</strong></p>
                @else
                    <p>You did not submit a score during the visiting period.</p>
                @endif
            </article>
        @empty
            <p>No participant artworks have been submitted yet.</p>
        @endforelse
    </div>

    <style>
        article h2{margin-top:0}form select{display:block;margin-top:6px;padding:9px;border:1px solid #d5d3c8;border-radius:6px;font:inherit}
        form button{padding:10px 14px;border:0;border-radius:6px;background:#414c2c;color:#fff;font:inherit;font-weight:700;cursor:pointer}
    </style>
@endsection
