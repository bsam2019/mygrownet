<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('achievements')) {
            // Drop and recreate category enum safely
            if (Schema::hasColumn('achievements', 'category')) {
                try {
                    Schema::table('achievements', function (Blueprint $table) {
                        $table->dropColumn('category');
                    });
                } catch (Throwable $e) {}
            }
            if (!Schema::hasColumn('achievements', 'category')) {
                Schema::table('achievements', function (Blueprint $table) {
                    $table->enum('category', [
                        'referral',
                        'subscription', 
                        'education',
                        'community',
                        'mentorship',
                        'tier_advancement',
                        'team_building',
                        'project_contribution',
                        'leadership',
                        'financial_milestone',
                        'course_completion',
                        'voting_participation',
                        'asset_rewards',
                        'profit_sharing'
                    ]);
                });
            }

            // Add MyGrowNet specific fields (guarded per column, avoid fragile after())
            Schema::table('achievements', function (Blueprint $table) {
                if (!Schema::hasColumn('achievements', 'tier_requirement')) {
                    $table->enum('tier_requirement', ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'])->nullable();
                }
                if (!Schema::hasColumn('achievements', 'monetary_reward')) {
                    $table->decimal('monetary_reward', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('achievements', 'tier_specific_rewards')) {
                    $table->json('tier_specific_rewards')->nullable();
                }
                if (!Schema::hasColumn('achievements', 'triggers_celebration')) {
                    $table->boolean('triggers_celebration')->default(false);
                }
                if (!Schema::hasColumn('achievements', 'celebration_message')) {
                    $table->string('celebration_message')->nullable();
                }
                // Leaderboard and ranking
                if (!Schema::hasColumn('achievements', 'counts_for_leaderboard')) {
                    $table->boolean('counts_for_leaderboard')->default(true);
                }
                if (!Schema::hasColumn('achievements', 'leaderboard_weight')) {
                    $table->integer('leaderboard_weight')->default(1);
                }
                if (!Schema::hasColumn('achievements', 'difficulty_level')) {
                    $table->enum('difficulty_level', ['easy', 'medium', 'hard', 'legendary'])->default('medium');
                }
                // Progress tracking
                if (!Schema::hasColumn('achievements', 'has_progress_tracking')) {
                    $table->boolean('has_progress_tracking')->default(false);
                }
                if (!Schema::hasColumn('achievements', 'progress_milestones')) {
                    $table->json('progress_milestones')->nullable();
                }
                if (!Schema::hasColumn('achievements', 'progress_unit')) {
                    $table->string('progress_unit')->nullable();
                }
                // Achievement dependencies
                if (!Schema::hasColumn('achievements', 'prerequisite_achievements')) {
                    $table->json('prerequisite_achievements')->nullable();
                }
                if (!Schema::hasColumn('achievements', 'unlocks_achievements')) {
                    $table->json('unlocks_achievements')->nullable();
                }
                // Seasonal and time-based
                if (!Schema::hasColumn('achievements', 'available_from')) {
                    $table->date('available_from')->nullable();
                }
                if (!Schema::hasColumn('achievements', 'available_until')) {
                    $table->date('available_until')->nullable();
                }
                if (!Schema::hasColumn('achievements', 'is_seasonal')) {
                    $table->boolean('is_seasonal')->default(false);
                }
                // Social features
                if (!Schema::hasColumn('achievements', 'is_shareable')) {
                    $table->boolean('is_shareable')->default(true);
                }
                if (!Schema::hasColumn('achievements', 'share_message')) {
                    $table->text('share_message')->nullable();
                }
            });

            // Add indexes with explicit names; ignore errors on reruns
            try { Schema::table('achievements', function (Blueprint $table) { $table->index(['tier_requirement', 'is_active'], 'ach_tier_active_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('achievements', function (Blueprint $table) { $table->index(['difficulty_level', 'category'], 'ach_diff_cat_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('achievements', function (Blueprint $table) { $table->index(['counts_for_leaderboard', 'leaderboard_weight'], 'ach_leaderboard_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('achievements', function (Blueprint $table) { $table->index(['available_from', 'available_until'], 'ach_available_idx'); }); } catch (Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('achievements')) {
            // Drop indexes by explicit names; ignore if missing
            try { Schema::table('achievements', function (Blueprint $table) { $table->dropIndex('ach_tier_active_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('achievements', function (Blueprint $table) { $table->dropIndex('ach_diff_cat_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('achievements', function (Blueprint $table) { $table->dropIndex('ach_leaderboard_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('achievements', function (Blueprint $table) { $table->dropIndex('ach_available_idx'); }); } catch (Throwable $e) {}

            // Drop columns (best-effort)
            try { Schema::table('achievements', function (Blueprint $table) { $table->dropColumn(['tier_requirement','monetary_reward','tier_specific_rewards','triggers_celebration','celebration_message','counts_for_leaderboard','leaderboard_weight','difficulty_level','has_progress_tracking','progress_milestones','progress_unit','prerequisite_achievements','unlocks_achievements','available_from','available_until','is_seasonal','is_shareable','share_message']); }); } catch (Throwable $e) {}

            // Revert category to original
            try { Schema::table('achievements', function (Blueprint $table) { $table->dropColumn('category'); }); } catch (Throwable $e) {}
            try { Schema::table('achievements', function (Blueprint $table) { $table->enum('category', ['referral', 'subscription', 'education', 'community', 'mentorship']); }); } catch (Throwable $e) {}
        }
    }
};