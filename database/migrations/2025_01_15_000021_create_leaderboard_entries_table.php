<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leaderboard_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('position');
            $table->decimal('score', 15, 2); // The metric being measured
            $table->string('tier_at_entry')->nullable();
            $table->json('score_breakdown')->nullable(); // Detailed breakdown of how score was calculated
            $table->integer('previous_position')->nullable();
            $table->decimal('previous_score', 15, 2)->nullable();
            $table->enum('trend', ['up', 'down', 'same', 'new'])->default('new');
            $table->timestamp('calculated_at');
            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['leaderboard_id', 'position']);
            $table->index(['user_id', 'leaderboard_id']);
            $table->index(['calculated_at', 'position']);
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['leaderboard_id', 'user_id'], 'unique_leaderboard_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
    }
};