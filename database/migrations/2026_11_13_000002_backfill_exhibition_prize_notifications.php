<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('artist_wallet_transactions as wallet_transactions')
            ->join('exhibitions', 'exhibitions.id', '=', 'wallet_transactions.exhibition_id')
            ->where('wallet_transactions.type', 'exhibition_prize')
            ->select([
                'wallet_transactions.id',
                'wallet_transactions.user_id',
                'wallet_transactions.exhibition_id',
                'wallet_transactions.amount',
                'wallet_transactions.created_at',
                'exhibitions.title as exhibition_title',
            ])
            ->orderBy('wallet_transactions.id')
            ->chunk(100, function ($transactions): void {
                foreach ($transactions as $transaction) {
                    $existing = DB::table('notifications')
                        ->where('type', 'artfy.exhibition_prize')
                        ->where('notifiable_type', 'App\\Models\\User')
                        ->where('notifiable_id', $transaction->user_id)
                        ->get(['data'])
                        ->contains(function ($notification) use ($transaction): bool {
                            $data = json_decode($notification->data, true);

                            return is_array($data)
                                && (
                                    (int) ($data['wallet_transaction_id'] ?? 0)
                                        === (int) $transaction->id
                                    || (
                                        (int) ($data['exhibition_id'] ?? 0)
                                            === (int) $transaction->exhibition_id
                                        && isset($data['amount'])
                                        && abs(
                                            (float) $data['amount']
                                            - (float) $transaction->amount
                                        ) < 0.005
                                    )
                                );
                        });

                    if ($existing) {
                        continue;
                    }

                    $amount = (float) $transaction->amount;
                    $createdAt = $transaction->created_at ?? now();
                    DB::table('notifications')->insert([
                        'id' => (string) Str::uuid(),
                        'type' => 'artfy.exhibition_prize',
                        'notifiable_type' => 'App\\Models\\User',
                        'notifiable_id' => $transaction->user_id,
                        'data' => json_encode([
                            'title' => 'Exhibition prize added to your wallet',
                            'message' => 'You received BDT '
                                . number_format($amount, 2)
                                . ' for your result in "'
                                . $transaction->exhibition_title . '".',
                            'url' => '/artist/wallet',
                            'exhibition_id' => $transaction->exhibition_id,
                            'amount' => $amount,
                            'wallet_transaction_id' => $transaction->id,
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'read_at' => null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Keep notification history when rolling back this data backfill.
    }
};
