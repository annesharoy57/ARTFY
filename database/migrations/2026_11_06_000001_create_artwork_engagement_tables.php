<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artwork_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['artwork_id', 'user_id']);
        });

        Schema::create('artwork_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['artwork_id', 'user_id']);
        });

        Schema::create('artwork_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('artwork_comments')
                ->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['artwork_id', 'parent_id']);
        });

        Schema::create('artwork_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_comment_id')
                ->constrained('artwork_comments')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['artwork_comment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artwork_comment_likes');
        Schema::dropIfExists('artwork_comments');
        Schema::dropIfExists('artwork_favorites');
        Schema::dropIfExists('artwork_likes');
    }
};
