<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('type', ['achievements', 'referrals', 'earnings', 'team_volume', 'course_completions', 'project_contributions']);
            $table->enum('period', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'all_time']);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->json('tier_restrictions')->nullable(); // Which tiers can participate
            $table->json('category_filters')->nullable(); // For achievement-based leaderboards
            $table->integer('max_positions')->default(100); // Top N positions to track
            $table->json('rewards')->nullable(); // Rewards for top positions
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_refresh')->default(true);
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            $table->index(['type', 'period', 'is_active']);
            $table->index(['period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};