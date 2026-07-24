<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes the issue where Phase 5 performance management tables
     * were created but the migration wasn't recorded in the migrations table.
     * It checks if tables exist and marks the migration as complete if they do.
     */
    public function up(): void
    {
        // Check if performance management tables exist
        $tablesExist = Schema::hasTable('cms_performance_cycles') &&
                      Schema::hasTable('cms_performance_reviews') &&
                      Schema::hasTable('cms_performance_criteria') &&
                      Schema::hasTable('cms_performance_ratings') &&
                      Schema::hasTable('cms_goals') &&
                      Schema::hasTable('cms_goal_progress') &&
                      Schema::hasTable('cms_improvement_plans') &&
                      Schema::hasTable('cms_pip_milestones');

        if ($tablesExist) {
            // Tables exist, so mark the original migration as run
            $migrationName = '2026_02_15_140000_create_cms_performance_management_tables';
            
            // Check if migration record exists
            $exists = DB::table('migrations')
                ->where('migration', $migrationName)
                ->exists();

            if (!$exists) {
                // Get the latest batch number
                $batch = DB::table('migrations')->max('batch') ?? 0;
                
                // Insert the migration record
                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $batch + 1
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the migration record if it was added by this fix
        DB::table('migrations')
            ->where('migration', '2026_02_15_140000_create_cms_performance_management_tables')
            ->delete();
    }
};
