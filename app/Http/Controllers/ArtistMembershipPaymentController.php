<?php

namespace App\Http\Controllers;

use App\Models\ArtistMembershipApplication;
use App\Models\User;
use App\Notifications\ArtfyDatabaseNotification;
use App\Services\SSLCommerzService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ArtistMembershipPaymentController extends Controller
{
    /**
     * Start or resume SSLCOMMERZ checkout for an artist membership.
     *
     * Applications may be pending or approved, but rejected applications
     * and already verified payments cannot start a new checkout.
     */
    public function store(
        Request $request,
        SSLCommerzService $sslCommerz
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user && $user->role === 'artist', 403);

        $application = ArtistMembershipApplication::where(
            'user_id',
            $user->id
        )->firstOrFail();

        if (! in_array($application->status, ['pending', 'approved'], true)) {
            return redirect()->route('artist.application')->with(
                'error',
                'Your rejected membership application cannot be paid.'
            );
        }

        if ($application->payment_status === 'verified') {
            return redirect()->route('artist.application')->with(
                'success',
                'Your membership payment has already been verified. Your application is awaiting any required admin approval.'
            );
        }

        $amount = (float) $application->membership_fee;

        if (! is_finite($amount) || $amount <= 0) {
            return redirect()->route('artist.application')->with(
                'error',
                'The membership fee is invalid. Please contact the administrator.'
            );
        }

        $baseUrl = rtrim((string) config('app.url'), '/');

        if (
            blank($baseUrl)
            || ! filter_var($baseUrl, FILTER_VALIDATE_URL)
            || strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME)) !== 'https'
            || str_contains(strtolower($baseUrl), 'localhost')
            || str_contains($baseUrl, '127.0.0.1')
        ) {
            return redirect()->route('artist.application')->with(
                'error',
                'The public HTTPS Artfy URL is not configured. Please contact the administrator.'
            );
        }

        try {
            // Keep the same transaction reference while checkout is pending.
            if (
                $application->payment_status !== 'pending'
                || blank($application->payment_reference)
            ) {
                $application->payment_reference =
                    'MEM-' . $application->id . '-' . Str::upper(Str::random(12));
            }

            $application->payment_method = 'sslcommerz';
            $application->payment_status = 'pending';
            $application->payment_submitted_at = now();
            $application->payment_verified_at = null;
            $application->save();

            $response = $sslCommerz->initiate([
                'total_amount' => number_format($amount, 2, '.', ''),
                'currency' => 'BDT',
                'tran_id' => $application->payment_reference,

                'success_url' => $baseUrl . '/membership/payment/success',
                'fail_url' => $baseUrl . '/membership/payment/fail',
                'cancel_url' => $baseUrl . '/membership/payment/cancel',
                'ipn_url' => $baseUrl . '/membership/payment/ipn',

                'product_name' => 'Artfy Artist Membership',
                'product_category' => 'Membership',
                'product_profile' => 'general',

                'cus_name' => $user->name,
                'cus_email' => $user->email,
                'cus_add1' => 'Not provided',
                'cus_city' => 'Dhaka',
                'cus_postcode' => '1200',
                'cus_country' => 'Bangladesh',
                'cus_phone' => '01700000000',

                'shipping_method' => 'NO',
                'num_of_item' => 1,

                'value_a' => (string) $application->id,
                'value_b' => 'artist_membership',
            ]);

            $gatewayUrl = $response['GatewayPageURL'] ?? null;

            if (
                ! is_string($gatewayUrl)
                || ! filter_var($gatewayUrl, FILTER_VALIDATE_URL)
                || strtolower((string) parse_url($gatewayUrl, PHP_URL_SCHEME)) !== 'https'
            ) {
                throw new RuntimeException(
                    'SSLCOMMERZ did not return a valid HTTPS checkout URL.'
                );
            }

            return redirect()->away($gatewayUrl);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('artist.application')->with(
                'error',
                'Could not start membership checkout. If you may have been charged, check your membership status or contact Artfy support before retrying.'
            );
        }
    }

    /**
     * Browser success callback.
     * The browser callback alone is not proof of payment.
     */
    public function success(
        Request $request,
        SSLCommerzService $sslCommerz
    ): RedirectResponse {
        $tranId = $request->input('tran_id');
        $validationId = $request->input('val_id');

        if (
            ! is_string($tranId) || $tranId === ''
            || ! is_string($validationId) || $validationId === ''
        ) {
            return redirect()->route('login')->with(
                'error',
                'The payment response could not be verified. Please check your membership status.'
            );
        }

        try {
            $this->verifyAndActivate($tranId, $validationId, $sslCommerz);

            return redirect()->route('login')->with(
                'success',
                'Your membership payment has been verified. Your application must be approved by an admin before your membership becomes active.'
            );
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('login')->with(
                'error',
                'Your payment is not yet confirmed. Do not pay again until you check your membership status or contact Artfy support.'
            );
        }
    }

    /**
     * Failed checkout callback.
     * Do not assume this callback proves no payment occurred.
     */
    public function fail(Request $request): RedirectResponse
    {
        return redirect()->route('login')->with(
            'error',
            'The membership payment was not confirmed. Check your membership status before trying again.'
        );
    }

    /**
     * Cancelled checkout callback.
     */
    public function cancel(Request $request): RedirectResponse
    {
        return redirect()->route('login')->with(
            'error',
            'You left the membership checkout. Check your membership status before attempting payment again.'
        );
    }

    /**
     * Server-to-server payment notification.
     */
    public function ipn(
        Request $request,
        SSLCommerzService $sslCommerz
    ) {
        $tranId = $request->input('tran_id');
        $validationId = $request->input('val_id');

        if (
            ! is_string($tranId) || $tranId === ''
            || ! is_string($validationId) || $validationId === ''
        ) {
            return response('Missing transaction details', 400);
        }

        try {
            $this->verifyAndActivate($tranId, $validationId, $sslCommerz);

            return response('Membership payment processed', 200);
        } catch (Throwable $e) {
            report($e);

            return response('Membership payment not confirmed', 400);
        }
    }

    /**
     * Verify the payment directly with SSLCOMMERZ.
     *
     * Despite this method's historical name, it verifies payment only.
     * It does not activate membership or set a membership expiry date.
     */
    private function verifyAndActivate(
        string $tranId,
        string $validationId,
        SSLCommerzService $sslCommerz
    ): void {
        $application = ArtistMembershipApplication::where(
            'payment_reference',
            $tranId
        )->firstOrFail();

        // A verified transaction is idempotent on repeated callbacks.
        if ($application->payment_status === 'verified') {
            return;
        }

        if (! in_array($application->status, ['pending', 'approved'], true)) {
            throw new RuntimeException(
                'The membership application is not eligible for payment verification.'
            );
        }

        if ($application->payment_status !== 'pending') {
            throw new RuntimeException(
                'The membership payment is not awaiting confirmation.'
            );
        }

        // Ask SSLCOMMERZ to validate the transaction server-side.
        $gateway = $sslCommerz->validateTransaction($validationId);

        $gatewayStatus = strtoupper(
            (string) ($gateway['status'] ?? '')
        );

        $gatewayTranId = (string) ($gateway['tran_id'] ?? '');
        $gatewayValId = (string) ($gateway['val_id'] ?? '');

        $gatewayCurrency = strtoupper(
            (string) (
                $gateway['currency']
                ?? $gateway['currency_type']
                ?? ''
            )
        );

        $gatewayAmount = $gateway['amount'] ?? null;

        if (! in_array($gatewayStatus, ['VALID', 'VALIDATED'], true)) {
            throw new RuntimeException(
                'SSLCOMMERZ did not validate this membership payment.'
            );
        }

        if ($gatewayTranId !== $tranId) {
            throw new RuntimeException(
                'The gateway transaction reference does not match.'
            );
        }

        if (
            $gatewayValId !== ''
            && ! hash_equals($gatewayValId, $validationId)
        ) {
            throw new RuntimeException(
                'The gateway validation ID does not match.'
            );
        }

        if ($gatewayCurrency !== 'BDT') {
            throw new RuntimeException(
                'The membership payment currency does not match.'
            );
        }

        $expectedAmount = (float) $application->membership_fee;

        if (
            ! is_numeric($gatewayAmount)
            || abs((float) $gatewayAmount - $expectedAmount) > 0.01
        ) {
            throw new RuntimeException(
                'The membership payment amount does not match.'
            );
        }

        // Only risk level 0 is automatically accepted when supplied.
        if (
            array_key_exists('risk_level', $gateway)
            && (string) $gateway['risk_level'] !== '0'
        ) {
            throw new RuntimeException(
                'The membership payment requires additional review.'
            );
        }

        DB::transaction(function () use ($application): void {
            $locked = ArtistMembershipApplication::whereKey(
                $application->id
            )->lockForUpdate()->firstOrFail();

            // Another callback may have completed verification meanwhile.
            if ($locked->payment_status === 'verified') {
                return;
            }

            if (
                ! in_array($locked->status, ['pending', 'approved'], true)
                || $locked->payment_status !== 'pending'
            ) {
                throw new RuntimeException(
                    'Membership status changed during payment verification.'
                );
            }

            $user = User::whereKey($locked->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($user->role !== 'artist') {
                throw new RuntimeException(
                    'The membership application does not belong to an artist account.'
                );
            }

            // Record successful payment verification only.
            $locked->payment_method = 'sslcommerz';
            $locked->payment_status = 'verified';
            $locked->payment_verified_at = now();
            $locked->save();

            // IMPORTANT:
            // Do not activate membership here.
            // Do not set membership_expires_at here.
            // Admin approval starts the one-year membership period.

            if ($user->membership_status !== 'active') {
                $user->membership_status = 'pending';
                $user->save();
            }

            // Notify admins only when this callback changes the payment
            // from pending to verified. The transaction prevents duplicate
            // notifications from concurrent success/IPN callbacks.
            $admins = User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                $admin->notify(
                    new ArtfyDatabaseNotification(
                        'artfy.membership_payment_verified',
                        [
                            'title' => 'Artist membership payment verified',
                            'message' => $user->name
                                . ' has successfully paid BDT '
                                . number_format(
                                    (float) $locked->membership_fee,
                                    2
                                )
                                . ' for artist membership.',
                            'url' => route(
                                'admin.memberships.index',
                                [],
                                false
                            ),
                            'artist_id' => $user->id,
                            'artist_name' => $user->name,
                            'application_id' => $locked->id,
                            'payment_reference' => $locked->payment_reference,
                            'payment_method' => 'sslcommerz',
                            'amount' => (float) $locked->membership_fee,
                            'payment_verified_at' => $locked->payment_verified_at
                                ?->toDateTimeString(),
                        ]
                    )
                );
            }
        });
    }
}
