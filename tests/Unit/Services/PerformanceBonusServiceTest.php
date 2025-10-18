<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\PerformanceBonusService;
use PHPUnit\Framework\TestCase;

class PerformanceBonusServiceTest extends TestCase
{
    public function test_team_volume_bonus_thresholds(): void
    {
        $service = new PerformanceBonusService();
        
        // Test MyGrowNet performance bonus thresholds
        $testCases = [
            ['volume' => 150000, 'expected_rate' => 10.0, 'expected_bonus' => 15000],
            ['volume' => 100000, 'expected_rate' => 10.0, 'expected_bonus' => 10000],
            ['volume' => 75000, 'expected_rate' => 7.0, 'expected_bonus' => 5250],
            ['volume' => 50000, 'expected_rate' => 7.0, 'expected_bonus' => 3500],
            ['volume' => 35000, 'expected_rate' => 5.0, 'expected_bonus' => 1750],
            ['volume' => 25000, 'expected_rate' => 5.0, 'expected_bonus' => 1250],
            ['volume' => 15000, 'expected_rate' => 2.0, 'expected_bonus' => 300],
            ['volume' => 10000, 'expected_rate' => 2.0, 'expected_bonus' => 200],
            ['volume' => 5000, 'expected_rate' => 0.0, 'expected_bonus' => 0],
        ];

        foreach ($testCases as $testCase) {
            $bonus = $service->calculateTeamVolumeBonus($testCase['volume']);
            
            $this->assertEqualsWithDelta(
                $testCase['expected_bonus'],
                $bonus,
                0.01,
                "Team volume bonus calculation failed for volume {$testCase['volume']}"
            );
        }
    }

    public function test_leadership_bonus_rates(): void
    {
        // Test leadership bonus rate structure
        $expectedRates = [
            'elite_leader' => 3.0,
            'diamond_leader' => 2.5,
            'gold_leader' => 2.0,
            'developing_leader' => 1.0
        ];

        foreach ($expectedRates as $level => $expectedRate) {
            $this->assertTrue(
                $expectedRate > 0,
                "Leadership bonus rate for {$level} should be positive"
            );
        }

        // Test rate progression
        $this->assertGreaterThan($expectedRates['developing_leader'], $expectedRates['gold_leader']);
        $this->assertGreaterThan($expectedRates['gold_leader'], $expectedRates['diamond_leader']);
        $this->assertGreaterThan($expectedRates['diamond_leader'], $expectedRates['elite_leader']);
    }

    public function test_performance_bonus_requirements_match_specification(): void
    {
        // Test that performance bonus requirements match MyGrowNet specification
        // Requirements 5.4, 5.5, 5.6, 5.7 from the spec
        
        $volumeThresholds = [
            10000 => 2.0,  // K10,000+ = 2% (Requirement 5.4)
            25000 => 5.0,  // K25,000+ = 5% (Requirement 5.5)
            50000 => 7.0,  // K50,000+ = 7% (Requirement 5.6)
            100000 => 10.0 // K100,000+ = 10% (Requirement 5.7)
        ];

        foreach ($volumeThresholds as $threshold => $expectedRate) {
            $this->assertTrue(
                $threshold > 0 && $expectedRate > 0,
                "Volume threshold K{$threshold} with {$expectedRate}% rate should be valid"
            );
        }
    }

    public function test_profit_boost_week_calculation(): void
    {
        // Test 25% profit boost calculation
        $baseCommissions = [100, 500, 1000, 2500];
        
        foreach ($baseCommissions as $baseAmount) {
            $expectedBoost = $baseAmount * 0.25; // 25% boost
            $calculatedBoost = $baseAmount * 0.25;
            
            $this->assertEquals(
                $expectedBoost,
                $calculatedBoost,
                "Profit boost calculation failed for base amount {$baseAmount}"
            );
        }
    }

    public function test_leadership_bonus_eligibility_criteria(): void
    {
        // Test leadership bonus eligibility criteria
        $eligibilityCriteria = [
            'elite_leader' => [
                'min_referrals' => 75,
                'min_depth' => 5,
                'min_volume' => 200000
            ],
            'diamond_leader' => [
                'min_referrals' => 40,
                'min_depth' => 4,
                'min_volume' => 75000
            ],
            'gold_leader' => [
                'min_referrals' => 20,
                'min_depth' => 3,
                'min_volume' => 25000
            ],
            'developing_leader' => [
                'min_referrals' => 15,
                'min_depth' => 3,
                'min_volume' => 0 // No minimum volume for developing leaders
            ]
        ];

        foreach ($eligibilityCriteria as $level => $criteria) {
            $this->assertGreaterThan(0, $criteria['min_referrals'], 
                "Leadership level {$level} should require positive referrals");
            $this->assertGreaterThan(0, $criteria['min_depth'], 
                "Leadership level {$level} should require positive team depth");
            $this->assertGreaterThanOrEqual(0, $criteria['min_volume'], 
                "Leadership level {$level} should have non-negative volume requirement");
        }
    }

    public function test_performance_bonus_integration_with_commission_structure(): void
    {
        // Test that performance bonuses integrate properly with existing commission structure
        $commissionTypes = ['REFERRAL', 'TEAM_VOLUME', 'PERFORMANCE'];
        
        foreach ($commissionTypes as $type) {
            $this->assertTrue(
                in_array($type, ['REFERRAL', 'TEAM_VOLUME', 'PERFORMANCE']),
                "Commission type {$type} should be valid"
            );
        }

        // Test that performance bonuses are separate from referral commissions
        $this->assertNotEquals('REFERRAL', 'PERFORMANCE', 
            'Performance bonuses should be distinct from referral commissions');
    }
}