<?php

namespace Tests\Unit;

use App\Models\InvestmentTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentTierTest extends TestCase
{
    use RefreshDatabase;

    protected InvestmentTier $basicTier;
    protected InvestmentTier $starterTier;
    protected InvestmentTier $builderTier;
    protected InvestmentTier $leaderTier;
    protected InvestmentTier $eliteTier;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Get or create test tiers with VBIF rates
        $this->basicTier = InvestmentTier::firstOrCreate(
            ['name' => 'Basic'],
            [
                'minimum_investment' => 500.00,
                'fixed_profit_rate' => 3.00,
                'direct_referral_rate' => 5.00,
                'level2_referral_rate' => 0.00,
                'level3_referral_rate' => 0.00,
                'reinvestment_bonus_rate' => 0.00,
                'order' => 1,
                'is_active' => true
            ]
        );

        $this->starterTier = InvestmentTier::firstOrCreate(
            ['name' => 'Starter'],
            [
                'minimum_investment' => 1000.00,
                'fixed_profit_rate' => 5.00,
                'direct_referral_rate' => 7.00,
                'level2_referral_rate' => 2.00,
                'level3_referral_rate' => 0.00,
                'reinvestment_bonus_rate' => 8.00,
                'order' => 2,
                'is_active' => true
            ]
        );

        $this->builderTier = InvestmentTier::firstOrCreate(
            ['name' => 'Builder'],
            [
                'minimum_investment' => 2500.00,
                'fixed_profit_rate' => 7.00,
                'direct_referral_rate' => 10.00,
                'level2_referral_rate' => 3.00,
                'level3_referral_rate' => 1.00,
                'reinvestment_bonus_rate' => 10.00,
                'order' => 3,
                'is_active' => true
            ]
        );

        $this->leaderTier = InvestmentTier::firstOrCreate(
            ['name' => 'Leader'],
            [
                'minimum_investment' => 5000.00,
                'fixed_profit_rate' => 10.00,
                'direct_referral_rate' => 12.00,
                'level2_referral_rate' => 5.00,
                'level3_referral_rate' => 2.00,
                'reinvestment_bonus_rate' => 12.00,
                'order' => 4,
                'is_active' => true
            ]
        );

        $this->eliteTier = InvestmentTier::firstOrCreate(
            ['name' => 'Elite'],
            [
                'minimum_investment' => 10000.00,
                'fixed_profit_rate' => 15.00,
                'direct_referral_rate' => 15.00,
                'level2_referral_rate' => 7.00,
                'level3_referral_rate' => 3.00,
                'reinvestment_bonus_rate' => 17.00,
                'order' => 5,
                'is_active' => true
            ]
        );
    }

    /** @test */
    public function it_calculates_multi_level_referral_commissions_correctly()
    {
        // Test Basic tier (only level 1)
        $this->assertEquals(25.00, $this->basicTier->calculateMultiLevelReferralCommission(500, 1)); // 5% of 500
        $this->assertEquals(0.00, $this->basicTier->calculateMultiLevelReferralCommission(500, 2)); // No level 2
        $this->assertEquals(0.00, $this->basicTier->calculateMultiLevelReferralCommission(500, 3)); // No level 3

        // Test Starter tier (levels 1-2)
        $this->assertEquals(70.00, $this->starterTier->calculateMultiLevelReferralCommission(1000, 1)); // 7% of 1000
        $this->assertEquals(20.00, $this->starterTier->calculateMultiLevelReferralCommission(1000, 2)); // 2% of 1000
        $this->assertEquals(0.00, $this->starterTier->calculateMultiLevelReferralCommission(1000, 3)); // No level 3

        // Test Builder tier (levels 1-3)
        $this->assertEquals(250.00, $this->builderTier->calculateMultiLevelReferralCommission(2500, 1)); // 10% of 2500
        $this->assertEquals(75.00, $this->builderTier->calculateMultiLevelReferralCommission(2500, 2)); // 3% of 2500
        $this->assertEquals(25.00, $this->builderTier->calculateMultiLevelReferralCommission(2500, 3)); // 1% of 2500

        // Test Leader tier
        $this->assertEquals(600.00, $this->leaderTier->calculateMultiLevelReferralCommission(5000, 1)); // 12% of 5000
        $this->assertEquals(250.00, $this->leaderTier->calculateMultiLevelReferralCommission(5000, 2)); // 5% of 5000
        $this->assertEquals(100.00, $this->leaderTier->calculateMultiLevelReferralCommission(5000, 3)); // 2% of 5000

        // Test Elite tier
        $this->assertEquals(1500.00, $this->eliteTier->calculateMultiLevelReferralCommission(10000, 1)); // 15% of 10000
        $this->assertEquals(700.00, $this->eliteTier->calculateMultiLevelReferralCommission(10000, 2)); // 7% of 10000
        $this->assertEquals(300.00, $this->eliteTier->calculateMultiLevelReferralCommission(10000, 3)); // 3% of 10000
    }

    /** @test */
    public function it_returns_correct_referral_rates_for_each_level()
    {
        // Basic tier
        $this->assertEquals(5.00, $this->basicTier->getReferralRateForLevel(1));
        $this->assertEquals(0.00, $this->basicTier->getReferralRateForLevel(2));
        $this->assertEquals(0.00, $this->basicTier->getReferralRateForLevel(3));

        // Elite tier
        $this->assertEquals(15.00, $this->eliteTier->getReferralRateForLevel(1));
        $this->assertEquals(7.00, $this->eliteTier->getReferralRateForLevel(2));
        $this->assertEquals(3.00, $this->eliteTier->getReferralRateForLevel(3));
    }

    /** @test */
    public function it_returns_all_referral_rates_structure()
    {
        $rates = $this->eliteTier->getAllReferralRates();
        
        $this->assertEquals([
            'level_1' => 15.00,
            'level_2' => 7.00,
            'level_3' => 3.00,
            'max_levels' => 3
        ], $rates);
    }

    /** @test */
    public function it_determines_max_referral_levels_correctly()
    {
        $this->assertEquals(1, $this->basicTier->getMaxReferralLevels());
        $this->assertEquals(2, $this->starterTier->getMaxReferralLevels());
        $this->assertEquals(3, $this->builderTier->getMaxReferralLevels());
        $this->assertEquals(3, $this->leaderTier->getMaxReferralLevels());
        $this->assertEquals(3, $this->eliteTier->getMaxReferralLevels());
    }

    /** @test */
    public function it_checks_referral_level_eligibility()
    {
        // Basic tier - only level 1
        $this->assertTrue($this->basicTier->isEligibleForReferralLevel(1));
        $this->assertFalse($this->basicTier->isEligibleForReferralLevel(2));
        $this->assertFalse($this->basicTier->isEligibleForReferralLevel(3));

        // Starter tier - levels 1-2
        $this->assertTrue($this->starterTier->isEligibleForReferralLevel(1));
        $this->assertTrue($this->starterTier->isEligibleForReferralLevel(2));
        $this->assertFalse($this->starterTier->isEligibleForReferralLevel(3));

        // Builder tier - levels 1-3
        $this->assertTrue($this->builderTier->isEligibleForReferralLevel(1));
        $this->assertTrue($this->builderTier->isEligibleForReferralLevel(2));
        $this->assertTrue($this->builderTier->isEligibleForReferralLevel(3));
    }

    /** @test */
    public function it_calculates_reinvestment_bonus_correctly()
    {
        // Basic tier has no reinvestment bonus
        $this->assertEquals(0.00, $this->basicTier->calculateReinvestmentBonus(500));

        // Starter tier has 8% reinvestment bonus
        $this->assertEquals(80.00, $this->starterTier->calculateReinvestmentBonus(1000)); // 8% of 1000

        // Elite tier has 17% reinvestment bonus
        $this->assertEquals(1700.00, $this->eliteTier->calculateReinvestmentBonus(10000)); // 17% of 10000
    }

    /** @test */
    public function it_returns_enhanced_profit_rates_for_reinvestment()
    {
        $this->assertEquals(5.00, $this->basicTier->getEnhancedProfitRateForReinvestment()); // Same as fixed rate
        $this->assertEquals(8.00, $this->starterTier->getEnhancedProfitRateForReinvestment());
        $this->assertEquals(10.00, $this->builderTier->getEnhancedProfitRateForReinvestment());
        $this->assertEquals(12.00, $this->leaderTier->getEnhancedProfitRateForReinvestment());
        $this->assertEquals(17.00, $this->eliteTier->getEnhancedProfitRateForReinvestment());
    }

    /** @test */
    public function it_checks_reinvestment_bonus_eligibility()
    {
        $this->assertFalse($this->basicTier->isEligibleForReinvestmentBonus());
        $this->assertTrue($this->starterTier->isEligibleForReinvestmentBonus());
        $this->assertTrue($this->builderTier->isEligibleForReinvestmentBonus());
        $this->assertTrue($this->leaderTier->isEligibleForReinvestmentBonus());
        $this->assertTrue($this->eliteTier->isEligibleForReinvestmentBonus());
    }

    /** @test */
    public function it_compares_tiers_correctly()
    {
        $comparison = $this->starterTier->compareWith($this->builderTier);

        $this->assertEquals('Starter', $comparison['current_tier']['name']);
        $this->assertEquals('Builder', $comparison['comparison_tier']['name']);
        $this->assertEquals(1500.00, $comparison['differences']['investment_increase']); // 2500 - 1000
        $this->assertEquals(2.00, $comparison['differences']['profit_rate_increase']); // 7% - 5%
        $this->assertEquals(3.00, $comparison['differences']['referral_rate_improvements']['level_1']); // 10% - 7%
        $this->assertEquals(1.00, $comparison['differences']['referral_rate_improvements']['level_2']); // 3% - 2%
        $this->assertEquals(1.00, $comparison['differences']['referral_rate_improvements']['level_3']); // 1% - 0%
    }

    /** @test */
    public function it_calculates_matrix_commissions_with_position_multipliers()
    {
        // Test Elite tier matrix commissions
        $investment = 10000.00;
        
        // Level 1 (direct) - full rate
        $this->assertEquals(1500.00, $this->eliteTier->calculateMatrixCommission($investment, 1, 1)); // 15% * 1.0
        
        // Level 2 - 80% of base rate
        $expectedLevel2 = ($investment * 7.00 * 0.8) / 100; // 7% * 0.8 = 5.6%
        $this->assertEquals($expectedLevel2, $this->eliteTier->calculateMatrixCommission($investment, 2, 1));
        
        // Level 3 - 60% of base rate
        $expectedLevel3 = ($investment * 3.00 * 0.6) / 100; // 3% * 0.6 = 1.8%
        $this->assertEquals($expectedLevel3, $this->eliteTier->calculateMatrixCommission($investment, 3, 1));
    }

    /** @test */
    public function it_returns_matrix_commission_structure()
    {
        $structure = $this->eliteTier->getMatrixCommissionStructure();

        $this->assertEquals([
            'level_1' => [
                'base_rate' => 15.00,
                'positions' => 3,
                'multiplier' => 1.0,
                'effective_rate' => 15.00
            ],
            'level_2' => [
                'base_rate' => 7.00,
                'positions' => 9,
                'multiplier' => 0.8,
                'effective_rate' => 5.6
            ],
            'level_3' => [
                'base_rate' => 3.00,
                'positions' => 27,
                'multiplier' => 0.6,
                'effective_rate' => 1.8
            ]
        ], $structure);
    }

    /** @test */
    public function it_calculates_max_matrix_earnings()
    {
        $investment = 10000.00;
        $maxEarnings = $this->eliteTier->calculateMaxMatrixEarnings($investment);

        // Level 1: 15% * 10000 * 3 positions = 4500
        $this->assertEquals(1500.00, $maxEarnings['level_1']['per_position']);
        $this->assertEquals(3, $maxEarnings['level_1']['max_positions']);
        $this->assertEquals(4500.00, $maxEarnings['level_1']['max_level_earnings']);

        // Level 2: 5.6% * 10000 * 9 positions = 5040
        $this->assertEquals(560.00, $maxEarnings['level_2']['per_position']);
        $this->assertEquals(9, $maxEarnings['level_2']['max_positions']);
        $this->assertEquals(5040.00, $maxEarnings['level_2']['max_level_earnings']);

        // Level 3: 1.8% * 10000 * 27 positions = 4860
        $this->assertEquals(180.00, $maxEarnings['level_3']['per_position']);
        $this->assertEquals(27, $maxEarnings['level_3']['max_positions']);
        $this->assertEquals(4860.00, $maxEarnings['level_3']['max_level_earnings']);

        // Total: 4500 + 5040 + 4860 = 14400
        $this->assertEquals(14400.00, $maxEarnings['total_max_earnings']);
    }

    /** @test */
    public function it_returns_tier_specific_benefits()
    {
        $benefits = $this->eliteTier->getTierSpecificBenefits();

        $this->assertEquals(15.00, $benefits['profit_rate']);
        $this->assertEquals(3, $benefits['referral_levels']);
        $this->assertEquals(17.00, $benefits['reinvestment_bonus']);
        $this->assertEquals(0.2, $benefits['withdrawal_penalty_reduction']); // 20% reduction for Elite
        $this->assertEquals(5, $benefits['matrix_spillover_priority']); // Highest priority
        $this->assertEquals(17.00, $benefits['enhanced_profit_rate_year2']);
    }

    /** @test */
    public function it_calculates_withdrawal_penalty_reductions()
    {
        $this->assertEquals(0, $this->basicTier->getWithdrawalPenaltyReduction());
        $this->assertEquals(0, $this->starterTier->getWithdrawalPenaltyReduction());
        $this->assertEquals(0.1, $this->builderTier->getWithdrawalPenaltyReduction()); // 10%
        $this->assertEquals(0.15, $this->leaderTier->getWithdrawalPenaltyReduction()); // 15%
        $this->assertEquals(0.2, $this->eliteTier->getWithdrawalPenaltyReduction()); // 20%
    }

    /** @test */
    public function it_assigns_matrix_spillover_priorities()
    {
        $this->assertEquals(1, $this->basicTier->getMatrixSpilloverPriority());
        $this->assertEquals(2, $this->starterTier->getMatrixSpilloverPriority());
        $this->assertEquals(3, $this->builderTier->getMatrixSpilloverPriority());
        $this->assertEquals(4, $this->leaderTier->getMatrixSpilloverPriority());
        $this->assertEquals(5, $this->eliteTier->getMatrixSpilloverPriority());
    }
}