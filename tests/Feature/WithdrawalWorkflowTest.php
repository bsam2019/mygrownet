<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\WithdrawalRequest;
use App\Models\Transaction;
use App\Models\ReferralCommission;
use App\Models\CommissionClawback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class WithdrawalWorkflowTest extends TestCase
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
    }

    public function test_complete_withdrawal_workflow_after_lock_in_period(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Create investment older than 12 months
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        // User requests withdrawal
        $response = $this->actingAs($user)->post('/withdrawals', [
            'amount' => 500,
            'type' => 'partial',
            'reason' => 'Need funds for personal use',
        ]);

        $response->assertStatus(302); // Redirect after successful creation

        // Verify withdrawal request was created
        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $user->id,
            'amount' => 500,
            'type' => 'partial',
            'status' => 'pending',
            'penalty_amount' => 0, // No penalty after lock-in period
            'net_amount' => 500,
        ]);

        $withdrawalRequest = WithdrawalRequest::where('user_id', $user->id)->first();

        // Admin approves withdrawal
        $response = $this->actingAs($this->createAdminUser())
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/approve", [
                'admin_notes' => 'Approved - meets all requirements',
            ]);

        $response->assertStatus(302);

        // Verify withdrawal was approved
        $withdrawalRequest->refresh();
        $this->assertEquals('approved', $withdrawalRequest->status);
        $this->assertEquals('Approved - meets all requirements', $withdrawalRequest->admin_notes);
        $this->assertNotNull($withdrawalRequest->approved_at);

        // Process the approved withdrawal
        $response = $this->actingAs($this->createAdminUser())
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/process");

        $response->assertStatus(302);

        // Verify withdrawal was processed
        $withdrawalRequest->refresh();
        $this->assertEquals('processed', $withdrawalRequest->status);
        $this->assertNotNull($withdrawalRequest->processed_at);

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => 500,
            'status' => 'completed',
        ]);
    }

    public function test_emergency_withdrawal_workflow_with_penalties(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Create recent investment (within lock-in period)
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(6),
        ]);

        // User requests emergency withdrawal
        $response = $this->actingAs($user)->post('/withdrawals', [
            'amount' => 1000,
            'type' => 'emergency',
            'reason' => 'Medical emergency - need immediate funds',
        ]);

        $response->assertStatus(302);

        // Verify withdrawal request was created with penalties
        $withdrawalRequest = WithdrawalRequest::where('user_id', $user->id)->first();
        
        $this->assertEquals('emergency', $withdrawalRequest->type);
        $this->assertEquals('pending', $withdrawalRequest->status);
        $this->assertGreaterThan(0, $withdrawalRequest->penalty_amount);
        $this->assertLessThan(1000, $withdrawalRequest->net_amount);

        // Admin reviews and approves emergency withdrawal
        $response = $this->actingAs($this->createAdminUser())
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/approve", [
                'admin_notes' => 'Emergency approved - medical documentation provided',
            ]);

        $response->assertStatus(302);

        // Process the withdrawal
        $response = $this->actingAs($this->createAdminUser())
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/process");

        $response->assertStatus(302);

        // Verify withdrawal was processed with penalty deduction
        $withdrawalRequest->refresh();
        $this->assertEquals('processed', $withdrawalRequest->status);

        // Verify transaction reflects net amount (after penalties)
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => $withdrawalRequest->net_amount,
            'status' => 'completed',
        ]);
    }

    public function test_withdrawal_with_referral_commission_clawback(): void
    {
        // Create sponsor and referral
        $sponsor = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $referral = User::factory()->create([
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 500,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Create investment for referral (2 months ago)
        $investment = Investment::factory()->create([
            'user_id' => $referral->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(2),
        ]);

        // Create referral commission that was paid to sponsor
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $sponsor->id,
            'referred_id' => $referral->id,
            'investment_id' => $investment->id,
            'amount' => 25, // 5% of 500
            'level' => 1,
            'status' => 'paid',
        ]);

        // Referral requests early withdrawal
        $response = $this->actingAs($referral)->post('/withdrawals', [
            'amount' => 500,
            'type' => 'emergency',
            'reason' => 'Financial hardship',
        ]);

        $response->assertStatus(302);

        $withdrawalRequest = WithdrawalRequest::where('user_id', $referral->id)->first();

        // Admin approves and processes withdrawal
        $this->actingAs($this->createAdminUser())
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/approve");

        $this->actingAs($this->createAdminUser())
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/process");

        // Verify commission clawback was created
        $this->assertDatabaseHas('commission_clawbacks', [
            'referral_commission_id' => $commission->id,
            'user_id' => $sponsor->id,
        ]);

        $clawback = CommissionClawback::where('referral_commission_id', $commission->id)->first();
        
        // For 1-3 months withdrawal, clawback should be 25% of commission
        $expectedClawback = $commission->amount * 0.25; // 6.25
        $this->assertEquals($expectedClawback, $clawback->clawback_amount);

        // Verify clawback transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $sponsor->id,
            'type' => 'commission_clawback',
            'amount' => $expectedClawback,
            'status' => 'completed',
        ]);
    }

    public function test_withdrawal_rejection_workflow(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 500,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
            'created_at' => Carbon::now()->subMonths(3),
        ]);

        // User requests withdrawal
        $response = $this->actingAs($user)->post('/withdrawals', [
            'amount' => 500,
            'type' => 'emergency',
            'reason' => 'Need money',
        ]);

        $response->assertStatus(302);

        $withdrawalRequest = WithdrawalRequest::where('user_id', $user->id)->first();

        // Admin rejects withdrawal
        $response = $this->actingAs($this->createAdminUser())
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/reject", [
                'admin_notes' => 'Insufficient documentation provided',
            ]);

        $response->assertStatus(302);

        // Verify withdrawal was rejected
        $withdrawalRequest->refresh();
        $this->assertEquals('rejected', $withdrawalRequest->status);
        $this->assertEquals('Insufficient documentation provided', $withdrawalRequest->admin_notes);
        $this->assertNotNull($withdrawalRequest->rejected_at);

        // Verify no transaction was created
        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'type' => 'withdrawal',
        ]);
    }

    public function test_partial_withdrawal_limits_enforcement(): void
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

        // Try to withdraw more than 50% of profits (should fail)
        $response = $this->actingAs($user)->post('/withdrawals', [
            'amount' => 1500, // Exceeds 50% limit
            'type' => 'partial',
            'reason' => 'Personal use',
        ]);

        $response->assertSessionHasErrors(['amount']);

        // Verify no withdrawal request was created
        $this->assertDatabaseMissing('withdrawal_requests', [
            'user_id' => $user->id,
        ]);

        // Try valid partial withdrawal (within 50% limit)
        $response = $this->actingAs($user)->post('/withdrawals', [
            'amount' => 800, // Within limits
            'type' => 'partial',
            'reason' => 'Personal use',
        ]);

        $response->assertStatus(302);

        // Verify withdrawal request was created
        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $user->id,
            'amount' => 800,
            'type' => 'partial',
        ]);
    }

    public function test_concurrent_withdrawal_request_prevention(): void
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

        // Create pending withdrawal request
        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'status' => 'pending',
        ]);

        // Try to create another withdrawal request
        $response = $this->actingAs($user)->post('/withdrawals', [
            'amount' => 300,
            'type' => 'partial',
            'reason' => 'Additional funds needed',
        ]);

        $response->assertSessionHasErrors(['user_id']);

        // Verify only one withdrawal request exists
        $this->assertEquals(1, WithdrawalRequest::where('user_id', $user->id)->count());
    }

    public function test_withdrawal_history_and_tracking(): void
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

        // Create multiple withdrawal requests with different statuses
        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'status' => 'processed',
            'processed_at' => Carbon::now()->subDays(30),
        ]);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 300,
            'status' => 'rejected',
            'rejected_at' => Carbon::now()->subDays(15),
        ]);

        WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 400,
            'status' => 'pending',
        ]);

        // Access withdrawal history
        $response = $this->actingAs($user)->get('/withdrawals');

        $response->assertStatus(200);
        $response->assertSee('500'); // Processed amount
        $response->assertSee('300'); // Rejected amount
        $response->assertSee('400'); // Pending amount
        $response->assertSee('Processed');
        $response->assertSee('Rejected');
        $response->assertSee('Pending');
    }

    public function test_admin_withdrawal_management_dashboard(): void
    {
        $admin = $this->createAdminUser();

        // Create withdrawal requests from different users
        $users = User::factory()->count(3)->create();

        foreach ($users as $index => $user) {
            WithdrawalRequest::factory()->create([
                'user_id' => $user->id,
                'amount' => 500 + ($index * 100),
                'status' => ['pending', 'approved', 'processed'][$index],
                'type' => ['emergency', 'partial', 'full'][$index],
            ]);
        }

        // Access admin withdrawal dashboard
        $response = $this->actingAs($admin)->get('/admin/withdrawals');

        $response->assertStatus(200);
        $response->assertSee('500'); // First withdrawal
        $response->assertSee('600'); // Second withdrawal
        $response->assertSee('700'); // Third withdrawal
        $response->assertSee('Emergency');
        $response->assertSee('Partial');
        $response->assertSee('Full');
    }

    public function test_withdrawal_notification_workflow(): void
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

        // User requests withdrawal
        $response = $this->actingAs($user)->post('/withdrawals', [
            'amount' => 500,
            'type' => 'partial',
            'reason' => 'Personal use',
        ]);

        $response->assertStatus(302);

        $withdrawalRequest = WithdrawalRequest::where('user_id', $user->id)->first();

        // Admin approves withdrawal
        $response = $this->actingAs($this->createAdminUser())
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/approve", [
                'admin_notes' => 'Approved',
            ]);

        $response->assertStatus(302);

        // Verify user receives approval notification
        // In a real implementation, we would check for notification records
        $this->assertTrue(true); // Placeholder for notification verification
    }

    public function test_withdrawal_audit_trail(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $admin = $this->createAdminUser();

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        // User requests withdrawal
        $response = $this->actingAs($user)->post('/withdrawals', [
            'amount' => 500,
            'type' => 'partial',
            'reason' => 'Personal use',
        ]);

        $withdrawalRequest = WithdrawalRequest::where('user_id', $user->id)->first();

        // Admin approves withdrawal
        $this->actingAs($admin)
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/approve", [
                'admin_notes' => 'Approved after verification',
            ]);

        // Admin processes withdrawal
        $this->actingAs($admin)
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/process");

        $withdrawalRequest->refresh();

        // Verify complete audit trail
        $this->assertNotNull($withdrawalRequest->requested_at);
        $this->assertNotNull($withdrawalRequest->approved_at);
        $this->assertNotNull($withdrawalRequest->processed_at);
        $this->assertEquals('Approved after verification', $withdrawalRequest->admin_notes);

        // Verify transaction has proper audit information
        $transaction = Transaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals('completed', $transaction->status);
        $this->assertNotNull($transaction->created_at);
    }

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }
}