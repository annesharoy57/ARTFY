<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('exhibition_application_id')
                ->nullable()
                ->after('artwork_id')
                ->constrained()
                ->restrictOnDelete();
            $table->index(
                ['exhibition_application_id', 'payment_status'],
                'orders_exhibition_application_payment_index'
            );
            $table->foreignId('artwork_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_exhibition_application_payment_index');
            $table->dropConstrainedForeignId('exhibition_application_id');
            $table->foreignId('artwork_id')
                ->nullable(false)
                ->change();
        });
    }
};
