<?php

namespace Tests\Feature;

use App\Models\ArtistMembershipApplication;
use App\Models\Exhibition;
use App\Models\ExhibitionApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExhibitionRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_checkout_uses_capacity_only_after_payment_is_verified(): void
    {
        config([
            'app.url' => 'https://artfy.test',
            'services.sslcommerz.store_id' => 'test-store',
            'services.sslcommerz.store_password' => 'test-password',
            'services.sslcommerz.api_url' => 'https://gateway.test/initiate',
        ]);

        $transactionId = null;
        Http::fake(function (ClientRequest $request) use (&$transactionId) {
            if ($request->url() === 'https://gateway.test/initiate') {
                $transactionId = $request['tran_id'];

                return Http::response([
                    'status' => 'SUCCESS',
                    'GatewayPageURL' => 'https://gateway.test/checkout',
                    'sessionkey' => 'session-123',
                ]);
            }

            return Http::response([
                'status' => 'VALID',
                'tran_id' => $transactionId,
                'val_id' => 'validation-123',
                'currency' => 'BDT',
                'amount' => '250.00',
                'risk_level' => '0',
            ]);
        });

        $artist = $this->artistAccount();
        $exhibition = $this->exhibition();

        $this->actingAs($artist)
            ->get(route('exhibitions.show', $exhibition->slug))
            ->assertOk()
            ->assertSee('Apply and pay ৳250.00')
            ->assertDontSee('Active artist membership is required');

        $this->actingAs($artist)
            ->post(route('exhibitions.apply', $exhibition))
            ->assertRedirect('https://gateway.test/checkout');

        $application = ExhibitionApplication::firstOrFail();
        $this->assertSame('pending', $application->status);
        $this->assertSame('pending', $application->payment_status);
        $this->assertSame('250.00', $application->registration_fee);

        $this->post(route('exhibition.payment.success'), [
            'tran_id' => $application->payment_reference,
            'val_id' => 'validation-123',
        ])->assertRedirect(route('exhibitions.show', $exhibition->slug));

        $this->assertDatabaseHas('exhibition_applications', [
            'id' => $application->id,
            'status' => 'awaiting_submission',
            'payment_status' => 'verified',
        ]);
        $this->assertSame(
            1,
            $exhibition->applications()->reservesArtistPlace()->count()
        );

        $this->get(route('exhibitions.show', $exhibition->slug))
            ->assertOk()
            ->assertSee($exhibition->title)
            ->assertSee('Submit your exhibition artwork')
            ->assertSee('Asking price (BDT)');

        Storage::fake('public');
        $this->actingAs($artist)
            ->post(route('exhibitions.submit-artwork', $exhibition), [
                'artwork_title' => 'Blue Horizon',
                'artwork_description' => 'An original watercolor for this exhibition.',
                'artwork_price' => '4550',
                'artwork_image' => UploadedFile::fake()->createWithContent(
                    'blue-horizon.png',
                    base64_decode(
                        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC'
                    )
                ),
            ])
            ->assertRedirect(route('exhibitions.show', $exhibition->slug))
            ->assertSessionHas('success');

        $application->refresh();
        $this->assertSame('confirmed', $application->status);
        $this->assertSame('Blue Horizon', $application->artwork_title);
        $this->assertSame('4550.00', $application->artwork_price);
        Storage::disk('public')->assertExists($application->artwork_image);

        $this->get(route('exhibitions.show', $exhibition->slug))
            ->assertOk()
            ->assertSee($artist->name)
            ->assertSee('Blue Horizon')
            ->assertSee('An original watercolor for this exhibition.')
            ->assertSee('৳4,550.00')
            ->assertSee('Edit your exhibition artwork');

        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin)
            ->get(route('admin.exhibitions.create'))
            ->assertOk()
            ->assertSee('Registration fee (BDT)');

        $this->actingAs($admin)
            ->get(route('admin.exhibitions.index'))
            ->assertOk()
            ->assertSee('Total verified exhibition fees')
            ->assertSee('1 / 5 artists enrolled')
            ->assertSee('৳250.00');

        $this->actingAs($admin)
            ->get(route('admin.exhibitions.applications', $exhibition))
            ->assertOk()
            ->assertSee('৳250.00')
            ->assertSee('Blue Horizon')
            ->assertSee('৳4,550.00');
    }

    public function test_admin_logout_redirects_to_discover(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'You have logged out successfully.');

        $this->assertGuest();
        $this->get(route('home'))->assertOk();
    }

    public function test_admin_viewing_an_exhibition_gets_admin_navigation(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $exhibition = $this->exhibition();

        $this->actingAs($admin)
            ->get(route('exhibitions.show', $exhibition->slug))
            ->assertOk()
            ->assertSee('Online exhibition preview')
            ->assertSee('Overview')
            ->assertSee('Online Exhibitions')
            ->assertSee(route('admin.dashboard'), false)
            ->assertSee('Back to admin exhibitions')
            ->assertDontSee('Only artist accounts can enroll in this exhibition.');
    }

    public function test_artist_can_edit_submitted_artwork_only_while_registration_is_open(): void
    {
        Storage::fake('public');
        $artist = $this->artistAccount();
        $exhibition = $this->exhibition();
        $imagePath = UploadedFile::fake()
            ->createWithContent(
                'original-artwork.png',
                base64_decode(
                    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC'
                )
            )
            ->store('exhibition-artworks', 'public');
        $application = ExhibitionApplication::create([
            'exhibition_id' => $exhibition->id,
            'user_id' => $artist->id,
            'registration_fee' => 250,
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'payment_reference' => 'EXH-1-1-EDIT',
            'artwork_title' => 'Original title',
            'artwork_description' => 'Original description.',
            'artwork_price' => '1250.00',
            'artwork_image' => $imagePath,
        ]);

        $this->actingAs($artist)
            ->get(route('exhibitions.show', $exhibition->slug))
            ->assertOk()
            ->assertSee('Edit your exhibition artwork')
            ->assertSee('value="1250.00"', false);

        $this->actingAs($artist)
            ->put(route('exhibitions.update-artwork', $exhibition), [
                'artwork_title' => 'Corrected title',
                'artwork_description' => 'Corrected description.',
                'artwork_price' => '4550.001',
            ])
            ->assertSessionHasErrors('artwork_price');

        $this->actingAs($artist)
            ->put(route('exhibitions.update-artwork', $exhibition), [
                'artwork_title' => 'Corrected title',
                'artwork_description' => 'Corrected description.',
                'artwork_price' => '4550',
            ])
            ->assertRedirect(route('exhibitions.show', $exhibition->slug))
            ->assertSessionHas('success');

        $application->refresh();
        $this->assertSame('Corrected title', $application->artwork_title);
        $this->assertSame('4550.00', $application->artwork_price);
        $this->assertSame($imagePath, $application->artwork_image);
        Storage::disk('public')->assertExists($imagePath);

        $exhibition->registration_closes_at = now()->subMinute();
        $exhibition->save();

        $this->actingAs($artist)
            ->put(route('exhibitions.update-artwork', $exhibition), [
                'artwork_title' => 'Too late title',
                'artwork_description' => 'This change must be rejected.',
                'artwork_price' => '9000',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Artwork can only be edited while exhibition applications are open.');

        $this->assertSame('Corrected title', $application->fresh()->artwork_title);
    }

    public function test_invalid_exhibition_image_returns_to_form_with_validation_message(): void
    {
        $artist = $this->artistAccount();
        $exhibition = $this->exhibition();
        $application = ExhibitionApplication::create([
            'exhibition_id' => $exhibition->id,
            'user_id' => $artist->id,
            'registration_fee' => 250,
            'status' => 'awaiting_submission',
            'payment_status' => 'verified',
            'payment_reference' => 'EXH-1-1-VERIFIED',
        ]);

        $this->actingAs($artist)
            ->from(route('exhibitions.show', $exhibition->slug))
            ->post(route('exhibitions.submit-artwork', $exhibition), [
                'artwork_title' => 'Blue Horizon',
                'artwork_description' => 'An original watercolor.',
                'artwork_price' => '1250.00',
                'artwork_image' => UploadedFile::fake()->create(
                    'not-an-image.txt',
                    1,
                    'text/plain'
                ),
            ])
            ->assertRedirect(route('exhibitions.show', $exhibition->slug))
            ->assertSessionHasErrors('artwork_image');

        $this->assertDatabaseHas('exhibition_applications', [
            'id' => $application->id,
            'status' => 'awaiting_submission',
            'artwork_title' => null,
        ]);
    }

    public function test_unpaid_application_does_not_consume_artist_capacity(): void
    {
        config([
            'app.url' => 'https://artfy.test',
            'services.sslcommerz.store_id' => 'test-store',
            'services.sslcommerz.store_password' => 'test-password',
            'services.sslcommerz.api_url' => 'https://gateway.test/initiate',
        ]);
        Http::fake([
            'https://gateway.test/initiate' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://gateway.test/checkout',
                'sessionkey' => 'session-next-artist',
            ]),
        ]);

        $firstArtist = $this->activeArtist('first@example.test');
        $secondArtist = $this->activeArtist('second@example.test');
        $exhibition = $this->exhibition(['max_artists' => 1]);

        ExhibitionApplication::create([
            'exhibition_id' => $exhibition->id,
            'user_id' => $firstArtist->id,
            'registration_fee' => 250,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_reference' => 'EXH-1-1-PENDING',
            'payment_submitted_at' => now(),
        ]);

        $this->actingAs($secondArtist)
            ->get(route('exhibitions.show', $exhibition->slug))
            ->assertOk()
            ->assertSee('Artists enrolled')
            ->assertSee('0 of 1')
            ->assertSee('pending registration')
            ->assertSee('not count until verified')
            ->assertSee('Apply and pay ৳250.00');

        $this->actingAs($secondArtist)
            ->post(route('exhibitions.apply', $exhibition))
            ->assertRedirect('https://gateway.test/checkout');

        $this->assertSame(0, $exhibition->applications()->reservesArtistPlace()->count());
        $this->assertSame(2, $exhibition->applications()->count());
    }

    public function test_expired_unpaid_checkout_releases_its_artist_place(): void
    {
        config([
            'app.url' => 'https://artfy.test',
            'services.sslcommerz.store_id' => 'test-store',
            'services.sslcommerz.store_password' => 'test-password',
            'services.sslcommerz.api_url' => 'https://gateway.test/initiate',
        ]);
        Http::fake([
            'https://gateway.test/initiate' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://gateway.test/checkout',
                'sessionkey' => 'session-after-expiry',
            ]),
        ]);

        $firstArtist = $this->artistAccount('expired@example.test');
        $secondArtist = $this->artistAccount('next@example.test');
        $exhibition = $this->exhibition(['max_artists' => 1]);
        $expiredApplication = ExhibitionApplication::create([
            'exhibition_id' => $exhibition->id,
            'user_id' => $firstArtist->id,
            'registration_fee' => 250,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_reference' => 'EXH-1-1-EXPIRED',
            'payment_submitted_at' => now()->subMinutes(
                ExhibitionApplication::PENDING_PAYMENT_HOLD_MINUTES + 1
            ),
        ]);

        $this->assertFalse($expiredApplication->reservesArtistPlace());

        $this->actingAs($secondArtist)
            ->get(route('exhibitions.show', $exhibition->slug))
            ->assertOk()
            ->assertSee('Apply and pay ৳250.00');

        $this->actingAs($secondArtist)
            ->post(route('exhibitions.apply', $exhibition))
            ->assertRedirect('https://gateway.test/checkout');

        $this->assertSame(
            0,
            $exhibition->applications()->reservesArtistPlace()->count()
        );
    }

    public function test_paid_artist_still_reserves_capacity_until_artwork_is_submitted(): void
    {
        $firstArtist = $this->activeArtist('paid-first@example.test');
        $secondArtist = $this->activeArtist('paid-second@example.test');
        $exhibition = $this->exhibition(['max_artists' => 1]);

        ExhibitionApplication::create([
            'exhibition_id' => $exhibition->id,
            'user_id' => $firstArtist->id,
            'registration_fee' => 250,
            'status' => 'awaiting_submission',
            'payment_status' => 'verified',
            'payment_reference' => 'EXH-1-1-PAID',
        ]);

        $this->actingAs($secondArtist)
            ->from(route('exhibitions.show', $exhibition->slug))
            ->post(route('exhibitions.apply', $exhibition))
            ->assertRedirect(route('exhibitions.show', $exhibition->slug))
            ->assertSessionHas('error', 'This exhibition has reached its artist capacity with paid registrations.');
    }

    public function test_payment_after_capacity_is_full_is_flagged_for_support_instead_of_overbooking(): void
    {
        config([
            'services.sslcommerz.store_id' => 'test-store',
            'services.sslcommerz.store_password' => 'test-password',
        ]);
        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => 'EXH-1-LATE-PAID',
                'val_id' => 'validation-late',
                'currency' => 'BDT',
                'amount' => '250.00',
                'risk_level' => '0',
            ]),
        ]);

        $paidArtist = $this->artistAccount('capacity-paid@example.test');
        $lateArtist = $this->artistAccount('late-payment@example.test');
        $exhibition = $this->exhibition(['max_artists' => 1]);
        ExhibitionApplication::create([
            'exhibition_id' => $exhibition->id,
            'user_id' => $paidArtist->id,
            'registration_fee' => 250,
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'payment_reference' => 'EXH-1-PAID',
        ]);
        $lateApplication = ExhibitionApplication::create([
            'exhibition_id' => $exhibition->id,
            'user_id' => $lateArtist->id,
            'registration_fee' => 250,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_reference' => 'EXH-1-LATE-PAID',
            'payment_submitted_at' => now(),
        ]);

        $this->post(route('exhibition.payment.success'), [
            'tran_id' => $lateApplication->payment_reference,
            'val_id' => 'validation-late',
        ])
            ->assertRedirect(route('exhibitions.show', $exhibition->slug))
            ->assertSessionHas('success', 'Your payment was received, but all exhibition places were paid for before this payment completed. Please contact Artfy support to resolve your registration.');

        $this->assertDatabaseHas('exhibition_applications', [
            'id' => $lateApplication->id,
            'status' => 'capacity_review',
            'payment_status' => 'verified',
        ]);
        $this->assertSame(
            1,
            $exhibition->applications()->reservesArtistPlace()->count()
        );

        $this->actingAs($lateArtist)
            ->get(route('exhibitions.show', $exhibition->slug))
            ->assertOk()
            ->assertSee('Please contact Artfy support to resolve your registration.');
    }

    public function test_registration_window_uses_bangladesh_local_time(): void
    {
        config([
            'app.timezone' => 'UTC',
            'app.url' => 'https://artfy.test',
            'services.sslcommerz.store_id' => 'test-store',
            'services.sslcommerz.store_password' => 'test-password',
            'services.sslcommerz.api_url' => 'https://gateway.test/initiate',
        ]);
        Carbon::setTestNow(Carbon::parse('2026-10-30 15:52:00', 'UTC'));

        try {
            $artist = $this->artistAccount();
            $exhibition = $this->exhibition([
                'registration_opens_at' => '2026-10-30 20:50:00',
                'registration_closes_at' => '2026-10-31 21:50:00',
                'starts_at' => '2026-11-01 21:38:00',
                'ends_at' => '2026-11-02 21:39:00',
            ]);
            Http::fake([
                'https://gateway.test/initiate' => Http::response([
                    'status' => 'SUCCESS',
                    'GatewayPageURL' => 'https://gateway.test/checkout',
                    'sessionkey' => 'session-local-time',
                ]),
            ]);

            $this->assertTrue($exhibition->registrationIsOpen());

            $this->actingAs($artist)
                ->get(route('exhibitions.show', $exhibition->slug))
                ->assertOk()
                ->assertSee('Apply and pay ৳250.00');

            $this->actingAs($artist)
                ->post(route('exhibitions.apply', $exhibition))
                ->assertRedirect('https://gateway.test/checkout');

            $this->assertSame(1, $exhibition->applications()->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_signed_in_non_artist_is_told_to_switch_to_an_artist_account(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-30 15:52:00', 'UTC'));

        try {
            $artLover = User::factory()->create();
            $artLover->forceFill(['role' => 'art_lover'])->save();
            $exhibition = $this->exhibition([
                'registration_opens_at' => '2026-10-30 20:50:00',
                'registration_closes_at' => '2026-10-31 21:50:00',
            ]);

            $this->actingAs($artLover)
                ->get(route('exhibitions.show', $exhibition->slug))
                ->assertOk()
                ->assertSee('Only artist accounts can enroll in this exhibition.')
                ->assertSee('Sign out to switch to an artist account');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_missing_payment_configuration_does_not_reserve_an_artist_place(): void
    {
        config([
            'app.url' => 'https://artfy.test',
            'services.sslcommerz.store_id' => '',
            'services.sslcommerz.store_password' => '',
            'services.sslcommerz.api_url' => '',
        ]);

        $artist = $this->activeArtist();
        $exhibition = $this->exhibition();

        $this->actingAs($artist)
            ->from(route('exhibitions.show', $exhibition->slug))
            ->post(route('exhibitions.apply', $exhibition))
            ->assertRedirect(route('exhibitions.show', $exhibition->slug))
            ->assertSessionHas('error');

        $this->assertSame(0, $exhibition->applications()->count());
    }

    private function activeArtist(string $email = 'artist@example.test'): User
    {
        $artist = User::factory()->create(['email' => $email]);
        $artist->forceFill([
            'role' => 'artist',
            'membership_status' => 'active',
        ])->save();

        ArtistMembershipApplication::create([
            'user_id' => $artist->id,
            'artist_name' => $artist->name,
            'biography' => 'A test artist.',
            'art_category' => 'Painting',
            'status' => 'approved',
            'payment_status' => 'verified',
        ]);

        return $artist;
    }

    private function artistAccount(string $email = 'artist@example.test'): User
    {
        $artist = User::factory()->create(['email' => $email]);
        $artist->forceFill([
            'role' => 'artist',
            'membership_status' => 'not_applicable',
        ])->save();

        return $artist;
    }

    private function exhibition(array $attributes = []): Exhibition
    {
        return Exhibition::create(array_merge([
            'title' => 'Test Exhibition',
            'slug' => 'test-exhibition',
            'description' => 'A test exhibition.',
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addDay(),
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(5),
            'max_artists' => 5,
            'registration_fee' => 250,
            'status' => 'published',
        ], $attributes));
    }
}
