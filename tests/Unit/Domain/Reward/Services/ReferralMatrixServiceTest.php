<?php

namespace Tests\Unit\Domain\Reward\Services;

use App\Domain\Reward\Services\ReferralMatrixService;
use App\Models\User;
use App\Models\MatrixPosition;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralMatrixServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReferralMatrixService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReferralMatrixService();
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
    }

    public function test_builds_empty_matrix_for_new_user(): void
    {
        $user = User::factory()->create();
        
        $matrix = $this->service->buildMatrix($user);
        
        $this->assertIsArray($matrix);
        $this->assertArrayHasKey('user_id', $matrix);
        $this->assertArrayHasKey('has_position', $matrix);
        $this->assertArrayHasKey('matrix_structure', $matrix);
        $this->assertArrayHasKey('total_downline', $matrix);
        
        $this->assertEquals($user->id, $matrix['user_id']);
        $this->assertFalse($matrix['has_position']);
        $this->assertEquals(0, $matrix['total_downline']);
    }

    public function test_builds_matrix_with_direct_referrals(): void
    {
        $sponsor = User::factory()->create();
        $referral1 = User::factory()->create(['referrer_id' => $sponsor->id]);
        $referral2 = User::factory()->create(['referrer_id' => $sponsor->id]);
        
        // Create matrix positions
        MatrixPosition::create([
            'user_id' => $referral1->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
        ]);

        MatrixPosition::create([
            'user_id' => $referral2->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 2,
            'is_active' => true,
        ]);
        
        $matrix = $this->service->buildMatrix($sponsor);
        
        $this->assertCount(2, $matrix['level_1']);
        $this->assertEquals($referral1->id, $matrix['level_1'][0]['id']);
        $this->assertEquals($referral2->id, $matrix['level_1'][1]['id']);
    }

    public function test_builds_complete_3x3_matrix(): void
    {
        $sponsor = User::factory()->create();
        
        // Create level 1 referrals (3 direct)
        $level1Users = [];
        for ($i = 1; $i <= 3; $i++) {
            $user = User::factory()->create(['referrer_id' => $sponsor->id]);
            $level1Users[] = $user;
            
            MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $i,
                'is_active' => true,
            ]);
        }
        
        // Create level 2 referrals (9 total, 3 under each level 1)
        $level2Users = [];
        foreach ($level1Users as $index => $level1User) {
            for ($i = 1; $i <= 3; $i++) {
                $user = User::factory()->create(['referrer_id' => $level1User->id]);
                $level2Users[] = $user;
                
                MatrixPosition::create([
                    'user_id' => $user->id,
                    'sponsor_id' => $sponsor->id,
                    'level' => 2,
                    'position' => ($index * 3) + $i,
                    'is_active' => true,
                ]);
            }
        }
        
        $matrix = $this->service->buildMatrix($sponsor);
        
        $this->assertCount(3, $matrix['level_1']);
        $this->assertCount(9, $matrix['level_2']);
        $this->assertCount(0, $matrix['level_3']); // No level 3 created yet
    }

    public function test_finds_next_available_position_in_empty_matrix(): void
    {
        $sponsor = User::factory()->create();
        
        $position = $this->service->findNextAvailablePosition($sponsor);
        
        $this->assertIsArray($position);
        $this->assertEquals(1, $position['level']);
        $this->assertEquals(1, $position['position']);
        $this->assertEquals($sponsor->id, $position['sponsor_id']);
    }

    public function test_finds_next_available_position_with_partial_level_1(): void
    {
        $sponsor = User::factory()->create();
        $referral1 = User::factory()->create(['referrer_id' => $sponsor->id]);
        
        MatrixPosition::create([
            'user_id' => $referral1->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
        ]);
        
        $position = $this->service->findNextAvailablePosition($sponsor);
        
        $this->assertEquals(1, $position['level']);
        $this->assertEquals(2, $position['position']);
    }

    public function test_finds_spillover_position_when_level_1_full(): void
    {
        $sponsor = User::factory()->create();
        
        // Fill level 1 completely
        for ($i = 1; $i <= 3; $i++) {
            $user = User::factory()->create(['referrer_id' => $sponsor->id]);
            MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $i,
                'is_active' => true,
            ]);
        }
        
        $position = $this->service->findNextAvailablePosition($sponsor);
        
        $this->assertEquals(2, $position['level']);
        $this->assertEquals(1, $position['position']);
    }

    public function test_returns_null_when_matrix_completely_full(): void
    {
        $sponsor = User::factory()->create();
        
        // Fill all 39 positions (3 + 9 + 27)
        $positionCount = 1;
        
        // Level 1: 3 positions
        for ($i = 1; $i <= 3; $i++) {
            $user = User::factory()->create();
            MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $i,
                'is_active' => true,
            ]);
        }
        
        // Level 2: 9 positions
        for ($i = 1; $i <= 9; $i++) {
            $user = User::factory()->create();
            MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'level' => 2,
                'position' => $i,
                'is_active' => true,
            ]);
        }
        
        // Level 3: 27 positions
        for ($i = 1; $i <= 27; $i++) {
            $user = User::factory()->create();
            MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'level' => 3,
                'position' => $i,
                'is_active' => true,
            ]);
        }
        
        $position = $this->service->findNextAvailablePosition($sponsor);
        
        $this->assertNull($position);
    }

    public function test_processes_spillover_correctly(): void
    {
        $sponsor = User::factory()->create();
        $newUser = User::factory()->create();
        
        // Fill sponsor's direct positions
        for ($i = 1; $i <= 3; $i++) {
            $user = User::factory()->create(['referrer_id' => $sponsor->id]);
            MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $i,
                'is_active' => true,
            ]);
        }
        
        $this->service->processSpillover($newUser, $sponsor);
        
        // Check that new user was placed in level 2
        $this->assertDatabaseHas('matrix_positions', [
            'user_id' => $newUser->id,
            'sponsor_id' => $sponsor->id,
            'level' => 2,
            'position' => 1,
            'is_active' => true,
        ]);
    }

    public function test_calculates_matrix_commissions_for_direct_referral(): void
    {
        $sponsor = User::factory()->create();
        $referral = User::factory()->create(['referrer_id' => $sponsor->id]);
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
            'position' => 1,
            'is_active' => true,
        ]);
        
        $commissions = $this->service->calculateMatrixCommissions($investment);
        
        $this->assertIsArray($commissions);
        $this->assertCount(1, $commissions);
        
        $commission = $commissions[0];
        $this->assertEquals($sponsor->id, $commission['user_id']);
        $this->assertEquals(1, $commission['level']);
        $this->assertEquals(25.00, $commission['amount']); // 500 * 5% = 25
    }

    public function test_calculates_matrix_commissions_for_multiple_levels(): void
    {
        $sponsor = User::factory()->create();
        $level1User = User::factory()->create(['referrer_id' => $sponsor->id]);
        $level2User = User::factory()->create(['referrer_id' => $level1User->id]);
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $level2User->id,
            'investment_tier_id' => $starterTier->id,
            'amount' => 1000,
        ]);
        
        // Create matrix positions
        MatrixPosition::create([
            'user_id' => $level1User->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
        ]);
        
        MatrixPosition::create([
            'user_id' => $level2User->id,
            'sponsor_id' => $sponsor->id,
            'level' => 2,
            'position' => 1,
            'is_active' => true,
        ]);
        
        $commissions = $this->service->calculateMatrixCommissions($investment);
        
        $this->assertCount(2, $commissions);
        
        // Level 1 commission (direct referral)
        $level1Commission = collect($commissions)->firstWhere('level', 1);
        $this->assertEquals($level1User->id, $level1Commission['user_id']);
        $this->assertEquals(70.00, $level1Commission['amount']); // 1000 * 7% = 70
        
        // Level 2 commission (spillover to sponsor)
        $level2Commission = collect($commissions)->firstWhere('level', 2);
        $this->assertEquals($sponsor->id, $level2Commission['user_id']);
        $this->assertEquals(20.00, $level2Commission['amount']); // 1000 * 2% = 20
    }

    public function test_handles_inactive_matrix_positions(): void
    {
        $sponsor = User::factory()->create();
        $referral = User::factory()->create(['referrer_id' => $sponsor->id]);
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $referral->id,
            'investment_tier_id' => $basicTier->id,
            'amount' => 500,
        ]);
        
        // Create inactive matrix position
        MatrixPosition::create([
            'user_id' => $referral->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => false,
        ]);
        
        $commissions = $this->service->calculateMatrixCommissions($investment);
        
        $this->assertCount(0, $commissions);
    }

    public function test_calculates_matrix_depth_correctly(): void
    {
        $sponsor = User::factory()->create();
        
        $depth = $this->service->getMatrixDepth($sponsor);
        $this->assertEquals(0, $depth);
        
        // Add level 1
        $level1User = User::factory()->create(['referrer_id' => $sponsor->id]);
        MatrixPosition::create([
            'user_id' => $level1User->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
        ]);
        
        $depth = $this->service->getMatrixDepth($sponsor);
        $this->assertEquals(1, $depth);
        
        // Add level 2
        $level2User = User::factory()->create(['referrer_id' => $level1User->id]);
        MatrixPosition::create([
            'user_id' => $level2User->id,
            'sponsor_id' => $sponsor->id,
            'level' => 2,
            'position' => 1,
            'is_active' => true,
        ]);
        
        $depth = $this->service->getMatrixDepth($sponsor);
        $this->assertEquals(2, $depth);
    }

    public function test_gets_matrix_statistics(): void
    {
        $sponsor = User::factory()->create();
        
        // Create some matrix positions
        for ($i = 1; $i <= 2; $i++) {
            $user = User::factory()->create(['referrer_id' => $sponsor->id]);
            MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $i,
                'is_active' => true,
            ]);
        }
        
        $stats = $this->service->getMatrixStatistics($sponsor);
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_positions', $stats);
        $this->assertArrayHasKey('active_positions', $stats);
        $this->assertArrayHasKey('level_1_count', $stats);
        $this->assertArrayHasKey('level_2_count', $stats);
        $this->assertArrayHasKey('level_3_count', $stats);
        $this->assertArrayHasKey('available_positions', $stats);
        
        $this->assertEquals(2, $stats['total_positions']);
        $this->assertEquals(2, $stats['active_positions']);
        $this->assertEquals(2, $stats['level_1_count']);
        $this->assertEquals(0, $stats['level_2_count']);
        $this->assertEquals(37, $stats['available_positions']); // 39 - 2 = 37
    }
}