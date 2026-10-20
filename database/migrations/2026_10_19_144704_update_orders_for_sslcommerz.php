<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('sender_number', 30)->nullable()->change();

            $table->string('transaction_id', 100)->nullable()->change();

            $table->string('gateway_session_key')
                ->nullable();

            $table->string('gateway_val_id')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_session_key',
                'gateway_val_id',
            ]);

            $table->string('sender_number', 30)
                ->nullable(false)
                ->change();

            $table->string('transaction_id', 100)
                ->nullable(false)
                ->change();
        });
    }
};
