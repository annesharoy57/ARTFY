<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Exhibition;
use App\Models\ExhibitionApplication;
use App\Models\Order;
use App\Models\User;
use App\Services\DeliveryService;
use App\Services\SSLCommerzService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ArtworkPurchaseController extends Controller
{
    /**
     * Show checkout for an approved artwork.
     */
    public function create(string $slug): View
    {
        abort_unless(
            auth()->check() && auth()->user()->isArtLover(),
            403,
            'Only art lovers can purchase artworks.'
        );

        $exhibition = null;
        $isExhibitionArtwork = preg_match('/^exhibition-(\d+)$/', $slug, $matches) === 1;

        if ($isExhibitionArtwork) {
            $application = ExhibitionApplication::query()
                ->with(['artist.artistProfile', 'exhibition'])
                ->where('status', 'confirmed')
                ->where('payment_status', 'verified')
                ->findOrFail((int) $matches[1]);
            $exhibition = $application->exhibition;

            abort_unless($exhibition->status === 'published', 404);
            abort_unless(
                $exhibition->visitIsOpen(),
                404,
                'This exhibition artwork is available for purchase only while the exhibition is open.'
            );
            abort_if(
                $application->orders()
                    ->where('payment_status', 'verified')
                    ->exists(),
                404,
                'This exhibition artwork has already been sold.'
            );
            abort_unless(
                $application->artwork_type !== 'digital'
                    || filled($application->digital_file_path),
                404,
                'The digital PDF for this exhibition artwork is unavailable.'
            );

            $artwork = new Artwork([
                'title' => $application->artwork_title,
                'image' => $application->artwork_image,
                'price' => $application->artwork_price,
                'user_id' => $application->user_id,
                'artwork_type' => $application->artwork_type ?: 'physical',
            ]);
            $artwork->id = $application->id;
            $artwork->setRelation('artist', $application->artist);
        } else {
            abort_unless(
                preg_match('/^uploaded-(\d+)$/', $slug, $matches),
                404
            );

            $artwork = Artwork::with('artist.artistProfile')
                ->where('status', 'available')
                ->where('moderation_status', 'approved')
                ->where('is_sold', false)
                ->findOrFail((int) $matches[1]);
        }

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
            'purchaseSlug' => $slug,
            'isExhibitionArtwork' => $isExhibitionArtwork,
            'exhibition' => $exhibition,
            'artistName' => $artwork->artist?->artistProfile?->artist_name
                ?: ($artwork->artist?->name ?? 'Artfy Artist'),
            'isDigitalArtwork' => $isExhibitionArtwork
                && $artwork->artwork_type === 'digital',
        ]);
    }

    /**
     * Geocode the buyer's address for the checkout map.
     */
    public function geocodeDestination(
        Request $request,
        DeliveryService $deliveryService
    ): JsonResponse {
        $user = $request->user();

        abort_unless(
            $user !== null
                && ($user->isArtLover() || $user->isArtist()),
            403,
            'Only artists and art lovers can search for addresses.'
        );

        $validated = $request->validate([
            'shipping_address' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],
        ]);

        try {
            $matches = $deliveryService->geocodeCandidates(
                trim($validated['shipping_address'])
            );

            if ($matches === []) {
                return response()->json([
                    'message' => 'No exact result was found. Add the area or district, or click your delivery point directly on the map.',
                ], 422);
            }

            return response()->json([
                'results' => $matches,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' =>
                    'We could not find this address in Bangladesh. '
                    . 'Please enter a more complete address or select '
                    . 'the location on the map.',
            ], 422);
        }
    }

    /**
     * Create an order, calculate delivery, and redirect to SSLCOMMERZ.
     */
    public function store(
        Request $request,
        string $slug,
        SSLCommerzService $sslCommerz,
        DeliveryService $deliveryService
    ): RedirectResponse {
        abort_unless(
            auth()->check() && auth()->user()->isArtLover(),
            403,
            'Only art lovers can purchase artworks.'
        );

        $exhibitionApplicationId = null;
        $isExhibitionArtwork = preg_match('/^exhibition-(\d+)$/', $slug, $matches) === 1;
        $digitalExhibitionPurchase = false;

        if ($isExhibitionArtwork) {
            $exhibitionApplicationId = (int) $matches[1];
            $artworkId = 0;
            $application = ExhibitionApplication::query()
                ->with('exhibition')
                ->findOrFail($exhibitionApplicationId);
            $digitalExhibitionPurchase = $application->artwork_type === 'digital';

            abort_unless(
                $application->exhibition->visitIsOpen(),
                409,
                'This exhibition artwork is available for purchase only while the exhibition is open.'
            );
        } else {
            abort_unless(
                preg_match('/^uploaded-(\d+)$/', $slug, $matches),
                404
            );
            $artworkId = (int) $matches[1];
        }

        /*
         * Resolve existing orders before creating another checkout.
         * Never treat an unknown gateway response as a failed payment.
         */
        $resolution = $this->resolveExistingPendingOrders(
            $artworkId,
            $sslCommerz,
            $exhibitionApplicationId
        );

        if (in_array($resolution, ['paid', 'already_processed'], true)) {
            return redirect()->route('home')->with(
                'success',
                'Your payment has already been processed. '
                . 'Please check your order history.'
            );
        }

        $shippingAddress = null;
        $delivery = ['distance_km' => 0, 'delivery_fee' => 0];

        if (! $digitalExhibitionPurchase) {
            $validated = $request->validate([
                'shipping_address' => ['required', 'string', 'min:10', 'max:1000'],
                'shipping_latitude' => ['required', 'numeric', 'between:20.5,26.7'],
                'shipping_longitude' => ['required', 'numeric', 'between:88.0,92.8'],
            ]);

            $shippingAddress = trim($validated['shipping_address']);
            $customerLatitude = (float) $validated['shipping_latitude'];
            $customerLongitude = (float) $validated['shipping_longitude'];

            if (! is_finite($customerLatitude) || ! is_finite($customerLongitude)) {
                return back()->withInput()->withErrors([
                    'shipping_address' => 'The selected map location is invalid. Please select your delivery location again.',
                ]);
            }
        }

        /*
         * Artist pickup coordinates remain private.
         */
        if (! $digitalExhibitionPurchase && $isExhibitionArtwork) {
            $deliveryApplication = ExhibitionApplication::with('artist.artistProfile')
                ->findOrFail($exhibitionApplicationId);
            $artistProfile = $deliveryApplication->artist?->artistProfile;
        } elseif (! $digitalExhibitionPurchase) {
            $deliveryArtwork = Artwork::with('artist.artistProfile')
                ->findOrFail($artworkId);
            $artistProfile = $deliveryArtwork->artist?->artistProfile;
        }

        if (! $digitalExhibitionPurchase && (
            !$artistProfile
            || $artistProfile->latitude === null
            || $artistProfile->longitude === null
        )) {
            return back()
                ->withInput()
                ->withErrors([
                    'shipping_address' =>
                        'This artist has not configured delivery coordinates yet. '
                        . 'Please contact the artist.',
                ]);
        }

        /*
         * Calculate the road distance and delivery fee on the server.
         */
        if (! $digitalExhibitionPurchase) try {
            $delivery = $deliveryService->calculateFromCoordinates(
                (float) $artistProfile->latitude,
                (float) $artistProfile->longitude,
                $shippingAddress,
                $customerLatitude,
                $customerLongitude
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'shipping_address' =>
                        'We could not calculate delivery for this map location. '
                        . 'Please select a valid location in Bangladesh '
                        . 'and try again.',
                ]);
        }

        /*
         * Keep delivery pricing server-controlled.
         * This retains the existing minimum of ৳40 and maximum of ৳300.
         */
        if (! $digitalExhibitionPurchase && (
            !isset($delivery['distance_km'], $delivery['delivery_fee'])
            || !is_numeric($delivery['distance_km'])
            || !is_numeric($delivery['delivery_fee'])
            || !is_finite((float) $delivery['distance_km'])
            || !is_finite((float) $delivery['delivery_fee'])
            || (float) $delivery['distance_km'] < 0
            || (float) $delivery['delivery_fee'] < 40
            || (float) $delivery['delivery_fee'] > 300
        )) {
            return back()
                ->withInput()
                ->withErrors([
                    'shipping_address' =>
                        'The delivery fee or route distance is invalid. '
                        . 'Please select your delivery location again.',
                ]);
        }

        /*
         * Create the order under a database transaction.
         */
        try {
            $order = DB::transaction(function () use (
                $artworkId,
                $exhibitionApplicationId,
                $isExhibitionArtwork,
                $shippingAddress,
                $delivery,
                $digitalExhibitionPurchase
            ) {
                if ($isExhibitionArtwork) {
                    $application = ExhibitionApplication::query()
                        ->whereKey($exhibitionApplicationId)
                        ->lockForUpdate()
                        ->firstOrFail();
                    $exhibition = Exhibition::query()
                        ->whereKey($application->exhibition_id)
                        ->firstOrFail();

                    abort_unless(
                        $application->status === 'confirmed'
                            && $application->payment_status === 'verified'
                            && $exhibition->status === 'published'
                            && $exhibition->visitIsOpen()
                            && $application->artwork_price !== null
                            && (float) $application->artwork_price > 0,
                        409,
                        'This exhibition artwork is no longer available for purchase.'
                    );

                    abort_unless(
                        $application->artwork_type !== 'digital'
                            || filled($application->digital_file_path),
                        409,
                        'The digital PDF for this exhibition artwork is unavailable.'
                    );
                    abort_unless(
                        ($application->artwork_type === 'digital') === $digitalExhibitionPurchase,
                        409,
                        'The artwork format changed during checkout. Please start checkout again.'
                    );

                    $existingOrder = $application->orders()
                        ->where(function ($query) {
                            $query->where('payment_status', 'verified')
                                ->orWhere(function ($pending) {
                                    $pending->where('payment_status', 'pending')
                                        ->where('order_status', 'pending');
                                });
                        })
                        ->exists();
                    $orderArtworkId = null;
                    $artistId = $application->user_id;
                    $title = $application->artwork_title;
                    $price = round((float) $application->artwork_price, 2);
                } else {
                    $artwork = Artwork::whereKey($artworkId)
                        ->lockForUpdate()
                        ->firstOrFail();

                    abort_unless(
                        $artwork->status === 'available'
                            && $artwork->moderation_status === 'approved'
                            && !$artwork->is_sold
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
                    $orderArtworkId = $artwork->id;
                    $artistId = $artwork->user_id;
                    $title = $artwork->title;
                    $price = round((float) $artwork->price, 2);
                }

                abort_if(
                    $existingOrder,
                    409,
                    'A previous checkout is still awaiting confirmation. '
                    . 'Please wait or contact Artfy support before trying again.'
                );

                /*
                 * Commission applies to artwork price only.
                 * Delivery is added to the buyer's total, not commission.
                 */
                $deliveryFee = $digitalExhibitionPurchase ? 0 : round(
                    (float) $delivery['delivery_fee'],
                    2
                );

                $totalAmount = round($price + $deliveryFee, 2);

                $commissionRate = 15.00;

                $commission = round(
                    $price * $commissionRate / 100,
                    2
                );

                return Order::create([
                    'order_number' => 'ART-' . Str::upper(
                        Str::random(12)
                    ),

                    'buyer_id' => auth()->id(),
                    'artwork_id' => $orderArtworkId,
                    'exhibition_application_id' => $exhibitionApplicationId,
                    'artist_id' => $artistId,
                    'artwork_title' => $title,

                    'price' => $price,

                    'shipping_address' => $shippingAddress,
                    'delivery_distance_km' => $delivery['distance_km'],
                    'delivery_fee' => $deliveryFee,
                    'total_amount' => $totalAmount,

                    'commission_rate' => $commissionRate,
                    'commission_amount' => $commission,
                    'artist_amount' => round(
                        $price - $commission,
                        2
                    ),

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

        /*
         * Initiate SSLCOMMERZ for artwork price plus delivery fee.
         */
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
                    (float) $order->total_amount,
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
            /*
             * Do not mark this order failed automatically.
             * The gateway might have created a session even if the
             * application failed to receive or save its response.
             */
            report($e);

            return redirect()
                ->route(
                    $isExhibitionArtwork ? 'exhibitions.show' : 'artworks.show',
                    $isExhibitionArtwork
                        ? ExhibitionApplication::findOrFail($exhibitionApplicationId)->exhibition->slug
                        : 'uploaded-' . $artworkId
                )
                ->with(
                    'error',
                    'We could not confirm the checkout session. '
                    . 'Please check your payment status before retrying. '
                    . 'If money may have been deducted, contact Artfy support.'
                );
        }
    }

    /**
     * Resolve pending orders before another checkout.
     *
     * Only a confirmed gateway failure can release an order.
     * PENDING, unknown statuses, mismatched transactions and gateway
     * errors keep the order protected.
     */
    private function resolveExistingPendingOrders(
        int $artworkId,
        SSLCommerzService $sslCommerz,
        ?int $exhibitionApplicationId = null
    ): string {
        $orders = Order::query()
            ->when(
                $exhibitionApplicationId !== null,
                fn ($query) => $query->where(
                    'exhibition_application_id',
                    $exhibitionApplicationId
                ),
                fn ($query) => $query->where('artwork_id', $artworkId)
            )
            ->where('payment_status', 'pending')
            ->where('order_status', 'pending')
            ->orderBy('id')
            ->get();

        foreach ($orders as $order) {
            if ($order->payment_method !== 'sslcommerz') {
                abort(
                    409,
                    'A previous manual-payment order is awaiting review. '
                    . 'Please contact Artfy support.'
                );
            }

            /*
             * Query the gateway. Any exception is an unknown outcome,
             * not proof that payment failed.
             */
            try {
                if (filled($order->gateway_session_key)) {
                    $gateway = $sslCommerz->queryBySessionKey(
                        $order->gateway_session_key
                    );
                } else {
                    $gateway = $sslCommerz->queryByTransactionId(
                        $order->order_number
                    );
                }
            } catch (Throwable $e) {
                report($e);

                abort(
                    409,
                    'We could not confirm the previous checkout status. '
                    . 'Your order has not been changed. Please try checking '
                    . 'again later or contact Artfy support.'
                );
            }

            if (
                !is_array($gateway)
                || ($gateway['APIConnect'] ?? null) !== 'DONE'
            ) {
                abort(
                    409,
                    'The gateway could not confirm the previous checkout. '
                    . 'Your order remains protected against duplicate payment.'
                );
            }

            /*
             * The session query can return a single transaction directly.
             * The transaction query can return transactions in "element".
             */
            if (isset($gateway['element'])) {
                if (!is_array($gateway['element'])) {
                    abort(
                        409,
                        'The gateway returned an unexpected transaction response. '
                        . 'Please contact Artfy support.'
                    );
                }

                $transactions = $gateway['element'];
            } elseif (isset($gateway['status'])) {
                $transactions = [$gateway];
            } else {
                $transactions = [];
            }

            /*
             * When multiple records are returned, find the transaction
             * belonging to this exact Artfy order.
             */
            $transaction = null;

            foreach ($transactions as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }

                if (
                    (string) ($candidate['tran_id'] ?? '')
                    === (string) $order->order_number
                ) {
                    $transaction = $candidate;
                    break;
                }
            }

            /*
             * A direct session response may not have an element array.
             */
            if (
                $transaction === null
                && !isset($gateway['element'])
                && isset($gateway['status'])
                && (
                    (string) ($gateway['tran_id'] ?? '')
                    === (string) $order->order_number
                )
            ) {
                $transaction = $gateway;
            }

            /*
             * A missing transaction is not enough to cancel an order
             * that already has a gateway session key.
             */
            if ($transaction === null) {
                $confirmedNoTransaction =
                    blank($order->gateway_session_key)
                    && (int) ($gateway['no_of_trans_found'] ?? -1) === 0;

                if (!$confirmedNoTransaction) {
                    abort(
                        409,
                        'No matching transaction could be confirmed. '
                        . 'Your order has not been changed. Contact Artfy support.'
                    );
                }

                $this->markPendingOrderFailed(
                    $order->id,
                    'The gateway transaction query confirmed that no transaction was found.'
                );

                continue;
            }

            /*
             * Verify transaction identity before using its status.
             */
            if (
                (string) ($transaction['tran_id'] ?? '')
                !== (string) $order->order_number
            ) {
                abort(
                    409,
                    'The gateway transaction does not match your order. '
                    . 'Please contact Artfy support.'
                );
            }

            $status = strtoupper(
                trim((string) ($transaction['status'] ?? ''))
            );

            /*
             * A successful gateway status still requires the existing
             * validation-ID verification flow.
             */
            if (in_array($status, ['VALID', 'VALIDATED'], true)) {
                $validationId = $transaction['val_id'] ?? null;

                if (blank($validationId)) {
                    abort(
                        409,
                        'The gateway reports a completed transaction without '
                        . 'a validation ID. Please contact Artfy support.'
                    );
                }

                try {
                    $result = $this->verifyAndRecordPayment(
                        $order->order_number,
                        (string) $validationId,
                        $sslCommerz
                    );
                } catch (Throwable $e) {
                    report($e);

                    abort(
                        409,
                        'The payment requires further verification. '
                        . 'Your order has not been cancelled. Please contact Artfy support.'
                    );
                }

                if (in_array(
                    $result,
                    ['paid', 'already_processed'],
                    true
                )) {
                    return $result;
                }

                abort(
                    409,
                    'The previous payment requires review. '
                    . 'Do not pay again until Artfy support resolves it.'
                );
            }

            /*
             * This is the result currently returned for order #3.
             * Keep the order unchanged while the gateway says PENDING.
             */
            if ($status === 'PENDING') {
                abort(
                    409,
                    'Your previous checkout is still pending at SSLCOMMERZ. '
                    . 'Your order has not been cancelled. Please wait and '
                    . 'check again before attempting another payment.'
                );
            }

            /*
             * Only explicit final failure statuses can release the order.
             * Unknown statuses must never be treated as failure.
             */
            if (in_array(
                $status,
                [
                    'FAILED',
                    'CANCELLED',
                    'CANCELED',
                    'EXPIRED',
                    'UNATTEMPTED',
                ],
             true

            )) {
                /*
                 * Only release an order after a final failure whose
                 * identity, currency and amount match. Successful orders
                 * are checked through the validator API above, which has
                 * the authoritative currency and amount fields.
                 */
                $currency = strtoupper((string) (
                    $transaction['currency_type']
                    ?? $transaction['currency']
                    ?? ''
                ));
                if ($currency !== 'BDT') {
                    abort(
                        409,
                        'The failed gateway transaction currency could not be verified. '
                        . 'The order was left unchanged.'
                    );
                }

                $gatewayAmount = round(
                    (float) ($transaction['amount'] ?? -1),
                    2
                );
                $expectedAmount = round(
                    (float) ($order->total_amount ?? $order->price),
                    2
                );
                if (
                    ! is_numeric($transaction['amount'] ?? null)
                    || $gatewayAmount !== $expectedAmount
                ) {
                    abort(
                        409,
                        'The failed gateway transaction amount does not match this order. '
                        . 'The order was left unchanged.'
                    );
                }

                $this->markPendingOrderFailed(
                    $order->id,
                    'SSLCOMMERZ confirmed a failed or cancelled transaction.'
                );

                continue;
            }

            abort(
                409,
                'The gateway returned an unrecognized payment status. '
                . 'Your order has not been changed. Please contact Artfy support.'
            );
        }

        return 'clear';
    }

    /**
     * Mark an order failed only after a confirmed gateway outcome.
     */
    private function markPendingOrderFailed(
        int $orderId,
        string $reason
    ): void {
        DB::transaction(function () use ($orderId, $reason) {
            $lockedOrder = Order::whereKey($orderId)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Recheck state because a callback or IPN may have updated
             * the order while the gateway query was running.
             */
            if (
                $lockedOrder->payment_status !== 'pending'
                || $lockedOrder->order_status !== 'pending'
            ) {
                abort(
                    409,
                    'The order changed while being checked. '
                    . 'Refresh the page and review your order history.'
                );
            }

            $lockedOrder->update([
                'payment_status' => 'failed',
                'order_status' => 'cancelled',
                'rejection_reason' => $reason,
            ]);
        });
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
                    'Payment successful! Your order '
                    . $data['tran_id'] . ' has been recorded.'
                );
            }

            if ($result === 'review') {
                return redirect()->route('home')->with(
                    'error',
                    'Your payment requires additional review. '
                    . 'Please contact Artfy support and do not pay again.'
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
                'The payment could not be confirmed. Please contact Artfy support '
                . 'before attempting another payment.'
            );
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('home')->with(
                'error',
                'We could not confirm your payment yet. '
                . 'Please contact Artfy support before attempting another payment.'
            );
        }
    }

    /** Recheck a pending gateway payment from the admin sales ledger. */
    public function refreshPendingPayment(
        Request $request,
        Order $order,
        SSLCommerzService $sslCommerz
    ): RedirectResponse {
        abort_unless($request->user()?->isAdmin(), 403);

        if ($order->payment_status === 'verified') {
            return redirect()->route('admin.sales.index')->with(
                'success',
                'Order ' . $order->order_number . ' is already verified.'
            );
        }

        abort_unless(
            $order->payment_method === 'sslcommerz'
                && $order->payment_status === 'pending'
                && $order->order_status === 'pending',
            422,
            'Only pending SSLCOMMERZ orders can be checked.'
        );

        try {
            $this->resolveExistingPendingOrders(
                (int) ($order->artwork_id ?? 0),
                $sslCommerz,
                $order->exhibition_application_id
                    ? (int) $order->exhibition_application_id
                    : null
            );
        } catch (Throwable $exception) {
            if ($exception instanceof HttpExceptionInterface) {
                $detail = trim($exception->getMessage());
            } else {
                report($exception);
                $detail = 'The gateway status lookup failed. Check the application log for the error.';
            }

            return redirect()->route('admin.sales.index')->with(
                'error',
                'Payment status check for '
                    . $order->order_number . ': '
                    . $detail
                    . ' The order was left unchanged.'
            );
        }

        $order->refresh();

        if ($order->payment_status === 'verified') {
            return redirect()->route('admin.sales.index')->with(
                'success',
                'Payment verified. Order ' . $order->order_number
                    . ' is now recorded as a sale.'
            );
        }

        return redirect()->route('admin.sales.index')->with(
            'error',
            'SSLCOMMERZ has not confirmed order '
                . $order->order_number
                . ' yet. Its payment status was left unchanged.'
        );
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
        $order = Order::where(
            'order_number',
            $transactionId
        )->first();

        if (!$order) {
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

        if (!in_array($gatewayStatus, ['VALID', 'VALIDATED'], true)) {
            throw new RuntimeException(
                'SSLCOMMERZ did not validate the transaction.'
            );
        }

        if (
            (string) ($gateway['tran_id'] ?? '')
            !== (string) $order->order_number
        ) {
            throw new RuntimeException(
                'The gateway transaction ID does not match the Artfy order.'
            );
        }

        $gatewayCurrency = strtoupper((string) (
            $gateway['currency_type']
            ?? $gateway['currency']
            ?? ''
        ));

        if ($gatewayCurrency !== 'BDT') {
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

        if (!is_numeric($gateway['amount'] ?? null)) {
            throw new RuntimeException(
                'The gateway returned an invalid transaction amount.'
            );
        }

        $gatewayAmount = round(
            (float) $gateway['amount'],
            2
        );

        $orderAmount = round(
            (float) ($order->total_amount ?? $order->price),
            2
        );

        if ($gatewayAmount !== $orderAmount) {
            throw new RuntimeException(
                'The gateway amount does not match the Artfy order.'
            );
        }

        if ((string) ($gateway['risk_level'] ?? '0') === '1') {
            report(new RuntimeException(
                'SSLCOMMERZ flagged order '
                . $order->order_number
                . ' for risk review.'
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

            if ($lockedOrder->exhibition_application_id !== null) {
                $application = ExhibitionApplication::query()
                    ->whereKey($lockedOrder->exhibition_application_id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $exhibition = Exhibition::findOrFail($application->exhibition_id);
                $alreadySold = $application->orders()
                    ->where('payment_status', 'verified')
                    ->where('id', '!=', $lockedOrder->id)
                    ->exists();

                if (
                    $application->status !== 'confirmed'
                    || $application->payment_status !== 'verified'
                    || $exhibition->status !== 'published'
                    || $alreadySold
                ) {
                    throw new RuntimeException(
                        'The exhibition artwork is no longer available. '
                        . 'Manual payment review is required for order '
                        . $lockedOrder->order_number
                    );
                }

                $lockedOrder->update([
                    'payment_status' => 'verified',
                    'order_status' => $application->artwork_type === 'digital'
                        ? 'completed'
                        : 'processing',
                    'fulfillment_status' => $application->artwork_type === 'digital'
                        ? 'completed'
                        : 'processing',
                    'transaction_id' => $lockedOrder->order_number,
                    'gateway_val_id' => $validationId,
                    'verified_at' => now(),
                ]);

                $this->createSaleNotifications($lockedOrder, null);
            } else {
                $artwork = Artwork::whereKey($lockedOrder->artwork_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $artwork->status !== 'available'
                    || $artwork->moderation_status !== 'approved'
                    || $artwork->is_sold
                ) {
                    throw new RuntimeException(
                        'The artwork is no longer available. '
                        . 'Manual payment review is required for order '
                        . $lockedOrder->order_number
                    );
                }

                $lockedOrder->update([
                    'payment_status' => 'verified',
                    'order_status' => $artwork->artwork_type === 'digital'
                        ? 'completed'
                        : 'processing',
                    'fulfillment_status' => $artwork->artwork_type === 'digital'
                        ? 'completed'
                        : 'processing',
                    'transaction_id' => $lockedOrder->order_number,
                    'gateway_val_id' => $validationId,
                    'verified_at' => now(),
                ]);

                $artwork->update([
                    'is_sold' => true,
                    'status' => 'sold',
                ]);

                $this->createSaleNotifications($lockedOrder, $artwork);
            }

            return 'paid';
        });
    }

    /**
     * Notify the artist and every admin about a verified sale.
     */
    private function createSaleNotifications(
        Order $order,
        ?Artwork $artwork
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

        $commissionRate = number_format(
            (float) $order->commission_rate,
            2
        );

        $artist = User::find($order->artist_id);

        if ($artist) {
            $artworkUrl = $artwork
                ? route('artworks.show', 'uploaded-'.$artwork->id, false)
                : route(
                    'exhibitions.show',
                    $order->exhibitionApplication->exhibition->slug,
                    false
                );
            $this->insertDatabaseNotification(
                $artist,
                'artfy.artwork_sold',
                [
                    'title' => 'Your artwork has been sold!',

                    'message' => '"' . $order->artwork_title
                        . '" was purchased for BDT ' . $salePrice
                        . '. Your recorded amount: BDT '
                        . $artistAmount . '.',

                    'url' => $artworkUrl,
                    'order_number' => $order->order_number,
                    'sale_price' => (float) $order->price,
                    'commission_amount' => (float) $order->commission_amount,
                    'artist_amount' => (float) $order->artist_amount,
                ]
            );
        }

        $admins = User::query()
            ->where('role', 'admin')
            ->get();

        foreach ($admins as $admin) {
            $this->insertDatabaseNotification(
                $admin,
                'artfy.commission_earned',
                [
                    'title' => 'New artwork sale — commission earned',

                    'message' => '"' . $order->artwork_title
                        . '" sold for BDT ' . $salePrice
                        . '. Artfy commission: BDT ' . $commission
                        . ' (' . $commissionRate . '%).'
                        . ' Order: ' . $order->order_number . '.',

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
            'The payment was not confirmed. If money was deducted, '
            . 'contact Artfy support before trying again.'
        );
    }

    /**
     * A Cancel callback alone is not proof that payment failed.
     */
    public function cancel(Request $request): RedirectResponse
    {
        return redirect()->route('home')->with(
            'error',
            'The checkout was cancelled or not completed. '
            . 'If money was deducted, contact Artfy support before trying again.'
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

    /**
     * Show the logged-in buyer's purchases.
     */
    public function myPurchases(): View
    {
        $orders = Order::with([
            'artwork.artist.artistProfile',
            'exhibitionApplication.exhibition',
        ])
            ->where('buyer_id', auth()->id())
            ->latest()
            ->get();

        return view('artfy.my-purchases', compact('orders'));
    }

    /**
     * Download the original file for a verified digital purchase.
     */
    public function downloadDigitalArtwork(Order $order)
    {
        // Only the buyer who owns this order can download it.
        abort_unless(
            (int) $order->buyer_id === (int) auth()->id(),
            403
        );

        // Payment must be verified by the existing payment flow.
        abort_unless(
            $order->payment_status === 'verified',
            403
        );

        // Original files are stored on Laravel's private local disk.
        $disk = Storage::disk('local');
        $artwork = $order->artwork;
        $application = $order->exhibitionApplication;
        $filePath = $artwork?->digital_file_path
            ?: ($application?->artwork_type === 'digital'
                ? $application->digital_file_path
                : null);

        abort_unless(filled($filePath), 404);

        abort_unless(
            $disk->exists($filePath),
            404
        );

        return $disk->download($filePath);
    }

}
