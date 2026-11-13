<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictPendingArtistAccess
{
    /**
     * Keep artists with an incomplete membership review in the application
     * flow until payment and admin approval are both complete.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'artist') {
            return $next($request);
        }

        $application = $user->artistApplication;
        $membershipIsApproved = $application
            && $application->status === 'approved'
            && $application->payment_status === 'verified'
            && in_array($user->membership_status, ['active', 'approved'], true);

        if ($membershipIsApproved) {
            return $next($request);
        }

        $allowedRoutes = [
            'artist.application',
            'artist.application.submit',
            'artist.application.payment.store',
            'logout',
            'login',
            'login.submit',
            'register',
            'register.submit',
            'auth.choose-role',
            'password.request',
            'password.email',
            'password.code',
            'password.code.verify',
            'password.reset',
            'password.update',
            'membership.payment.success',
            'membership.payment.fail',
            'membership.payment.cancel',
            'membership.payment.ipn',
        ];

        if (in_array($request->route()?->getName(), $allowedRoutes, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your artist membership must be approved before you can use this feature.',
            ], 403);
        }

        return redirect()
            ->route('artist.application')
            ->with('error', 'Your artist account is waiting for membership approval. You can check your application and payment status here.');
    }
}
