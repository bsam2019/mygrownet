<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Application\Services\CommissionProcessingService;
use App\Application\Services\TierAdvancementService;
use App\Application\UseCases\MLM\ProcessCommissionsUseCase;
use App\Application\UseCases\MLM\ProcessTierAdvancementUseCase;
use App\Domain\MLM\Repositories\CommissionRepository;
use App\Domain\MLM\Repositories\TeamVolumeRepository;
use App\Domain\MLM\Services\MLMCommissionCalculationService;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;
use App\Models\TierQualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class MLMWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private CommissionProcessingService $commissionService;
    private TierAdvancementService $tierService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createTiers();
        $this->setupServices();
    }

    private function createTiers(): void
    {
        InvestmentTier::factory()->create(['name' => 'Bronze Member', 'minimum_amount' => 150]);
        InvestmentTier::factory()->create(['name' => 'Silver Member', 'minimum_amount' => 300]);
        InvestmentTier::factory()->create(['name' => 'Gold Member', 'minimum_amount' => 500]);
        InvestmentTier::factory()->create(['name' => 'Diamond Member', 'minimum_amount' => 1000]);
        InvestmentTier::factory()->create(['name' => 'Elite Member', 'minimum_amount' => 1500]);
    }

    private function setupServices(): void
    {
        // Create mock repositories
        $commissionRepo = $this->createMock(CommissionRepository::class);
        $teamVolumeRepo = $this->createMock(TeamVolumeRepository::class);
        $calculationService = $this->createMock(MLMCommissionCalculationService::class);

        // Create use cases
        $processCommissionsUseCase = new ProcessCommissionsUseCase(
            $commissionRepo,
            $teamVolumeRepo,
            $calculationService
        );

        $processTierAdvancementUseCase = new ProcessTierAdvancementUseCase(
            app(MyGrowNetTierAdvancementService::class)
        );

        // Create services
        $this->commissionService = new CommissionProcessingService(
            $processCommissionsUseCase,
            $processTierAdvancementUseCase
        );

        $this->tierService = new TierAdvancementService($processTierAdvancementUseCase);
    }

    public function test_complete_mlm_workflow_five_level_network()
    {
        // Arrange: Create a 5-level network
        $bronzeTier = InvestmentTier::where('name', 'Bronze Member')->first();
        
        // Level 0 (purchaser)
        $purchaser = User::factory()->create(['investment_tier_id' => $bronzeTier->id]);
        
        // Level 1 (direct referrer)
        $level1 = User::factory()->create([
            'investment_tier_id' => $bronzeTier->id,
            'monthly_team_volume' => 0
        ]);
        $purchaser->update(['referrer_id' => $level1->id]);
        
        // Level 2
        $level2 = User::factory()->create([
            'investment_tier_id' => $bronzeTier->id,
            'monthly_team_volume' => 0
        ]);
        $level1->update(['referrer_id' => $level2->id]);
        
        // Level 3
        $level3 = User::factory()->create([
            'investment_tier_id' => $bronzeTier->id,
            'monthly_team_volume' => 0
        ]);
        $level2->update(['referrer_id' => $level3->id]);
        
        // Level 4
        $level4 = User::factory()->create([
            'investment_tier_id' => $bronzeTier->id,
            'monthly_team_volume' => 0
        ]);
        $level3->update(['referrer_id' => $level4->id]);
        
        // Level 5
        $level5 = User::factory()->create([
            'investment_tier_id' => $bronzeTier->id,
            'monthly_team_volume' => 0
        ]);
        $level4->update(['referrer_id' => $level5->id]);

        $packageAmount = 1000.0;

        // Act: Process commission for package purchase
        $result = $this->commissionService->processPackagePurchaseCommissions(
            $purchaser->id,
            $packageAmount
        );

        // Assert: Verify commissions were created for all 5 levels
        $this->assertTrue($result['success']);
        $this->assertEquals(5, $result['commissions_created']);
        
        // Verify commission amounts
        $expectedCommissions = [
            $level1->id => 120.0, // 12%
            $level2->id => 60.0,  // 6%
            $level3->id => 40.0,  // 4%
            $level4->id => 20.0,  // 2%
            $level5->id => 10.0,  // 1%
        ];

        foreach ($expectedCommissions as $userId => $expectedAmount) {
            $this->assertDatabaseHas('referral_commissions', [
                'user_id' => $userId,
                'referred_user_id' => $purchaser->id,
                'amount' => $expectedAmount,
                'commission_type' => 'REFERRAL',
                'status' => 'pending'
            ]);
        }

        // Verify team volumes were updated
        $level1->refresh();
        $level2->refresh();
        $level3->refresh();
        $level4->refresh();
        $level5->refresh();

        $this->assertEquals($packageAmount, $level1->team_volume);
        $this->assertEquals($packageAmount, $level2->team_volume);
        $this->assertEquals($packageAmount, $level3->team_volume);
        $this->assertEquals($packageAmount, $level4->team_volume);
        $this->assertEquals($packageAmount, $level5->team_volume);
    }

    public function test_tier_advancement_workflow_bronze_to_silver()
    {
        // Arrange: Create user with qualifying stats for Silver tier
        $bronzeTier = InvestmentTier::where('name', 'Bronze Member')->first();
        $silverTier = InvestmentTier::where('name', 'Silver Member')->first();
        
        $user = User::factory()->create([
            'investment_tier_id' => $bronzeTier->id,
            'monthly_team_volume' => 6000 // Above Silver requirement of 5000
        ]);

        // Create 4 active referrals (above Silver requirement of 3)
        User::factory()->count(4)->create([
            'referrer_id' => $user->id,
            'investment_tier_id' => $bronzeTier->id
        ]);

        // Act: Process tier advancement
        $result = $this->tierService->processUserTierAdvancement($user->id);

        // Assert: Verify tier advancement
        $this->assertTrue($result['qualified']);
        $this->assertTrue($result['upgraded']);
        $this->assertEquals('Bronze Member', $result['old_tier']);
        $this->assertEquals('Silver Member', $result['new_tier']);
        $this->assertEquals(500, $result['achievement_bonus']);

        // Verify user tier was updated
        $user->refresh();
        $this->assertEquals($silverTier->id, $user->investment_tier_id);

        // Verify tier qualification record was created
        $this->assertDatabaseHas('tier_qualifications', [
            'user_id' => $user->id,
            'tier_id' => $silverTier->id,
            'active_referrals' => 4,
            'team_volume' => 6000,
            'consecutive_months' => 1
        ]);

        // Verify achievement bonus commission was created
        $this->assertDatabaseHas('referral_commissions', [
            'user_id' => $user->id,
            'referred_user_id' => $user->id,
            'amount' => 500,
            'commission_type' => 'ACHIEVEMENT',
            'level' => 0
        ]);
    }

    public function test_team_volume_bonus_calculation_and_payment()
    {
        // Arrange: Create users with different tiers and team volumes
        $silverTier = InvestmentTier::where('name', 'Silver Member')->first();
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $diamondTier = InvestmentTier::where('name', 'Diamond Member')->first();

        $silverUser = User::factory()->create([
            'investment_tier_id' => $silverTier->id,
            'monthly_team_volume' => 10000
        ]);

        $goldUser = User::factory()->create([
            'investment_tier_id' => $goldTier->id,
            'monthly_team_volume' => 20000
        ]);

        $diamondUser = User::factory()->create([
            'investment_tier_id' => $diamondTier->id,
            'monthly_team_volume' => 50000
        ]);

        // Act: Process monthly team volume bonuses
        $results = $this->commissionService->processMonthlyTeamVolumeBonuses();

        // Assert: Verify bonuses were calculated correctly
        $this->assertCount(3, $results);

        $expectedBonuses = [
            $silverUser->id => 200.0,   // 10000 * 2%
            $goldUser->id => 1000.0,    // 20000 * 5%
            $diamondUser->id => 3500.0, // 50000 * 7%
        ];

        foreach ($results as $result) {
            $userId = $result['user_id'];
            $this->assertEquals($expectedBonuses[$userId], $result['bonus_amount']);
            
            // Verify commission was created
            $this->assertDatabaseHas('referral_commissions', [
                'user_id' => $userId,
                'commission_type' => 'TEAM_VOLUME',
                'amount' => $expectedBonuses[$userId]
            ]);
        }
    }

    public function test_commission_payment_processing()
    {
        // Arrange: Create pending commissions older than 24 hours
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $commission1 = ReferralCommission::factory()->create([
            'user_id' => $user1->id,
            'amount' => 100.0,
            'status' => 'pending',
            'earned_at' => now()->subDays(2)
        ]);

        $commission2 = ReferralCommission::factory()->create([
            'user_id' => $user2->id,
            'amount' => 250.0,
            'status' => 'pending',
            'earned_at' => now()->subHours(25)
        ]);

        // Create a recent commission that shouldn't be processed yet
        $recentCommission = ReferralCommission::factory()->create([
            'user_id' => $user1->id,
            'amount' => 50.0,
            'status' => 'pending',
            'earned_at' => now()->subHours(12)
        ]);

        // Act: Process pending payments
        $results = $this->commissionService->processPendingPayments();

        // Assert: Verify only eligible commissions were processed
        $this->assertEquals(2, $results['processed']);
        $this->assertEquals(0, $results['failed']);
        $this->assertEquals(350.0, $results['total_amount']);

        // Verify commissions were marked as paid
        $commission1->refresh();
        $commission2->refresh();
        $recentCommission->refresh();

        $this->assertEquals('paid', $commission1->status);
        $this->assertNotNull($commission1->paid_at);
        
        $this->assertEquals('paid', $commission2->status);
        $this->assertNotNull($commission2->paid_at);
        
        $this->assertEquals('pending', $recentCommission->status);
        $this->assertNull($recentCommission->paid_at);
    }

    public function test_tier_maintenance_and_permanent_status()
    {
        // Arrange: Create user with Gold tier qualification for 2 months
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $user = User::factory()->create([
            'investment_tier_id' => $goldTier->id,
            'monthly_team_volume' => 20000
        ]);

        // Create sufficient referrals
        User::factory()->count(12)->create([
            'referrer_id' => $user->id,
            'investment_tier_id' => $goldTier->id
        ]);

        $qualification = TierQualification::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $goldTier->id,
            'active_referrals' => 12,
            'team_volume' => 20000,
            'consecutive_months' => 2,
            'is_permanent' => false,
            'created_at' => now()->subMonth()
        ]);

        // Act: Process monthly tier maintenance
        $results = $this->tierService->processMonthlyTierMaintenance();

        // Assert: Verify tier was maintained and permanent status granted
        $this->assertEquals(1, $results['maintained']);
        $this->assertEquals(0, $results['downgraded']);

        $qualification->refresh();
        $this->assertEquals(3, $qualification->consecutive_months);
        $this->assertTrue($qualification->is_permanent);
    }

    public function test_comprehensive_commission_statistics()
    {
        // Arrange: Create user with various commission types
        $user = User::factory()->create();

        // Create different types of commissions
        ReferralCommission::factory()->create([
            'user_id' => $user->id,
            'amount' => 120.0,
            'commission_type' => 'REFERRAL',
            'level' => 1,
            'status' => 'paid',
            'paid_at' => now()
        ]);

        ReferralCommission::factory()->create([
            'user_id' => $user->id,
            'amount' => 60.0,
            'commission_type' => 'REFERRAL',
            'level' => 2,
            'status' => 'paid',
            'paid_at' => now()
        ]);

        ReferralCommission::factory()->create([
            'user_id' => $user->id,
            'amount' => 500.0,
            'commission_type' => 'TEAM_VOLUME',
            'level' => 0,
            'status' => 'paid',
            'paid_at' => now()
        ]);

        ReferralCommission::factory()->create([
            'user_id' => $user->id,
            'amount' => 2000.0,
            'commission_type' => 'ACHIEVEMENT',
            'level' => 0,
            'status' => 'paid',
            'paid_at' => now()
        ]);

        ReferralCommission::factory()->create([
            'user_id' => $user->id,
            'amount' => 100.0,
            'commission_type' => 'REFERRAL',
            'level' => 1,
            'status' => 'pending'
        ]);

        // Act: Get commission statistics
        $stats = $this->commissionService->getCommissionStats($user->id);

        // Assert: Verify comprehensive statistics
        $this->assertEquals(2680.0, $stats['total_earned']);
        $this->assertEquals(100.0, $stats['pending_amount']);
        $this->assertEquals(2680.0, $stats['this_month']);

        $expectedBreakdown = [
            'REFERRAL' => 180.0,
            'TEAM_VOLUME' => 500.0,
            'ACHIEVEMENT' => 2000.0
        ];
        $this->assertEquals($expectedBreakdown, $stats['commission_breakdown']);

        $expectedLevelBreakdown = [
            1 => 120.0,
            2 => 60.0,
            0 => 2500.0 // TEAM_VOLUME + ACHIEVEMENT
        ];
        $this->assertEquals($expectedLevelBreakdown, $stats['level_breakdown']);
    }
}