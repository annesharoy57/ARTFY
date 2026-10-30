<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibition_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('registration_fee', 12, 2);
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable()->unique();
            $table->timestamp('payment_submitted_at')->nullable();
            $table->timestamp('payment_verified_at')->nullable();
            $table->timestamps();

            $table->unique(['exhibition_id', 'user_id']);
            $table->index(['exhibition_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibition_applications');
    }
};
