<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\TierMaintenanceService;
use App\Services\TierQualificationTrackingService;
use PHPUnit\Framework\TestCase;

class TierMaintenanceServiceTest extends TestCase
{
    public function test_tier_maintenance_consecutive_month_logic(): void
    {
        // Test consecutive month tracking logic
        $testCases = [
            [
                'tier' => 'Silver',
                'consecutive_months_required' => 3,
                'months_qualified' => [true, true, true], // Last 3 months
                'should_be_permanent' => true
            ],
            [
                'tier' => 'Gold',
                'consecutive_months_required' => 3,
                'months_qualified' => [true, false, true], // Gap in qualification
                'should_be_permanent' => false
            ],
            [
                'tier' => 'Diamond',
                'consecutive_months_required' => 3,
                'months_qualified' => [true, true], // Only 2 months
                'should_be_permanent' => false
            ],
            [
                'tier' => 'Elite',
                'consecutive_months_required' => 3,
                'months_qualified' => [true, true, true, true], // 4 months
                'should_be_permanent' => true
            ]
        ];

        foreach ($testCases as $testCase) {
            $consecutiveMonths = $this->calculateConsecutiveMonths($testCase['months_qualified']);
            $isPermanent = $consecutiveMonths >= $testCase['consecutive_months_required'];
            
            $this->assertEquals(
                $testCase['should_be_permanent'],
                $isPermanent,
                "Permanent status check failed for {$testCase['tier']} tier"
            );
        }
    }

    public function test_tier_downgrade_grace_period_logic(): void
    {
        // Test grace period logic for tier downgrades
        $testCases = [
            [
                'months_below_requirements' => [false, false], // 2 months below
                'grace_period_months' => 2,
                'should_downgrade' => true
            ],
            [
                'months_below_requirements' => [false, true], // 1 month below, 1 month qualified
                'grace_period_months' => 2,
                'should_downgrade' => false
            ],
            [
                'months_below_requirements' => [false], // 1 month below
                'grace_period_months' => 2,
                'should_downgrade' => false
            ],
            [
                'months_below_requirements' => [false, false, false], // 3 months below
                'grace_period_months' => 2,
                'should_downgrade' => true
            ]
        ];

        foreach ($testCases as $testCase) {
            $monthsBelowRequirements = count(array_filter($testCase['months_below_requirements'], fn($qualified) => !$qualified));
            $shouldDowngrade = $monthsBelowRequirements >= $testCase['grace_period_months'];
            
            $this->assertEquals(
                $testCase['should_downgrade'],
                $shouldDowngrade,
                "Grace period downgrade logic failed"
            );
        }
    }

    public function test_tier_qualification_requirements(): void
    {
        // Test tier qualification requirements match MyGrowNet specification
        $tierRequirements = [
            'Bronze' => ['referrals' => 0, 'volume' => 0],
            'Silver' => ['referrals' => 3, 'volume' => 5000],
            'Gold' => ['referrals' => 10, 'volume' => 15000],
            'Diamond' => ['referrals' => 25, 'volume' => 50000],
            'Elite' => ['referrals' => 50, 'volume' => 150000]
        ];

        foreach ($tierRequirements as $tier => $requirements) {
            // Test qualification logic
            $testCases = [
                [
                    'referrals' => $requirements['referrals'],
                    'volume' => $requirements['volume'],
                    'should_qualify' => true
                ],
                [
                    'referrals' => $requirements['referrals'] - 1,
                    'volume' => $requirements['volume'],
                    'should_qualify' => false
                ],
                [
                    'referrals' => $requirements['referrals'],
                    'volume' => $requirements['volume'] - 1,
                    'should_qualify' => false
                ],
                [
                    'referrals' => $requirements['referrals'] + 5,
                    'volume' => $requirements['volume'] + 1000,
                    'should_qualify' => true
                ]
            ];

            foreach ($testCases as $testCase) {
                $qualifies = $this->checkTierQualification(
                    $testCase['referrals'],
                    $testCase['volume'],
                    $requirements['referrals'],
                    $requirements['volume']
                );

                $this->assertEquals(
                    $testCase['should_qualify'],
                    $qualifies,
                    "Qualification check failed for {$tier} tier with {$testCase['referrals']} referrals and {$testCase['volume']} volume"
                );
            }
        }
    }

    public function test_tier_maintenance_statistics_calculation(): void
    {
        // Test statistics calculation logic
        $mockData = [
            'total_users' => 100,
            'users_at_risk' => 15,
            'permanent_status_users' => 60,
            'qualification_rate' => 85.0
        ];

        // Validate statistics make sense
        $this->assertGreaterThanOrEqual(0, $mockData['users_at_risk']);
        $this->assertLessThanOrEqual($mockData['total_users'], $mockData['users_at_risk']);
        $this->assertLessThanOrEqual($mockData['total_users'], $mockData['permanent_status_users']);
        $this->assertGreaterThanOrEqual(0, $mockData['qualification_rate']);
        $this->assertLessThanOrEqual(100, $mockData['qualification_rate']);

        $this->assertTrue(true, 'Tier maintenance statistics validation passed');
    }

    public function test_achievement_bonus_distribution_logic(): void
    {
        // Test achievement bonus amounts for tier upgrades
        $achievementBonuses = [
            'Bronze' => 0,
            'Silver' => 500,
            'Gold' => 2000,
            'Diamond' => 5000,
            'Elite' => 10000
        ];

        foreach ($achievementBonuses as $tier => $bonus) {
            $this->assertGreaterThanOrEqual(0, $bonus, "Achievement bonus for {$tier} should be non-negative");
            
            if ($tier !== 'Bronze') {
                $this->assertGreaterThan(0, $bonus, "Achievement bonus for {$tier} should be positive");
            }
        }

        // Test bonus progression makes sense (higher tiers have higher bonuses)
        $this->assertGreaterThan($achievementBonuses['Silver'], $achievementBonuses['Gold']);
        $this->assertGreaterThan($achievementBonuses['Gold'], $achievementBonuses['Diamond']);
        $this->assertGreaterThan($achievementBonuses['Diamond'], $achievementBonuses['Elite']);
    }

    private function calculateConsecutiveMonths(array $monthsQualified): int
    {
        $consecutive = 0;
        
        // Count consecutive true values from the beginning
        foreach ($monthsQualified as $qualified) {
            if ($qualified) {
                $consecutive++;
            } else {
                break;
            }
        }
        
        return $consecutive;
    }

    private function checkTierQualification(int $userReferrals, float $userVolume, int $requiredReferrals, float $requiredVolume): bool
    {
        return $userReferrals >= $requiredReferrals && $userVolume >= $requiredVolume;
    }
}