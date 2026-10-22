<?php

namespace App\Http\Controllers;

use App\Models\ArtistMembershipApplication;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMembershipController extends Controller
{
    /**
     * Ensure only administrators can access these pages.
     */
    private function checkAdmin(): void
    {
        abort_unless(
            Auth::check() && Auth::user()->isAdmin(),
            403,
            'Only administrators can access this page.'
        );
    }

    /**
     * Main admin dashboard overview.
     */
    public function index()
    {
        $this->checkAdmin();

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

        // Approved applications that have not completed payment.
        $paymentsToVerify = $applications
            ->where('status', 'approved')
            ->where('payment_status', 'pending')
            ->count();

        $totalPayments = $applications
            ->where('payment_status', 'verified')
            ->count();

        return view('admin.dashboard', compact(
            'totalApplications',
            'pendingApplications',
            'approvedApplications',
            'rejectedApplications',
            'paymentsToVerify',
            'totalPayments'
        ));
    }

    /**
     * Verified artwork sales and commissions.
     */
    public function sales()
    {
        $this->checkAdmin();

        $verifiedOrders = Order::query()
            ->where('payment_status', 'verified');

        $totalSoldArtworks = (clone $verifiedOrders)->count();

        $totalSalesValue = (clone $verifiedOrders)->sum('price');

        $totalCommissionEarned = (clone $verifiedOrders)
            ->sum('commission_amount');

        $totalArtistEarnings = (clone $verifiedOrders)
            ->sum('artist_amount');

        $sales = (clone $verifiedOrders)
            ->with(['buyer', 'artist'])
            ->orderByDesc('verified_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.sales', compact(
            'totalSoldArtworks',
            'totalSalesValue',
            'totalCommissionEarned',
            'totalArtistEarnings',
            'sales'
        ));
    }

    /**
     * Artist membership applications.
     */
    public function memberships()
    {
        $this->checkAdmin();

        $applications = ArtistMembershipApplication::with('user')
            ->latest()
            ->paginate(12);

        return view('admin.memberships', compact('applications'));
    }

    /**
     * Membership payments awaiting completion.
     *
     * This is not a manual transaction-verification queue.
     */
    public function payments()
    {
        $this->checkAdmin();

        $applications = ArtistMembershipApplication::with('user')
            ->where('status', 'approved')
            ->where('payment_status', 'pending')
            ->latest()
            ->paginate(12);

        return view('admin.payments', compact('applications'));
    }

    /**
     * Membership reports and activity.
     */
    public function reports()
    {
        $this->checkAdmin();

        $applications = ArtistMembershipApplication::with('user')
            ->latest()
            ->paginate(15);

        $totalApplications = ArtistMembershipApplication::count();

        $pendingApplications = ArtistMembershipApplication::where(
            'status',
            'pending'
        )->count();

        $approvedApplications = ArtistMembershipApplication::where(
            'status',
            'approved'
        )->count();

        $rejectedApplications = ArtistMembershipApplication::where(
            'status',
            'rejected'
        )->count();

        $paymentsToVerify = ArtistMembershipApplication::where(
            'status',
            'approved'
        )
            ->where('payment_status', 'pending')
            ->count();

        $totalPayments = ArtistMembershipApplication::where(
            'payment_status',
            'verified'
        )->count();

        return view('admin.reports', compact(
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
     * Approval does not activate paid membership.
     */
    public function approve(
        Request $request,
        ArtistMembershipApplication $application
    ) {
        $this->checkAdmin();

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

        // Payment must still be verified by SSLCOMMERZ.
        $user = $application->user;
        $user->membership_status = 'pending';
        $user->save();

        return back()->with(
            'success',
            'Application approved. The artist can now pay the membership fee online.'
        );
    }

    /**
     * Reject an artist membership application.
     */
    public function reject(
        Request $request,
        ArtistMembershipApplication $application
    ) {
        $this->checkAdmin();

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

        $user = $application->user;
        $user->membership_status = 'rejected';
        $user->save();

        return back()->with(
            'success',
            'Artist membership application rejected.'
        );
    }
}
