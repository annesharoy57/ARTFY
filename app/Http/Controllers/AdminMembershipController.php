<?php

namespace App\Http\Controllers;

use App\Models\ArtistMembershipApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMembershipController extends Controller
{
    /**
     * Display real artist membership applications
     * and payment statistics in the Admin Dashboard.
     */
    public function index()
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can access this page.');
        }

        $applications = ArtistMembershipApplication::with('user')
            ->latest()
            ->get();

        $totalApplications = $applications->count();

        $pendingApplications = $applications
            ->where('status', 'pending')
            ->count();

        $approvedApplications = $applications
            ->where('status', 'approved')
            ->count();

        $rejectedApplications = $applications
            ->where('status', 'rejected')
            ->count();

        $paymentsToVerify = $applications
            ->filter(function ($application) {
                return $application->status === 'approved'
                    && $application->payment_status === 'pending'
                    && filled($application->payment_reference);
            })
            ->count();

        $totalPayments = $applications
            ->where('payment_status', 'verified')
            ->count();

        return view('admin.dashboard', compact(
            'applications',
            'totalApplications',
            'pendingApplications',
            'approvedApplications',
            'rejectedApplications',
            'paymentsToVerify',
            'totalPayments'
        ));
    }

    /**
     * Approve an artist membership application.
     */
    public function approve(
        Request $request,
        ArtistMembershipApplication $application
    ) {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can approve applications.');
        }

        if ($application->status !== 'pending') {
            return back()->with(
                'error',
                'Only pending applications can be approved.'
            );
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application->status = 'approved';
        $application->admin_notes = $validated['admin_notes'] ?? null;
        $application->save();

        $application->user->membership_status = 'approved';
        $application->user->save();

        return back()->with(
            'success',
            'Artist membership approved. Payment must be verified separately.'
        );
    }

    /**
     * Reject an artist membership application.
     */
    public function reject(
        Request $request,
        ArtistMembershipApplication $application
    ) {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can reject applications.');
        }

        if ($application->status !== 'pending') {
            return back()->with(
                'error',
                'Only pending applications can be rejected.'
            );
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:2000'],
        ]);

        $application->status = 'rejected';
        $application->admin_notes = $validated['admin_notes'];
        $application->save();

        $application->user->membership_status = 'rejected';
        $application->user->save();

        return back()->with(
            'success',
            'Artist membership application rejected.'
        );
    }

    /**
     * Verify a payment after manually checking the transaction.
     */
    public function verifyPayment(
        Request $request,
        ArtistMembershipApplication $application
    ) {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can verify payments.');
        }

        if ($application->status !== 'approved') {
            return back()->with(
                'error',
                'Approve the artist membership before verifying payment.'
            );
        }

        if (
            $application->payment_status !== 'pending'
            || blank($application->payment_reference)
        ) {
            return back()->with(
                'error',
                'No submitted transaction ID is awaiting verification.'
            );
        }

        $application->payment_status = 'verified';
        $application->payment_verified_at = now();
        $application->save();

        return back()->with(
            'success',
            'Payment marked as verified. The artist can now access Artist Studio.'
        );
    }
}
