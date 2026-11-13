<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->string('theme', 180)->nullable()->after('subtitle');
            $table->string('cover_image')->nullable()->after('theme');
            $table->string('accent_color', 7)->default('#55613a')->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->dropColumn(['theme', 'cover_image', 'accent_color']);
        });
    }
};
