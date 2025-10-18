<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvestmentTier;
use App\Models\TierSetting;

class InvestmentTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Basic',
                'minimum_investment' => 100.00,
                'fixed_profit_rate' => 5.00,
                'direct_referral_rate' => 5.00,
                'level2_referral_rate' => 2.00,
                'level3_referral_rate' => 1.00,
                'benefits' => [
                    'Basic investment opportunity',
                    'Standard profit rates',
                    '3-level referral program',
                    'Quarterly profit sharing'
                ],
                'description' => 'Entry-level investment tier for new investors',
                'order' => 1,
                'settings' => [
                    'early_withdrawal_penalty' => 50.00,
                    'partial_withdrawal_limit' => 50.00,
                    'minimum_lock_in_period' => 3,
                    'performance_bonus_rate' => 2.00,
                    'requires_kyc' => true,
                    'requires_approval' => true
                ]
            ],
            [
                'name' => 'Starter',
                'minimum_investment' => 500.00,
                'fixed_profit_rate' => 7.00,
                'direct_referral_rate' => 7.00,
                'level2_referral_rate' => 3.00,
                'level3_referral_rate' => 1.50,
                'benefits' => [
                    'Higher profit rates',
                    'Enhanced referral bonuses',
                    'Priority support',
                    'Monthly profit sharing'
                ],
                'description' => 'Perfect for growing investors',
                'order' => 2,
                'settings' => [
                    'early_withdrawal_penalty' => 40.00,
                    'partial_withdrawal_limit' => 60.00,
                    'minimum_lock_in_period' => 6,
                    'performance_bonus_rate' => 3.00,
                    'requires_kyc' => true,
                    'requires_approval' => true
                ]
            ],
            [
                'name' => 'Builder',
                'minimum_investment' => 1000.00,
                'fixed_profit_rate' => 10.00,
                'direct_referral_rate' => 10.00,
                'level2_referral_rate' => 4.00,
                'level3_referral_rate' => 2.00,
                'benefits' => [
                    'Premium profit rates',
                    'Maximum referral bonuses',
                    'VIP support',
                    'Weekly profit sharing',
                    'Early access to new features'
                ],
                'description' => 'For serious investors building long-term wealth',
                'order' => 3,
                'settings' => [
                    'early_withdrawal_penalty' => 30.00,
                    'partial_withdrawal_limit' => 70.00,
                    'minimum_lock_in_period' => 9,
                    'performance_bonus_rate' => 4.00,
                    'requires_kyc' => true,
                    'requires_approval' => true
                ]
            ],
            [
                'name' => 'Leader',
                'minimum_investment' => 5000.00,
                'fixed_profit_rate' => 12.00,
                'direct_referral_rate' => 12.00,
                'level2_referral_rate' => 5.00,
                'level3_referral_rate' => 2.50,
                'benefits' => [
                    'Elite profit rates',
                    'Maximum referral bonuses',
                    '24/7 VIP support',
                    'Daily profit sharing',
                    'Exclusive investment opportunities',
                    'Leadership rewards program'
                ],
                'description' => 'For investment leaders and high-net-worth individuals',
                'order' => 4,
                'settings' => [
                    'early_withdrawal_penalty' => 20.00,
                    'partial_withdrawal_limit' => 80.00,
                    'minimum_lock_in_period' => 12,
                    'performance_bonus_rate' => 5.00,
                    'requires_kyc' => true,
                    'requires_approval' => true
                ]
            ],
            [
                'name' => 'Elite',
                'minimum_investment' => 10000.00,
                'fixed_profit_rate' => 15.00,
                'direct_referral_rate' => 15.00,
                'level2_referral_rate' => 6.00,
                'level3_referral_rate' => 3.00,
                'benefits' => [
                    'Ultimate profit rates',
                    'Maximum referral bonuses',
                    'Dedicated account manager',
                    'Real-time profit sharing',
                    'Exclusive investment opportunities',
                    'Elite rewards program',
                    'Private investment consultations'
                ],
                'description' => 'The highest tier for elite investors',
                'order' => 5,
                'settings' => [
                    'early_withdrawal_penalty' => 10.00,
                    'partial_withdrawal_limit' => 90.00,
                    'minimum_lock_in_period' => 12,
                    'performance_bonus_rate' => 6.00,
                    'requires_kyc' => true,
                    'requires_approval' => true
                ]
            ]
        ];

        foreach ($tiers as $tierData) {
            $settings = $tierData['settings'];
            unset($tierData['settings']);

            $tier = InvestmentTier::updateOrCreate(
                ['name' => $tierData['name']],
                $tierData
            );
            
            TierSetting::updateOrCreate(
                ['investment_tier_id' => $tier->id],
                array_merge(
                    ['investment_tier_id' => $tier->id],
                    $settings
                )
            );
        }
    }
} 