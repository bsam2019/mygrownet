<?php

namespace Tests\Unit;

use App\Models\InvestmentTier;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseConnectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_investment_tier()
    {
        $tier = InvestmentTier::create([
            'name' => 'Test Tier',
            'minimum_investment' => 500,
            'fixed_profit_rate' => 3.0,
            'direct_referral_rate' => 5.0,
            'level2_referral_rate' => 0.0,
            'level3_referral_rate' => 0.0,
            'reinvestment_bonus_rate' => 0.0,
            'order' => 1
        ]);

        $this->assertDatabaseHas('investment_tiers', [
            'name' => 'Test Tier',
            'minimum_investment' => 500
        ]);

        $this->assertEquals('Test Tier', $tier->name);
    }

    public function test_memory_usage_with_database()
    {
        $memoryBefore = memory_get_usage();
        
        // Create multiple tiers
        for ($i = 1; $i <= 5; $i++) {
            InvestmentTier::create([
                'name' => "Tier $i",
                'minimum_investment' => $i * 500,
                'fixed_profit_rate' => $i * 2.0,
                'direct_referral_rate' => $i * 3.0,
                'level2_referral_rate' => 0.0,
                'level3_referral_rate' => 0.0,
                'reinvestment_bonus_rate' => 0.0,
                'order' => $i
            ]);
        }
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        $this->assertLessThan(10 * 1024 * 1024, $memoryUsed); // Less than 10MB
        $this->assertEquals(5, InvestmentTier::count());
    }
}