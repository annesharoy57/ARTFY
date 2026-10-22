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
     * Start or resume checkout for an approved artist membership.
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

        if ($application->status !== 'approved') {
            return redirect()->route('artist.application')->with(
                'error',
                'Your membership application must be approved before payment.'
            );
        }

        if ($application->payment_status === 'verified') {
            return redirect()->route('artist.application')->with(
                'success',
                'Your membership payment has already been verified.'
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
            // Keep the same reference while this checkout is pending.
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
     * The callback alone is not proof of payment.
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
                'Your membership payment has been verified. You can now log in.'
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
     * Verify payment directly with SSLCOMMERZ.
     *
     * Membership is activated only after gateway validation and matching
     * transaction details. Admin notifications are created in the same
     * transaction as the status change, preventing duplicate notifications
     * from repeated success/IPN callbacks.
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

        if ($application->status !== 'approved') {
            throw new RuntimeException(
                'The membership application is not approved.'
            );
        }

        // A previously verified payment must not be processed again.
        if ($application->payment_status === 'verified') {
            return;
        }

        if ($application->payment_status !== 'pending') {
            throw new RuntimeException(
                'The membership payment is not awaiting confirmation.'
            );
        }

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

            // Another callback may have verified the payment while
            // gateway validation was in progress.
            if ($locked->payment_status === 'verified') {
                return;
            }

            if (
                $locked->status !== 'approved'
                || $locked->payment_status !== 'pending'
            ) {
                throw new RuntimeException(
                    'Membership status changed during payment verification.'
                );
            }

            $user = $locked->user()->lockForUpdate()->firstOrFail();

            if ($user->role !== 'artist') {
                throw new RuntimeException(
                    'The membership application does not belong to an artist account.'
                );
            }

            // Mark the payment as verified and activate membership.
            $locked->payment_method = 'sslcommerz';
            $locked->payment_status = 'verified';
            $locked->payment_verified_at = now();
            $locked->save();

            $user->membership_status = 'active';
            $user->save();

            // Notify all admins only for this successful status transition.
            $admins = User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                $admin->notify(new ArtfyDatabaseNotification(
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
                        'url' => route('admin.memberships.index', [], false),
                        'artist_id' => $user->id,
                        'artist_name' => $user->name,
                        'application_id' => $locked->id,
                        'payment_reference' => $locked->payment_reference,
                        'payment_method' => 'sslcommerz',
                        'amount' => (float) $locked->membership_fee,
                        'payment_verified_at' => $locked->payment_verified_at
                            ?->toDateTimeString(),
                    ]
                ));
            }
        });
    }
}
