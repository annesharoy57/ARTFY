<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exhibition_applications', function (Blueprint $table) {
            $table->string('artwork_title')->nullable();
            $table->text('artwork_description')->nullable();
            $table->decimal('artwork_price', 12, 2)->nullable();
            $table->string('artwork_image')->nullable();
        });

        DB::table('exhibition_applications')
            ->where('payment_status', 'verified')
            ->where('status', 'confirmed')
            ->update(['status' => 'awaiting_submission']);
    }

    public function down(): void
    {
        DB::table('exhibition_applications')
            ->where('payment_status', 'verified')
            ->where('status', 'awaiting_submission')
            ->update(['status' => 'confirmed']);

        Schema::table('exhibition_applications', function (Blueprint $table) {
            $table->dropColumn([
                'artwork_title',
                'artwork_description',
                'artwork_price',
                'artwork_image',
            ]);
        });
    }
};
