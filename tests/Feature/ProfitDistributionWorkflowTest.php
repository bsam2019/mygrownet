<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ProfitDistribution;
use App\Models\ProfitShare;
use App\Models\Transaction;
use App\Console\Commands\AnnualProfitDistributionCommand;
use App\Console\Commands\QuarterlyBonusDistributionCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class ProfitDistributionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
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

    public function test_annual_profit_distribution_workflow(): void
    {
        // Create users with investments older than 12 months
        $user1 = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Basic')->first()->id,
        ]);

        $user2 = User::factory()->create([
            'total_investment_amount' => 2000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        $user3 = User::factory()->create([
            'total_investment_amount' => 10000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Elite')->first()->id,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        $eliteTier = InvestmentTier::where('name', 'Elite')->first();

        // Create investments older than 12 months
        Investment::factory()->create([
            'user_id' => $user1->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        Investment::factory()->create([
            'user_id' => $user2->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 2000,
            'created_at' => Carbon::now()->subMonths(18),
        ]);

        Investment::factory()->create([
            'user_id' => $user3->id,
            'investment_tier_id' => $eliteTier->id,
            'amount' => 10000,
            'created_at' => Carbon::now()->subMonths(24),
        ]);

        // Run annual profit distribution command
        $totalProfit = 500000;
        
        Artisan::call('profit:distribute-annual', [
            'total_profit' => $totalProfit,
        ]);

        // Verify profit distribution record was created
        $this->assertDatabaseHas('profit_distributions', [
            'period_type' => 'annual',
            'total_profit' => $totalProfit,
            'status' => 'completed',
        ]);

        $distribution = ProfitDistribution::where('period_type', 'annual')->first();

        // Verify individual profit shares were created
        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $user1->id,
            'profit_distribution_id' => $distribution->id,
            'type' => 'annual',
        ]);

        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $user2->id,
            'profit_distribution_id' => $distribution->id,
            'type' => 'annual',
        ]);

        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $user3->id,
            'profit_distribution_id' => $distribution->id,
            'type' => 'annual',
        ]);

        // Verify profit share amounts are calculated correctly
        $user1Share = ProfitShare::where('user_id', $user1->id)->first();
        $user2Share = ProfitShare::where('user_id', $user2->id)->first();
        $user3Share = ProfitShare::where('user_id', $user3->id)->first();

        // User1: 1000 * 3% = 30
        $this->assertEquals(30, $user1Share->amount);
        
        // User2: 2000 * 5% = 100
        $this->assertEquals(100, $user2Share->amount);
        
        // User3: 10000 * 15% = 1500
        $this->assertEquals(1500, $user3Share->amount);

        // Verify transactions were created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user1->id,
            'type' => 'profit_share',
            'amount' => 30,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user2->id,
            'type' => 'profit_share',
            'amount' => 100,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user3->id,
            'type' => 'profit_share',
            'amount' => 1500,
            'status' => 'completed',
        ]);
    }

    public function test_quarterly_bonus_distribution_workflow(): void
    {
        // Create users with various investment amounts
        $user1 = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $user2 = User::factory()->create([
            'total_investment_amount' => 3000,
        ]);

        $user3 = User::factory()->create([
            'total_investment_amount' => 6000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();

        // Create recent investments (eligible for quarterly bonus)
        Investment::factory()->create([
            'user_id' => $user1->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(2),
        ]);

        Investment::factory()->create([
            'user_id' => $user2->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 3000,
            'created_at' => Carbon::now()->subMonths(1),
        ]);

        Investment::factory()->create([
            'user_id' => $user3->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 6000,
            'created_at' => Carbon::now()->subWeeks(2),
        ]);

        // Run quarterly bonus distribution command
        $bonusPool = 100000;
        
        Artisan::call('profit:distribute-quarterly', [
            'bonus_pool' => $bonusPool,
        ]);

        // Verify profit distribution record was created
        $this->assertDatabaseHas('profit_distributions', [
            'period_type' => 'quarterly',
            'total_profit' => $bonusPool,
            'status' => 'completed',
        ]);

        $distribution = ProfitDistribution::where('period_type', 'quarterly')->first();

        // Verify bonus shares were created
        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $user1->id,
            'profit_distribution_id' => $distribution->id,
            'type' => 'quarterly_bonus',
        ]);

        // Calculate expected proportional bonuses
        $totalInvestmentPool = 10000; // 1000 + 3000 + 6000
        
        $user1ExpectedBonus = ($bonusPool * 1000) / $totalInvestmentPool; // 10000
        $user2ExpectedBonus = ($bonusPool * 3000) / $totalInvestmentPool; // 30000
        $user3ExpectedBonus = ($bonusPool * 6000) / $totalInvestmentPool; // 60000

        $user1Bonus = ProfitShare::where('user_id', $user1->id)
            ->where('type', 'quarterly_bonus')
            ->first();
        $user2Bonus = ProfitShare::where('user_id', $user2->id)
            ->where('type', 'quarterly_bonus')
            ->first();
        $user3Bonus = ProfitShare::where('user_id', $user3->id)
            ->where('type', 'quarterly_bonus')
            ->first();

        $this->assertEquals($user1ExpectedBonus, $user1Bonus->amount);
        $this->assertEquals($user2ExpectedBonus, $user2Bonus->amount);
        $this->assertEquals($user3ExpectedBonus, $user3Bonus->amount);
    }

    public function test_annual_distribution_excludes_recent_investments(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Basic')->first()->id,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Create recent investment (less than 12 months)
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(6),
        ]);

        // Run annual profit distribution
        Artisan::call('profit:distribute-annual', [
            'total_profit' => 100000,
        ]);

        // Verify NO profit share was created for recent investment
        $this->assertDatabaseMissing('profit_shares', [
            'user_id' => $user->id,
            'type' => 'annual',
        ]);
    }

    public function test_profit_distribution_with_tier_changes(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();

        // Create investment that started in Basic tier
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(18),
        ]);

        // Create additional investment that upgraded to Starter tier
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 2000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        // Run annual profit distribution
        Artisan::call('profit:distribute-annual', [
            'total_profit' => 100000,
        ]);

        // Verify profit share was calculated based on current tier
        $profitShare = ProfitShare::where('user_id', $user->id)->first();
        
        // Should use Starter tier rate (5%) for total investment (2500)
        $expectedShare = 2500 * 0.05; // 125
        $this->assertEquals($expectedShare, $profitShare->amount);
    }

    public function test_profit_distribution_handles_zero_profit(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Basic')->first()->id,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        // Run distribution with zero profit
        Artisan::call('profit:distribute-annual', [
            'total_profit' => 0,
        ]);

        // Verify distribution record was created
        $this->assertDatabaseHas('profit_distributions', [
            'period_type' => 'annual',
            'total_profit' => 0,
            'status' => 'completed',
        ]);

        // Verify profit share was created with zero amount
        $profitShare = ProfitShare::where('user_id', $user->id)->first();
        $this->assertEquals(0, $profitShare->amount);
    }

    public function test_profit_distribution_creates_audit_trail(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Basic')->first()->id,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        // Run annual profit distribution
        Artisan::call('profit:distribute-annual', [
            'total_profit' => 100000,
        ]);

        $distribution = ProfitDistribution::where('period_type', 'annual')->first();

        // Verify distribution has proper audit information
        $this->assertNotNull($distribution->started_at);
        $this->assertNotNull($distribution->completed_at);
        $this->assertEquals('completed', $distribution->status);

        // Verify transaction has proper audit trail
        $transaction = Transaction::where('user_id', $user->id)
            ->where('type', 'profit_share')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals('completed', $transaction->status);
        $this->assertNotNull($transaction->created_at);
    }

    public function test_profit_distribution_handles_large_user_base(): void
    {
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();

        // Create 100 users with investments
        $users = [];
        for ($i = 0; $i < 100; $i++) {
            $user = User::factory()->create([
                'total_investment_amount' => 1000,
                'current_investment_tier_id' => $i % 2 === 0 ? $basicTier->id : $starterTier->id,
            ]);

            Investment::factory()->create([
                'user_id' => $user->id,
                'investment_tier_id' => $i % 2 === 0 ? $basicTier->id : $starterTier->id,
                'amount' => 1000,
                'created_at' => Carbon::now()->subMonths(15),
            ]);

            $users[] = $user;
        }

        // Run annual profit distribution
        $startTime = microtime(true);
        
        Artisan::call('profit:distribute-annual', [
            'total_profit' => 1000000,
        ]);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Verify all profit shares were created
        $profitShareCount = ProfitShare::count();
        $this->assertEquals(100, $profitShareCount);

        // Verify distribution completed in reasonable time (less than 30 seconds)
        $this->assertLessThan(30, $executionTime);

        // Verify total distributed amount is correct
        $totalDistributed = ProfitShare::sum('amount');
        
        // 50 Basic users: 50 * (1000 * 3%) = 1500
        // 50 Starter users: 50 * (1000 * 5%) = 2500
        // Total: 4000
        $expectedTotal = (50 * 30) + (50 * 50); // 1500 + 2500 = 4000
        $this->assertEquals($expectedTotal, $totalDistributed);
    }

    public function test_profit_distribution_failure_handling(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Basic')->first()->id,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        // Simulate failure by using invalid profit amount
        try {
            Artisan::call('profit:distribute-annual', [
                'total_profit' => -100000, // Invalid negative amount
            ]);
        } catch (\Exception $e) {
            // Expected to fail
        }

        // Verify no partial data was saved
        $this->assertDatabaseMissing('profit_distributions', [
            'total_profit' => -100000,
        ]);

        $this->assertDatabaseMissing('profit_shares', [
            'user_id' => $user->id,
        ]);
    }

    public function test_profit_distribution_summary_generation(): void
    {
        // Create users with different tiers
        $basicUser = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Basic')->first()->id,
        ]);

        $starterUser = User::factory()->create([
            'total_investment_amount' => 2000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();

        Investment::factory()->create([
            'user_id' => $basicUser->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        Investment::factory()->create([
            'user_id' => $starterUser->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 2000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        // Run annual profit distribution
        Artisan::call('profit:distribute-annual', [
            'total_profit' => 100000,
        ]);

        $distribution = ProfitDistribution::where('period_type', 'annual')->first();

        // Verify distribution summary data
        $this->assertEquals(2, $distribution->getParticipantCount());
        $this->assertEquals(130, $distribution->getTotalDistributedAmount()); // 30 + 100
        $this->assertEquals(65, $distribution->getAverageShare()); // 130 / 2
    }
}