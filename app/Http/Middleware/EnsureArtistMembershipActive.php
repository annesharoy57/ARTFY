<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureArtistMembershipActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'artist') {
            abort(403, 'Only artist accounts can access this page.');
        }

        $application = $user->artistApplication;

        if (
            !$application ||
            $application->status !== 'approved' ||
            $application->payment_status !== 'verified' ||
            $user->membership_status !== 'active'
        ) {
            return redirect()
                ->route('artist.application')
                ->with(
                    'error',
                    'Your artist membership must be approved and your payment verified before you can access artist features.'
                );
        }

        return $next($request);
    }
}
