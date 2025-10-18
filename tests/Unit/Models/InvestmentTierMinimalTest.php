<?php

namespace Tests\Unit\Models;

use App\Models\InvestmentTier;
use Tests\TestCase;

class InvestmentTierMinimalTest extends TestCase
{
    public function test_it_calculates_referral_commission_correctly()
    {
        // Create model instance without database
        $tier = new InvestmentTier([
            'name' => 'Test Tier',
            'direct_referral_rate' => 10.0,
            'level2_referral_rate' => 5.0,
            'level3_referral_rate' => 2.0
        ]);

        $this->assertEquals(100.0, $tier->calculateReferralCommission(1000, 1));
        $this->assertEquals(50.0, $tier->calculateReferralCommission(1000, 2));
        $this->assertEquals(20.0, $tier->calculateReferralCommission(1000, 3));
        $this->assertEquals(0.0, $tier->calculateReferralCommission(1000, 4));
    }

    public function test_it_calculates_monthly_profit_correctly()
    {
        $tier = new InvestmentTier([
            'name' => 'Test Tier',
            'fixed_profit_rate' => 12.0 // 12% annual = 1% monthly
        ]);

        $monthlyProfit = $tier->calculateMonthlyProfit(1000);
        $this->assertEquals(10.0, $monthlyProfit); // 1000 * 12% / 12 months = 10
    }

    public function test_it_gets_referral_rate_for_level()
    {
        $tier = new InvestmentTier([
            'direct_referral_rate' => 15.0,
            'level2_referral_rate' => 7.0,
            'level3_referral_rate' => 3.0
        ]);

        $this->assertEquals(15.0, $tier->getReferralRateForLevel(1));
        $this->assertEquals(7.0, $tier->getReferralRateForLevel(2));
        $this->assertEquals(3.0, $tier->getReferralRateForLevel(3));
        $this->assertEquals(0.0, $tier->getReferralRateForLevel(4));
    }
}