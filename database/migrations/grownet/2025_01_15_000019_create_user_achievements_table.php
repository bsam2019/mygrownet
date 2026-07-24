<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_achievements')) {
            Schema::create('user_achievements', function (Blueprint $table) {
                $table->id();
                // define as plain columns first; add FKs conditionally after creation
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('achievement_id');
                $table->timestamp('earned_at');
                $table->decimal('progress', 5, 2)->default(0); // Progress percentage (0-100)
                $table->integer('times_earned')->default(1); // For repeatable achievements
                $table->string('tier_at_earning')->nullable(); // User's tier when earned
                $table->json('earning_context')->nullable(); // Additional context about how it was earned
                $table->boolean('is_celebrated')->default(false); // Whether celebration was triggered
                $table->boolean('is_shared')->default(false); // Whether user shared this achievement
                $table->timestamps();

                // Indexes for efficient queries (short names)
                $table->index(['user_id', 'earned_at'], 'ua_user_earned_idx');
                $table->index(['achievement_id', 'earned_at'], 'ua_ach_earned_idx');
                $table->index(['tier_at_earning', 'earned_at'], 'ua_tier_earned_idx');
                
                // Unique constraint for non-repeatable achievements
                $table->unique(['user_id', 'achievement_id'], 'unique_user_achievement');
            });
        }

        // Add foreign keys conditionally
        try {
            if (Schema::hasTable('user_achievements') && Schema::hasTable('users')) {
                Schema::table('user_achievements', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}
        try {
            if (Schema::hasTable('user_achievements') && Schema::hasTable('achievements')) {
                Schema::table('user_achievements', function (Blueprint $table) {
                    $table->foreign('achievement_id')->references('id')->on('achievements')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};