<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add MyGrowNet-specific fields to existing investment_tiers table
        Schema::table('investment_tiers', function (Blueprint $table) {
            // Monthly subscription fields
            if (!Schema::hasColumn('investment_tiers', 'monthly_fee')) {
                $table->decimal('monthly_fee', 10, 2)->default(0)->after('minimum_investment');
            }
            if (!Schema::hasColumn('investment_tiers', 'monthly_share')) {
                $table->decimal('monthly_share', 10, 2)->default(0)->after('monthly_fee');
            }
            
            // Team volume requirements for tier advancement
            if (!Schema::hasColumn('investment_tiers', 'required_team_volume')) {
                $table->decimal('required_team_volume', 15, 2)->default(0)->after('monthly_share');
            }
            if (!Schema::hasColumn('investment_tiers', 'required_active_referrals')) {
                $table->integer('required_active_referrals')->default(0)->after('required_team_volume');
            }
            if (!Schema::hasColumn('investment_tiers', 'consecutive_months_required')) {
                $table->integer('consecutive_months_required')->default(3)->after('required_active_referrals');
            }
            
            // Achievement bonuses
            if (!Schema::hasColumn('investment_tiers', 'achievement_bonus')) {
                $table->decimal('achievement_bonus', 10, 2)->default(0)->after('consecutive_months_required');
            }
            if (!Schema::hasColumn('investment_tiers', 'monthly_team_volume_bonus_rate')) {
                $table->decimal('monthly_team_volume_bonus_rate', 5, 2)->default(0)->after('achievement_bonus');
            }
            
            // Profit sharing eligibility
            if (!Schema::hasColumn('investment_tiers', 'quarterly_profit_sharing_eligible')) {
                $table->boolean('quarterly_profit_sharing_eligible')->default(false)->after('monthly_team_volume_bonus_rate');
            }
            if (!Schema::hasColumn('investment_tiers', 'annual_profit_sharing_eligible')) {
                $table->boolean('annual_profit_sharing_eligible')->default(false)->after('quarterly_profit_sharing_eligible');
            }
            if (!Schema::hasColumn('investment_tiers', 'profit_sharing_percentage')) {
                $table->decimal('profit_sharing_percentage', 5, 2)->default(0)->after('annual_profit_sharing_eligible');
            }
            
            // Leadership and business facilitation
            if (!Schema::hasColumn('investment_tiers', 'leadership_bonus_eligible')) {
                $table->boolean('leadership_bonus_eligible')->default(false)->after('profit_sharing_percentage');
            }
            if (!Schema::hasColumn('investment_tiers', 'business_facilitation_eligible')) {
                $table->boolean('business_facilitation_eligible')->default(false)->after('leadership_bonus_eligible');
            }
        });

        // Add indexes for efficient queries with short names (ignore if already exist)
        try {
            Schema::table('investment_tiers', function (Blueprint $table) {
                $table->index(['required_team_volume', 'required_active_referrals'], 'tiers_req_tv_req_ar_idx');
                $table->index(['monthly_fee', 'is_active'], 'tiers_monthly_active_idx');
            });
        } catch (Throwable $e) {
            // Index may already exist, ignore
        }

        // Insert MyGrowNet membership tiers
        $this->insertMyGrowNetTiers();
    }

    public function down(): void
    {
        Schema::table('investment_tiers', function (Blueprint $table) {
            // Drop indexes first by explicit names
            try { $table->dropIndex('tiers_req_tv_req_ar_idx'); } catch (Throwable $e) {}
            try { $table->dropIndex('tiers_monthly_active_idx'); } catch (Throwable $e) {}

            // Drop MyGrowNet-specific columns if they exist
            foreach ([
                'monthly_fee',
                'monthly_share',
                'required_team_volume',
                'required_active_referrals',
                'consecutive_months_required',
                'achievement_bonus',
                'monthly_team_volume_bonus_rate',
                'quarterly_profit_sharing_eligible',
                'annual_profit_sharing_eligible',
                'profit_sharing_percentage',
                'leadership_bonus_eligible',
                'business_facilitation_eligible',
            ] as $col) {
                if (Schema::hasColumn('investment_tiers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function insertMyGrowNetTiers(): void
    {
        $tiers = [
            [
                'name' => 'Bronze',
                'minimum_investment' => 0, // No minimum investment, subscription-based
                'monthly_fee' => 150,
                'monthly_share' => 50,
                'required_team_volume' => 0, // Starting tier
                'required_active_referrals' => 0,
                'consecutive_months_required' => 1,
                'achievement_bonus' => 0,
                'monthly_team_volume_bonus_rate' => 0,
                'fixed_profit_rate' => 0, // No fixed profit, subscription-based
                'direct_referral_rate' => 12, // Level 1: 12%
                'level2_referral_rate' => 6, // Level 2: 6%
                'level3_referral_rate' => 4, // Level 3: 4%
                'quarterly_profit_sharing_eligible' => false,
                'annual_profit_sharing_eligible' => false,
                'profit_sharing_percentage' => 0,
                'leadership_bonus_eligible' => false,
                'business_facilitation_eligible' => false,
                'benefits' => json_encode([
                    'branded_starter_kit',
                    'basic_educational_content',
                    'peer_circle_access',
                    'five_level_commissions'
                ]),
                'description' => 'Entry-level membership with basic benefits and commission structure',
                'order' => 1,
                'is_active' => true,
                'is_archived' => false
            ],
            [
                'name' => 'Silver',
                'minimum_investment' => 0,
                'monthly_fee' => 300,
                'monthly_share' => 150,
                'required_team_volume' => 5000,
                'required_active_referrals' => 3,
                'consecutive_months_required' => 3,
                'achievement_bonus' => 500,
                'monthly_team_volume_bonus_rate' => 2,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 4,
                'quarterly_profit_sharing_eligible' => false,
                'annual_profit_sharing_eligible' => false,
                'profit_sharing_percentage' => 0,
                'leadership_bonus_eligible' => false,
                'business_facilitation_eligible' => false,
                'benefits' => json_encode([
                    'smartphone_tablet_reward',
                    'advanced_educational_content',
                    'mentorship_access',
                    'achievement_bonus',
                    'team_volume_bonus'
                ]),
                'description' => 'Silver membership with enhanced benefits and team volume bonuses',
                'order' => 2,
                'is_active' => true,
                'is_archived' => false
            ],
            [
                'name' => 'Gold',
                'minimum_investment' => 0,
                'monthly_fee' => 500,
                'monthly_share' => 300,
                'required_team_volume' => 15000,
                'required_active_referrals' => 10,
                'consecutive_months_required' => 3,
                'achievement_bonus' => 2000,
                'monthly_team_volume_bonus_rate' => 5,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 4,
                'quarterly_profit_sharing_eligible' => true,
                'annual_profit_sharing_eligible' => false,
                'profit_sharing_percentage' => 5,
                'leadership_bonus_eligible' => true,
                'business_facilitation_eligible' => false,
                'benefits' => json_encode([
                    'motorbike_office_equipment_reward',
                    'business_planning_toolkits',
                    'quarterly_profit_sharing',
                    'leadership_bonuses',
                    'expert_office_hours'
                ]),
                'description' => 'Gold membership with leadership benefits and quarterly profit sharing',
                'order' => 3,
                'is_active' => true,
                'is_archived' => false
            ],
            [
                'name' => 'Diamond',
                'minimum_investment' => 0,
                'monthly_fee' => 1000,
                'monthly_share' => 500,
                'required_team_volume' => 50000,
                'required_active_referrals' => 25,
                'consecutive_months_required' => 3,
                'achievement_bonus' => 5000,
                'monthly_team_volume_bonus_rate' => 7,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 4,
                'quarterly_profit_sharing_eligible' => true,
                'annual_profit_sharing_eligible' => true,
                'profit_sharing_percentage' => 10,
                'leadership_bonus_eligible' => true,
                'business_facilitation_eligible' => false,
                'benefits' => json_encode([
                    'car_property_down_payment_reward',
                    'investment_courses',
                    'annual_profit_sharing',
                    'enhanced_leadership_bonuses',
                    'community_project_voting'
                ]),
                'description' => 'Diamond membership with premium benefits and annual profit sharing',
                'order' => 4,
                'is_active' => true,
                'is_archived' => false
            ],
            [
                'name' => 'Elite',
                'minimum_investment' => 0,
                'monthly_fee' => 1500,
                'monthly_share' => 700,
                'required_team_volume' => 150000,
                'required_active_referrals' => 50,
                'consecutive_months_required' => 3,
                'achievement_bonus' => 10000,
                'monthly_team_volume_bonus_rate' => 10,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 4,
                'quarterly_profit_sharing_eligible' => true,
                'annual_profit_sharing_eligible' => true,
                'profit_sharing_percentage' => 15,
                'leadership_bonus_eligible' => true,
                'business_facilitation_eligible' => true,
                'benefits' => json_encode([
                    'luxury_car_property_investment_reward',
                    'vip_mentorship_content',
                    'innovation_lab_access',
                    'business_facilitation_services',
                    'dedicated_manager_access',
                    'premium_profit_sharing'
                ]),
                'description' => 'Elite membership with exclusive benefits and business facilitation',
                'order' => 5,
                'is_active' => true,
                'is_archived' => false
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
};