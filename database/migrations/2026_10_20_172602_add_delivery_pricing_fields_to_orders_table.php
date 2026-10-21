<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_distance_km', 8, 2)
                ->nullable();

            $table->decimal('delivery_fee', 10, 2)
                ->default(0);

            $table->decimal('total_amount', 10, 2)
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_distance_km',
                'delivery_fee',
                'total_amount',
            ]);
        });
    }
};
