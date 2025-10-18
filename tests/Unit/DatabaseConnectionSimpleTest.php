<?php

namespace Tests\Unit;

use App\Models\InvestmentTier;
use Tests\TestCase;

class DatabaseConnectionSimpleTest extends TestCase
{
    public function test_can_query_existing_data()
    {
        // Try to query without creating new data
        $count = InvestmentTier::count();
        
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function test_can_create_model_without_database()
    {
        // Create model instance without saving to database
        $tier = new InvestmentTier([
            'name' => 'Test Tier',
            'minimum_investment' => 500,
            'fixed_profit_rate' => 3.0,
            'direct_referral_rate' => 5.0,
            'level2_referral_rate' => 0.0,
            'level3_referral_rate' => 0.0,
            'reinvestment_bonus_rate' => 0.0,
            'order' => 1
        ]);

        $this->assertEquals('Test Tier', $tier->name);
        $this->assertEquals(500, $tier->minimum_investment);
    }
}