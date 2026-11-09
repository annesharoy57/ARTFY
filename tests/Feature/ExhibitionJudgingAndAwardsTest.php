<?php

namespace Tests\Feature;

use App\Models\ArtistProfile;
use App\Models\Exhibition;
use App\Models\ExhibitionApplication;
use App\Models\ExhibitionScore;
use App\Models\ExhibitionVote;
use App\Models\Order;
use App\Models\User;
use App\Services\ExhibitionAwardService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExhibitionJudgingAndAwardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_co_admin_scores_and_art_lover_votes_only_during_visiting_period(): void
    {
        $exhibition = $this->exhibition([
            'starts_at' => now('Asia/Dhaka')->subHour(),
            'ends_at' => now('Asia/Dhaka')->addHour(),
        ]);
        $application = $this->participant($exhibition, 'Participant One');
        $coAdmin = $this->account('co_admin', 'judge@example.test');
        $artLover = $this->account('art_lover', 'lover@example.test');

        $this->actingAs($coAdmin)
            ->get(route('co-admin.exhibitions.index'))
            ->assertOk()
            ->assertSee('Online Exhibitions')
            ->assertDontSee('Overview');

        $this->actingAs($coAdmin)
            ->get(route('co-admin.exhibitions.show', $exhibition))
            ->assertOk()
            ->assertSee('Participant One')
            ->assertSee('score');

        $this->actingAs($coAdmin)
            ->post(route('co-admin.exhibitions.score', [$exhibition, $application]), [
                'score' => 8,
            ])
            ->assertSessionHas('success', 'Artwork score saved.');

        $this->assertDatabaseHas('exhibition_scores', [
            'exhibition_application_id' => $application->id,
            'co_admin_id' => $coAdmin->id,
            'score' => 8,
        ]);

        $this->actingAs($artLover)
            ->get(route('exhibitions.show', $exhibition))
            ->assertOk()
            ->assertSee('Vote for this artwork')
            ->assertSee('Buy this artwork');

        $this->actingAs($artLover)
            ->post(route('exhibitions.vote', [$exhibition, $application]))
            ->assertSessionHas('success', 'Your vote has been recorded.');

        $this->actingAs($artLover)
            ->post(route('exhibitions.vote', [$exhibition, $application]))
            ->assertSessionHas('error', 'You have already voted for this artwork.');

        $exhibition->update(['ends_at' => now()->subMinute()]);

        $this->actingAs($coAdmin)
            ->post(route('co-admin.exhibitions.score', [$exhibition, $application]), [
                'score' => 10,
            ])
            ->assertSessionHas('error');

        $this->actingAs($artLover)
            ->post(route('exhibitions.vote', [$exhibition, $application]))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('exhibition_scores', [
            'exhibition_application_id' => $application->id,
            'co_admin_id' => $coAdmin->id,
            'score' => 8,
        ]);
        $this->assertSame(1, ExhibitionVote::where(
            'exhibition_application_id',
            $application->id
        )->count());
    }

    public function test_admin_can_create_at_most_three_co_admin_accounts(): void
    {
        $admin = $this->account('admin', 'admin@example.test');

        foreach (range(1, 3) as $number) {
            $this->actingAs($admin)
                ->post(route('admin.co-admins.store'), [
                    'name' => 'Judge '.$number,
                    'email' => 'judge'.$number.'@example.test',
                    'password' => 'password-123',
                    'password_confirmation' => 'password-123',
                ])
                ->assertRedirect(route('admin.co-admins.index'));
        }

        $this->actingAs($admin)
            ->post(route('admin.co-admins.store'), [
                'name' => 'Judge Four',
                'email' => 'judge4@example.test',
                'password' => 'password-123',
                'password_confirmation' => 'password-123',
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame(3, User::where('role', 'co_admin')->count());
        $this->actingAs(User::where('role', 'co_admin')->first())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_edit_co_admin_details_and_optionally_change_password(): void
    {
        $admin = $this->account('admin', 'admin@example.test');
        $coAdmin = $this->account('co_admin', 'judge@example.test');

        $this->actingAs($admin)
            ->get(route('admin.co-admins.index'))
            ->assertOk()
            ->assertSee('Edit '.$coAdmin->name)
            ->assertSee('Save co-admin');

        $this->actingAs($admin)
            ->put(route('admin.co-admins.update', $coAdmin), [
                'name' => 'Updated Judge',
                'email' => $coAdmin->email,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.co-admins.index'))
            ->assertSessionHas('success', 'Co-admin account updated.');

        $coAdmin->refresh();
        $this->assertSame('Updated Judge', $coAdmin->name);
        $this->assertSame('judge@example.test', $coAdmin->email);

        $this->actingAs($admin)
            ->put(route('admin.co-admins.update', $coAdmin), [
                'name' => 'Updated Judge',
                'email' => 'updated-judge@example.test',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('admin.co-admins.index'));

        $coAdmin->refresh();
        $this->assertSame('updated-judge@example.test', $coAdmin->email);
        $this->assertTrue(password_verify('new-password-123', $coAdmin->password));
        $this->assertSame('co_admin', $coAdmin->role);
    }

    public function test_closed_exhibition_is_settled_automatically_when_visited(): void
    {
        $exhibition = $this->exhibition([
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subHour(),
        ]);
        $application = $this->participant($exhibition, 'Automatic winner');
        $this->account('admin', 'automatic-settlement-admin@example.test');

        $this->get(route('exhibitions.show', $exhibition))
            ->assertOk()
            ->assertSee('Total points:')
            ->assertSee('Place:');

        $settlement = $exhibition->fresh()->settlement;
        $this->assertNotNull($settlement);
        $this->assertSame('50.00', $settlement->admin_fund_amount);
        $this->assertSame('100.00', $settlement->unallocated_prize_amount);
        $this->assertDatabaseHas('artist_wallet_transactions', [
            'exhibition_application_id' => $application->id,
            'amount' => '100.00',
        ]);
    }

    public function test_tied_winners_split_the_combined_prizes_and_receive_wallet_credits_once(): void
    {
        $exhibition = $this->exhibition([
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subHour(),
        ]);
        $applications = [
            $this->participant($exhibition, 'First tied artist'),
            $this->participant($exhibition, 'Second tied artist'),
            $this->participant($exhibition, 'Third artist'),
        ];
        $judges = [
            $this->account('co_admin', 'judge1@example.test'),
            $this->account('co_admin', 'judge2@example.test'),
            $this->account('co_admin', 'judge3@example.test'),
        ];

        foreach (array_slice($applications, 0, 2) as $application) {
            foreach ($judges as $judge) {
                ExhibitionScore::create([
                    'exhibition_application_id' => $application->id,
                    'co_admin_id' => $judge->id,
                    'score' => 10,
                ]);
            }
        }

        foreach ($judges as $index => $judge) {
            ExhibitionScore::create([
                'exhibition_application_id' => $applications[2]->id,
                'co_admin_id' => $judge->id,
                'score' => [7, 7, 6][$index],
            ]);
        }

        foreach (range(1, 5) as $number) {
            $voter = $this->account(
                'art_lover',
                'vote'.$number.'@example.test'
            );
            ExhibitionVote::create([
                'exhibition_application_id' => $applications[0]->id,
                'user_id' => $voter->id,
            ]);

            $secondVoter = $this->account(
                'art_lover',
                'second-vote'.$number.'@example.test'
            );
            ExhibitionVote::create([
                'exhibition_application_id' => $applications[1]->id,
                'user_id' => $secondVoter->id,
            ]);
        }

        $admin = $this->account('admin', 'settlement-admin@example.test');
        $awardService = app(ExhibitionAwardService::class);
        $this->actingAs($admin)
            ->post(route('admin.exhibitions.finalize-awards', $exhibition))
            ->assertRedirect(route('admin.exhibitions.applications', $exhibition))
            ->assertSessionHas('success');
        $settlement = $exhibition->fresh()->settlement;

        $this->assertSame('750.00', $settlement->total_fees);
        $this->assertSame('150.00', $settlement->admin_fund_amount);
        $this->assertSame('0.00', $settlement->unallocated_prize_amount);
        $this->assertDatabaseHas('artist_wallet_transactions', [
            'exhibition_application_id' => $applications[0]->id,
            'amount' => '262.50',
        ]);
        $this->assertDatabaseHas('artist_wallet_transactions', [
            'exhibition_application_id' => $applications[1]->id,
            'amount' => '262.50',
        ]);
        $this->assertDatabaseHas('artist_wallet_transactions', [
            'exhibition_application_id' => $applications[2]->id,
            'amount' => '75.00',
        ]);

        $this->actingAs(User::findOrFail($applications[0]->user_id))
            ->get(route('artist.wallet'))
            ->assertOk()
            ->assertSee('৳262.50');

        $this->actingAs($admin)
            ->get(route('admin.exhibitions.index'))
            ->assertOk()
            ->assertSee('Artfy commission by Exhibition')
            ->assertSee('৳150.00')
            ->assertSee('Artist prizes credited:');

        $this->actingAs($admin)
            ->get(route('admin.exhibitions.applications', $exhibition))
            ->assertOk()
            ->assertSee('Artfy commission (20%): ৳150.00')
            ->assertSee('Artist prize credits issued: ৳600.00')
            ->assertSee('Prize credited to artist wallet:');

        $this->expectException(DomainException::class);
        $awardService->settle($exhibition, $admin->id);
    }

    public function test_art_lovers_can_open_exhibition_artwork_checkout(): void
    {
        config([
            'services.openrouteservice.key' => 'test-key',
            'services.sslcommerz.store_id' => 'test-store',
            'services.sslcommerz.store_password' => 'test-password',
            'services.sslcommerz.api_url' => 'https://gateway.test/initiate',
            'services.sslcommerz.callback_url' => 'https://artfy.test',
        ]);
        Http::fake(function (ClientRequest $request) {
            if ($request->url() === 'https://api.openrouteservice.org/v2/directions/driving-car') {
                return Http::response([
                    'routes' => [['summary' => ['distance' => 1000]]],
                ]);
            }

            if ($request->url() === 'https://gateway.test/initiate') {
                return Http::response([
                    'status' => 'SUCCESS',
                    'GatewayPageURL' => 'https://gateway.test/checkout',
                    'sessionkey' => 'session-exhibition',
                ]);
            }

            if (str_starts_with(
                $request->url(),
                'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
            )) {
                return Http::response([
                    'status' => 'VALID',
                    'tran_id' => Order::firstOrFail()->order_number,
                    'val_id' => 'validation-exhibition',
                    'currency_type' => 'BDT',
                    'currency' => 'BDT',
                    'amount' => '1040.00',
                    'risk_level' => '0',
                ]);
            }

            return Http::response([], 404);
        });

        $this->account('admin', 'settlement-admin@example.test');
        $exhibition = $this->exhibition([
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->addDays(2),
        ]);
        $application = $this->participant($exhibition, 'Artwork for sale');
        ArtistProfile::create([
            'user_id' => $application->user_id,
            'latitude' => 23.8103,
            'longitude' => 90.4125,
        ]);
        $buyer = $this->account('art_lover', 'buyer@example.test');

        $this->actingAs($buyer)
            ->get(route('exhibitions.show', $exhibition))
            ->assertOk()
            ->assertSee('Exhibition is open for visits, voting, judging, and artwork purchases.')
            ->assertSee('Buy this artwork');

        $this->actingAs($buyer)
            ->get(route('artworks.purchase.create', 'exhibition-'.$application->id))
            ->assertOk()
            ->assertSee('Artwork for sale')
            ->assertSee('Back to exhibition');

        $this->actingAs($buyer)
            ->post(route('artworks.purchase.store', 'exhibition-'.$application->id), [
                'shipping_address' => '12 Road, Dhaka, Bangladesh',
                'shipping_latitude' => '23.8103',
                'shipping_longitude' => '90.4125',
            ])
            ->assertRedirect('https://gateway.test/checkout');

        $this->assertDatabaseHas('orders', [
            'exhibition_application_id' => $application->id,
            'artwork_id' => null,
            'artwork_title' => 'Artwork for sale',
            'buyer_id' => $buyer->id,
            'payment_status' => 'pending',
        ]);

        $order = Order::firstOrFail();
        $this->post(route('payment.sslcommerz.success'), [
            'tran_id' => $order->order_number,
            'val_id' => 'validation-exhibition',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'verified',
        ]);
        $this->actingAs($buyer)
            ->get(route('exhibitions.show', $exhibition))
            ->assertOk()
            ->assertSee('Sold')
            ->assertDontSee('Buy this artwork');
    }

    public function test_exhibition_artwork_cannot_be_bought_after_exhibition_closes(): void
    {
        $this->account('admin', 'settlement-admin@example.test');
        $exhibition = $this->exhibition([
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subDays(2),
        ]);
        $application = $this->participant($exhibition, 'Closed exhibition artwork');
        $buyer = $this->account('art_lover', 'closed-exhibition-buyer@example.test');

        $this->actingAs($buyer)
            ->get(route('exhibitions.show', $exhibition))
            ->assertOk()
            ->assertSee('closed for visits, voting, judging, and artwork purchases')
            ->assertDontSee('Buy this artwork')
            ->assertDontSee('Log in as an art lover to buy');

        $this->actingAs($buyer)
            ->get(route('artworks.purchase.create', 'exhibition-'.$application->id))
            ->assertNotFound();

        $this->actingAs($buyer)
            ->post(route('artworks.purchase.store', 'exhibition-'.$application->id), [
                'shipping_address' => '12 Road, Dhaka, Bangladesh',
                'shipping_latitude' => '23.8103',
                'shipping_longitude' => '90.4125',
            ])
            ->assertStatus(409);

        $this->assertDatabaseMissing('orders', [
            'exhibition_application_id' => $application->id,
        ]);
    }

    private function account(string $role, string $email): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function exhibition(array $attributes = []): Exhibition
    {
        return Exhibition::create(array_merge([
            'title' => 'Judged exhibition',
            'slug' => 'judged-exhibition',
            'description' => 'An exhibition for judging tests.',
            'registration_opens_at' => now()->subDays(10),
            'registration_closes_at' => now()->subDays(6),
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'max_artists' => 5,
            'registration_fee' => 250,
            'status' => 'published',
        ], $attributes));
    }

    private function participant(
        Exhibition $exhibition,
        string $title
    ): ExhibitionApplication {
        $artist = $this->account(
            'artist',
            str_replace(' ', '-', strtolower($title)).'@example.test'
        );

        return ExhibitionApplication::create([
            'exhibition_id' => $exhibition->id,
            'user_id' => $artist->id,
            'registration_fee' => 250,
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'payment_reference' => 'REF-'.str_replace(' ', '-', strtoupper($title)),
            'artwork_title' => $title,
            'artwork_description' => 'Description for '.$title,
            'artwork_price' => 1000,
            'artwork_image' => 'exhibition-artworks/'.$title.'.jpg',
        ]);
    }
}
