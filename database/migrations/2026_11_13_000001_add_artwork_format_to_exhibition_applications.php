<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exhibition_applications', function (Blueprint $table) {
            $table->string('artwork_type')->default('physical')->after('artwork_price');
            $table->string('digital_file_path')->nullable()->after('artwork_image');
        });
    }

    public function down(): void
    {
        Schema::table('exhibition_applications', function (Blueprint $table) {
            $table->dropColumn(['artwork_type', 'digital_file_path']);
        });
    }
};
