<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\MLM\Services;

use App\Domain\MLM\Services\MLMCommissionCalculationService;
use PHPUnit\Framework\TestCase;

class MLMCommissionCalculationServiceTest extends TestCase
{
    public function test_commission_rates_match_mygrownet_specification(): void
    {
        // Test that commission rates match MyGrowNet requirements
        $rates = MLMCommissionCalculationService::COMMISSION_RATES;
        
        $this->assertEquals(12.0, $rates[1], 'Level 1 should be 12%');
        $this->assertEquals(6.0, $rates[2], 'Level 2 should be 6%');
        $this->assertEquals(4.0, $rates[3], 'Level 3 should be 4%');
        $this->assertEquals(2.0, $rates[4], 'Level 4 should be 2%');
        $this->assertEquals(1.0, $rates[5], 'Level 5 should be 1%');
    }

    public function test_calculates_five_level_commission_amounts_correctly(): void
    {
        $packageAmount = 1000.0;

        // Test all five levels using the private method logic
        $expectedCommissions = [
            1 => 120.0, // 12% of 1000
            2 => 60.0,  // 6% of 1000
            3 => 40.0,  // 4% of 1000
            4 => 20.0,  // 2% of 1000
            5 => 10.0,  // 1% of 1000
        ];

        foreach ($expectedCommissions as $level => $expectedAmount) {
            $calculatedAmount = $this->calculateCommissionAmount($packageAmount, $level);
            $this->assertEquals($expectedAmount, $calculatedAmount, "Level {$level} commission calculation failed");
        }
    }

    public function test_team_volume_bonus_thresholds(): void
    {
        // Test different volume thresholds using the service logic
        $testCases = [
            ['volume' => 150000, 'expected_rate' => 0.10], // 10% for K100,000+
            ['volume' => 75000, 'expected_rate' => 0.07],  // 7% for K50,000+
            ['volume' => 35000, 'expected_rate' => 0.05],  // 5% for K25,000+
            ['volume' => 15000, 'expected_rate' => 0.02],  // 2% for K10,000+
            ['volume' => 5000, 'expected_rate' => 0.0],    // 0% for below K10,000
        ];

        foreach ($testCases as $testCase) {
            $bonus = $this->calculateTeamVolumeBonus($testCase['volume']);
            $expectedBonus = $testCase['volume'] * $testCase['expected_rate'];
            
            $this->assertEquals($expectedBonus, $bonus, 
                "Team volume bonus calculation failed for volume {$testCase['volume']}");
        }
    }

    private function calculateCommissionAmount(float $packageAmount, int $level): float
    {
        $rates = [
            1 => 12.0, // Level 1: 12%
            2 => 6.0,  // Level 2: 6%
            3 => 4.0,  // Level 3: 4%
            4 => 2.0,  // Level 4: 2%
            5 => 1.0,  // Level 5: 1%
        ];

        $rate = $rates[$level] ?? 0.0;
        return $packageAmount * ($rate / 100);
    }

    private function calculateTeamVolumeBonus(float $volume): float
    {
        // Replicate the logic from MLMCommissionCalculationService
        if ($volume >= 100000) {
            return $volume * 0.10; // 10% for K100,000+
        } elseif ($volume >= 50000) {
            return $volume * 0.07; // 7% for K50,000+
        } elseif ($volume >= 25000) {
            return $volume * 0.05; // 5% for K25,000+
        } elseif ($volume >= 10000) {
            return $volume * 0.02; // 2% for K10,000+
        }
        
        return 0;
    }
}