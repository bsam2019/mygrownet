<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;
use App\Models\MatrixPosition;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class InvestmentWorkflowTest extends TestCase
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

    public function test_complete_investment_workflow_for_new_user(): void
    {
        // Create a new user
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'total_investment_amount' => 0,
        ]);

        // User makes their first investment
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $response = $this->actingAs($user)->post('/investments', [
            'amount' => 500,
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302); // Redirect after successful creation
        
        // Verify investment was created
        $this->assertDatabaseHas('investments', [
            'user_id' => $user->id,
            'amount' => 500,
            'investment_tier_id' => $basicTier->id,
            'status' => 'active',
        ]);

        // Verify user's total investment amount was updated
        $user->refresh();
        $this->assertEquals(500, $user->total_investment_amount);

        // Verify user was assigned to the correct tier
        $this->assertEquals($basicTier->id, $user->current_investment_tier_id);

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'investment',
            'amount' => 500,
            'status' => 'completed',
        ]);
    }

    public function test_investment_with_referral_commission_processing(): void
    {
        // Create sponsor and referral users
        $sponsor = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        $referral = User::factory()->create([
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 0,
        ]);

        // Create matrix position for referral
        MatrixPosition::create([
            'user_id' => $referral->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Referral makes investment
        $response = $this->actingAs($referral)->post('/investments', [
            'amount' => 500,
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        // Verify investment was created
        $investment = Investment::where('user_id', $referral->id)->first();
        $this->assertNotNull($investment);

        // Verify referral commission was created for sponsor
        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $sponsor->id,
            'referred_id' => $referral->id,
            'investment_id' => $investment->id,
            'level' => 1,
            'status' => 'pending',
        ]);

        // Calculate expected commission (7% for Starter tier)
        $expectedCommission = 500 * 0.07; // 35
        $commission = ReferralCommission::where('referrer_id', $sponsor->id)->first();
        $this->assertEquals($expectedCommission, $commission->amount);
    }

    public function test_tier_upgrade_workflow(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 500,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();

        // Create initial investment
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
        ]);

        $user->update(['current_investment_tier_id' => $basicTier->id]);

        // User makes additional investment that qualifies for tier upgrade
        $response = $this->actingAs($user)->post('/investments', [
            'amount' => 600, // Total will be 1100, qualifying for Starter tier
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        // Verify user was upgraded to Starter tier
        $user->refresh();
        $this->assertEquals(1100, $user->total_investment_amount);
        $this->assertEquals($starterTier->id, $user->current_investment_tier_id);

        // Verify tier upgrade record was created
        $this->assertDatabaseHas('tier_upgrades', [
            'user_id' => $user->id,
            'from_tier_id' => $basicTier->id,
            'to_tier_id' => $starterTier->id,
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

        // Investor makes investment
        $response = $this->actingAs($investor)->post('/investments', [
            'amount' => 500,
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        $investment = Investment::where('user_id', $investor->id)->first();

        // Verify commissions were created for both levels
        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $level2Sponsor->id,
            'referred_id' => $investor->id,
            'level' => 1,
        ]);

        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $level1Sponsor->id,
            'referred_id' => $investor->id,
            'level' => 2,
        ]);

        // Verify commission amounts
        $level1Commission = ReferralCommission::where('referrer_id', $level2Sponsor->id)->first();
        $level2Commission = ReferralCommission::where('referrer_id', $level1Sponsor->id)->first();

        // Level 1: 7% (Starter tier rate)
        $this->assertEquals(35, $level1Commission->amount);
        
        // Level 2: 3% (Builder tier level 2 rate)
        $this->assertEquals(15, $level2Commission->amount);
    }

    public function test_investment_validation_and_error_handling(): void
    {
        $user = User::factory()->create();

        // Test insufficient amount
        $response = $this->actingAs($user)->post('/investments', [
            'amount' => 100, // Below minimum
            'investment_tier_id' => InvestmentTier::where('name', 'Basic')->first()->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHasErrors(['amount']);

        // Test invalid tier
        $response = $this->actingAs($user)->post('/investments', [
            'amount' => 500,
            'investment_tier_id' => 999, // Non-existent tier
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHasErrors(['investment_tier_id']);

        // Test missing required fields
        $response = $this->actingAs($user)->post('/investments', []);

        $response->assertSessionHasErrors(['amount', 'investment_tier_id', 'payment_method']);
    }

    public function test_investment_dashboard_integration(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1500,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();

        // Create multiple investments
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

        $user->update(['current_investment_tier_id' => $starterTier->id]);

        // Access dashboard
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('investments');
        $response->assertViewHas('currentTier');
        $response->assertViewHas('totalInvestment');

        // Verify dashboard data
        $investments = $response->viewData('investments');
        $this->assertCount(2, $investments);

        $currentTier = $response->viewData('currentTier');
        $this->assertEquals('Starter', $currentTier->name);

        $totalInvestment = $response->viewData('totalInvestment');
        $this->assertEquals(1500, $totalInvestment);
    }

    public function test_investment_history_and_tracking(): void
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Create investment
        $response = $this->actingAs($user)->post('/investments', [
            'amount' => 500,
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        // Access investment history
        $response = $this->actingAs($user)->get('/investments');

        $response->assertStatus(200);
        $response->assertSee('500'); // Investment amount
        $response->assertSee('Basic'); // Tier name
        $response->assertSee('Active'); // Status
    }

    public function test_concurrent_investment_handling(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 0,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Simulate concurrent requests
        $response1 = $this->actingAs($user)->post('/investments', [
            'amount' => 500,
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response2 = $this->actingAs($user)->post('/investments', [
            'amount' => 600,
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        // Both should succeed
        $response1->assertStatus(302);
        $response2->assertStatus(302);

        // Verify both investments were created
        $this->assertEquals(2, Investment::where('user_id', $user->id)->count());

        // Verify total investment amount is correct
        $user->refresh();
        $this->assertEquals(1100, $user->total_investment_amount);
    }

    public function test_investment_with_reinvestment_bonus(): void
    {
        $user = User::factory()->create([
            'total_investment_amount' => 1000,
        ]);

        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        $user->update(['current_investment_tier_id' => $starterTier->id]);

        // Create existing investment (older than 1 year for reinvestment eligibility)
        Investment::factory()->create([
            'user_id' => $user->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 1000,
            'created_at' => Carbon::now()->subMonths(15),
        ]);

        // Make reinvestment
        $response = $this->actingAs($user)->post('/investments', [
            'amount' => 500,
            'investment_tier_id' => $starterTier->id,
            'payment_method' => 'bank_transfer',
            'is_reinvestment' => true,
        ]);

        $response->assertStatus(302);

        // Verify reinvestment bonus was applied (3% for Starter tier)
        $investment = Investment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $expectedBonus = 500 * 0.03; // 15
        $this->assertEquals($expectedBonus, $investment->reinvestment_bonus);
    }
}