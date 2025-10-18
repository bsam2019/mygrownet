<?php

namespace Tests\Unit\Domain\Investment\Services;

use App\Domain\Investment\Services\InvestmentTierService;
use App\Models\InvestmentTier;
use App\Models\User;
use App\Models\Investment;
use App\Models\TierUpgrade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class InvestmentTierServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvestmentTierService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvestmentTierService();
        $this->seedInvestmentTiers();
    }

    private function seedInvestmentTiers(): void
    {
        InvestmentTier::create([
            'name' => 'Basic',
            'minimum_investment' => 500,
            'fixed_profit_rate' => 3.00,
            'direct_referral_rate' => 5.00,
            'level2_referral_rate' => 0.00,
            'level3_referral_rate' => 0.00,
            'reinvestment_bonus_rate' => 0.00,
            'order' => 1,
        ]);

        InvestmentTier::create([
            'name' => 'Starter',
            'minimum_investment' => 1000,
            'fixed_profit_rate' => 5.00,
            'direct_referral_rate' => 7.00,
            'level2_referral_rate' => 2.00,
            'level3_referral_rate' => 0.00,
            'reinvestment_bonus_rate' => 3.00,
            'order' => 2,
        ]);

        InvestmentTier::create([
            'name' => 'Builder',
            'minimum_investment' => 2500,
            'fixed_profit_rate' => 7.00,
            'direct_referral_rate' => 10.00,
            'level2_referral_rate' => 3.00,
            'level3_referral_rate' => 1.00,
            'reinvestment_bonus_rate' => 3.00,
            'order' => 3,
        ]);

        InvestmentTier::create([
            'name' => 'Leader',
            'minimum_investment' => 5000,
            'fixed_profit_rate' => 10.00,
            'direct_referral_rate' => 12.00,
            'level2_referral_rate' => 5.00,
            'level3_referral_rate' => 2.00,
            'reinvestment_bonus_rate' => 2.00,
            'order' => 4,
        ]);

        InvestmentTier::create([
            'name' => 'Elite',
            'minimum_investment' => 10000,
            'fixed_profit_rate' => 15.00,
            'direct_referral_rate' => 15.00,
            'level2_referral_rate' => 7.00,
            'level3_referral_rate' => 3.00,
            'reinvestment_bonus_rate' => 2.00,
            'order' => 5,
        ]);
    }

    public function test_calculates_correct_tier_for_basic_amount(): void
    {
        $tier = $this->service->calculateTierForAmount(500);
        
        $this->assertEquals('Basic', $tier->name);
        $this->assertEquals(3.00, $tier->fixed_profit_rate);
    }

    public function test_calculates_correct_tier_for_starter_amount(): void
    {
        $tier = $this->service->calculateTierForAmount(1500);
        
        $this->assertEquals('Starter', $tier->name);
        $this->assertEquals(5.00, $tier->fixed_profit_rate);
    }

    public function test_calculates_correct_tier_for_elite_amount(): void
    {
        $tier = $this->service->calculateTierForAmount(15000);
        
        $this->assertEquals('Elite', $tier->name);
        $this->assertEquals(15.00, $tier->fixed_profit_rate);
    }

    public function test_calculates_tier_for_boundary_amounts(): void
    {
        // Test lower boundary
        $tier = $this->service->calculateTierForAmount(999);
        $this->assertEquals('Basic', $tier->name);

        // Test upper boundary
        $tier = $this->service->calculateTierForAmount(1000);
        $this->assertEquals('Starter', $tier->name);

        // Test exact boundary
        $tier = $this->service->calculateTierForAmount(2500);
        $this->assertEquals('Builder', $tier->name);
    }

    public function test_returns_null_for_invalid_amount(): void
    {
        $tier = $this->service->calculateTierForAmount(0);
        $this->assertNull($tier);
    }

    public function test_returns_null_for_negative_amount(): void
    {
        $tier = $this->service->calculateTierForAmount(-100);
        $this->assertNull($tier);
    }

    public function test_upgrades_tier_when_eligible(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        // Create initial investment
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
        ]);

        // Add more investment to qualify for upgrade
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
        ]);

        $upgraded = $this->service->upgradeTierIfEligible($user);
        
        $this->assertTrue($upgraded);
        
        // Check tier upgrade record was created
        $this->assertDatabaseHas('tier_upgrades', [
            'user_id' => $user->id,
            'from_tier_id' => $basicTier->id,
        ]);
    }

    public function test_does_not_upgrade_when_not_eligible(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
        ]);

        $upgraded = $this->service->upgradeTierIfEligible($user);
        
        $this->assertFalse($upgraded);
        $this->assertDatabaseMissing('tier_upgrades', [
            'user_id' => $user->id,
        ]);
    }

    public function test_calculates_profit_share_correctly(): void
    {
        $user = User::factory()->create();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 1000,
        ]);

        $profitShare = $this->service->calculateProfitShare($user, 100000);
        
        // User has 1000 out of total pool, with 5% rate
        // Expected: (1000 / total_pool) * 100000 * 0.05
        $expectedShare = (1000 / $user->investments->sum('amount')) * 100000 * 0.05;
        
        $this->assertEquals($expectedShare, $profitShare);
    }

    public function test_calculates_weighted_profit_share_for_multiple_tiers(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        // Create investments in different tiers
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(6),
        ]);

        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(3),
        ]);

        $profitShare = $this->service->calculateProfitShare($user, 100000);
        
        $this->assertGreaterThan(0, $profitShare);
        $this->assertIsFloat($profitShare);
    }

    public function test_gets_tier_benefits_correctly(): void
    {
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        $benefits = $this->service->getTierBenefits($starterTier);
        
        $this->assertIsArray($benefits);
        $this->assertArrayHasKey('fixed_profit_rate', $benefits);
        $this->assertArrayHasKey('direct_referral_rate', $benefits);
        $this->assertArrayHasKey('level_2_rate', $benefits);
        $this->assertArrayHasKey('reinvestment_bonus_rate', $benefits);
        
        $this->assertEquals(5.00, $benefits['fixed_profit_rate']);
        $this->assertEquals(7.00, $benefits['direct_referral_rate']);
        $this->assertEquals(2.00, $benefits['level_2_rate']);
    }

    public function test_handles_user_with_no_investments(): void
    {
        $user = User::factory()->create();
        
        $profitShare = $this->service->calculateProfitShare($user, 100000);
        
        $this->assertEquals(0, $profitShare);
    }

    public function test_calculates_tier_upgrade_requirements(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 750,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Basic')->first()->id,
        ]);

        $requirements = $this->service->getTierUpgradeRequirements($user);
        
        $this->assertIsArray($requirements);
        $this->assertArrayHasKey('current_tier', $requirements);
        $this->assertArrayHasKey('next_tier', $requirements);
        $this->assertArrayHasKey('remaining_amount', $requirements);
        
        $this->assertEquals('Basic', $requirements['current_tier']->name);
        $this->assertEquals('Starter', $requirements['next_tier']->name);
        $this->assertEquals(250, $requirements['remaining_amount']);
    }

    public function test_handles_elite_tier_upgrade_requirements(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 15000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Elite')->first()->id,
        ]);

        $requirements = $this->service->getTierUpgradeRequirements($user);
        
        $this->assertArrayHasKey('current_tier', $requirements);
        $this->assertEquals('Elite', $requirements['current_tier']->name);
        $this->assertNull($requirements['next_tier']);
        $this->assertEquals(0, $requirements['remaining_amount']);
    }
}