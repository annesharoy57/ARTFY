<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_membership_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('artist_name');
            $table->text('biography');
            $table->string('art_category');
            $table->string('portfolio_url')->nullable();

            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');

            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_membership_applications');
    }
};
