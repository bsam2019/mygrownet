<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tier_upgrades')) {
            Schema::table('tier_upgrades', function (Blueprint $table) {
                // Add MyGrowNet-specific tracking fields if missing
                if (!Schema::hasColumn('tier_upgrades', 'team_volume')) {
                    $table->decimal('team_volume', 15, 2)->default(0)->after('total_investment_amount');
                }
                if (!Schema::hasColumn('tier_upgrades', 'active_referrals')) {
                    $table->integer('active_referrals')->default(0)->after('team_volume');
                }
                if (!Schema::hasColumn('tier_upgrades', 'achievement_bonus_awarded')) {
                    $table->decimal('achievement_bonus_awarded', 10, 2)->default(0)->after('active_referrals');
                }
                if (!Schema::hasColumn('tier_upgrades', 'consecutive_months_qualified')) {
                    $table->integer('consecutive_months_qualified')->default(0)->after('achievement_bonus_awarded');
                }
            });

            // Add indexes (ignore errors if they exist)
            try {
                Schema::table('tier_upgrades', function (Blueprint $table) {
                    $table->index(['user_id', 'created_at'], 'tier_upgrades_user_created_idx');
                    $table->index(['to_tier_id', 'created_at'], 'tier_upgrades_to_tier_created_idx');
                });
            } catch (Throwable $e) {
                // ignore if exists
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tier_upgrades')) {
            Schema::table('tier_upgrades', function (Blueprint $table) {
                // Drop indexes first by name
                try { $table->dropIndex('tier_upgrades_user_created_idx'); } catch (Throwable $e) {}
                try { $table->dropIndex('tier_upgrades_to_tier_created_idx'); } catch (Throwable $e) {}

                // Drop MyGrowNet-specific columns if they exist
                foreach ([
                    'team_volume',
                    'active_referrals',
                    'achievement_bonus_awarded',
                    'consecutive_months_qualified',
                ] as $col) {
                    if (Schema::hasColumn('tier_upgrades', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};