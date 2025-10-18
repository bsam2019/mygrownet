<?php

namespace Tests\Unit\Models;

use App\Models\InvestmentTier;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvestmentTierTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_monthly_share_correctly(): void
    {
        $tier = InvestmentTier::factory()->create([
            'name' => 'Bronze',
            'monthly_share' => 50.00
        ]);

        $this->assertEquals(50.00, $tier->calculateMonthlyShare());
    }

    public function test_it_calculates_team_volume_bonus(): void
    {
        $tier = InvestmentTier::factory()->create([
            'monthly_team_volume_bonus_rate' => 5.0
        ]);

        $bonus = $tier->calculateTeamVolumeBonus(10000);
        $this->assertEquals(500.0, $bonus); // 10000 * 5% = 500
    }

    public function test_it_checks_upgrade_qualification(): void
    {
        $tier = InvestmentTier::factory()->create([
            'required_active_referrals' => 3,
            'required_team_volume' => 5000
        ]);

        $this->assertTrue($tier->qualifiesForUpgrade(5, 10000));
        $this->assertFalse($tier->qualifiesForUpgrade(2, 10000));
        $this->assertFalse($tier->qualifiesForUpgrade(5, 3000));
    }

    public function test_it_returns_mygrownet_upgrade_requirements(): void
    {
        $tier = InvestmentTier::factory()->create([
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
        $tier = InvestmentTier::factory()->create([
            'quarterly_profit_sharing_eligible' => true,
            'annual_profit_sharing_eligible' => false
        ]);

        $this->assertTrue($tier->isEligibleForProfitSharing('quarterly'));
        $this->assertFalse($tier->isEligibleForProfitSharing('annual'));
    }

    public function test_it_returns_mygrownet_benefits(): void
    {
        $tier = InvestmentTier::factory()->create([
            'monthly_fee' => 300,
            'monthly_share' => 150,
            'achievement_bonus' => 500,
            'monthly_team_volume_bonus_rate' => 2.0,
            'quarterly_profit_sharing_eligible' => false,
            'annual_profit_sharing_eligible' => false,
            'profit_sharing_percentage' => 0,
            'leadership_bonus_eligible' => false,
            'business_facilitation_eligible' => false,
            'benefits' => ['smartphone_tablet_reward', 'advanced_educational_content']
        ]);

        $benefits = $tier->getMyGrowNetBenefits();

        $this->assertEquals(300, $benefits['monthly_fee']);
        $this->assertEquals(150, $benefits['monthly_share']);
        $this->assertEquals(500, $benefits['achievement_bonus']);
        $this->assertEquals(2.0, $benefits['team_volume_bonus_rate']);
        $this->assertFalse($benefits['profit_sharing']['quarterly_eligible']);
        $this->assertFalse($benefits['leadership_bonus_eligible']);
        $this->assertFalse($benefits['business_facilitation_eligible']);
        $this->assertIsArray($benefits['benefits']);
    }

    public function test_it_returns_mygrownet_commission_structure(): void
    {
        $tier = InvestmentTier::factory()->create([
            'direct_referral_rate' => 12.0,
            'level2_referral_rate' => 6.0,
            'level3_referral_rate' => 4.0,
            'monthly_team_volume_bonus_rate' => 5.0
        ]);

        $structure = $tier->getMyGrowNetCommissionStructure();

        $this->assertEquals(12.0, $structure['level_1']);
        $this->assertEquals(6.0, $structure['level_2']);
        $this->assertEquals(4.0, $structure['level_3']);
        $this->assertEquals(2.0, $structure['level_4']); // MyGrowNet Level 4: 2%
        $this->assertEquals(1.0, $structure['level_5']); // MyGrowNet Level 5: 1%
        $this->assertEquals(5.0, $structure['team_volume_bonus_rate']);
    }
}