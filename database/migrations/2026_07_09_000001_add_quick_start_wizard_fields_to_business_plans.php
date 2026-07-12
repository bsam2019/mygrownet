<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a small set of "Quick Start wizard" columns to user_business_plans.
 *
 * These are captured before the user fills in the full 20-step plan and are
 * used as anchor context for AI generation across all later steps.
 *
 *   business_description  : 1-2 sentence snapshot of the business.
 *   target_customer        : Short description of who they serve.
 *   short_term_goal        : What they want to achieve in 6-12 months.
 *   ai_tone                : 'formal' | 'casual' | 'investor' | 'personal'.
 *   years_operating        : Numeric (0 = idea/pre-launch).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_business_plans')) {
            return;
        }

        Schema::table('user_business_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('user_business_plans', 'business_description')) {
                $table->text('business_description')->nullable()->after('background');
            }
            if (!Schema::hasColumn('user_business_plans', 'wizard_completed')) {
                $table->boolean('wizard_completed')->default(false)->after('business_description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_business_plans')) {
            return;
        }

        Schema::table('user_business_plans', function (Blueprint $table) {
            foreach (['wizard_completed', 'business_description'] as $col) {
                if (Schema::hasColumn('user_business_plans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
