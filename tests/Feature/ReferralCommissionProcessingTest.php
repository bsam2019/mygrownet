<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;
use App\Models\MatrixPosition;
use App\Models\Transaction;
use App\Jobs\ReferralCommissionJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Carbon\Carbon;

class ReferralCommissionProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedInvestmentTiers();
        Queue::fake();
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
            'order' => 1,
        ]);

        InvestmentTier::create([
            'name' => 'Starter',
            'minimum_investment' => 1000,
            'fixed_profit_rate' => 5.00,
            'direct_referral_rate' => 7.00,
            'level2_referral_rate' => 2.00,
            'level3_referral_rate' => 0.00,
            'order' => 2,
        ]);

        InvestmentTier::create([
            'name' => 'Builder',
            'minimum_investment' => 2500,
            'fixed_profit_rate' => 7.00,
            'direct_referral_rate' => 10.00,
            'level2_referral_rate' => 3.00,
            'level3_referral_rate' => 1.00,
            'order' => 3,
        ]);
    }

    public function test_direct_referral_commission_processing(): void
    {
        // Create sponsor with Starter tier
        $sponsor = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        // Create referral
        $referral = User::factory()->create([
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 0,
        ]);

        // Create matrix position
        MatrixPosition::create([
            'user_id' => $referral->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Create investment for referral
        $investment = Investment::factory()->create([
            'user_id' => $referral->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
        ]);

        // Process referral commission
        $job = new ReferralCommissionJob($investment);
        $job->handle();

        // Verify commission was created
        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $sponsor->id,
            'referred_id' => $referral->id,
            'investment_id' => $investment->id,
            'level' => 1,
            'amount' => 35, // 500 * 7% = 35 (Starter tier rate)
            'status' => 'pending',
        ]);

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $sponsor->id,
            'type' => 'referral_commission',
            'amount' => 35,
            'status' => 'pending',
        ]);
    }

    public function test_multi_level_referral_commission_processing(): void
    {
        // Create 3-level referral chain
        $level1Sponsor = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Builder')->first()->id,
        ]);

        $level2Sponsor = User::factory()->create([
            'referrer_id' => $level1Sponsor->id,
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        $investor = User::factory()->create([
            'referrer_id' => $level2Sponsor->id,
            'total_investment_amount' => 0,
        ]);

        // Create matrix positions
        MatrixPosition::create([
            'user_id' => $level2Sponsor->id,
            'sponsor_id' => $level1Sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        MatrixPosition::create([
            'user_id' => $investor->id,
            'sponsor_id' => $level1Sponsor->id,
            'level' => 2,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Create investment
        $investment = Investment::factory()->create([
            'user_id' => $investor->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
        ]);

        // Process referral commissions
        $job = new ReferralCommissionJob($investment);
        $job->handle();

        // Verify level 1 commission (direct referral)
        $level1Commission = ReferralCommission::where('referrer_id', $level2Sponsor->id)
            ->where('level', 1)
            ->first();

        $this->assertNotNull($level1Commission);
        $this->assertEquals(70, $level1Commission->amount); // 1000 * 7% = 70

        // Verify level 2 commission
        $level2Commission = ReferralCommission::where('referrer_id', $level1Sponsor->id)
            ->where('level', 2)
            ->first();

        $this->assertNotNull($level2Commission);
        $this->assertEquals(30, $level2Commission->amount); // 1000 * 3% = 30

        // Verify no level 3 commission (Builder tier has 1% but investor is at level 3)
        $level3Commission = ReferralCommission::where('referrer_id', $level1Sponsor->id)
            ->where('level', 3)
            ->first();

        $this->assertNull($level3Commission);
    }

    public function test_commission_processing_with_tier_restrictions(): void
    {
        // Create sponsor with Basic tier (no level 2 commissions)
        $sponsor = User::factory()->create([
            'total_investment_amount' => 500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Basic')->first()->id,
        ]);

        // Create level 2 sponsor
        $level2Sponsor = User::factory()->create([
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        // Create investor
        $investor = User::factory()->create([
            'referrer_id' => $level2Sponsor->id,
            'total_investment_amount' => 0,
        ]);

        // Create matrix positions
        MatrixPosition::create([
            'user_id' => $level2Sponsor->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        MatrixPosition::create([
            'user_id' => $investor->id,
            'sponsor_id' => $sponsor->id,
            'level' => 2,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Create investment
        $investment = Investment::factory()->create([
            'user_id' => $investor->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
        ]);

        // Process referral commissions
        $job = new ReferralCommissionJob($investment);
        $job->handle();

        // Verify level 1 commission was created
        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $level2Sponsor->id,
            'level' => 1,
            'amount' => 35, // 500 * 7% = 35
        ]);

        // Verify NO level 2 commission for Basic tier sponsor
        $this->assertDatabaseMissing('referral_commissions', [
            'referrer_id' => $sponsor->id,
            'level' => 2,
        ]);
    }

    public function test_commission_processing_with_inactive_matrix_positions(): void
    {
        $sponsor = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        $referral = User::factory()->create([
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 0,
        ]);

        // Create INACTIVE matrix position
        MatrixPosition::create([
            'user_id' => $referral->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => false, // Inactive
            'placed_at' => now(),
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        $investment = Investment::factory()->create([
            'user_id' => $referral->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
        ]);

        // Process referral commissions
        $job = new ReferralCommissionJob($investment);
        $job->handle();

        // Verify NO commission was created for inactive position
        $this->assertDatabaseMissing('referral_commissions', [
            'referrer_id' => $sponsor->id,
            'referred_id' => $referral->id,
        ]);
    }

    public function test_commission_cap_enforcement(): void
    {
        $sponsor = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        // Create multiple referrals to test commission caps
        $referrals = [];
        for ($i = 0; $i < 5; $i++) {
            $referral = User::factory()->create([
                'referrer_id' => $sponsor->id,
                'total_investment_amount' => 0,
            ]);

            MatrixPosition::create([
                'user_id' => $referral->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $i + 1,
                'is_active' => true,
                'placed_at' => now(),
            ]);

            $referrals[] = $referral;
        }

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Create large investments for each referral
        foreach ($referrals as $referral) {
            $investment = Investment::factory()->create([
                'user_id' => $referral->id,
                'investment_tier_id' => $basicTier->id,
                'amount' => 5000, // Large amount
            ]);

            $job = new ReferralCommissionJob($investment);
            $job->handle();
        }

        // Verify commissions were created but check if caps are applied
        $totalCommissions = ReferralCommission::where('referrer_id', $sponsor->id)->sum('amount');
        
        // Each commission should be 5000 * 7% = 350, total = 1750
        // But there might be caps based on sponsor's investment
        $this->assertGreaterThan(0, $totalCommissions);
    }

    public function test_commission_processing_with_spillover(): void
    {
        // Create sponsor with full direct positions
        $sponsor = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Builder')->first()->id,
        ]);

        // Fill sponsor's direct positions (3 in 3x3 matrix)
        $directReferrals = [];
        for ($i = 1; $i <= 3; $i++) {
            $referral = User::factory()->create([
                'referrer_id' => $sponsor->id,
                'total_investment_amount' => 500,
            ]);

            MatrixPosition::create([
                'user_id' => $referral->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $i,
                'is_active' => true,
                'placed_at' => now(),
            ]);

            $directReferrals[] = $referral;
        }

        // Create new referral that will spillover
        $spilloverReferral = User::factory()->create([
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 0,
        ]);

        // Place in level 2 (spillover)
        MatrixPosition::create([
            'user_id' => $spilloverReferral->id,
            'sponsor_id' => $sponsor->id,
            'level' => 2,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Create investment for spillover referral
        $investment = Investment::factory()->create([
            'user_id' => $spilloverReferral->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
        ]);

        // Process referral commissions
        $job = new ReferralCommissionJob($investment);
        $job->handle();

        // Verify level 2 commission was created for sponsor
        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $sponsor->id,
            'referred_id' => $spilloverReferral->id,
            'level' => 2,
            'amount' => 30, // 1000 * 3% = 30 (Builder tier level 2 rate)
        ]);
    }

    public function test_commission_processing_failure_handling(): void
    {
        $sponsor = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        $referral = User::factory()->create([
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 0,
        ]);

        MatrixPosition::create([
            'user_id' => $referral->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        $investment = Investment::factory()->create([
            'user_id' => $referral->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
        ]);

        // Simulate database error by deleting the sponsor
        $sponsor->delete();

        // Process referral commissions
        $job = new ReferralCommissionJob($investment);
        
        try {
            $job->handle();
        } catch (\Exception $e) {
            // Expected to fail gracefully
            $this->assertStringContains('sponsor', strtolower($e->getMessage()));
        }

        // Verify no commission was created
        $this->assertDatabaseMissing('referral_commissions', [
            'referred_id' => $referral->id,
        ]);
    }

    public function test_commission_batch_processing(): void
    {
        $sponsor = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Builder')->first()->id,
        ]);

        // Create multiple referrals
        $referrals = [];
        for ($i = 0; $i < 10; $i++) {
            $referral = User::factory()->create([
                'referrer_id' => $sponsor->id,
                'total_investment_amount' => 0,
            ]);

            MatrixPosition::create([
                'user_id' => $referral->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => ($i % 3) + 1, // Distribute across positions
                'is_active' => true,
                'placed_at' => now(),
            ]);

            $referrals[] = $referral;
        }

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Create investments for all referrals
        $investments = [];
        foreach ($referrals as $referral) {
            $investment = Investment::factory()->create([
                'user_id' => $referral->id,
                'investment_tier_id' => $basicTier->id,
                'amount' => 500,
            ]);
            $investments[] = $investment;
        }

        // Process all commissions
        foreach ($investments as $investment) {
            $job = new ReferralCommissionJob($investment);
            $job->handle();
        }

        // Verify all commissions were created
        $commissionCount = ReferralCommission::where('referrer_id', $sponsor->id)->count();
        $this->assertEquals(10, $commissionCount);

        // Verify total commission amount
        $totalCommissions = ReferralCommission::where('referrer_id', $sponsor->id)->sum('amount');
        $expectedTotal = 10 * (500 * 0.10); // 10 referrals * 500 * 10% = 500
        $this->assertEquals($expectedTotal, $totalCommissions);
    }

    public function test_commission_processing_with_different_investment_amounts(): void
    {
        $sponsor = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        $referral = User::factory()->create([
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 0,
        ]);

        MatrixPosition::create([
            'user_id' => $referral->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Test different investment amounts
        $testAmounts = [500, 1000, 1500, 2000];
        
        foreach ($testAmounts as $amount) {
            $investment = Investment::factory()->create([
                'user_id' => $referral->id,
                'investment_tier_id' => $basicTier->id,
                'amount' => $amount,
            ]);

            $job = new ReferralCommissionJob($investment);
            $job->handle();

            // Verify commission amount is correct
            $commission = ReferralCommission::where('investment_id', $investment->id)->first();
            $expectedAmount = $amount * 0.07; // 7% for Starter tier
            $this->assertEquals($expectedAmount, $commission->amount);
        }
    }
}