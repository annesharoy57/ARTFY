<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibition_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_application_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(
                ['exhibition_application_id', 'user_id'],
                'exhibition_votes_application_user_unique'
            );
        });

        Schema::create('exhibition_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_application_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('co_admin_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamps();
            $table->unique(
                ['exhibition_application_id', 'co_admin_id'],
                'exhibition_scores_application_co_admin_unique'
            );
        });

        Schema::create('exhibition_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('settled_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->decimal('total_fees', 12, 2);
            $table->decimal('admin_fund_amount', 12, 2);
            $table->timestamp('settled_at');
            $table->timestamps();
        });

        Schema::create('artist_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('exhibition_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('exhibition_application_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->timestamps();
            $table->unique(
                ['exhibition_application_id', 'type'],
                'artist_wallet_application_type_unique'
            );
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_wallet_transactions');
        Schema::dropIfExists('exhibition_settlements');
        Schema::dropIfExists('exhibition_scores');
        Schema::dropIfExists('exhibition_votes');
    }
};
