<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exhibition_settlements', function (Blueprint $table) {
            $table->decimal('unallocated_prize_amount', 12, 2)->default(0);
        });

        DB::table('exhibition_settlements')
            ->orderBy('id')
            ->get(['id', 'exhibition_id', 'total_fees'])
            ->each(function (object $settlement): void {
                $commissionCents = (int) round(
                    (float) $settlement->total_fees * 100 * 0.20
                );
                $awardedCents = (int) round(
                    (float) DB::table('artist_wallet_transactions')
                        ->where('exhibition_id', $settlement->exhibition_id)
                        ->where('type', 'exhibition_prize')
                        ->sum('amount') * 100
                );
                $totalCents = (int) round(
                    (float) $settlement->total_fees * 100
                );

                DB::table('exhibition_settlements')
                    ->where('id', $settlement->id)
                    ->update([
                        'admin_fund_amount' => $commissionCents / 100,
                        'unallocated_prize_amount' => max(
                            0,
                            $totalCents - $commissionCents - $awardedCents
                        ) / 100,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('exhibition_settlements', function (Blueprint $table) {
            $table->dropColumn('unallocated_prize_amount');
        });
    }
};
