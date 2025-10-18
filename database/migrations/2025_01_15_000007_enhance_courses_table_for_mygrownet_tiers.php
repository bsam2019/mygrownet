<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                // Monthly content update tracking
                if (!Schema::hasColumn('courses', 'content_update_frequency')) {
                    $table->enum('content_update_frequency', ['weekly', 'monthly', 'quarterly'])
                          ->default('monthly')
                          ->after('order');
                }
                if (!Schema::hasColumn('courses', 'last_content_update')) {
                    $table->timestamp('last_content_update')->nullable()->after('content_update_frequency');
                }
                
                // Tier-specific content structure
                if (!Schema::hasColumn('courses', 'tier_specific_content')) {
                    $table->json('tier_specific_content')->nullable()->after('last_content_update');
                }
                if (!Schema::hasColumn('courses', 'monthly_content_schedule')) {
                    $table->json('monthly_content_schedule')->nullable()->after('tier_specific_content');
                }
            });

            // Drop old category and recreate with new enum safely
            if (Schema::hasColumn('courses', 'category')) {
                try {
                    Schema::table('courses', function (Blueprint $table) {
                        $table->dropColumn('category');
                    });
                } catch (Throwable $e) {}
            }
            if (!Schema::hasColumn('courses', 'category')) {
                Schema::table('courses', function (Blueprint $table) {
                    $table->enum('category', [
                        'financial_literacy',
                        'business_skills', 
                        'life_skills',
                        'investment_strategies',
                        'mlm_training',
                        'leadership_development'
                    ])->after('learning_objectives');
                });
            }

            // Add indexes for efficient tier-based queries with explicit names
            try {
                Schema::table('courses', function (Blueprint $table) {
                    $table->index(['content_update_frequency', 'last_content_update'], 'courses_cuf_lcu_idx');
                    $table->index(['category', 'difficulty_level', 'status'], 'courses_cat_diff_stat_idx');
                });
            } catch (Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('courses')) {
            // Drop indexes first by name
            try { Schema::table('courses', function (Blueprint $table) { $table->dropIndex('courses_cuf_lcu_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('courses', function (Blueprint $table) { $table->dropIndex('courses_cat_diff_stat_idx'); }); } catch (Throwable $e) {}

            // Drop MyGrowNet-specific columns if they exist
            Schema::table('courses', function (Blueprint $table) {
                foreach ([
                    'content_update_frequency',
                    'last_content_update',
                    'tier_specific_content',
                    'monthly_content_schedule'
                ] as $col) {
                    if (Schema::hasColumn('courses', $col)) {
                        $table->dropColumn($col);
                    }
                }
                // Remove enum category we added in up()
                if (Schema::hasColumn('courses', 'category')) {
                    try { $table->dropColumn('category'); } catch (Throwable $e) {}
                }
            });

            // Restore original category column
            if (!Schema::hasColumn('courses', 'category')) {
                Schema::table('courses', function (Blueprint $table) {
                    $table->enum('category', ['financial_literacy', 'business_skills', 'life_skills', 'investment_strategies'])
                          ->after('learning_objectives');
                });
            }
        }
    }
};