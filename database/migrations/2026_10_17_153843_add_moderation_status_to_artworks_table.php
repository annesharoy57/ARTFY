<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            $table->string('moderation_status')
                ->default('pending')
                ->after('status');
        });

        // Preserve existing available artworks in the public gallery.
        DB::table('artworks')
            ->where('status', 'available')
            ->update(['moderation_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            $table->dropColumn('moderation_status');
        });
    }
};
