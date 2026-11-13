<?php

namespace App\Services;

use App\Models\ArtistWalletTransaction;
use App\Models\Exhibition;
use App\Models\ExhibitionApplication;
use App\Models\ExhibitionSettlement;
use App\Models\User;
use App\Notifications\ArtfyDatabaseNotification;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ExhibitionAwardService
{
    public function settleAllClosedExhibitions(): int
    {
        $closedExhibitions = Exhibition::query()
            ->where('status', 'published')
            ->where(
                'ends_at',
                '<=',
                Carbon::now('Asia/Dhaka')->format('Y-m-d H:i:s')
            )
            ->whereDoesntHave('settlement')
            ->orderBy('ends_at')
            ->get();

        if ($closedExhibitions->isEmpty()) {
            return 0;
        }

        $admin = User::query()
            ->where('role', 'admin')
            ->orderBy('id')
            ->first();

        if (! $admin) {
            throw new DomainException(
                'Cannot automatically credit exhibition awards because no admin account exists.'
            );
        }

        $settledCount = 0;

        foreach ($closedExhibitions as $exhibition) {
            $this->settle($exhibition, (int) $admin->id);
            $settledCount++;
        }

        return $settledCount;
    }

    public function settle(Exhibition $exhibition, int $adminId): ExhibitionSettlement
    {
        return DB::transaction(function () use ($exhibition, $adminId) {
            $lockedExhibition = Exhibition::query()
                ->whereKey($exhibition->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedExhibition->status !== 'published'
                || ! $lockedExhibition->visitHasEnded()
            ) {
                throw new DomainException(
                    'Awards can only be finalized after a published exhibition closes.'
                );
            }

            if ($lockedExhibition->settlement()->exists()) {
                throw new DomainException(
                    'Awards for this exhibition have already been finalized.'
                );
            }

            $applications = $lockedExhibition->applications()
                ->where('payment_status', 'verified')
                ->get();

            $totalFeesCents = $applications->sum(
                fn (ExhibitionApplication $application): int => (int) round(
                    (float) $application->registration_fee * 100
                )
            );
            $commissionCents = (int) round($totalFeesCents * 0.20);

            $participants = $lockedExhibition->applications()
                ->where('status', 'confirmed')
                ->withCount('votes')
                ->withSum('scores', 'score')
                ->get()
                ->map(fn (ExhibitionApplication $application): array => [
                    'application' => $application,
                    'points' => (int) $application->votes_count
                        + (int) ($application->scores_sum_score ?? 0),
                ])
                ->sort(function (array $left, array $right): int {
                    $pointsOrder = $right['points'] <=> $left['points'];

                    return $pointsOrder !== 0
                        ? $pointsOrder
                        : $left['application']->id
                            <=> $right['application']->id;
                })
                ->values();

            $prizePercents = [40, 30, 10];
            $awards = [];
            $position = 1;

            foreach ($this->scoreGroups($participants) as $group) {
                $groupSize = $group->count();
                $groupPrizeCents = 0;

                for (
                    $place = $position;
                    $place < $position + $groupSize && $place <= 3;
                    $place++
                ) {
                    $groupPrizeCents += (int) round(
                        $totalFeesCents * $prizePercents[$place - 1] / 100
                    );
                }

                if ($groupPrizeCents > 0) {
                    $baseShare = intdiv($groupPrizeCents, $groupSize);
                    $remainder = $groupPrizeCents % $groupSize;

                    foreach ($group->values() as $index => $entry) {
                        $awards[] = [
                            'application' => $entry['application'],
                            'amount_cents' => $baseShare
                                + ($index < $remainder ? 1 : 0),
                        ];
                    }
                }

                $position += $groupSize;
            }

            $awardedCents = collect($awards)->sum('amount_cents');
            $settlement = ExhibitionSettlement::create([
                'exhibition_id' => $lockedExhibition->id,
                'settled_by' => $adminId,
                'total_fees' => $totalFeesCents / 100,
                'admin_fund_amount' => $commissionCents / 100,
                'unallocated_prize_amount' => max(
                    0,
                    $totalFeesCents - $commissionCents - $awardedCents
                ) / 100,
                'settled_at' => now(),
            ]);

            foreach ($awards as $award) {
                if ($award['amount_cents'] === 0) {
                    continue;
                }

                /** @var ExhibitionApplication $application */
                $application = $award['application'];
                $walletTransaction = ArtistWalletTransaction::create([
                    'user_id' => $application->user_id,
                    'exhibition_id' => $lockedExhibition->id,
                    'exhibition_application_id' => $application->id,
                    'type' => 'exhibition_prize',
                    'amount' => $award['amount_cents'] / 100,
                    'description' => 'Online exhibition prize award',
                ]);

                $artist = User::find($application->user_id);
                if ($artist) {
                    $artist->notify(new ArtfyDatabaseNotification(
                        'artfy.exhibition_prize',
                        [
                            'title' => 'Exhibition prize added to your wallet',
                            'message' => 'You received BDT '
                                . number_format($award['amount_cents'] / 100, 2)
                                . ' for your result in "'
                                . $lockedExhibition->title . '".',
                            'url' => route(
                                'artist.wallet',
                                [],
                                false
                            ),
                            'exhibition_id' => $lockedExhibition->id,
                            'amount' => $award['amount_cents'] / 100,
                            'wallet_transaction_id' => $walletTransaction->id,
                        ]
                    ));
                }
            }

            return $settlement;
        });
    }

    private function scoreGroups(Collection $participants): Collection
    {
        return $participants
            ->groupBy('points')
            ->sortKeysDesc()
            ->values();
    }
}
