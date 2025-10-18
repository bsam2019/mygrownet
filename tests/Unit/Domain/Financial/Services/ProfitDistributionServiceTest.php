<?php

namespace Tests\Unit\Domain\Financial\Services;

use App\Domain\Financial\Services\ProfitDistributionService;
use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ProfitDistribution;
use App\Models\ProfitShare;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ProfitDistributionServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProfitDistributionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProfitDistributionService();
        $this->seedInvestmentTiers();
    }

    private function seedInvestmentTiers(): void
    {
        InvestmentTier::create([
            'name' => 'Basic',
            'minimum_investment' => 500,
            'fixed_profit_rate' => 3.00,
            'order' => 1,
        ]);

        InvestmentTier::create([
            'name' => 'Starter',
            'minimum_investment' => 1000,
            'fixed_profit_rate' => 5.00,
            'order' => 2,
        ]);

        InvestmentTier::create([
            'name' => 'Elite',
            'minimum_investment' => 10000,
            'fixed_profit_rate' => 15.00,
            'order' => 5,
        ]);
    }

    public function test_distributes_annual_profits_correctly(): void
    {
        // Create users with investments
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        Investment::factory()->create([
            'user_id' => $user1->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subYear(),
        ]);
        
        Investment::factory()->create([
            'user_id' => $user2->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subYear(),
        ]);
        
        $totalProfit = 100000;
        
        $this->service->distributeAnnualProfits($totalProfit);
        
        // Check profit distribution record was created
        $this->assertDatabaseHas('profit_distributions', [
            'period_type' => 'annual',
            'total_profit' => $totalProfit,
            'status' => 'completed',
        ]);
        
        // Check individual profit shares were created
        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $user1->id,
            'type' => 'annual',
        ]);
        
        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $user2->id,
            'type' => 'annual',
        ]);
        
        // Check transactions were created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user1->id,
            'type' => 'profit_share',
            'status' => 'completed',
        ]);
    }

    public function test_calculates_correct_annual_profit_shares(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        // User1: 500 at 3% = 15
        Investment::factory()->create([
            'user_id' => $user1->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subYear(),
        ]);
        
        // User2: 1000 at 5% = 50
        Investment::factory()->create([
            'user_id' => $user2->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subYear(),
        ]);
        
        $totalProfit = 100000;
        $totalInvestmentPool = 1500; // 500 + 1000
        
        $this->service->distributeAnnualProfits($totalProfit);
        
        // Calculate expected shares
        $user1ExpectedShare = (500 / $totalInvestmentPool) * $totalProfit * 0.03;
        $user2ExpectedShare = (1000 / $totalInvestmentPool) * $totalProfit * 0.05;
        
        $user1Share = ProfitShare::where('user_id', $user1->id)->first();
        $user2Share = ProfitShare::where('user_id', $user2->id)->first();
        
        $this->assertEquals($user1ExpectedShare, $user1Share->amount);
        $this->assertEquals($user2ExpectedShare, $user2Share->amount);
    }

    public function test_distributes_quarterly_bonuses_correctly(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        Investment::factory()->create([
            'user_id' => $user1->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
        ]);
        
        Investment::factory()->create([
            'user_id' => $user2->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 1500,
        ]);
        
        $bonusPool = 50000;
        
        $this->service->distributeQuarterlyBonuses($bonusPool);
        
        // Check profit distribution record was created
        $this->assertDatabaseHas('profit_distributions', [
            'period_type' => 'quarterly',
            'total_profit' => $bonusPool,
            'status' => 'completed',
        ]);
        
        // Check bonus shares were created
        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $user1->id,
            'type' => 'quarterly_bonus',
        ]);
        
        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $user2->id,
            'type' => 'quarterly_bonus',
        ]);
    }

    public function test_calculates_proportional_quarterly_bonuses(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        // User1: 1000 (50% of pool)
        Investment::factory()->create([
            'user_id' => $user1->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
        ]);
        
        // User2: 1000 (50% of pool)
        Investment::factory()->create([
            'user_id' => $user2->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
        ]);
        
        $bonusPool = 10000;
        
        $this->service->distributeQuarterlyBonuses($bonusPool);
        
        $user1Bonus = ProfitShare::where('user_id', $user1->id)
            ->where('type', 'quarterly_bonus')
            ->first();
        $user2Bonus = ProfitShare::where('user_id', $user2->id)
            ->where('type', 'quarterly_bonus')
            ->first();
        
        // Each should get 50% of bonus pool
        $this->assertEquals(5000, $user1Bonus->amount);
        $this->assertEquals(5000, $user2Bonus->amount);
    }

    public function test_calculates_investor_share_correctly(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 2000,
        ]);
        
        // Create another user to establish total pool
        $user2 = User::factory()->create();
        Investment::factory()->create([
            'user_id' => $user2->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 3000,
        ]);
        
        $totalPool = 10000;
        $share = $this->service->calculateInvestorShare($user, $totalPool);
        
        // User has 2000 out of 5000 total = 40% of pool
        $expectedShare = (2000 / 5000) * $totalPool;
        
        $this->assertEquals($expectedShare, $share);
    }

    public function test_handles_user_with_no_investments(): void
    {
        $user = User::factory()->create();
        
        $share = $this->service->calculateInvestorShare($user, 10000);
        
        $this->assertEquals(0, $share);
    }

    public function test_handles_weighted_profit_calculation_for_tier_upgrades(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        // Investment made 8 months ago in basic tier
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(8),
        ]);
        
        // Additional investment 4 months ago that upgraded to starter
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(4),
        ]);
        
        $totalProfit = 100000;
        
        $this->service->distributeAnnualProfits($totalProfit);
        
        $profitShare = ProfitShare::where('user_id', $user->id)->first();
        
        $this->assertGreaterThan(0, $profitShare->amount);
        $this->assertIsFloat($profitShare->amount);
    }

    public function test_excludes_recent_investments_from_annual_distribution(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        // Recent investment (less than 12 months)
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(6),
        ]);
        
        $this->service->distributeAnnualProfits(100000);
        
        // Should not create profit share for recent investment
        $this->assertDatabaseMissing('profit_shares', [
            'user_id' => $user->id,
            'type' => 'annual',
        ]);
    }

    public function test_includes_all_investments_in_quarterly_distribution(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        // Recent investment
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonth(),
        ]);
        
        $this->service->distributeQuarterlyBonuses(10000);
        
        // Should create bonus share for recent investment
        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $user->id,
            'type' => 'quarterly_bonus',
        ]);
    }

    public function test_handles_zero_profit_distribution(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subYear(),
        ]);
        
        $this->service->distributeAnnualProfits(0);
        
        $profitShare = ProfitShare::where('user_id', $user->id)->first();
        
        $this->assertEquals(0, $profitShare->amount);
    }

    public function test_creates_audit_trail_for_distributions(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subYear(),
        ]);
        
        $this->service->distributeAnnualProfits(100000);
        
        // Check that transaction was created
        $transaction = Transaction::where('user_id', $user->id)
            ->where('type', 'profit_share')
            ->first();
        
        $this->assertNotNull($transaction);
        $this->assertEquals('completed', $transaction->status);
        $this->assertGreaterThan(0, $transaction->amount);
    }

    public function test_handles_distribution_failure_gracefully(): void
    {
        // Create a scenario that might cause failure
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subYear(),
        ]);
        
        // Mock a database error scenario by using invalid profit amount
        try {
            $this->service->distributeAnnualProfits(-100000);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Check that no partial data was saved
            $this->assertDatabaseMissing('profit_distributions', [
                'total_profit' => -100000,
            ]);
        }
    }

    public function test_calculates_distribution_summary(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $eliteTier = InvestmentTier::where('name', 'Elite')->first();
        
        Investment::factory()->create([
            'user_id' => $user1->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subYear(),
        ]);
        
        Investment::factory()->create([
            'user_id' => $user2->id,
            'investment_tier_id' => $eliteTier->id,
            'amount' => 10000,
            'created_at' => Carbon::now()->subYear(),
        ]);
        
        $summary = $this->service->getDistributionSummary(100000, 'annual');
        
        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_profit', $summary);
        $this->assertArrayHasKey('total_investors', $summary);
        $this->assertArrayHasKey('total_investment_pool', $summary);
        $this->assertArrayHasKey('average_share', $summary);
        $this->assertArrayHasKey('tier_breakdown', $summary);
        
        $this->assertEquals(100000, $summary['total_profit']);
        $this->assertEquals(2, $summary['total_investors']);
        $this->assertEquals(11000, $summary['total_investment_pool']);
    }
}