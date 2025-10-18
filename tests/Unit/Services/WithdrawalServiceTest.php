<?php

namespace Tests\Unit\Services;

use App\Services\WithdrawalService;
use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\WithdrawalRequest;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class WithdrawalServiceTest extends TestCase
{
    use RefreshDatabase;

    private WithdrawalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WithdrawalService::class);
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

    public function test_processes_withdrawal_request_successfully(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15), // After lock-in period
        ]);

        $result = $this->service->processWithdrawalRequest($user, 500, 'full', 'Need funds for emergency');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('withdrawal_request_id', $result);
        
        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $user->id,
            'amount' => 500,
            'type' => 'full',
            'status' => 'pending',
        ]);
    }

    public function test_calculates_withdrawal_penalties_correctly(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(2), // Within lock-in period
        ]);

        $penalties = $this->service->calculateWithdrawalPenalties($user, 1000);

        $this->assertIsArray($penalties);
        $this->assertArrayHasKey('penalty_amount', $penalties);
        $this->assertArrayHasKey('net_amount', $penalties);
        $this->assertArrayHasKey('penalty_percentage', $penalties);
        
        $this->assertGreaterThan(0, $penalties['penalty_amount']);
        $this->assertLessThan(1000, $penalties['net_amount']);
    }

    public function test_validates_withdrawal_eligibility(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15), // After lock-in period
        ]);

        $eligibility = $this->service->checkWithdrawalEligibility($user, 500);

        $this->assertIsArray($eligibility);
        $this->assertArrayHasKey('eligible', $eligibility);
        $this->assertArrayHasKey('reason', $eligibility);
        $this->assertArrayHasKey('penalty_amount', $eligibility);
        
        $this->assertTrue($eligibility['eligible']);
        $this->assertEquals(0, $eligibility['penalty_amount']);
    }

    public function test_rejects_withdrawal_within_lock_in_period(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(6), // Within lock-in period
        ]);

        $eligibility = $this->service->checkWithdrawalEligibility($user, 1000);

        $this->assertFalse($eligibility['eligible']);
        $this->assertStringContains('lock-in period', $eligibility['reason']);
        $this->assertGreaterThan(0, $eligibility['penalty_amount']);
    }

    public function test_processes_emergency_withdrawal_with_approval(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(6),
        ]);

        $result = $this->service->processWithdrawalRequest($user, 1000, 'emergency', 'Medical emergency');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['requires_approval']);
        
        $withdrawalRequest = WithdrawalRequest::find($result['withdrawal_request_id']);
        $this->assertEquals('emergency', $withdrawalRequest->type);
        $this->assertEquals('pending', $withdrawalRequest->status);
    }

    public function test_calculates_partial_withdrawal_limits(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 2000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 2000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        $limits = $this->service->calculatePartialWithdrawalLimits($user);

        $this->assertIsArray($limits);
        $this->assertArrayHasKey('max_withdrawal_amount', $limits);
        $this->assertArrayHasKey('available_profit', $limits);
        $this->assertArrayHasKey('withdrawal_percentage_limit', $limits);
        
        // Should allow up to 50% of profits
        $this->assertGreaterThan(0, $limits['max_withdrawal_amount']);
        $this->assertEquals(50, $limits['withdrawal_percentage_limit']);
    }

    public function test_handles_insufficient_balance(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 500,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        $result = $this->service->processWithdrawalRequest($user, 1000, 'full', 'Need more than available');

        $this->assertFalse($result['success']);
        $this->assertStringContains('insufficient', strtolower($result['message']));
    }

    public function test_approves_withdrawal_request(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'status' => 'pending',
            'type' => 'emergency',
        ]);

        $result = $this->service->approveWithdrawalRequest($withdrawalRequest, 'Approved by admin');

        $this->assertTrue($result['success']);
        
        $withdrawalRequest->refresh();
        $this->assertEquals('approved', $withdrawalRequest->status);
        $this->assertEquals('Approved by admin', $withdrawalRequest->admin_notes);
        $this->assertNotNull($withdrawalRequest->approved_at);
    }

    public function test_rejects_withdrawal_request(): void
    {
        $user = User::factory()->create();

        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'status' => 'pending',
            'type' => 'emergency',
        ]);

        $result = $this->service->rejectWithdrawalRequest($withdrawalRequest, 'Insufficient documentation');

        $this->assertTrue($result['success']);
        
        $withdrawalRequest->refresh();
        $this->assertEquals('rejected', $withdrawalRequest->status);
        $this->assertEquals('Insufficient documentation', $withdrawalRequest->admin_notes);
        $this->assertNotNull($withdrawalRequest->rejected_at);
    }

    public function test_processes_approved_withdrawal(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'net_amount' => 500,
            'status' => 'approved',
            'type' => 'partial',
        ]);

        $result = $this->service->processApprovedWithdrawal($withdrawalRequest);

        $this->assertTrue($result['success']);
        
        $withdrawalRequest->refresh();
        $this->assertEquals('processed', $withdrawalRequest->status);
        $this->assertNotNull($withdrawalRequest->processed_at);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => 500,
            'status' => 'completed',
        ]);
    }

    public function test_gets_withdrawal_history(): void
    {
        $user = User::factory()->create();

        WithdrawalRequest::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        WithdrawalRequest::factory()->count(2)->create(); // Other users

        $history = $this->service->getWithdrawalHistory($user);

        $this->assertCount(3, $history);
        $history->each(function ($withdrawal) use ($user) {
            $this->assertEquals($user->id, $withdrawal->user_id);
        });
    }

    public function test_gets_withdrawal_statistics(): void
    {
        $user = User::factory()->create();

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'status' => 'processed',
        ]);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 300,
            'status' => 'pending',
        ]);

        $stats = $this->service->getWithdrawalStatistics($user);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_withdrawals', $stats);
        $this->assertArrayHasKey('total_amount_withdrawn', $stats);
        $this->assertArrayHasKey('pending_withdrawals', $stats);
        $this->assertArrayHasKey('pending_amount', $stats);
        
        $this->assertEquals(2, $stats['total_withdrawals']);
        $this->assertEquals(800, $stats['total_amount_withdrawn'] + $stats['pending_amount']);
        $this->assertEquals(1, $stats['pending_withdrawals']);
    }

    public function test_calculates_available_withdrawal_amount(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 2000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 2000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        $available = $this->service->calculateAvailableWithdrawalAmount($user);

        $this->assertIsArray($available);
        $this->assertArrayHasKey('total_available', $available);
        $this->assertArrayHasKey('investment_principal', $available);
        $this->assertArrayHasKey('accumulated_profits', $available);
        $this->assertArrayHasKey('partial_withdrawal_limit', $available);
        
        $this->assertEquals(2000, $available['investment_principal']);
        $this->assertGreaterThanOrEqual(0, $available['accumulated_profits']);
    }

    public function test_handles_concurrent_withdrawal_requests(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        // Create pending withdrawal
        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 600,
            'status' => 'pending',
        ]);

        // Try to create another withdrawal
        $result = $this->service->processWithdrawalRequest($user, 500, 'partial', 'Additional withdrawal');

        $this->assertFalse($result['success']);
        $this->assertStringContains('pending', strtolower($result['message']));
    }

    public function test_validates_withdrawal_amount_limits(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        // Try to withdraw more than 50% of profits for partial withdrawal
        $result = $this->service->processWithdrawalRequest($user, 800, 'partial', 'Large partial withdrawal');

        $this->assertFalse($result['success']);
        $this->assertStringContains('exceeds', strtolower($result['message']));
    }
}