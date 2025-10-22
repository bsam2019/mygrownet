<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add points system columns if they don't exist
            if (!Schema::hasColumn('users', 'life_points')) {
                $table->decimal('life_points', 10, 2)->default(0)->after('email_verified_at');
            }
            
            if (!Schema::hasColumn('users', 'monthly_activity_points')) {
                $table->decimal('monthly_activity_points', 10, 2)->default(0)->after('life_points');
            }
            
            if (!Schema::hasColumn('users', 'points_last_reset_at')) {
                $table->timestamp('points_last_reset_at')->nullable()->after('monthly_activity_points');
            }
            
            if (!Schema::hasColumn('users', 'current_streak_months')) {
                $table->integer('current_streak_months')->default(0)->after('points_last_reset_at');
            }
            
            if (!Schema::hasColumn('users', 'longest_streak_months')) {
                $table->integer('longest_streak_months')->default(0)->after('current_streak_months');
            }
            
            if (!Schema::hasColumn('users', 'performance_tier')) {
                $table->enum('performance_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze')->after('longest_streak_months');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'life_points',
                'monthly_activity_points',
                'points_last_reset_at',
                'current_streak_months',
                'longest_streak_months',
                'performance_tier'
            ]);
        });
    }
};
