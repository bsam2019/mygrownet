<?php

namespace Tests\Unit\Models;

use App\Models\InvestmentTier;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvestmentTierOptimizedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_monthly_share_correctly(): void
    {
        $tier = InvestmentTier::create([
            'name' => 'Bronze',
            'minimum_investment' => 500,
            'fixed_profit_rate' => 3.0,
            'direct_referral_rate' => 5.0,
            'level2_referral_rate' => 0.0,
            'level3_referral_rate' => 0.0,
            'reinvestment_bonus_rate' => 0.0,
            'order' => 1,
            'monthly_share' => 50.00
        ]);

        $this->assertEquals(50.00, $tier->calculateMonthlyShare());
    }

    public function test_it_calculates_team_volume_bonus(): void
    {
        $tier = InvestmentTier::create([
            'name' => 'Silver',
            'minimum_investment' => 1000,
            'fixed_profit_rate' => 5.0,
            'direct_referral_rate' => 7.0,
            'level2_referral_rate' => 2.0,
            'level3_referral_rate' => 0.0,
            'reinvestment_bonus_rate' => 8.0,
            'order' => 2,
            'monthly_team_volume_bonus_rate' => 5.0
        ]);

        $bonus = $tier->calculateTeamVolumeBonus(10000);
        $this->assertEquals(500.0, $bonus); // 10000 * 5% = 500
    }

    public function test_it_checks_upgrade_qualification(): void
    {
        $tier = InvestmentTier::create([
            'name' => 'Gold',
            'minimum_investment' => 2500,
            'fixed_profit_rate' => 7.0,
            'direct_referral_rate' => 10.0,
            'level2_referral_rate' => 3.0,
            'level3_referral_rate' => 1.0,
            'reinvestment_bonus_rate' => 10.0,
            'order' => 3,
            'required_active_referrals' => 3,
            'required_team_volume' => 5000
        ]);

        $this->assertTrue($tier->qualifiesForUpgrade(5, 10000));
        $this->assertFalse($tier->qualifiesForUpgrade(2, 10000));
        $this->assertFalse($tier->qualifiesForUpgrade(5, 3000));
    }

    public function test_it_returns_mygrownet_upgrade_requirements(): void
    {
        $tier = InvestmentTier::create([
            'name' => 'Platinum',
            'minimum_investment' => 5000,
            'fixed_profit_rate' => 10.0,
            'direct_referral_rate' => 12.0,
            'level2_referral_rate' => 5.0,
            'level3_referral_rate' => 2.0,
            'reinvestment_bonus_rate' => 12.0,
            'order' => 4,
            'required_active_referrals' => 10,
            'required_team_volume' => 15000,
            'consecutive_months_required' => 3,
            'achievement_bonus' => 2000
        ]);

        $requirements = $tier->getMyGrowNetUpgradeRequirements();

        $this->assertEquals(10, $requirements['required_active_referrals']);
        $this->assertEquals(15000, $requirements['required_team_volume']);
        $this->assertEquals(3, $requirements['consecutive_months_required']);
        $this->assertEquals(2000, $requirements['achievement_bonus']);
    }

    public function test_it_checks_profit_sharing_eligibility(): void
    {
        $tier = InvestmentTier::create([
            'name' => 'Diamond',
            'minimum_investment' => 10000,
            'fixed_profit_rate' => 15.0,
            'direct_referral_rate' => 15.0,
            'level2_referral_rate' => 7.0,
            'level3_referral_rate' => 3.0,
            'reinvestment_bonus_rate' => 17.0,
            'order' => 5,
            'quarterly_profit_sharing_eligible' => true,
            'annual_profit_sharing_eligible' => false
        ]);

        $this->assertTrue($tier->isEligibleForProfitSharing('quarterly'));
        $this->assertFalse($tier->isEligibleForProfitSharing('annual'));
    }
}