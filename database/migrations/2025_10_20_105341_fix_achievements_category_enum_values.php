<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to alter the enum by recreating it
        DB::statement("ALTER TABLE achievements MODIFY COLUMN category ENUM(
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
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE achievements MODIFY COLUMN category ENUM(
            'referral',
            'subscription',
            'education',
            'community',
            'mentorship'
        ) NOT NULL");
    }
};
