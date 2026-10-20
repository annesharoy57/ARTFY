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
     * Shows membership and membership-payment statistics only.
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
            'totalApplications',
            'pendingApplications',
            'approvedApplications',
            'rejectedApplications',
            'paymentsToVerify',
            'totalPayments'
        ));
    }

    /**
     * Dedicated verified artwork sales and commission page.
     *
     * Includes only verified artwork orders.
     * Reads the existing recorded amounts without recalculating
     * or overwriting historical commissions.
     */
    public function sales()
    {
        $this->checkAdmin();

        $verifiedOrders = Order::query()
            ->where('payment_status', 'verified');

        $totalSoldArtworks = (clone $verifiedOrders)->count();

        $totalSalesValue = (clone $verifiedOrders)
            ->sum('price');

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
     * Dedicated artist membership applications page.
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
     * Dedicated membership payment verification page.
     */
    public function payments()
    {
        $this->checkAdmin();

        $applications = ArtistMembershipApplication::with('user')
            ->where('status', 'approved')
            ->where('payment_status', 'pending')
            ->whereNotNull('payment_reference')
            ->where('payment_reference', '!=', '')
            ->latest()
            ->paginate(12);

        return view('admin.payments', compact('applications'));
    }

    /**
     * Dedicated reports and activity page.
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
            ->whereNotNull('payment_reference')
            ->where('payment_reference', '!=', '')
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

        $application->user->membership_status = 'rejected';
        $application->user->save();

        return back()->with(
            'success',
            'Artist membership application rejected.'
        );
    }

    /**
     * Verify a membership payment after manually checking
     * the submitted transaction reference.
     */
    public function verifyPayment(
        Request $request,
        ArtistMembershipApplication $application
    ) {
        $this->checkAdmin();

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
