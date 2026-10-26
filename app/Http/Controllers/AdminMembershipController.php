<?php

namespace App\Http\Controllers;

use App\Models\ArtistMembershipApplication;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // Payments awaiting SSLCOMMERZ confirmation.
        $paymentsToVerify = $applications
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
     */
    public function payments()
    {
        $this->checkAdmin();

        $applications = ArtistMembershipApplication::with('user')
            ->where('status', 'pending')
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
            'payment_status',
            'pending'
        )->count();

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
     * Approve an artist application only after verified payment.
     * Membership lasts one year from approval.
     */
    public function approve(
        Request $request,
        ArtistMembershipApplication $application
    ) {
        $this->checkAdmin();

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $result = DB::transaction(function () use (
            $application,
            $validated
        ) {
            $lockedApplication = ArtistMembershipApplication::query()
                ->whereKey($application->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedApplication->status !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Only pending applications can be approved.',
                ];
            }

            if ($lockedApplication->payment_status !== 'verified') {
                return [
                    'success' => false,
                    'message' => 'Approval is not allowed until SSLCOMMERZ payment has been verified.',
                ];
            }

            $user = $lockedApplication->user;

            if (! $user || $user->role !== 'artist') {
                return [
                    'success' => false,
                    'message' => 'The artist account could not be found.',
                ];
            }

            $lockedApplication->status = 'approved';
            $lockedApplication->admin_notes =
                $validated['admin_notes'] ?? null;
            $lockedApplication->save();

            // Start the annual membership when the admin approves.
            $user->membership_status = 'active';
            $user->membership_expires_at = now()->addYear();
            $user->save();

            return [
                'success' => true,
                'message' => 'Application approved. Artist membership is active for one year.',
            ];
        });

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
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

        DB::transaction(function () use ($application, $validated) {
            $lockedApplication = ArtistMembershipApplication::query()
                ->whereKey($application->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedApplication->status !== 'pending') {
                return;
            }

            $lockedApplication->status = 'rejected';
            $lockedApplication->admin_notes = $validated['admin_notes'];
            $lockedApplication->save();

            $user = $lockedApplication->user;

            if ($user) {
                $user->membership_status = 'rejected';
                $user->membership_expires_at = null;
                $user->save();
            }
        });

        return back()->with(
            'success',
            'Artist membership application rejected.'
        );
    }
}
