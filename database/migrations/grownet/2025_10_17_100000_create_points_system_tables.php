<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User Points Table
        Schema::create('user_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('lifetime_points')->default(0);
            $table->integer('monthly_points')->default(0);
            $table->integer('last_month_points')->default(0);
            $table->decimal('three_month_average', 10, 2)->default(0);
            $table->integer('current_streak_months')->default(0);
            $table->integer('longest_streak_months')->default(0);
            $table->decimal('active_multiplier', 3, 2)->default(1.00);
            $table->timestamp('last_activity_date')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('lifetime_points');
            $table->index('monthly_points');
        });

        // Point Transactions Table
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('point_type', ['lifetime', 'monthly', 'both'])->default('both');
            $table->integer('lp_amount')->default(0);
            $table->integer('map_amount')->default(0);
            $table->string('source', 50);
            $table->text('description')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('multiplier_applied', 3, 2)->default(1.00);
            $table->timestamps();

            $table->index('user_id');
            $table->index('source');
            $table->index('created_at');
            $table->index(['reference_type', 'reference_id']);
        });

        // Monthly Activity Status Table
        Schema::create('monthly_activity_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('month');
            $table->integer('year');
            $table->integer('map_earned')->default(0);
            $table->integer('map_required');
            $table->boolean('qualified')->default(false);
            $table->enum('performance_tier', ['bronze', 'silver', 'gold', 'platinum'])->nullable();
            $table->decimal('commission_bonus_percent', 5, 2)->default(0);
            $table->boolean('team_synergy_bonus')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'year', 'month']);
            $table->index('qualified');
            $table->index(['year', 'month']);
        });

        // User Badges Table
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('badge_code', 50);
            $table->string('badge_name', 100);
            $table->integer('lp_reward')->default(0);
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['user_id', 'badge_code']);
            $table->index('badge_code');
        });

        // Monthly Challenges Table
        Schema::create('monthly_challenges', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('month');
            $table->integer('year');
            $table->string('challenge_name', 100);
            $table->text('description')->nullable();
            $table->decimal('point_multiplier', 3, 2)->default(1.00);
            $table->string('target_activity', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['year', 'month']);
            $table->index('is_active');
        });

        // Add fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('current_professional_level', [
                'associate',
                'professional',
                'senior',
                'manager',
                'director',
                'executive',
                'ambassador'
            ])->default('associate')->after('email');
            $table->timestamp('level_achieved_at')->nullable()->after('current_professional_level');
            $table->integer('courses_completed_count')->default(0)->after('level_achieved_at');
            $table->integer('days_active_count')->default(0)->after('courses_completed_count');
            $table->boolean('is_currently_active')->default(true)->after('days_active_count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'current_professional_level',
                'level_achieved_at',
                'courses_completed_count',
                'days_active_count',
                'is_currently_active'
            ]);
        });

        Schema::dropIfExists('monthly_challenges');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('monthly_activity_status');
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('user_points');
    }
};
