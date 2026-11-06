<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use App\Models\ExhibitionApplication;
use App\Services\SSLCommerzService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ExhibitionApplicationController extends Controller
{
    public function apply(
        Request $request,
        Exhibition $exhibition,
        SSLCommerzService $sslCommerz
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user && $user->role === 'artist', 403);

        $baseUrl = rtrim((string) config('app.url'), '/');
        if (
            blank($baseUrl)
            || ! filter_var($baseUrl, FILTER_VALIDATE_URL)
            || strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME)) !== 'https'
            || str_contains(strtolower($baseUrl), 'localhost')
            || str_contains($baseUrl, '127.0.0.1')
        ) {
            return back()->with(
                'error',
                'The public HTTPS Artfy URL is not configured. Please contact the administrator.'
            );
        }

        if (
            blank(config('services.sslcommerz.store_id'))
            || blank(config('services.sslcommerz.store_password'))
            || blank(config('services.sslcommerz.api_url'))
        ) {
            return back()->with(
                'error',
                'SSLCOMMERZ is not configured for exhibition payments. Please contact the administrator.'
            );
        }

        try {
            $application = DB::transaction(function () use (
                $exhibition,
                $user
            ): ExhibitionApplication {
                $lockedExhibition = Exhibition::query()
                    ->whereKey($exhibition->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $application = ExhibitionApplication::query()
                    ->where('exhibition_id', $lockedExhibition->id)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();
                $needsNewReservation = ! $application
                    || $application->status === 'cancelled'
                    || (
                        $application->status === 'pending'
                        && ! $application->isActivePaymentReservation()
                    );

                if ($application?->payment_status === 'verified') {
                    return $application->load('exhibition');
                }

                if (
                    $lockedExhibition->status !== 'published'
                    || (
                        $needsNewReservation
                        && ! $lockedExhibition->registrationIsOpen()
                    )
                ) {
                    throw new RuntimeException(
                        'Applications for this exhibition are not open.'
                    );
                }

                $reservedCount = $lockedExhibition->applications()
                    ->reservesArtistPlace()
                    ->count();

                if (
                    $needsNewReservation
                    && $reservedCount >= $lockedExhibition->max_artists
                ) {
                    throw new RuntimeException(
                        'This exhibition has reached its artist capacity with paid registrations.'
                    );
                }

                if (! $application) {
                    $application = new ExhibitionApplication([
                        'exhibition_id' => $lockedExhibition->id,
                        'user_id' => $user->id,
                        'registration_fee' => $lockedExhibition->registration_fee,
                    ]);
                } elseif ($needsNewReservation) {
                    $application->registration_fee =
                        $lockedExhibition->registration_fee;
                    $application->status = 'pending';
                    $application->payment_status = 'pending';
                    $application->payment_reference = null;
                }

                if (blank($application->payment_reference)) {
                    $application->payment_reference = 'EXH-'
                        .$lockedExhibition->id.'-'
                        .$user->id.'-'
                        .Str::upper(Str::random(10));
                }

                $application->payment_method = 'sslcommerz';
                $application->payment_status = 'pending';
                $application->payment_submitted_at = now();
                $application->save();

                return $application;
            });
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if ($application->payment_status === 'verified') {
            return redirect()->route(
                'exhibitions.show',
                $application->exhibition->slug
            )->with(
                'success',
                $application->status === 'awaiting_submission'
                    ? 'Your payment is verified. Submit your artwork to complete the application.'
                    : 'Your exhibition application is already complete.'
            );
        }

        try {
            $response = $sslCommerz->initiate([
                'total_amount' => number_format(
                    (float) $application->registration_fee,
                    2,
                    '.',
                    ''
                ),
                'currency' => 'BDT',
                'tran_id' => $application->payment_reference,
                'success_url' => $baseUrl.'/exhibition/payment/success',
                'fail_url' => $baseUrl.'/exhibition/payment/fail',
                'cancel_url' => $baseUrl.'/exhibition/payment/cancel',
                'ipn_url' => $baseUrl.'/exhibition/payment/ipn',
                'product_name' => 'Online Exhibition Registration',
                'product_category' => 'Exhibition',
                'product_profile' => 'general',
                'cus_name' => $user->name,
                'cus_email' => $user->email,
                'cus_add1' => 'Not provided',
                'cus_city' => 'Dhaka',
                'cus_postcode' => '1200',
                'cus_country' => 'Bangladesh',
                'cus_phone' => $user->phone ?: '01700000000',
                'shipping_method' => 'NO',
                'num_of_item' => 1,
                'value_a' => (string) $application->id,
                'value_b' => 'exhibition_application',
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
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Could not start exhibition checkout. Your place is temporarily reserved; please retry from this exhibition page.'
            );
        }
    }

    public function submitArtwork(
        Request $request,
        Exhibition $exhibition
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user && $user->role === 'artist', 403);

        $application = $exhibition->applications()
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (
            $application->payment_status !== 'verified'
            || $application->status !== 'awaiting_submission'
        ) {
            return back()->with(
                'error',
                'Submit artwork details only after the exhibition fee is verified and before your application is finalized.'
            );
        }

        $validated = $this->validateArtwork($request, true);

        try {
            $imagePath = $request->file('artwork_image')->store(
                'exhibition-artworks',
                'public'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput($request->except('artwork_image'))
                ->withErrors([
                    'artwork_image' => 'The artwork image could not be uploaded. Please try a JPG, PNG, or WebP image up to 5 MB.',
                ]);
        }

        if (! $imagePath) {
            return back()
                ->withInput($request->except('artwork_image'))
                ->withErrors([
                    'artwork_image' => 'The artwork image could not be saved. Please try again or contact support.',
                ]);
        }

        try {
            DB::transaction(function () use (
                $application,
                $validated,
                $imagePath
            ): void {
                $locked = ExhibitionApplication::query()
                    ->whereKey($application->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->payment_status !== 'verified'
                    || $locked->status !== 'awaiting_submission'
                ) {
                    throw new RuntimeException(
                        'The exhibition application is no longer awaiting artwork.'
                    );
                }

                $locked->artwork_title = $validated['artwork_title'];
                $locked->artwork_description =
                    $validated['artwork_description'];
                $locked->artwork_price = $validated['artwork_price'];
                $locked->artwork_image = $imagePath;
                $locked->status = 'confirmed';
                $locked->save();
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($imagePath);
            report($exception);

            return back()->with(
                'error',
                'Your exhibition artwork could not be submitted. Please try again.'
            );
        }

        return redirect()->route(
            'exhibitions.show',
            $exhibition->slug
        )->with(
            'success',
            'Your artwork has been submitted and your exhibition application is complete.'
        );
    }

    public function updateArtwork(
        Request $request,
        Exhibition $exhibition
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user && $user->role === 'artist', 403);

        $application = $exhibition->applications()
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (
            $application->payment_status !== 'verified'
            || $application->status !== 'confirmed'
        ) {
            return back()->with(
                'error',
                'Only a completed, paid exhibition application can be edited.'
            );
        }

        if (
            $exhibition->status !== 'published'
            || ! $exhibition->registrationIsOpen()
        ) {
            return back()->with(
                'error',
                'Artwork can only be edited while exhibition applications are open.'
            );
        }

        $validated = $this->validateArtwork($request, false);
        $imagePath = null;

        if ($request->hasFile('artwork_image')) {
            try {
                $imagePath = $request->file('artwork_image')->store(
                    'exhibition-artworks',
                    'public'
                );
            } catch (Throwable $exception) {
                report($exception);

                return back()
                    ->withInput($request->except('artwork_image'))
                    ->withErrors([
                        'artwork_image' => 'The artwork image could not be uploaded. Please try a JPG, PNG, or WebP image up to 5 MB.',
                    ]);
            }

            if (! $imagePath) {
                return back()
                    ->withInput($request->except('artwork_image'))
                    ->withErrors([
                        'artwork_image' => 'The artwork image could not be saved. Please try again or contact support.',
                    ]);
            }
        }

        $oldImagePath = null;

        try {
            DB::transaction(function () use (
                $application,
                $exhibition,
                $user,
                $validated,
                $imagePath,
                &$oldImagePath
            ): void {
                $lockedExhibition = Exhibition::query()
                    ->whereKey($exhibition->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $locked = ExhibitionApplication::query()
                    ->whereKey($application->id)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->payment_status !== 'verified'
                    || $locked->status !== 'confirmed'
                    || $lockedExhibition->status !== 'published'
                    || ! $lockedExhibition->registrationIsOpen()
                ) {
                    throw new DomainException(
                        'Artwork can only be edited while exhibition applications are open.'
                    );
                }

                $oldImagePath = $locked->artwork_image;
                $locked->artwork_title = $validated['artwork_title'];
                $locked->artwork_description =
                    $validated['artwork_description'];
                $locked->artwork_price = $validated['artwork_price'];
                if ($imagePath !== null) {
                    $locked->artwork_image = $imagePath;
                }
                $locked->save();
            });
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }
            report($exception);

            return back()->with(
                'error',
                $exception instanceof DomainException
                    ? $exception->getMessage()
                    : 'Your exhibition artwork could not be updated. Please try again.'
            );
        }

        if ($imagePath !== null && $oldImagePath !== null) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()->route(
            'exhibitions.show',
            $exhibition->slug
        )->with('success', 'Your exhibition artwork has been updated.');
    }

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
            return redirect()->route('exhibitions.index')->with(
                'error',
                'The exhibition payment response could not be verified.'
            );
        }

        try {
            $application = $this->verifyPayment(
                $tranId,
                $validationId,
                $sslCommerz
            );

            return redirect()->route(
                'exhibitions.show',
                $application->exhibition->slug
            )->with(
                'success',
                $application->status === 'capacity_review'
                    ? 'Your payment was received, but all exhibition places were paid for before this payment completed. Please contact Artfy support to resolve your registration.'
                    : 'Payment verified. Submit your artwork details to complete your exhibition application.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('exhibitions.index')->with(
                'error',
                'Your exhibition payment is not yet confirmed. Check your application before retrying.'
            );
        }
    }

    public function fail(): RedirectResponse
    {
        return redirect()->route('exhibitions.index')->with(
            'error',
            'The exhibition payment was not confirmed. Check your application before trying again.'
        );
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('exhibitions.index')->with(
            'error',
            'You left exhibition checkout. Your place remains reserved for up to 30 minutes from checkout start.'
        );
    }

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
            $this->verifyPayment($tranId, $validationId, $sslCommerz);

            return response('Exhibition payment processed', 200);
        } catch (Throwable $exception) {
            report($exception);

            return response('Exhibition payment not confirmed', 400);
        }
    }

    private function verifyPayment(
        string $tranId,
        string $validationId,
        SSLCommerzService $sslCommerz
    ): ExhibitionApplication {
        $application = ExhibitionApplication::query()
            ->with('exhibition')
            ->where('payment_reference', $tranId)
            ->firstOrFail();

        if ($application->payment_status === 'verified') {
            return $application;
        }

        if (
            $application->status !== 'pending'
            || $application->payment_status !== 'pending'
        ) {
            throw new RuntimeException(
                'This exhibition application is not awaiting payment.'
            );
        }

        $gateway = $sslCommerz->validateTransaction($validationId);
        $status = strtoupper((string) ($gateway['status'] ?? ''));
        $gatewayTranId = (string) ($gateway['tran_id'] ?? '');
        $gatewayValId = (string) ($gateway['val_id'] ?? '');
        $currency = strtoupper((string) (
            $gateway['currency'] ?? $gateway['currency_type'] ?? ''
        ));
        $amount = $gateway['amount'] ?? null;

        if (! in_array($status, ['VALID', 'VALIDATED'], true)) {
            throw new RuntimeException('SSLCOMMERZ did not validate payment.');
        }
        if ($gatewayTranId !== $tranId) {
            throw new RuntimeException('The payment reference does not match.');
        }
        if (
            $gatewayValId !== ''
            && ! hash_equals($gatewayValId, $validationId)
        ) {
            throw new RuntimeException('The validation ID does not match.');
        }
        if ($currency !== 'BDT') {
            throw new RuntimeException('The payment currency does not match.');
        }
        if (
            ! is_numeric($amount)
            || abs((float) $amount - (float) $application->registration_fee) > 0.01
        ) {
            throw new RuntimeException('The payment amount does not match.');
        }
        if (
            array_key_exists('risk_level', $gateway)
            && (string) $gateway['risk_level'] !== '0'
        ) {
            throw new RuntimeException('The payment requires additional review.');
        }

        return DB::transaction(function () use (
            $application,
            $tranId
        ): ExhibitionApplication {
            $exhibition = Exhibition::query()
                ->whereKey($application->exhibition_id)
                ->lockForUpdate()
                ->firstOrFail();
            $locked = ExhibitionApplication::query()
                ->whereKey($application->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->payment_status === 'verified') {
                return $locked->load('exhibition');
            }
            if (
                $locked->status !== 'pending'
                || $locked->payment_status !== 'pending'
                || $locked->payment_reference !== $tranId
            ) {
                throw new RuntimeException(
                    'Application status changed during payment verification.'
                );
            }

            $locked->payment_status = 'verified';
            $paidCount = $exhibition->applications()
                ->reservesArtistPlace()
                ->count();
            $locked->status = $paidCount >= $exhibition->max_artists
                ? 'capacity_review'
                : 'awaiting_submission';
            $locked->payment_verified_at = now();
            $locked->save();

            return $locked->load('exhibition');
        });
    }

    private function validateArtwork(Request $request, bool $imageRequired): array
    {
        return $request->validate([
            'artwork_title' => ['required', 'string', 'max:180'],
            'artwork_description' => ['required', 'string', 'max:5000'],
            'artwork_price' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:9999999999.99',
            ],
            'artwork_image' => [
                $imageRequired ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);
    }
}
