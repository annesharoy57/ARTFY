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
            ->with(['buyer', 'artist', 'artwork', 'exhibitionApplication'])
            ->orderByDesc('verified_at')
            ->orderByDesc('id')
            ->paginate(15);

        $pendingSales = Order::query()
            ->where('payment_status', 'pending')
            ->where('order_status', 'pending')
            ->with([
                'buyer',
                'artist',
                'artwork',
                'exhibitionApplication.exhibition',
            ])
            ->latest('created_at')
            ->paginate(15, ['*'], 'pending_page');

        return view('admin.sales', compact(
            'totalSoldArtworks',
            'totalSalesValue',
            'totalCommissionEarned',
            'totalArtistEarnings',
            'sales',
            'pendingSales'
        ));
    }

    /** Update the fulfillment progress of a paid physical artwork order. */
    public function updateOrderFulfillment(Request $request, Order $order)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'fulfillment_status' => ['required', 'in:processing,shipped,delivered'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        $order->load(['artwork', 'exhibitionApplication']);
        $artworkType = $order->exhibitionApplication?->artwork_type
            ?? $order->artwork?->artwork_type
            ?? 'physical';

        abort_unless($order->payment_status === 'verified' && $artworkType === 'physical', 422,
            'Only paid physical artwork orders can be updated here.');

        $status = $validated['fulfillment_status'];
        $order->fulfillment_status = $status;
        $order->order_status = $status;
        if (filled($validated['tracking_number'] ?? null)) {
            $order->tracking_number = trim($validated['tracking_number']);
        }
        if ($status === 'shipped' && ! $order->shipped_at) {
            $order->shipped_at = now();
        }
        if ($status === 'delivered' && ! $order->delivered_at) {
            $order->delivered_at = now();
        }
        $order->save();

        return redirect()->route('admin.sales.index')
            ->with('success', 'Order ' . $order->order_number . ' updated to ' . str_replace('_', ' ', $status) . '.');
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
     * SSLCOMMERZ membership payment ledger and callback statuses.
     */
    public function payments(Request $request)
    {
        $this->checkAdmin();

        $allowedStatuses = ['all', 'verified', 'pending', 'unpaid'];
        $filter = $request->query('status', 'all');

        if (! in_array($filter, $allowedStatuses, true)) {
            $filter = 'all';
        }

        $paymentsQuery = ArtistMembershipApplication::query()
            ->with('user')
            ->latest('updated_at');

        if ($filter !== 'all') {
            $paymentsQuery->where('payment_status', $filter);
        }

        $payments = $paymentsQuery
            ->paginate(15)
            ->withQueryString();

        $paymentSummary = [
            'total' => ArtistMembershipApplication::count(),
            'verified' => ArtistMembershipApplication::where(
                'payment_status',
                'verified'
            )->count(),
            'pending' => ArtistMembershipApplication::where(
                'payment_status',
                'pending'
            )->count(),
            'unpaid' => ArtistMembershipApplication::where(
                'payment_status',
                'unpaid'
            )->count(),
            'received' => (float) ArtistMembershipApplication::where(
                'payment_status',
                'verified'
            )->sum('membership_fee'),
            'membershipReview' => ArtistMembershipApplication::where(
                'payment_status',
                'verified'
            )->where('status', 'pending')->count(),
        ];

        return view('admin.payments', compact(
            'payments',
            'paymentSummary',
            'filter'
        ));
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
