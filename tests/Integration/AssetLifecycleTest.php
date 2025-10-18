<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Application\Services\AssetAllocationService;
use App\Application\Services\AssetMaintenanceService;
use App\Application\UseCases\Asset\ProcessAssetAllocationUseCase;
use App\Application\UseCases\Asset\ProcessAssetMaintenanceUseCase;
use App\Domain\Reward\Repositories\AssetRepository;
use App\Domain\Reward\Repositories\PhysicalRewardAllocationRepository;
use App\Services\AssetIncomeTrackingService;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use App\Models\TierQualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class AssetLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private AssetAllocationService $allocationService;
    private AssetMaintenanceService $maintenanceService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createTiers();
        $this->setupServices();
    }

    private function createTiers(): void
    {
        // Create tiers directly without factories to reduce memory usage
        $tiers = [
            ['name' => 'Bronze Member', 'order' => 1, 'is_active' => true],
            ['name' => 'Silver Member', 'order' => 2, 'is_active' => true],
            ['name' => 'Gold Member', 'order' => 3, 'is_active' => true],
            ['name' => 'Diamond Member', 'order' => 4, 'is_active' => true],
            ['name' => 'Elite Member', 'order' => 5, 'is_active' => true],
        ];
        
        foreach ($tiers as $tier) {
            InvestmentTier::create($tier);
        }
    }

    private function setupServices(): void
    {
        // Create mock repositories
        $assetRepo = $this->createMock(AssetRepository::class);
        $allocationRepo = $this->createMock(PhysicalRewardAllocationRepository::class);
        $incomeService = $this->createMock(AssetIncomeTrackingService::class);

        // Create use cases
        $processAssetAllocationUseCase = new ProcessAssetAllocationUseCase($assetRepo, $allocationRepo);
        $processAssetMaintenanceUseCase = new ProcessAssetMaintenanceUseCase();

        // Create services
        $this->allocationService = new AssetAllocationService(
            $processAssetAllocationUseCase,
            $processAssetMaintenanceUseCase,
            $incomeService
        );

        $this->maintenanceService = new AssetMaintenanceService($processAssetMaintenanceUseCase);
    }

    public function test_complete_asset_lifecycle_silver_member_smartphone()
    {
        // Arrange: Create Silver member with qualifying stats
        $silverTier = InvestmentTier::where('name', 'Silver Member')->first();
        $user = User::factory()->create([
            'investment_tier_id' => $silverTier->id,
            'monthly_team_volume' => 16000 // Above required 15000
        ]);

        // Create tier qualification (3 months at Silver tier)
        TierQualification::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $silverTier->id,
            'active_referrals' => 4,
            'team_volume' => 16000,
            'consecutive_months' => 3,
            'created_at' => now()->subMonths(3)
        ]);

        // Create sufficient referrals
        User::factory()->count(4)->create([
            'referrer_id' => $user->id,
            'investment_tier_id' => $silverTier->id
        ]);

        // Create available smartphone
        $smartphone = PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'model' => 'iPhone 14',
            'value' => 3000,
            'status' => 'available',
            'tier_requirement' => 'Silver Member'
        ]);

        // Act 1: Process asset allocation
        $allocationResult = $this->allocationService->processUserAssetAllocation($user->id);

        // Assert 1: Asset was allocated
        $this->assertTrue($allocationResult['success']);
        $this->assertEquals(1, $allocationResult['total_allocated']);
        $this->assertEquals('Silver Member', $allocationResult['tier']);

        $allocation = PhysicalRewardAllocation::where('user_id', $user->id)->first();
        $this->assertNotNull($allocation);
        $this->assertEquals('pending', $allocation->status);
        $this->assertEquals($smartphone->id, $allocation->physical_reward_id);

        // Verify asset status updated
        $smartphone->refresh();
        $this->assertEquals('allocated', $smartphone->status);

        // Act 2: Process maintenance (should maintain - user meets requirements)
        $maintenanceResult = $this->maintenanceService->checkAllocationMaintenance($allocation->id);

        // Assert 2: Maintenance requirements met
        $this->assertEquals(1, $maintenanceResult['processed']);
        $this->assertEquals(1, $maintenanceResult['maintained']);
        $this->assertEquals(0, $maintenanceResult['violated']);

        $allocation->refresh();
        $this->assertEquals('active', $allocation->status);
        $this->assertEquals('maintained', $allocation->maintenance_status);

        // Act 3: Simulate 12 months passing (maintenance period complete)
        $allocation->update(['allocated_at' => now()->subMonths(12)]);
        $maintenanceResult = $this->maintenanceService->checkAllocationMaintenance($allocation->id);

        // Assert 3: Asset ownership completed
        $this->assertEquals(1, $maintenanceResult['completed']);
        
        $allocation->refresh();
        $smartphone->refresh();
        
        $this->assertEquals('completed', $allocation->status);
        $this->assertEquals('completed', $allocation->maintenance_status);
        $this->assertEquals('transferred', $smartphone->status);
        $this->assertEquals($user->id, $smartphone->owner_id);
        $this->assertNotNull($allocation->completed_at);
    }

    public function test_asset_maintenance_violation_and_forfeiture()
    {
        // Arrange: Create user with insufficient qualifications
        $silverTier = InvestmentTier::where('name', 'Silver Member')->first();
        $bronzeTier = InvestmentTier::where('name', 'Bronze Member')->first();
        
        $user = User::factory()->create([
            'investment_tier_id' => $bronzeTier->id, // Downgraded from Silver
            'monthly_team_volume' => 8000 // Below required 15000
        ]);

        // Create only 1 referral (needs 3)
        User::factory()->create(['referrer_id' => $user->id]);

        $smartphone = PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'value' => 3000,
            'status' => 'allocated'
        ]);

        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $smartphone->id,
            'tier_id' => $silverTier->id,
            'status' => 'active',
            'allocated_at' => now()->subMonths(6)
        ]);

        // Act 1: First maintenance check (should record violation)
        $maintenanceResult = $this->maintenanceService->checkAllocationMaintenance($allocation->id);

        // Assert 1: Violation recorded
        $this->assertEquals(1, $maintenanceResult['violated']);
        
        $allocation->refresh();
        $this->assertEquals('violated', $allocation->maintenance_status);
        $this->assertNotNull($allocation->last_maintenance_check);

        // Act 2: Second maintenance check (should forfeit asset)
        // Simulate repeated violation by updating maintenance status history
        $allocation->update([
            'maintenance_status' => 'violated',
            'last_maintenance_check' => now()->subMonths(2)
        ]);
        
        $maintenanceResult = $this->maintenanceService->checkAllocationMaintenance($allocation->id);

        // Assert 2: Asset forfeited
        $this->assertEquals(1, $maintenanceResult['forfeited']);
        
        $allocation->refresh();
        $smartphone->refresh();
        
        $this->assertEquals('forfeited', $allocation->status);
        $this->assertEquals('forfeited', $allocation->maintenance_status);
        $this->assertEquals('available', $smartphone->status);
        $this->assertNull($smartphone->owner_id);
        $this->assertNotNull($allocation->forfeited_at);
    }

    public function test_gold_member_motorbike_allocation_and_maintenance()
    {
        // Arrange: Create Gold member with qualifying stats
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $user = User::factory()->create([
            'investment_tier_id' => $goldTier->id,
            'monthly_team_volume' => 55000 // Above required 50000
        ]);

        // Create tier qualification (6 months at Gold tier)
        TierQualification::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $goldTier->id,
            'active_referrals' => 12,
            'team_volume' => 55000,
            'consecutive_months' => 6,
            'created_at' => now()->subMonths(6)
        ]);

        // Create sufficient referrals
        User::factory()->count(12)->create([
            'referrer_id' => $user->id,
            'investment_tier_id' => $goldTier->id
        ]);

        // Create available motorbike
        $motorbike = PhysicalReward::factory()->create([
            'type' => 'MOTORBIKE',
            'model' => 'Honda CB150',
            'value' => 12000,
            'status' => 'available',
            'tier_requirement' => 'Gold Member'
        ]);

        // Act: Process asset allocation
        $allocationResult = $this->allocationService->processUserAssetAllocation($user->id);

        // Assert: Motorbike allocated
        $this->assertTrue($allocationResult['success']);
        $this->assertEquals(1, $allocationResult['total_allocated']);
        
        $allocation = PhysicalRewardAllocation::where('user_id', $user->id)->first();
        $this->assertEquals('MOTORBIKE', $allocation->physicalReward->type);
        $this->assertEquals(12000, $allocation->physicalReward->value);
        $this->assertEquals('pending', $allocation->status);

        // Verify maintenance requirements are higher for Gold tier
        $maintenanceResult = $this->maintenanceService->checkAllocationMaintenance($allocation->id);
        $this->assertEquals(1, $maintenanceResult['maintained']);
        
        $allocation->refresh();
        $this->assertEquals('active', $allocation->status);
    }

    public function test_multiple_tier_asset_allocation_workflow()
    {
        // Arrange: Create users at different tiers
        $silverTier = InvestmentTier::where('name', 'Silver Member')->first();
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $diamondTier = InvestmentTier::where('name', 'Diamond Member')->first();

        $silverUser = User::factory()->create([
            'investment_tier_id' => $silverTier->id,
            'monthly_team_volume' => 16000
        ]);

        $goldUser = User::factory()->create([
            'investment_tier_id' => $goldTier->id,
            'monthly_team_volume' => 55000
        ]);

        $diamondUser = User::factory()->create([
            'investment_tier_id' => $diamondTier->id,
            'monthly_team_volume' => 200000
        ]);

        // Create tier qualifications
        TierQualification::factory()->create([
            'user_id' => $silverUser->id,
            'tier_id' => $silverTier->id,
            'consecutive_months' => 3,
            'created_at' => now()->subMonths(3)
        ]);

        TierQualification::factory()->create([
            'user_id' => $goldUser->id,
            'tier_id' => $goldTier->id,
            'consecutive_months' => 6,
            'created_at' => now()->subMonths(6)
        ]);

        TierQualification::factory()->create([
            'user_id' => $diamondUser->id,
            'tier_id' => $diamondTier->id,
            'consecutive_months' => 9,
            'created_at' => now()->subMonths(9)
        ]);

        // Create referrals
        User::factory()->count(4)->create(['referrer_id' => $silverUser->id]);
        User::factory()->count(12)->create(['referrer_id' => $goldUser->id]);
        User::factory()->count(30)->create(['referrer_id' => $diamondUser->id]);

        // Create available assets
        PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'value' => 3000,
            'status' => 'available',
            'tier_requirement' => 'Silver Member'
        ]);

        PhysicalReward::factory()->create([
            'type' => 'MOTORBIKE',
            'value' => 12000,
            'status' => 'available',
            'tier_requirement' => 'Gold Member'
        ]);

        PhysicalReward::factory()->create([
            'type' => 'CAR',
            'value' => 35000,
            'status' => 'available',
            'tier_requirement' => 'Diamond Member'
        ]);

        // Act: Process allocations for all eligible users
        $allocationResults = $this->allocationService->processAllEligibleAllocations();

        // Assert: All users received appropriate assets
        $this->assertEquals(3, $allocationResults['processed']);
        $this->assertEquals(3, $allocationResults['allocated']);
        $this->assertEquals(0, $allocationResults['failed']);

        // Verify specific allocations
        $silverAllocation = PhysicalRewardAllocation::where('user_id', $silverUser->id)->first();
        $goldAllocation = PhysicalRewardAllocation::where('user_id', $goldUser->id)->first();
        $diamondAllocation = PhysicalRewardAllocation::where('user_id', $diamondUser->id)->first();

        $this->assertEquals('SMARTPHONE', $silverAllocation->physicalReward->type);
        $this->assertEquals('MOTORBIKE', $goldAllocation->physicalReward->type);
        $this->assertEquals('CAR', $diamondAllocation->physicalReward->type);

        // Act: Process maintenance for all allocations
        $maintenanceResults = $this->maintenanceService->monitorAssetMaintenance();

        // Assert: All allocations maintained (users meet requirements)
        $this->assertEquals(3, $maintenanceResults['processed']);
        $this->assertEquals(3, $maintenanceResults['maintained']);
        $this->assertEquals(0, $maintenanceResults['violated']);
    }

    public function test_asset_buyback_workflow()
    {
        // Arrange: Create completed asset allocation
        $user = User::factory()->create();
        $smartphone = PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'value' => 3000,
            'status' => 'transferred',
            'owner_id' => $user->id
        ]);

        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $smartphone->id,
            'status' => 'completed',
            'completed_at' => now()->subMonths(6)
        ]);

        // Act: Create buyback offer
        $buybackResult = $this->allocationService->createBuybackOffer($allocation->id, 2200);

        // Assert: Buyback offer created successfully
        $this->assertTrue($buybackResult['success']);
        
        $offer = $buybackResult['buyback_offer'];
        $this->assertEquals($allocation->id, $offer['allocation_id']);
        $this->assertEquals($user->id, $offer['user_id']);
        $this->assertEquals('SMARTPHONE', $offer['asset_type']);
        $this->assertEquals(3000, $offer['original_value']);
        $this->assertEquals(2200, $offer['offer_amount']);
        $this->assertEquals('pending', $offer['status']);
    }

    public function test_asset_recovery_workflow()
    {
        // Arrange: Create forfeited asset
        $user = User::factory()->create();
        $smartphone = PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'value' => 3000,
            'status' => 'allocated',
            'owner_id' => $user->id
        ]);

        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $smartphone->id,
            'status' => 'forfeited',
            'forfeited_at' => now()->subDays(30)
        ]);

        // Act: Process asset recovery
        $recoveryResult = $this->allocationService->processAssetRecovery($allocation->id);

        // Assert: Asset recovered successfully
        $this->assertTrue($recoveryResult['success']);
        $this->assertEquals('Asset recovered and made available for reallocation', $recoveryResult['message']);

        // Verify database updates
        $allocation->refresh();
        $smartphone->refresh();

        $this->assertEquals('recovered', $allocation->status);
        $this->assertNotNull($allocation->recovered_at);
        $this->assertEquals('available', $smartphone->status);
        $this->assertNull($smartphone->owner_id);
    }

    public function test_comprehensive_asset_statistics_and_reporting()
    {
        // Arrange: Create various asset allocations
        $users = User::factory()->count(5)->create();
        $assets = PhysicalReward::factory()->count(5)->create(['value' => 5000]);

        // Create allocations with different statuses
        PhysicalRewardAllocation::factory()->create([
            'user_id' => $users[0]->id,
            'physical_reward_id' => $assets[0]->id,
            'status' => 'active'
        ]);

        PhysicalRewardAllocation::factory()->create([
            'user_id' => $users[1]->id,
            'physical_reward_id' => $assets[1]->id,
            'status' => 'completed'
        ]);

        PhysicalRewardAllocation::factory()->create([
            'user_id' => $users[2]->id,
            'physical_reward_id' => $assets[2]->id,
            'status' => 'forfeited'
        ]);

        PhysicalRewardAllocation::factory()->create([
            'user_id' => $users[3]->id,
            'physical_reward_id' => $assets[3]->id,
            'status' => 'pending'
        ]);

        // Act: Get comprehensive statistics
        $stats = $this->allocationService->getAssetAllocationStatistics();
        $report = $this->maintenanceService->generateMaintenanceReport();

        // Assert: Statistics are accurate
        $this->assertEquals(4, $stats['total_allocations']);
        $this->assertEquals(2, $stats['active_allocations']); // active + pending
        $this->assertEquals(1, $stats['completed_allocations']);
        $this->assertEquals(1, $stats['forfeited_allocations']);
        $this->assertEquals(25.0, $stats['completion_rate']);
        $this->assertEquals(25.0, $stats['forfeiture_rate']);
        $this->assertEquals(20000, $stats['total_asset_value']); // 4 * 5000

        // Assert: Report contains all sections
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('alerts', $report);
        $this->assertArrayHasKey('schedule', $report);
        $this->assertNotNull($report['generated_at']);
    }
}