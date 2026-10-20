<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_number')->unique();

            $table->foreignId('buyer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('artwork_id')
                ->constrained('artworks')
                ->restrictOnDelete();

            $table->foreignId('artist_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Snapshot the sale price and title at order creation.
            $table->string('artwork_title');
            $table->decimal('price', 10, 2);

            // Commission percentage and financial breakdown.
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->decimal('commission_amount', 10, 2);
            $table->decimal('artist_amount', 10, 2);

            // Manual bKash / Nagad verification.
            $table->string('payment_method');
            $table->string('sender_number', 30);
            $table->string('transaction_id', 100);

            $table->string('payment_status')->default('pending');
            $table->string('order_status')->default('pending');

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['artwork_id', 'payment_status']);
            $table->index(['buyer_id', 'created_at']);
            $table->index(['artist_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
