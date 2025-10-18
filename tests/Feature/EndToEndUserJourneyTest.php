<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\MatrixPosition;
use App\Models\ReferralCommission;
use App\Models\WithdrawalRequest;
use App\Models\ProfitDistribution;
use App\Models\ProfitShare;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class EndToEndUserJourneyTest extends TestCase
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

    public function test_complete_user_journey_from_registration_to_profit_distribution(): void
    {
        // === PHASE 1: USER REGISTRATION AND INITIAL INVESTMENT ===
        
        // Create founding member (sponsor)
        $sponsor = User::factory()->create([
            'name' => 'John Sponsor',
            'email' => 'sponsor@example.com',
            'total_investment_amount' => 0,
        ]);

        // Sponsor makes initial investment
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $response = $this->actingAs($sponsor)->post('/investments', [
            'amount' => 500,
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        // Verify sponsor's investment
        $this->assertDatabaseHas('investments', [
            'user_id' => $sponsor->id,
            'amount' => 500,
            'status' => 'active',
        ]);

        $sponsor->refresh();
        $this->assertEquals(500, $sponsor->total_investment_amount);
        $this->assertEquals($basicTier->id, $sponsor->current_investment_tier_id);

        // === PHASE 2: REFERRAL JOINS AND CREATES MATRIX STRUCTURE ===
        
        // Create referral user
        $referral = User::factory()->create([
            'name' => 'Jane Referral',
            'email' => 'referral@example.com',
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 0,
        ]);

        // Referral makes investment
        $response = $this->actingAs($referral)->post('/investments', [
            'amount' => 500,
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        // Verify matrix position was created
        $this->assertDatabaseHas('matrix_positions', [
            'user_id' => $referral->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
        ]);

        // Verify referral commission was created
        $referralInvestment = Investment::where('user_id', $referral->id)->first();
        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $sponsor->id,
            'referred_id' => $referral->id,
            'investment_id' => $referralInvestment->id,
            'amount' => 25, // 500 * 5% = 25
            'level' => 1,
            'status' => 'pending',
        ]);

        // === PHASE 3: TIER UPGRADE THROUGH ADDITIONAL INVESTMENT ===
        
        // Sponsor makes additional investment to upgrade tier
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        $response = $this->actingAs($sponsor)->post('/investments', [
            'amount' => 600, // Total becomes 1100, qualifies for Starter
            'investment_tier_id' => $starterTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        // Verify tier upgrade
        $sponsor->refresh();
        $this->assertEquals(1100, $sponsor->total_investment_amount);
        $this->assertEquals($starterTier->id, $sponsor->current_investment_tier_id);

        // Verify tier upgrade record
        $this->assertDatabaseHas('tier_upgrades', [
            'user_id' => $sponsor->id,
            'from_tier_id' => $basicTier->id,
            'to_tier_id' => $starterTier->id,
        ]);

        // === PHASE 4: MULTI-LEVEL REFERRAL STRUCTURE ===
        
        // Create second-level referral
        $secondLevelReferral = User::factory()->create([
            'name' => 'Bob SecondLevel',
            'email' => 'second@example.com',
            'referrer_id' => $referral->id,
            'total_investment_amount' => 0,
        ]);

        // Create matrix position for second level
        MatrixPosition::create([
            'user_id' => $secondLevelReferral->id,
            'sponsor_id' => $sponsor->id,
            'level' => 2,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        // Second level referral makes investment
        $response = $this->actingAs($secondLevelReferral)->post('/investments', [
            'amount' => 1000,
            'investment_tier_id' => $starterTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        $secondLevelInvestment = Investment::where('user_id', $secondLevelReferral->id)->first();

        // Verify multi-level commissions
        // Level 1 commission (referral gets 7% as Starter tier)
        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $referral->id,
            'referred_id' => $secondLevelReferral->id,
            'level' => 1,
            'amount' => 70, // 1000 * 7% = 70
        ]);

        // Level 2 commission (sponsor gets 2% as Starter tier)
        $this->assertDatabaseHas('referral_commissions', [
            'referrer_id' => $sponsor->id,
            'referred_id' => $secondLevelReferral->id,
            'level' => 2,
            'amount' => 20, // 1000 * 2% = 20
        ]);

        // === PHASE 5: QUARTERLY BONUS DISTRIBUTION ===
        
        // Simulate quarterly bonus distribution
        $bonusPool = 50000;
        
        Artisan::call('profit:distribute-quarterly', [
            'bonus_pool' => $bonusPool,
        ]);

        // Verify quarterly distribution was created
        $this->assertDatabaseHas('profit_distributions', [
            'period_type' => 'quarterly',
            'total_profit' => $bonusPool,
            'status' => 'completed',
        ]);

        // Verify bonus shares were distributed proportionally
        $totalInvestmentPool = 500 + 500 + 1100 + 1000; // All investments
        
        $sponsorExpectedBonus = ($bonusPool * 1100) / $totalInvestmentPool;
        $referralExpectedBonus = ($bonusPool * 500) / $totalInvestmentPool;
        $secondLevelExpectedBonus = ($bonusPool * 1000) / $totalInvestmentPool;

        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $sponsor->id,
            'type' => 'quarterly_bonus',
            'amount' => $sponsorExpectedBonus,
        ]);

        // === PHASE 6: ANNUAL PROFIT DISTRIBUTION (AFTER 12 MONTHS) ===
        
        // Fast-forward investments to be older than 12 months
        Investment::query()->update(['created_at' => Carbon::now()->subMonths(15)]);

        $annualProfit = 200000;
        
        Artisan::call('profit:distribute-annual', [
            'total_profit' => $annualProfit,
        ]);

        // Verify annual distribution
        $this->assertDatabaseHas('profit_distributions', [
            'period_type' => 'annual',
            'total_profit' => $annualProfit,
            'status' => 'completed',
        ]);

        // Verify annual profit shares based on tier rates
        // Sponsor: 1100 * 5% = 55 (Starter tier)
        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $sponsor->id,
            'type' => 'annual',
            'amount' => 55,
        ]);

        // Referral: 500 * 3% = 15 (Basic tier)
        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $referral->id,
            'type' => 'annual',
            'amount' => 15,
        ]);

        // Second level: 1000 * 5% = 50 (Starter tier)
        $this->assertDatabaseHas('profit_shares', [
            'user_id' => $secondLevelReferral->id,
            'type' => 'annual',
            'amount' => 50,
        ]);

        // === PHASE 7: WITHDRAWAL REQUEST AND PROCESSING ===
        
        // Sponsor requests partial withdrawal after lock-in period
        $response = $this->actingAs($sponsor)->post('/withdrawals', [
            'amount' => 400,
            'type' => 'partial',
            'reason' => 'Personal expenses',
        ]);

        $response->assertStatus(302);

        $withdrawalRequest = WithdrawalRequest::where('user_id', $sponsor->id)->first();
        
        // Verify withdrawal request
        $this->assertEquals(400, $withdrawalRequest->amount);
        $this->assertEquals('partial', $withdrawalRequest->type);
        $this->assertEquals('pending', $withdrawalRequest->status);
        $this->assertEquals(0, $withdrawalRequest->penalty_amount); // No penalty after 12 months

        // Admin approves and processes withdrawal
        $admin = User::factory()->create(['is_admin' => true]);
        
        $this->actingAs($admin)
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/approve", [
                'admin_notes' => 'Approved - meets requirements',
            ]);

        $this->actingAs($admin)
            ->patch("/admin/withdrawals/{$withdrawalRequest->id}/process");

        // Verify withdrawal was processed
        $withdrawalRequest->refresh();
        $this->assertEquals('processed', $withdrawalRequest->status);

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $sponsor->id,
            'type' => 'withdrawal',
            'amount' => 400,
            'status' => 'completed',
        ]);

        // === PHASE 8: EARLY WITHDRAWAL WITH PENALTIES AND CLAWBACK ===
        
        // Referral requests early withdrawal (triggers penalties and clawback)
        Investment::where('user_id', $referral->id)->update(['created_at' => Carbon::now()->subMonths(2)]);
        
        $response = $this->actingAs($referral)->post('/withdrawals', [
            'amount' => 500,
            'type' => 'emergency',
            'reason' => 'Medical emergency',
        ]);

        $response->assertStatus(302);

        $earlyWithdrawal = WithdrawalRequest::where('user_id', $referral->id)->first();
        
        // Verify penalties were calculated
        $this->assertGreaterThan(0, $earlyWithdrawal->penalty_amount);
        $this->assertLessThan(500, $earlyWithdrawal->net_amount);

        // Admin approves and processes early withdrawal
        $this->actingAs($admin)
            ->patch("/admin/withdrawals/{$earlyWithdrawal->id}/approve");

        $this->actingAs($admin)
            ->patch("/admin/withdrawals/{$earlyWithdrawal->id}/process");

        // Verify commission clawback was processed
        $originalCommission = ReferralCommission::where('referrer_id', $sponsor->id)
            ->where('referred_id', $referral->id)
            ->first();

        $this->assertDatabaseHas('commission_clawbacks', [
            'referral_commission_id' => $originalCommission->id,
            'user_id' => $sponsor->id,
        ]);

        // === PHASE 9: DASHBOARD AND REPORTING VERIFICATION ===
        
        // Verify sponsor dashboard shows complete journey
        $response = $this->actingAs($sponsor)->get('/dashboard');
        $response->assertStatus(200);
        
        // Should show investments, commissions, matrix structure, profit shares
        $response->assertViewHas('investments');
        $response->assertViewHas('totalInvestment');
        $response->assertViewHas('currentTier');

        // Verify referral performance report
        $response = $this->actingAs($sponsor)->get('/referrals/performance');
        $response->assertStatus(200);
        $response->assertViewHas('performanceMetrics');

        // Verify matrix visualization
        $response = $this->actingAs($sponsor)->get('/referrals/matrix');
        $response->assertStatus(200);
        $response->assertViewHas('matrixData');

        // === PHASE 10: ADMIN REPORTING AND ANALYTICS ===
        
        // Verify admin can access comprehensive reports
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/investments');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/withdrawals');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get('/admin/commissions');
        $response->assertStatus(200);

        // === FINAL VERIFICATION: DATA INTEGRITY ===
        
        // Verify total system integrity
        $totalInvestments = Investment::sum('amount');
        $totalCommissions = ReferralCommission::where('status', 'paid')->sum('amount');
        $totalWithdrawals = WithdrawalRequest::where('status', 'processed')->sum('net_amount');
        $totalProfitShares = ProfitShare::sum('amount');

        $this->assertGreaterThan(0, $totalInvestments);
        $this->assertGreaterThan(0, $totalCommissions);
        $this->assertGreaterThan(0, $totalWithdrawals);
        $this->assertGreaterThan(0, $totalProfitShares);

        // Verify user counts and tier distribution
        $totalUsers = User::count();
        $basicTierUsers = User::where('current_investment_tier_id', $basicTier->id)->count();
        $starterTierUsers = User::where('current_investment_tier_id', $starterTier->id)->count();

        $this->assertEquals(4, $totalUsers); // sponsor, referral, secondLevel, admin
        $this->assertGreaterThan(0, $basicTierUsers);
        $this->assertGreaterThan(0, $starterTierUsers);

        // Verify matrix structure integrity
        $totalMatrixPositions = MatrixPosition::where('is_active', true)->count();
        $level1Positions = MatrixPosition::where('level', 1)->where('is_active', true)->count();
        $level2Positions = MatrixPosition::where('level', 2)->where('is_active', true)->count();

        $this->assertGreaterThan(0, $totalMatrixPositions);
        $this->assertGreaterThan(0, $level1Positions);
        $this->assertGreaterThan(0, $level2Positions);

        // Verify transaction audit trail
        $totalTransactions = Transaction::count();
        $investmentTransactions = Transaction::where('type', 'investment')->count();
        $commissionTransactions = Transaction::where('type', 'referral_commission')->count();
        $withdrawalTransactions = Transaction::where('type', 'withdrawal')->count();
        $profitShareTransactions = Transaction::where('type', 'profit_share')->count();

        $this->assertGreaterThan(0, $totalTransactions);
        $this->assertGreaterThan(0, $investmentTransactions);
        $this->assertGreaterThan(0, $withdrawalTransactions);

        // === SUCCESS: COMPLETE USER JOURNEY VERIFIED ===
        $this->assertTrue(true, 'Complete end-to-end user journey successfully tested');
    }

    public function test_system_performance_under_load(): void
    {
        // Create 50 users with investments to test system performance
        $users = User::factory()->count(50)->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        $startTime = microtime(true);

        foreach ($users as $user) {
            Investment::factory()->create([
                'user_id' => $user->id,
                'investment_tier_id' => $basicTier->id,
                'amount' => 500,
                'created_at' => Carbon::now()->subMonths(15),
            ]);

            $user->update([
                'total_investment_amount' => 500,
                'current_investment_tier_id' => $basicTier->id,
            ]);
        }

        // Run annual profit distribution
        Artisan::call('profit:distribute-annual', [
            'total_profit' => 100000,
        ]);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Verify performance (should complete within reasonable time)
        $this->assertLessThan(30, $executionTime, 'System should handle 50 users within 30 seconds');

        // Verify all profit shares were created
        $profitShareCount = ProfitShare::count();
        $this->assertEquals(50, $profitShareCount);

        // Verify data integrity
        $totalDistributed = ProfitShare::sum('amount');
        $expectedTotal = 50 * (500 * 0.03); // 50 users * 500 * 3% = 750
        $this->assertEquals($expectedTotal, $totalDistributed);
    }
}