<?php

namespace Tests\Unit;

use App\Models\InvestmentTier;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ExistingDatabaseTest extends TestCase
{
    public function test_can_query_existing_investment_tiers()
    {
        $memoryBefore = memory_get_usage();
        
        // Query existing data without creating new records
        $count = InvestmentTier::count();
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        $this->assertLessThan(5 * 1024 * 1024, $memoryUsed); // Less than 5MB
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function test_can_create_tier_in_transaction_and_rollback()
    {
        $memoryBefore = memory_get_usage();
        
        DB::beginTransaction();
        
        try {
            $tier = InvestmentTier::create([
                'name' => 'Test Tier ' . uniqid(),
                'minimum_investment' => 500,
                'fixed_profit_rate' => 3.0,
                'direct_referral_rate' => 5.0,
                'level2_referral_rate' => 0.0,
                'level3_referral_rate' => 0.0,
                'reinvestment_bonus_rate' => 0.0,
                'order' => 999
            ]);
            
            $this->assertNotNull($tier->id);
            $this->assertEquals('Test Tier ' . substr($tier->name, -13), $tier->name);
            
            // Rollback to avoid affecting other tests
            DB::rollBack();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        $this->assertLessThan(10 * 1024 * 1024, $memoryUsed); // Less than 10MB
    }

    public function test_memory_usage_with_multiple_queries()
    {
        $memoryBefore = memory_get_usage();
        
        // Perform multiple queries to test memory accumulation
        for ($i = 0; $i < 10; $i++) {
            $tiers = InvestmentTier::select(['id', 'name', 'minimum_investment'])
                ->limit(5)
                ->get();
            
            $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $tiers);
        }
        
        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        $this->assertLessThan(20 * 1024 * 1024, $memoryUsed); // Less than 20MB
    }
}