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
        Schema::table('achievements', function (Blueprint $table) {
            // Add monetary_reward if it doesn't exist
            if (!Schema::hasColumn('achievements', 'monetary_reward')) {
                $table->decimal('monetary_reward', 10, 2)->default(0)->after('points');
            }
            
            // Add tier_requirement if it doesn't exist
            if (!Schema::hasColumn('achievements', 'tier_requirement')) {
                $table->string('tier_requirement')->nullable()->after('monetary_reward');
            }
            
            // Add tier_specific_rewards if it doesn't exist
            if (!Schema::hasColumn('achievements', 'tier_specific_rewards')) {
                $table->json('tier_specific_rewards')->nullable()->after('tier_requirement');
            }
            
            // Add triggers_celebration if it doesn't exist
            if (!Schema::hasColumn('achievements', 'triggers_celebration')) {
                $table->boolean('triggers_celebration')->default(false)->after('tier_specific_rewards');
            }
            
            // Add celebration_message if it doesn't exist
            if (!Schema::hasColumn('achievements', 'celebration_message')) {
                $table->string('celebration_message')->nullable()->after('triggers_celebration');
            }
            
            // Add difficulty_level if it doesn't exist
            if (!Schema::hasColumn('achievements', 'difficulty_level')) {
                $table->enum('difficulty_level', ['easy', 'medium', 'hard', 'legendary'])->default('medium')->after('celebration_message');
            }
            
            // Add progress tracking fields
            if (!Schema::hasColumn('achievements', 'has_progress_tracking')) {
                $table->boolean('has_progress_tracking')->default(false)->after('difficulty_level');
            }
            
            if (!Schema::hasColumn('achievements', 'progress_milestones')) {
                $table->json('progress_milestones')->nullable()->after('has_progress_tracking');
            }
            
            if (!Schema::hasColumn('achievements', 'progress_unit')) {
                $table->string('progress_unit')->nullable()->after('progress_milestones');
            }
            
            // Add achievement dependencies
            if (!Schema::hasColumn('achievements', 'prerequisite_achievements')) {
                $table->json('prerequisite_achievements')->nullable()->after('progress_unit');
            }
            
            if (!Schema::hasColumn('achievements', 'unlocks_achievements')) {
                $table->json('unlocks_achievements')->nullable()->after('prerequisite_achievements');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $columns = [
                'monetary_reward', 
                'tier_requirement', 
                'tier_specific_rewards',
                'triggers_celebration', 
                'celebration_message', 
                'difficulty_level',
                'has_progress_tracking',
                'progress_milestones',
                'progress_unit',
                'prerequisite_achievements',
                'unlocks_achievements'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('achievements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
