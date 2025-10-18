<?php

namespace Tests\Unit;

use App\Models\InvestmentTier;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class MinimalDatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Run only essential migrations manually
        $this->runEssentialMigrations();
    }

    private function runEssentialMigrations(): void
    {
        // Create investment_tiers table manually with minimal structure
        Schema::create('investment_tiers', function ($table) {
            $table->id();
            $table->string('name');
            $table->decimal('minimum_investment', 10, 2);
            $table->decimal('fixed_profit_rate', 5, 2);
            $table->decimal('direct_referral_rate', 5, 2);
            $table->decimal('level2_referral_rate', 5, 2)->default(0);
            $table->decimal('level3_referral_rate', 5, 2)->default(0);
            $table->decimal('reinvestment_bonus_rate', 5, 2)->default(0);
            $table->integer('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->text('description')->nullable();
            $table->json('benefits')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_can_create_investment_tier_with_minimal_schema()
    {
        $memoryBefore = memory_get_usage();
        
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

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        $this->assertLessThan(5 * 1024 * 1024, $memoryUsed); // Less than 5MB
        $this->assertEquals('Test Tier', $tier->name);
        $this->assertEquals(1, InvestmentTier::count());
    }

    public function test_can_create_multiple_tiers_efficiently()
    {
        $memoryBefore = memory_get_usage();
        
        // Create 10 tiers
        for ($i = 1; $i <= 10; $i++) {
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
        $this->assertEquals(10, InvestmentTier::count());
    }
}