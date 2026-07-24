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
        // First, add any missing columns if they don't exist
        Schema::table('investment_tiers', function (Blueprint $table) {
            if (!Schema::hasColumn('investment_tiers', 'reinvestment_bonus_rate')) {
                $table->decimal('reinvestment_bonus_rate', 5, 2)->nullable()->after('level3_referral_rate');
            }
        });

        // Insert or update VBIF-specific investment tiers
        $tiers = [
            [
                'name' => 'Basic',
                'minimum_investment' => 500.00,
                'fixed_profit_rate' => 3.00,
                'direct_referral_rate' => 5.00,
                'level2_referral_rate' => 0.00,
                'level3_referral_rate' => 0.00,
                'reinvestment_bonus_rate' => 0.00,
                'description' => 'Entry level investment tier',
                'order' => 1,
                'is_active' => true
            ],
            [
                'name' => 'Starter',
                'minimum_investment' => 1000.00,
                'fixed_profit_rate' => 5.00,
                'direct_referral_rate' => 7.00,
                'level2_referral_rate' => 2.00,
                'level3_referral_rate' => 0.00,
                'reinvestment_bonus_rate' => 8.00,
                'description' => 'Starter investment tier with level 2 bonuses',
                'order' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Builder',
                'minimum_investment' => 2500.00,
                'fixed_profit_rate' => 7.00,
                'direct_referral_rate' => 10.00,
                'level2_referral_rate' => 3.00,
                'level3_referral_rate' => 1.00,
                'reinvestment_bonus_rate' => 10.00,
                'description' => 'Builder tier with 3-level referral bonuses',
                'order' => 3,
                'is_active' => true
            ],
            [
                'name' => 'Leader',
                'minimum_investment' => 5000.00,
                'fixed_profit_rate' => 10.00,
                'direct_referral_rate' => 12.00,
                'level2_referral_rate' => 5.00,
                'level3_referral_rate' => 2.00,
                'reinvestment_bonus_rate' => 12.00,
                'description' => 'Leadership tier with enhanced bonuses',
                'order' => 4,
                'is_active' => true
            ],
            [
                'name' => 'Elite',
                'minimum_investment' => 10000.00,
                'fixed_profit_rate' => 15.00,
                'direct_referral_rate' => 15.00,
                'level2_referral_rate' => 7.00,
                'level3_referral_rate' => 3.00,
                'reinvestment_bonus_rate' => 17.00,
                'description' => 'Elite investor tier with maximum benefits',
                'order' => 5,
                'is_active' => true
            ]
        ];

        foreach ($tiers as $tier) {
            DB::table('investment_tiers')->updateOrInsert(
                ['name' => $tier['name']],
                array_merge($tier, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the reinvestment_bonus_rate column
        Schema::table('investment_tiers', function (Blueprint $table) {
            if (Schema::hasColumn('investment_tiers', 'reinvestment_bonus_rate')) {
                $table->dropColumn('reinvestment_bonus_rate');
            }
        });

        // Optionally, you could also remove the VBIF tiers, but this might be destructive
        // DB::table('investment_tiers')->whereIn('name', ['Basic', 'Starter', 'Builder', 'Leader', 'Elite'])->delete();
    }
};
