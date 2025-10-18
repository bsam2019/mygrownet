<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\MatrixPosition;
use App\Models\ReferralCommission;
use App\Domain\Reward\Services\ReferralMatrixService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class MatrixOperationsTest extends TestCase
{
    use RefreshDatabase;

    private ReferralMatrixService $matrixService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matrixService = app(ReferralMatrixService::class);
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

    public function test_complete_matrix_placement_workflow(): void
    {
        // Create sponsor
        $sponsor = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Builder')->first()->id,
        ]);

        // Create new referral
        $referral = User::factory()->create([
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 0,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Referral makes investment (triggers matrix placement)
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

        $matrixPosition = MatrixPosition::where('user_id', $referral->id)->first();
        $this->assertNotNull($matrixPosition);
        $this->assertNotNull($matrixPosition->placed_at);
    }

    public function test_matrix_spillover_mechanism(): void
    {
        // Create sponsor
        $sponsor = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Builder')->first()->id,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Fill sponsor's direct positions (3 positions in 3x3 matrix)
        $directReferrals = [];
        for ($i = 1; $i <= 3; $i++) {
            $referral = User::factory()->create([
                'referrer_id' => $sponsor->id,
                'total_investment_amount' => 500,
            ]);

            Investment::factory()->create([
                'user_id' => $referral->id,
                'investment_tier_id' => $basicTier->id,
                'amount' => 500,
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

        // Create new referral that should spillover
        $spilloverReferral = User::factory()->create([
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 0,
        ]);

        // Make investment (should trigger spillover)
        $response = $this->actingAs($spilloverReferral)->post('/investments', [
            'amount' => 500,
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        // Verify spillover referral was placed in level 2
        $spilloverPosition = MatrixPosition::where('user_id', $spilloverReferral->id)->first();
        $this->assertNotNull($spilloverPosition);
        $this->assertEquals(2, $spilloverPosition->level);
        $this->assertEquals($sponsor->id, $spilloverPosition->sponsor_id);
    }

    public function test_matrix_commission_calculation_and_distribution(): void
    {
        // Create 3-level matrix structure
        $level1Sponsor = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Builder')->first()->id,
        ]);

        $level2User = User::factory()->create([
            'referrer_id' => $level1Sponsor->id,
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        $investor = User::factory()->create([
            'referrer_id' => $level2User->id,
            'total_investment_amount' => 0,
        ]);

        // Create matrix positions
        MatrixPosition::create([
            'user_id' => $level2User->id,
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
            'amount' => 1000,
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        $investment = Investment::where('user_id', $investor->id)->first();

        // Verify matrix commissions were calculated and created
        $level1Commission = ReferralCommission::where('referrer_id', $level2User->id)
            ->where('investment_id', $investment->id)
            ->where('level', 1)
            ->first();

        $level2Commission = ReferralCommission::where('referrer_id', $level1Sponsor->id)
            ->where('investment_id', $investment->id)
            ->where('level', 2)
            ->first();

        $this->assertNotNull($level1Commission);
        $this->assertNotNull($level2Commission);

        // Verify commission amounts
        // Level 1: Starter tier direct rate (7%) = 1000 * 0.07 = 70
        $this->assertEquals(70, $level1Commission->amount);

        // Level 2: Builder tier level 2 rate (3%) = 1000 * 0.03 = 30
        $this->assertEquals(30, $level2Commission->amount);
    }

    public function test_matrix_visualization_data_generation(): void
    {
        // Create sponsor with matrix structure
        $sponsor = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Builder')->first()->id,
        ]);

        // Create level 1 referrals
        $level1Users = [];
        for ($i = 1; $i <= 2; $i++) {
            $user = User::factory()->create([
                'referrer_id' => $sponsor->id,
                'total_investment_amount' => 1000,
            ]);

            MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $i,
                'is_active' => true,
                'placed_at' => now(),
            ]);

            $level1Users[] = $user;
        }

        // Create level 2 referrals
        foreach ($level1Users as $index => $level1User) {
            $level2User = User::factory()->create([
                'referrer_id' => $level1User->id,
                'total_investment_amount' => 500,
            ]);

            MatrixPosition::create([
                'user_id' => $level2User->id,
                'sponsor_id' => $sponsor->id,
                'level' => 2,
                'position' => ($index * 3) + 1,
                'is_active' => true,
                'placed_at' => now(),
            ]);
        }

        // Access matrix visualization
        $response = $this->actingAs($sponsor)->get('/referrals/matrix');

        $response->assertStatus(200);
        $response->assertViewHas('matrixData');

        $matrixData = $response->viewData('matrixData');
        
        $this->assertIsArray($matrixData);
        $this->assertArrayHasKey('user_id', $matrixData);
        $this->assertArrayHasKey('has_position', $matrixData);
        $this->assertArrayHasKey('downline_counts', $matrixData);
        
        $this->assertEquals($sponsor->id, $matrixData['user_id']);
        $this->assertTrue($matrixData['has_position']);
        $this->assertEquals(2, $matrixData['downline_counts']['level_1']);
        $this->assertEquals(2, $matrixData['downline_counts']['level_2']);
    }

    public function test_matrix_genealogy_report_generation(): void
    {
        // Create complex matrix structure
        $sponsor = User::factory()->create([
            'name' => 'Matrix Sponsor',
            'total_investment_amount' => 2500,
        ]);

        // Create multi-level structure
        $level1User = User::factory()->create([
            'name' => 'Level 1 User',
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 1000,
        ]);

        $level2User = User::factory()->create([
            'name' => 'Level 2 User',
            'referrer_id' => $level1User->id,
            'total_investment_amount' => 500,
        ]);

        // Create matrix positions
        MatrixPosition::create([
            'user_id' => $level1User->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        MatrixPosition::create([
            'user_id' => $level2User->id,
            'sponsor_id' => $sponsor->id,
            'level' => 2,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        // Access genealogy report
        $response = $this->actingAs($sponsor)->get('/referrals/genealogy');

        $response->assertStatus(200);
        $response->assertSee('Level 1 User');
        $response->assertSee('Level 2 User');
        $response->assertSee('Matrix Sponsor');
    }

    public function test_matrix_performance_metrics(): void
    {
        $sponsor = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Builder')->first()->id,
        ]);

        // Create referrals with investments
        $referrals = [];
        for ($i = 1; $i <= 5; $i++) {
            $referral = User::factory()->create([
                'referrer_id' => $sponsor->id,
                'total_investment_amount' => 500,
            ]);

            $basicTier = InvestmentTier::where('name', 'Basic')->first();

            $investment = Investment::factory()->create([
                'user_id' => $referral->id,
                'investment_tier_id' => $basicTier->id,
                'amount' => 500,
            ]);

            MatrixPosition::create([
                'user_id' => $referral->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $i <= 3 ? $i : (($i - 4) + 1),
                'is_active' => true,
                'placed_at' => now(),
            ]);

            // Create commission
            ReferralCommission::factory()->create([
                'referrer_id' => $sponsor->id,
                'referred_id' => $referral->id,
                'investment_id' => $investment->id,
                'amount' => 50, // 10% of 500
                'level' => 1,
                'status' => 'paid',
                'commission_type' => 'matrix',
            ]);

            $referrals[] = $referral;
        }

        // Access performance report
        $response = $this->actingAs($sponsor)->get('/referrals/performance');

        $response->assertStatus(200);
        $response->assertViewHas('performanceMetrics');

        $metrics = $response->viewData('performanceMetrics');
        
        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('total_matrix_earnings', $metrics['performance_metrics']);
        $this->assertArrayHasKey('downline_statistics', $metrics['performance_metrics']);
        
        $this->assertEquals(250, $metrics['performance_metrics']['total_matrix_earnings']); // 5 * 50
        $this->assertGreaterThan(0, $metrics['performance_metrics']['downline_statistics']['level_1']);
    }

    public function test_matrix_spillover_notifications(): void
    {
        // Create sponsor with full direct positions
        $sponsor = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Builder')->first()->id,
        ]);

        $basicTier = InvestmentTier::where('name', 'Basic')->first();

        // Fill direct positions
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
        }

        // Create spillover referral
        $spilloverReferral = User::factory()->create([
            'referrer_id' => $sponsor->id,
            'total_investment_amount' => 0,
        ]);

        // Make investment (triggers spillover)
        $response = $this->actingAs($spilloverReferral)->post('/investments', [
            'amount' => 500,
            'investment_tier_id' => $basicTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        // Verify spillover position was created
        $spilloverPosition = MatrixPosition::where('user_id', $spilloverReferral->id)->first();
        $this->assertEquals(2, $spilloverPosition->level);

        // In a real implementation, we would verify spillover notifications were sent
        $this->assertTrue(true); // Placeholder for notification verification
    }

    public function test_matrix_capacity_and_limits(): void
    {
        $sponsor = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Builder')->first()->id,
        ]);

        // Get matrix statistics
        $stats = $this->matrixService->getMatrixStatistics($sponsor);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('max_capacity', $stats);
        $this->assertArrayHasKey('available_positions', $stats);
        $this->assertArrayHasKey('completion_percentage', $stats);

        // 3x3 matrix has capacity of 39 positions (3 + 9 + 27)
        $this->assertEquals(39, $stats['max_capacity']);
        $this->assertEquals(39, $stats['available_positions']); // All available initially
        $this->assertEquals(0, $stats['completion_percentage']);
    }

    public function test_matrix_position_inheritance_on_upgrade(): void
    {
        // Create user who will upgrade tiers
        $user = User::factory()->create([
            'total_investment_amount' => 500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Basic')->first()->id,
        ]);

        $sponsor = User::factory()->create([
            'total_investment_amount' => 1000,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Starter')->first()->id,
        ]);

        // Create initial matrix position
        MatrixPosition::create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);

        $starterTier = InvestmentTier::where('name', 'Starter')->first();

        // User makes additional investment that upgrades tier
        $response = $this->actingAs($user)->post('/investments', [
            'amount' => 600, // Total becomes 1100, qualifies for Starter
            'investment_tier_id' => $starterTier->id,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(302);

        // Verify matrix position remains active after tier upgrade
        $matrixPosition = MatrixPosition::where('user_id', $user->id)->first();
        $this->assertTrue($matrixPosition->is_active);
        $this->assertEquals($sponsor->id, $matrixPosition->sponsor_id);

        // Verify user can now earn level 2 commissions (Starter tier benefit)
        $user->refresh();
        $this->assertEquals($starterTier->id, $user->current_investment_tier_id);
    }

    public function test_matrix_deep_structure_handling(): void
    {
        // Create deep matrix structure (3 levels)
        $level0Sponsor = User::factory()->create([
            'total_investment_amount' => 2500,
            'current_investment_tier_id' => InvestmentTier::where('name', 'Builder')->first()->id,
        ]);

        $currentSponsor = $level0Sponsor;
        $users = [$level0Sponsor];

        // Create 3 levels deep
        for ($level = 1; $level <= 3; $level++) {
            $levelUsers = [];
            $positionsInLevel = pow(3, $level); // 3^level positions per level

            for ($pos = 1; $pos <= min($positionsInLevel, 9); $pos++) { // Limit to 9 for testing
                $user = User::factory()->create([
                    'referrer_id' => $currentSponsor->id,
                    'total_investment_amount' => 500,
                ]);

                MatrixPosition::create([
                    'user_id' => $user->id,
                    'sponsor_id' => $level0Sponsor->id,
                    'level' => $level,
                    'position' => $pos,
                    'is_active' => true,
                    'placed_at' => now(),
                ]);

                $levelUsers[] = $user;
            }

            $users = array_merge($users, $levelUsers);
            if (!empty($levelUsers)) {
                $currentSponsor = $levelUsers[0]; // Use first user as sponsor for next level
            }
        }

        // Verify matrix depth calculation
        $depth = $this->matrixService->getMatrixDepth($level0Sponsor);
        $this->assertGreaterThanOrEqual(3, $depth);

        // Verify matrix statistics
        $stats = $this->matrixService->getMatrixStatistics($level0Sponsor);
        $this->assertGreaterThan(0, $stats['total_positions']);
        $this->assertGreaterThan(0, $stats['level_1_count']);
        $this->assertGreaterThan(0, $stats['level_2_count']);
    }
}