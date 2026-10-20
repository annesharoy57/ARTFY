<?php
namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Order;
use App\Models\User;
use App\Services\SSLCommerzService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ArtworkPurchaseController extends Controller
{
    /**
     * Show checkout for an approved artwork.
     */
    public function create(string $slug): View
    {
        abort_unless(
            preg_match('/^uploaded-(\d+)$/', $slug, $matches),
            404
        );

        $artwork = Artwork::with('artist.artistProfile')
            ->where('status', 'available')
            ->where('moderation_status', 'approved')
            ->where('is_sold', false)
            ->findOrFail((int) $matches[1]);

        abort_if(
            $artwork->price === null || (float) $artwork->price <= 0,
            404,
            'This artwork is not currently available for purchase.'
        );

        abort_if(
            (int) $artwork->user_id === (int) auth()->id(),
            403,
            'You cannot purchase your own artwork.'
        );

        return view('artfy.purchase', [
            'artwork' => $artwork,
            'artistName' => $artwork->artist?->artistProfile?->artist_name
                ?: ($artwork->artist?->name ?? 'Artfy Artist'),
        ]);
    }

    /**
     * Create an order and redirect the buyer to SSLCOMMERZ.
     */
    public function store(
        Request $request,
        string $slug,
        SSLCommerzService $sslCommerz
    ): RedirectResponse {
        abort_unless(
            auth()->check() && auth()->user()->isArtLover(),
            403,
            'Only art lovers can purchase artworks.'
        );

        abort_unless(
            preg_match('/^uploaded-(\d+)$/', $slug, $matches),
            404
        );

        $artworkId = (int) $matches[1];

        $resolution = $this->resolveExistingPendingOrders(
            $artworkId,
            $sslCommerz
        );

        if (in_array($resolution, ['paid', 'already_processed'], true)) {
            return redirect()->route('home')->with(
                'success',
                'Your payment has already been processed. Please check your order history.'
            );
        }

        try {
            $order = DB::transaction(function () use ($artworkId) {
                $artwork = Artwork::whereKey($artworkId)
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_unless(
                    $artwork->status === 'available'
                    && $artwork->moderation_status === 'approved'
                    && ! $artwork->is_sold
                    && $artwork->price !== null
                    && (float) $artwork->price > 0,
                    409,
                    'This artwork is no longer available for purchase.'
                );

                abort_if(
                    (int) $artwork->user_id === (int) auth()->id(),
                    403,
                    'You cannot purchase your own artwork.'
                );

                $existingOrder = Order::where('artwork_id', $artwork->id)
                    ->where('payment_status', 'pending')
                    ->where('order_status', 'pending')
                    ->exists();

                abort_if(
                    $existingOrder,
                    409,
                    'A previous checkout is still awaiting confirmation. Please wait or contact Artfy support before trying again.'
                );

                $price = round((float) $artwork->price, 2);
                $commissionRate = 15.00;

                $commission = round(
                    $price * $commissionRate / 100,
                    2
                );

                return Order::create([
                    'order_number' => 'ART-' . Str::upper(Str::random(12)),
                    'buyer_id' => auth()->id(),
                    'artwork_id' => $artwork->id,
                    'artist_id' => $artwork->user_id,
                    'artwork_title' => $artwork->title,
                    'price' => $price,
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $commission,
                    'artist_amount' => round($price - $commission, 2),
                    'payment_method' => 'sslcommerz',
                    'sender_number' => null,
                    'transaction_id' => null,
                    'payment_status' => 'pending',
                    'order_status' => 'pending',
                ]);
            });
        } catch (ModelNotFoundException $e) {
            abort(404);
        }

        try {
            $baseUrl = rtrim(
                (string) config('services.sslcommerz.callback_url'),
                '/'
            );

            if (blank($baseUrl)) {
                throw new RuntimeException(
                    'The SSLCOMMERZ callback URL is not configured.'
                );
            }

            $response = $sslCommerz->initiate([
                'total_amount' => number_format(
                    (float) $order->price,
                    2,
                    '.',
                    ''
                ),
                'currency' => 'BDT',
                'tran_id' => $order->order_number,
                'success_url' => $baseUrl . '/payment/sslcommerz/success',
                'fail_url' => $baseUrl . '/payment/sslcommerz/fail',
                'cancel_url' => $baseUrl . '/payment/sslcommerz/cancel',
                'ipn_url' => $baseUrl . '/payment/sslcommerz/ipn',
                'product_name' => $order->artwork_title,
                'product_category' => 'Artwork',
                'product_profile' => 'general',
                'cus_name' => auth()->user()->name,
                'cus_email' => auth()->user()->email,
                'cus_add1' => 'Not provided',
                'cus_city' => 'Dhaka',
                'cus_postcode' => '1200',
                'cus_country' => 'Bangladesh',
                'cus_phone' => '01700000000',
                'shipping_method' => 'NO',
                'num_of_item' => 1,
                'value_a' => (string) $order->id,
                'value_b' => 'artwork',
            ]);

            $sessionKey = $response['sessionkey'] ?? null;
            $gatewayUrl = $response['GatewayPageURL'] ?? null;

            if (blank($sessionKey) || blank($gatewayUrl)) {
                throw new RuntimeException(
                    'SSLCOMMERZ did not return a valid checkout session.'
                );
            }

            $order->update([
                'gateway_session_key' => $sessionKey,
            ]);

            return redirect()->away($gatewayUrl);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('artworks.show', 'uploaded-' . $artworkId)
                ->with(
                    'error',
                    'We could not confirm the checkout session. Please try again shortly. If a payment may have been made, contact Artfy support before retrying.'
                );
        }
    }

    /**
     * Resolve pending orders before starting another checkout.
     */
    private function resolveExistingPendingOrders(
        int $artworkId,
        SSLCommerzService $sslCommerz
    ): string {
        $orders = Order::where('artwork_id', $artworkId)
            ->where('payment_status', 'pending')
            ->where('order_status', 'pending')
            ->orderBy('id')
            ->get();

        foreach ($orders as $order) {
            if ($order->payment_method !== 'sslcommerz') {
                abort(
                    409,
                    'A previous manual-payment order is awaiting review. Please contact Artfy support.'
                );
            }

            $gateway = filled($order->gateway_session_key)
                ? $sslCommerz->queryBySessionKey(
                    $order->gateway_session_key
                )
                : $sslCommerz->queryByTransactionId(
                    $order->order_number
                );

            if (($gateway['APIConnect'] ?? null) !== 'DONE') {
                abort(
                    409,
                    'We could not confirm the previous checkout status. Please wait or contact Artfy support before retrying.'
                );
            }

            $transactions = $gateway['element'] ?? [];

            if (! is_array($transactions)) {
                abort(
                    409,
                    'The gateway returned an unexpected transaction response. Contact Artfy support before retrying.'
                );
            }

            if (
                isset($gateway['status'])
                && ! isset($gateway['element'])
            ) {
                $transactions = [$gateway];
            }

            $transaction = $transactions[0] ?? null;

            $status = strtoupper(
                (string) ($transaction['status'] ?? '')
            );

            if (in_array($status, ['VALID', 'VALIDATED'], true)) {
                $validationId = $transaction['val_id'] ?? null;

                if (blank($validationId)) {
                    abort(
                        409,
                        'The gateway reports a completed transaction without a validation ID. Contact Artfy support.'
                    );
                }

                $result = $this->verifyAndRecordPayment(
                    $order->order_number,
                    (string) $validationId,
                    $sslCommerz
                );

                if (in_array($result, ['paid', 'already_processed'], true)) {
                    return $result;
                }

                abort(
                    409,
                    'The previous payment requires review. Do not pay again until Artfy support resolves it.'
                );
            }

            if ($status === 'PENDING') {
                abort(
                    409,
                    'Your previous checkout is still pending. Please wait for confirmation before trying again.'
                );
            }

            $confirmedFailure = in_array(
                $status,
                ['FAILED', 'CANCELLED', 'CANCELED'],
                true
            );

            $confirmedNoTransaction =
                blank($order->gateway_session_key)
                && (int) ($gateway['no_of_trans_found'] ?? -1) === 0;

            if (! $confirmedFailure && ! $confirmedNoTransaction) {
                abort(
                    409,
                    'We could not safely determine whether the previous checkout can be retried. Please contact Artfy support before paying again.'
                );
            }

            DB::transaction(function () use ($order) {
                $lockedOrder = Order::whereKey($order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedOrder->payment_status !== 'pending'
                    || $lockedOrder->order_status !== 'pending'
                ) {
                    abort(
                        409,
                        'The previous order changed while being checked. Refresh the page and review your order history.'
                    );
                }

                $lockedOrder->update([
                    'payment_status' => 'failed',
                    'order_status' => 'cancelled',
                    'rejection_reason' =>
                        'Gateway query confirmed a failed transaction or no transaction was found during retry review.',
                ]);
            });
        }

        return 'clear';
    }

    /**
     * Handle the browser's successful-payment callback.
     */
    public function success(
        Request $request,
        SSLCommerzService $sslCommerz
    ): RedirectResponse {
        $data = $request->validate([
            'tran_id' => ['required', 'string', 'max:100'],
            'val_id' => ['required', 'string', 'max:255'],
        ]);

        try {
            $result = $this->verifyAndRecordPayment(
                $data['tran_id'],
                $data['val_id'],
                $sslCommerz
            );

            if ($result === 'paid') {
                return redirect()->route('home')->with(
                    'success',
                    'Payment successful! Your order ' .
                    $data['tran_id'] . ' has been recorded.'
                );
            }

            if ($result === 'review') {
                return redirect()->route('home')->with(
                    'error',
                    'Your payment requires additional review. Please contact Artfy support and do not pay again.'
                );
            }

            if ($result === 'already_processed') {
                return redirect()->route('home')->with(
                    'success',
                    'This payment has already been processed.'
                );
            }

            return redirect()->route('home')->with(
                'error',
                'The payment could not be confirmed. Please contact Artfy support before attempting another payment.'
            );
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('home')->with(
                'error',
                'We could not confirm your payment yet. Please contact Artfy support before attempting another payment.'
            );
        }
    }

    /**
     * Verify with SSLCOMMERZ before recording a payment.
     * Shared by the browser callback and IPN.
     */
    private function verifyAndRecordPayment(
        string $transactionId,
        string $validationId,
        SSLCommerzService $sslCommerz
    ): string {
        $order = Order::where('order_number', $transactionId)->first();

        if (! $order) {
            throw new RuntimeException(
                'No Artfy order matches transaction ' . $transactionId
            );
        }

        if ($order->payment_status === 'verified') {
            if ($order->gateway_val_id === $validationId) {
                return 'already_processed';
            }

            throw new RuntimeException(
                'A different validation ID was supplied for a verified order.'
            );
        }

        if (
            $order->payment_status !== 'pending'
            || $order->order_status !== 'pending'
        ) {
            throw new RuntimeException(
                'The order is not awaiting payment.'
            );
        }

        $gateway = $sslCommerz->validateTransaction($validationId);

        $gatewayStatus = strtoupper(
            (string) ($gateway['status'] ?? '')
        );

        if (! in_array($gatewayStatus, ['VALID', 'VALIDATED'], true)) {
            throw new RuntimeException(
                'SSLCOMMERZ did not validate the transaction.'
            );
        }

        if (
            (string) ($gateway['tran_id'] ?? '') !== $order->order_number
        ) {
            throw new RuntimeException(
                'The gateway transaction ID does not match the Artfy order.'
            );
        }

        if (
            strtoupper((string) ($gateway['currency_type'] ?? '')) !== 'BDT'
        ) {
            throw new RuntimeException(
                'The gateway transaction currency is not BDT.'
            );
        }

        if (
            isset($gateway['currency'])
            && strtoupper((string) $gateway['currency']) !== 'BDT'
        ) {
            throw new RuntimeException(
                'The gateway settlement currency is not BDT.'
            );
        }

        $gatewayAmount = round(
            (float) ($gateway['amount'] ?? -1),
            2
        );

        $orderAmount = round((float) $order->price, 2);

        if ($gatewayAmount !== $orderAmount) {
            throw new RuntimeException(
                'The gateway amount does not match the Artfy order.'
            );
        }

        if ((string) ($gateway['risk_level'] ?? '0') === '1') {
            report(new RuntimeException(
                'SSLCOMMERZ flagged order ' .
                $order->order_number .
                ' for risk review.'
            ));

            return 'review';
        }

        return DB::transaction(function () use (
            $order,
            $validationId
        ) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->payment_status === 'verified') {
                if ($lockedOrder->gateway_val_id === $validationId) {
                    return 'already_processed';
                }

                throw new RuntimeException(
                    'The order was verified by a different transaction.'
                );
            }

            if (
                $lockedOrder->payment_status !== 'pending'
                || $lockedOrder->order_status !== 'pending'
            ) {
                throw new RuntimeException(
                    'The order is no longer awaiting payment.'
                );
            }

            $validationAlreadyUsed = Order::where(
                'gateway_val_id',
                $validationId
            )
                ->where('id', '!=', $lockedOrder->id)
                ->exists();

            if ($validationAlreadyUsed) {
                throw new RuntimeException(
                    'This gateway validation ID has already been used.'
                );
            }

            $artwork = Artwork::whereKey($lockedOrder->artwork_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $artwork->status !== 'available'
                || $artwork->moderation_status !== 'approved'
                || $artwork->is_sold
            ) {
                throw new RuntimeException(
                    'The artwork is no longer available. Manual payment review is required for order ' .
                    $lockedOrder->order_number
                );
            }

            // Record the verified payment.
            $lockedOrder->update([
                'payment_status' => 'verified',
                'transaction_id' => $lockedOrder->order_number,
                'gateway_val_id' => $validationId,
                'verified_at' => now(),
            ]);

            // Mark the artwork as sold.
            $artwork->update([
                'is_sold' => true,
                'status' => 'sold',
            ]);

            /*
             * Notifications are created only after verification.
             * They share the transaction with the order update, so
             * a failure rolls back all three database changes.
             */
            $this->createSaleNotifications($lockedOrder, $artwork);

            return 'paid';
        });
    }

    /**
     * Notify the artist and every admin about a verified sale.
     */
    private function createSaleNotifications(
        Order $order,
        Artwork $artwork
    ): void {
        $salePrice = number_format((float) $order->price, 2);

        $commission = number_format(
            (float) $order->commission_amount,
            2
        );

        $artistAmount = number_format(
            (float) $order->artist_amount,
            2
        );

        /*
         * Use the rate recorded on the order, not a hardcoded rate.
         * That preserves historical commission rates such as 10%
         * and shows the correct rate for newer orders such as 15%.
         */
        $commissionRate = number_format(
            (float) $order->commission_rate,
            2
        );

        // Notify the artist who owns the artwork.
        $artist = User::find($order->artist_id);

        if ($artist) {
            $this->insertDatabaseNotification(
                $artist,
                'artfy.artwork_sold',
                [
                    'title' => 'Your artwork has been sold!',

                    'message' => '"' . $order->artwork_title .
                        '" was purchased for BDT ' . $salePrice .
                        '. Your recorded amount: BDT ' . $artistAmount . '.',

                    'artwork_slug' => 'uploaded-' . $artwork->id,
                    'order_number' => $order->order_number,
                    'sale_price' => (float) $order->price,
                    'commission_amount' => (float) $order->commission_amount,
                    'artist_amount' => (float) $order->artist_amount,
                ]
            );
        }

        // Notify every account whose role is admin.
        $admins = User::query()
            ->where('role', 'admin')
            ->get();

        foreach ($admins as $admin) {
            $this->insertDatabaseNotification(
                $admin,
                'artfy.commission_earned',
                [
                    'title' => 'New artwork sale — commission earned',

                    'message' => '"' . $order->artwork_title .
                        '" sold for BDT ' . $salePrice .
                        '. Artfy commission: BDT ' . $commission .
                        ' (' . $commissionRate . '%).' .
                        ' Order: ' . $order->order_number . '.',

                    // Opening this notification leads to Sales & Commission.
                    'url' => route('admin.sales.index', [], false),

                    'order_number' => $order->order_number,
                    'artwork_title' => $order->artwork_title,
                    'sale_price' => (float) $order->price,
                    'commission_rate' => (float) $order->commission_rate,
                    'commission_amount' => (float) $order->commission_amount,
                    'artist_amount' => (float) $order->artist_amount,
                ]
            );
        }
    }

    /**
     * Insert a standard Laravel database notification.
     */
    private function insertDatabaseNotification(
        User $recipient,
        string $type,
        array $data
    ): void {
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => $recipient->getMorphClass(),
            'notifiable_id' => $recipient->getKey(),
            'data' => json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * A Fail callback alone is not proof that payment failed.
     */
    public function fail(Request $request): RedirectResponse
    {
        return redirect()->route('home')->with(
            'error',
            'The payment was not confirmed. If money was deducted, contact Artfy support before trying again.'
        );
    }

    /**
     * A Cancel callback alone is not proof that payment failed.
     */
    public function cancel(Request $request): RedirectResponse
    {
        return redirect()->route('home')->with(
            'error',
            'The checkout was cancelled or not completed. If money was deducted, contact Artfy support before trying again.'
        );
    }

    /**
     * Process server-to-server SSLCOMMERZ IPN.
     */
    public function ipn(
        Request $request,
        SSLCommerzService $sslCommerz
    ) {
        $data = $request->validate([
            'tran_id' => ['required', 'string', 'max:100'],
            'val_id' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->verifyAndRecordPayment(
                $data['tran_id'],
                $data['val_id'],
                $sslCommerz
            );

            return response('IPN processed', 200);
        } catch (Throwable $e) {
            report($e);

            return response('IPN could not be verified', 422);
        }
    }
}
