<?php

namespace Tests\Unit\Domain\Financial\Services;

use App\Domain\Financial\Services\WithdrawalPolicyService;
use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\WithdrawalRequest;
use App\Models\CommissionClawback;
use App\Models\ReferralCommission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class WithdrawalPolicyServiceTest extends TestCase
{
    use RefreshDatabase;

    private WithdrawalPolicyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WithdrawalPolicyService();
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
    }

    public function test_validates_withdrawal_within_lock_in_period(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        // Investment made 6 months ago (within 12-month lock-in)
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(6),
        ]);
        
        $validation = $this->service->validateWithdrawal($user, 500);
        
        $this->assertIsArray($validation);
        $this->assertArrayHasKey('allowed', $validation);
        $this->assertArrayHasKey('requires_approval', $validation);
        $this->assertArrayHasKey('penalty_amount', $validation);
        $this->assertArrayHasKey('net_amount', $validation);
        $this->assertArrayHasKey('reason', $validation);
        
        $this->assertFalse($validation['allowed']);
        $this->assertTrue($validation['requires_approval']);
        $this->assertGreaterThan(0, $validation['penalty_amount']);
    }

    public function test_validates_withdrawal_after_lock_in_period(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        // Investment made 15 months ago (after 12-month lock-in)
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(15),
        ]);
        
        $validation = $this->service->validateWithdrawal($user, 500);
        
        $this->assertTrue($validation['allowed']);
        $this->assertFalse($validation['requires_approval']);
        $this->assertEquals(0, $validation['penalty_amount']);
        $this->assertEquals(500, $validation['net_amount']);
    }

    public function test_calculates_penalties_for_0_to_1_month_withdrawal(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subWeeks(2), // 2 weeks ago
        ]);
        
        $penalties = $this->service->calculatePenalties($investment);
        
        $this->assertIsArray($penalties);
        $this->assertArrayHasKey('profit_penalty_percentage', $penalties);
        $this->assertArrayHasKey('capital_penalty_percentage', $penalties);
        $this->assertArrayHasKey('total_penalty_amount', $penalties);
        
        // 0-1 month: 100% profit + 12% capital penalty
        $this->assertEquals(100, $penalties['profit_penalty_percentage']);
        $this->assertEquals(12, $penalties['capital_penalty_percentage']);
        $this->assertEquals(120, $penalties['total_penalty_amount']); // 12% of 1000
    }

    public function test_calculates_penalties_for_1_to_3_month_withdrawal(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(2), // 2 months ago
        ]);
        
        $penalties = $this->service->calculatePenalties($investment);
        
        // 1-3 months: 100% profit + 12% capital penalty
        $this->assertEquals(100, $penalties['profit_penalty_percentage']);
        $this->assertEquals(12, $penalties['capital_penalty_percentage']);
        $this->assertEquals(120, $penalties['total_penalty_amount']);
    }

    public function test_calculates_penalties_for_3_to_6_month_withdrawal(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(4), // 4 months ago
        ]);
        
        $penalties = $this->service->calculatePenalties($investment);
        
        // 3-6 months: 50% profit + 6% capital penalty
        $this->assertEquals(50, $penalties['profit_penalty_percentage']);
        $this->assertEquals(6, $penalties['capital_penalty_percentage']);
        $this->assertEquals(60, $penalties['total_penalty_amount']); // 6% of 1000
    }

    public function test_calculates_penalties_for_6_to_12_month_withdrawal(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(8), // 8 months ago
        ]);
        
        $penalties = $this->service->calculatePenalties($investment);
        
        // 6-12 months: 30% profit + 3% capital penalty
        $this->assertEquals(30, $penalties['profit_penalty_percentage']);
        $this->assertEquals(3, $penalties['capital_penalty_percentage']);
        $this->assertEquals(30, $penalties['total_penalty_amount']); // 3% of 1000
    }

    public function test_calculates_no_penalties_after_12_months(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15), // 15 months ago
        ]);
        
        $penalties = $this->service->calculatePenalties($investment);
        
        // After 12 months: no penalties
        $this->assertEquals(0, $penalties['profit_penalty_percentage']);
        $this->assertEquals(0, $penalties['capital_penalty_percentage']);
        $this->assertEquals(0, $penalties['total_penalty_amount']);
    }

    public function test_processes_clawback_for_early_withdrawal(): void
    {
        $sponsor = User::factory()->create();
        $referral = User::factory()->create(['referrer_id' => $sponsor->id]);
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $referral->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(2), // 2 months ago
        ]);
        
        // Create referral commission that was paid
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $sponsor->id,
            'referred_user_id' => $referral->id,
            'investment_id' => $investment->id,
            'amount' => 50, // 5% commission
            'level' => 1,
            'status' => 'paid',
        ]);
        
        $this->service->processClawback($referral, 1000);
        
        // Check clawback was created
        $this->assertDatabaseHas('commission_clawbacks', [
            'referral_commission_id' => $commission->id,
            'user_id' => $sponsor->id,
            'clawback_amount' => 12.5, // 25% of 50 (1-3 months = 25% clawback)
        ]);
    }

    public function test_calculates_correct_clawback_percentage_for_0_to_1_month(): void
    {
        $sponsor = User::factory()->create();
        $referral = User::factory()->create(['referrer_id' => $sponsor->id]);
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $referral->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subWeeks(2), // 2 weeks ago
        ]);
        
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $sponsor->id,
            'referred_user_id' => $referral->id,
            'investment_id' => $investment->id,
            'amount' => 50,
            'level' => 1,
            'status' => 'paid',
        ]);
        
        $this->service->processClawback($referral, 1000);
        
        // 0-1 month = 50% clawback
        $this->assertDatabaseHas('commission_clawbacks', [
            'referral_commission_id' => $commission->id,
            'clawback_amount' => 25, // 50% of 50
        ]);
    }

    public function test_no_clawback_after_3_months(): void
    {
        $sponsor = User::factory()->create();
        $referral = User::factory()->create(['referrer_id' => $sponsor->id]);
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $referral->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(4), // 4 months ago
        ]);
        
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $sponsor->id,
            'referred_user_id' => $referral->id,
            'investment_id' => $investment->id,
            'amount' => 50,
            'level' => 1,
            'status' => 'paid',
        ]);
        
        $this->service->processClawback($referral, 1000);
        
        // No clawback after 3 months
        $this->assertDatabaseMissing('commission_clawbacks', [
            'referral_commission_id' => $commission->id,
        ]);
    }

    public function test_can_withdraw_returns_true_after_lock_in(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(15),
        ]);
        
        $canWithdraw = $this->service->canWithdraw($user);
        
        $this->assertTrue($canWithdraw);
    }

    public function test_can_withdraw_returns_false_within_lock_in(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(6),
        ]);
        
        $canWithdraw = $this->service->canWithdraw($user);
        
        $this->assertFalse($canWithdraw);
    }

    public function test_validates_partial_withdrawal_amount(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);
        
        // Try to withdraw more than 50% of profits (should fail)
        $validation = $this->service->validateWithdrawal($user, 600);
        
        $this->assertFalse($validation['allowed']);
        $this->assertStringContains('exceeds maximum', $validation['reason']);
    }

    public function test_allows_valid_partial_withdrawal(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);
        
        // Withdraw 30% (within 50% limit)
        $validation = $this->service->validateWithdrawal($user, 300);
        
        $this->assertTrue($validation['allowed']);
        $this->assertEquals(0, $validation['penalty_amount']);
    }

    public function test_handles_user_with_no_investments(): void
    {
        $user = User::factory()->create();
        
        $validation = $this->service->validateWithdrawal($user, 100);
        
        $this->assertFalse($validation['allowed']);
        $this->assertStringContains('no active investments', $validation['reason']);
    }

    public function test_calculates_lock_in_period_remaining(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(8), // 8 months ago
        ]);
        
        $remaining = $this->service->getLockInPeriodRemaining($user);
        
        $this->assertEquals(4, $remaining); // 12 - 8 = 4 months remaining
    }

    public function test_returns_zero_lock_in_remaining_after_period(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(15),
        ]);
        
        $remaining = $this->service->getLockInPeriodRemaining($user);
        
        $this->assertEquals(0, $remaining);
    }

    public function test_gets_withdrawal_eligibility_summary(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(8),
        ]);
        
        $summary = $this->service->getWithdrawalEligibilitySummary($user);
        
        $this->assertIsArray($summary);
        $this->assertArrayHasKey('can_withdraw', $summary);
        $this->assertArrayHasKey('lock_in_remaining_months', $summary);
        $this->assertArrayHasKey('total_investment', $summary);
        $this->assertArrayHasKey('penalty_preview', $summary);
        $this->assertArrayHasKey('max_partial_withdrawal', $summary);
        
        $this->assertFalse($summary['can_withdraw']);
        $this->assertEquals(4, $summary['lock_in_remaining_months']);
        $this->assertEquals(1000, $summary['total_investment']);
    }
}