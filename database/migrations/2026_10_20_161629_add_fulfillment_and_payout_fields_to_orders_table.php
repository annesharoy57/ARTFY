<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Delivery is separate from payment verification.
            $table->string('fulfillment_status')
                ->default('pending');

            $table->string('shipping_address')
                ->nullable();

            $table->string('tracking_number')
                ->nullable();

            $table->timestamp('shipped_at')
                ->nullable();

            $table->timestamp('delivered_at')
                ->nullable();

            $table->timestamp('buyer_confirmed_at')
                ->nullable();

            // Artist payout tracking.
            $table->string('payout_status')
                ->default('pending');

            $table->string('payout_reference')
                ->nullable();

            $table->timestamp('payout_at')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'fulfillment_status',
                'shipping_address',
                'tracking_number',
                'shipped_at',
                'delivered_at',
                'buyer_confirmed_at',
                'payout_status',
                'payout_reference',
                'payout_at',
            ]);
        });
    }
};
