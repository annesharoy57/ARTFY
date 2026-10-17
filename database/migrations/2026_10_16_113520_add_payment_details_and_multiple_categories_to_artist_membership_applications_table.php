<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artist_membership_applications', function (Blueprint $table) {
            $table->json('art_categories')->nullable();
            $table->unsignedInteger('membership_fee')->default(1000);
            $table->string('payment_method')->default('manual');
            $table->string('payment_reference')->nullable();
            $table->timestamp('payment_submitted_at')->nullable();
            $table->timestamp('payment_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('artist_membership_applications', function (Blueprint $table) {
            $table->dropColumn([
                'art_categories',
                'membership_fee',
                'payment_method',
                'payment_reference',
                'payment_submitted_at',
                'payment_verified_at',
            ]);
        });
    }
};