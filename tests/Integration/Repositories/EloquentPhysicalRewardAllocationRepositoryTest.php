<?php

namespace Tests\Integration\Repositories;

use Tests\TestCase;
use App\Infrastructure\Persistence\Repositories\EloquentPhysicalRewardAllocationRepository;
use App\Domain\Reward\Entities\PhysicalRewardAllocation;
use App\Domain\Reward\ValueObjects\RewardAllocationId;
use App\Domain\Reward\ValueObjects\RewardId;
use App\Domain\Reward\ValueObjects\AllocationStatus;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;
use App\Models\User;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation as PhysicalRewardAllocationModel;
use App\Models\InvestmentTier;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EloquentPhysicalRewardAllocationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentPhysicalRewardAllocationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentPhysicalRewardAllocationRepository();
    }

    public function test_can_find_allocation_by_id()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $asset = PhysicalReward::create([
            'name' => 'Test Asset',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'available_quantity' => 10,
            'is_active' => true
        ]);

        $allocation = PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'team_volume_at_allocation' => 25000.00,
            'active_referrals_at_allocation' => 12,
            'team_depth_at_allocation' => 3,
            'status' => 'allocated',
            'allocated_at' => now(),
            'maintenance_compliant' => true,
            'maintenance_months_completed' => 0,
            'total_income_generated' => 0,
            'monthly_income_average' => 0
        ]);

        $found = $this->repository->findById(RewardAllocationId::fromInt($allocation->id));

        $this->assertNotNull($found);
        $this->assertEquals($allocation->id, $found->getId()->value());
        $this->assertEquals($user->id, $found->getUserId()->value());
        $this->assertEquals($asset->id, $found->getRewardId()->value());
        $this->assertTrue($found->getStatus()->isAllocated());
        $this->assertEquals(25000.00, $found->getPerformanceAtAllocation()['team_volume']);
        $this->assertEquals(12, $found->getPerformanceAtAllocation()['active_referrals']);
    }

    public function test_can_find_allocations_by_user_id()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $asset1 = PhysicalReward::create([
            'name' => 'Asset 1',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'available_quantity' => 10,
            'is_active' => true
        ]);

        $asset2 = PhysicalReward::create([
            'name' => 'Asset 2',
            'category' => 'TABLET',
            'estimated_value' => 2500.00,
            'available_quantity' => 8,
            'is_active' => true
        ]);

        PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset1->id,
            'team_volume_at_allocation' => 20000.00,
            'active_referrals_at_allocation' => 10,
            'status' => 'allocated',
            'allocated_at' => now()->subDays(5)
        ]);

        PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset2->id,
            'team_volume_at_allocation' => 30000.00,
            'active_referrals_at_allocation' => 15,
            'status' => 'delivered',
            'allocated_at' => now()->subDays(3),
            'delivered_at' => now()->subDays(1)
        ]);

        $allocations = $this->repository->findByUserId(UserId::fromInt($user->id));

        $this->assertCount(2, $allocations);
        $this->assertEquals($user->id, $allocations[0]->getUserId()->value());
        $this->assertEquals($user->id, $allocations[1]->getUserId()->value());
        
        // Should be ordered by allocated_at desc (most recent first)
        $this->assertTrue($allocations[0]->getStatus()->isDelivered());
        $this->assertTrue($allocations[1]->getStatus()->isAllocated());
    }

    public function test_can_find_allocations_by_status()
    {
        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password')
        ]);

        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password')
        ]);

        $asset = PhysicalReward::create([
            'name' => 'Test Asset',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'available_quantity' => 10,
            'is_active' => true
        ]);

        PhysicalRewardAllocationModel::create([
            'user_id' => $user1->id,
            'physical_reward_id' => $asset->id,
            'team_volume_at_allocation' => 20000.00,
            'active_referrals_at_allocation' => 10,
            'status' => 'delivered',
            'allocated_at' => now()->subDays(10),
            'delivered_at' => now()->subDays(5)
        ]);

        PhysicalRewardAllocationModel::create([
            'user_id' => $user2->id,
            'physical_reward_id' => $asset->id,
            'team_volume_at_allocation' => 25000.00,
            'active_referrals_at_allocation' => 12,
            'status' => 'delivered',
            'allocated_at' => now()->subDays(8),
            'delivered_at' => now()->subDays(3)
        ]);

        PhysicalRewardAllocationModel::create([
            'user_id' => $user1->id,
            'physical_reward_id' => $asset->id,
            'team_volume_at_allocation' => 15000.00,
            'active_referrals_at_allocation' => 8,
            'status' => 'allocated',
            'allocated_at' => now()->subDays(2)
        ]);

        $deliveredAllocations = $this->repository->findByStatus(AllocationStatus::delivered());

        $this->assertCount(2, $deliveredAllocations);
        foreach ($deliveredAllocations as $allocation) {
            $this->assertTrue($allocation->getStatus()->isDelivered());
        }
    }

    public function test_can_find_allocations_requiring_maintenance_check()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $asset = PhysicalReward::create([
            'name' => 'Maintenance Asset',
            'category' => 'PROPERTY',
            'estimated_value' => 100000.00,
            'requires_performance_maintenance' => true,
            'maintenance_period_months' => 12,
            'available_quantity' => 5,
            'is_active' => true
        ]);

        // Allocation needing maintenance check (no previous check)
        PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'team_volume_at_allocation' => 50000.00,
            'active_referrals_at_allocation' => 25,
            'status' => 'delivered',
            'allocated_at' => now()->subMonths(2),
            'delivered_at' => now()->subMonths(1),
            'maintenance_compliant' => true,
            'last_maintenance_check' => null
        ]);

        // Allocation needing maintenance check (old check)
        PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'team_volume_at_allocation' => 60000.00,
            'active_referrals_at_allocation' => 30,
            'status' => 'delivered',
            'allocated_at' => now()->subMonths(3),
            'delivered_at' => now()->subMonths(2),
            'maintenance_compliant' => true,
            'last_maintenance_check' => now()->subMonths(2)
        ]);

        // Recent maintenance check - should not be included
        PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'team_volume_at_allocation' => 40000.00,
            'active_referrals_at_allocation' => 20,
            'status' => 'delivered',
            'allocated_at' => now()->subMonths(1),
            'delivered_at' => now()->subWeeks(2),
            'maintenance_compliant' => true,
            'last_maintenance_check' => now()->subDays(15)
        ]);

        $maintenanceDue = $this->repository->findRequiringMaintenanceCheck();

        $this->assertCount(2, $maintenanceDue);
    }

    public function test_can_find_allocations_eligible_for_ownership_transfer()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $asset = PhysicalReward::create([
            'name' => 'Transferable Asset',
            'category' => 'CAR',
            'estimated_value' => 40000.00,
            'requires_performance_maintenance' => true,
            'maintenance_period_months' => 12,
            'available_quantity' => 3,
            'is_active' => true
        ]);

        // Eligible for transfer (completed maintenance period)
        PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'team_volume_at_allocation' => 80000.00,
            'active_referrals_at_allocation' => 40,
            'status' => 'delivered',
            'allocated_at' => now()->subMonths(15),
            'delivered_at' => now()->subMonths(14),
            'maintenance_compliant' => true,
            'maintenance_months_completed' => 12
        ]);

        // Not eligible - insufficient maintenance months
        PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'team_volume_at_allocation' => 70000.00,
            'active_referrals_at_allocation' => 35,
            'status' => 'delivered',
            'allocated_at' => now()->subMonths(8),
            'delivered_at' => now()->subMonths(7),
            'maintenance_compliant' => true,
            'maintenance_months_completed' => 6
        ]);

        $eligibleForTransfer = $this->repository->findEligibleForOwnershipTransfer();

        $this->assertCount(1, $eligibleForTransfer);
        $this->assertEquals(12, $eligibleForTransfer[0]->getMaintenanceStatus()->getMonthsCompleted());
    }

    public function test_can_check_if_user_has_allocation_for_reward()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $asset1 = PhysicalReward::create([
            'name' => 'Asset 1',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'available_quantity' => 10,
            'is_active' => true
        ]);

        $asset2 = PhysicalReward::create([
            'name' => 'Asset 2',
            'category' => 'TABLET',
            'estimated_value' => 2500.00,
            'available_quantity' => 8,
            'is_active' => true
        ]);

        PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset1->id,
            'team_volume_at_allocation' => 20000.00,
            'active_referrals_at_allocation' => 10,
            'status' => 'allocated',
            'allocated_at' => now()
        ]);

        $hasAllocation1 = $this->repository->userHasAllocationForReward(
            UserId::fromInt($user->id),
            RewardId::fromInt($asset1->id)
        );

        $hasAllocation2 = $this->repository->userHasAllocationForReward(
            UserId::fromInt($user->id),
            RewardId::fromInt($asset2->id)
        );

        $this->assertTrue($hasAllocation1);
        $this->assertFalse($hasAllocation2);
    }

    public function test_can_get_allocation_statistics()
    {
        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password')
        ]);

        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password')
        ]);

        $smartphone = PhysicalReward::create([
            'name' => 'Smartphone',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'income_generating' => false,
            'available_quantity' => 10,
            'is_active' => true
        ]);

        $property = PhysicalReward::create([
            'name' => 'Property',
            'category' => 'PROPERTY',
            'estimated_value' => 100000.00,
            'income_generating' => true,
            'estimated_monthly_income' => 5000.00,
            'available_quantity' => 2,
            'is_active' => true
        ]);

        // Delivered smartphone allocation
        PhysicalRewardAllocationModel::create([
            'user_id' => $user1->id,
            'physical_reward_id' => $smartphone->id,
            'team_volume_at_allocation' => 20000.00,
            'active_referrals_at_allocation' => 10,
            'status' => 'delivered',
            'allocated_at' => now()->subMonths(2),
            'delivered_at' => now()->subMonth(),
            'maintenance_compliant' => true,
            'total_income_generated' => 0
        ]);

        // Property allocation with income
        PhysicalRewardAllocationModel::create([
            'user_id' => $user2->id,
            'physical_reward_id' => $property->id,
            'team_volume_at_allocation' => 80000.00,
            'active_referrals_at_allocation' => 40,
            'status' => 'delivered',
            'allocated_at' => now()->subMonths(6),
            'delivered_at' => now()->subMonths(5),
            'maintenance_compliant' => true,
            'maintenance_months_completed' => 5,
            'total_income_generated' => 25000.00,
            'monthly_income_average' => 5000.00
        ]);

        // Forfeited allocation
        PhysicalRewardAllocationModel::create([
            'user_id' => $user1->id,
            'physical_reward_id' => $smartphone->id,
            'team_volume_at_allocation' => 15000.00,
            'active_referrals_at_allocation' => 8,
            'status' => 'forfeited',
            'allocated_at' => now()->subMonths(3),
            'forfeited_at' => now()->subMonth(),
            'maintenance_compliant' => false,
            'total_income_generated' => 0
        ]);

        $stats = $this->repository->getStatistics();

        $this->assertEquals(3, $stats['total_allocations']);
        
        $this->assertArrayHasKey('by_status', $stats);
        $this->assertEquals(2, $stats['by_status']['delivered']['count']);
        $this->assertEquals(1, $stats['by_status']['forfeited']['count']);
        
        $this->assertArrayHasKey('income_metrics', $stats);
        $this->assertEquals(25000.00, $stats['income_metrics']['total_income_generated']);
        $this->assertEquals(1, $stats['income_metrics']['income_generating_allocations']);
        
        $this->assertArrayHasKey('maintenance_metrics', $stats);
        $this->assertEquals(1, $stats['maintenance_metrics']['compliant_count']);
        $this->assertEquals(1, $stats['maintenance_metrics']['non_compliant_count']);
        
        $this->assertArrayHasKey('by_reward_type', $stats);
        $this->assertEquals(2, $stats['by_reward_type']['SMARTPHONE']['allocation_count']);
        $this->assertEquals(1, $stats['by_reward_type']['PROPERTY']['allocation_count']);
    }

    public function test_can_get_user_income_report()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $property1 = PhysicalReward::create([
            'name' => 'Property 1',
            'category' => 'PROPERTY',
            'estimated_value' => 80000.00,
            'income_generating' => true,
            'estimated_monthly_income' => 4000.00,
            'available_quantity' => 3,
            'is_active' => true
        ]);

        $property2 = PhysicalReward::create([
            'name' => 'Property 2',
            'category' => 'PROPERTY',
            'estimated_value' => 120000.00,
            'income_generating' => true,
            'estimated_monthly_income' => 6000.00,
            'available_quantity' => 2,
            'is_active' => true
        ]);

        $motorbike = PhysicalReward::create([
            'name' => 'Motorbike',
            'category' => 'MOTORBIKE',
            'estimated_value' => 12000.00,
            'income_generating' => true,
            'estimated_monthly_income' => 800.00,
            'available_quantity' => 5,
            'is_active' => true
        ]);

        // Property 1 allocation with good income
        PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $property1->id,
            'team_volume_at_allocation' => 60000.00,
            'active_referrals_at_allocation' => 30,
            'status' => 'delivered',
            'allocated_at' => now()->subMonths(8),
            'delivered_at' => now()->subMonths(7),
            'total_income_generated' => 28000.00,
            'monthly_income_average' => 4000.00,
            'income_tracking_started' => now()->subMonths(7)
        ]);

        // Property 2 allocation with excellent income
        PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $property2->id,
            'team_volume_at_allocation' => 100000.00,
            'active_referrals_at_allocation' => 50,
            'status' => 'ownership_transferred',
            'allocated_at' => now()->subMonths(12),
            'delivered_at' => now()->subMonths(11),
            'ownership_transferred_at' => now()->subMonth(),
            'total_income_generated' => 66000.00,
            'monthly_income_average' => 6600.00,
            'income_tracking_started' => now()->subMonths(10)
        ]);

        // Motorbike allocation with lower income
        PhysicalRewardAllocationModel::create([
            'user_id' => $user->id,
            'physical_reward_id' => $motorbike->id,
            'team_volume_at_allocation' => 25000.00,
            'active_referrals_at_allocation' => 15,
            'status' => 'delivered',
            'allocated_at' => now()->subMonths(4),
            'delivered_at' => now()->subMonths(3),
            'total_income_generated' => 2400.00,
            'monthly_income_average' => 800.00,
            'income_tracking_started' => now()->subMonths(3)
        ]);

        $incomeReport = $this->repository->getUserIncomeReport(UserId::fromInt($user->id));

        $this->assertEquals($user->id, $incomeReport['user_id']);
        $this->assertEquals(3, $incomeReport['total_allocations']);
        $this->assertEquals(3, $incomeReport['income_generating_allocations']);
        $this->assertEquals(96400.00, $incomeReport['total_income_generated']); // 28000 + 66000 + 2400
        
        $this->assertArrayHasKey('income_by_asset_type', $incomeReport);
        $this->assertArrayHasKey('asset_performance', $incomeReport);
        $this->assertArrayHasKey('roi_analysis', $incomeReport);
        
        // Check ROI analysis
        $expectedTotalAssetValue = 80000 + 120000 + 12000; // 212000
        $this->assertEquals($expectedTotalAssetValue, $incomeReport['roi_analysis']['total_asset_value']);
        $this->assertEquals(96400.00, $incomeReport['roi_analysis']['total_income_generated']);
        
        $expectedROI = (96400 / 212000) * 100; // ~45.47%
        $this->assertEqualsWithDelta($expectedROI, $incomeReport['roi_analysis']['overall_roi_percentage'], 0.1);
    }

    public function test_repository_performance_with_large_dataset()
    {
        $users = User::factory()->count(50)->create();
        $assets = PhysicalReward::factory()->count(20)->create();

        // Create 200 allocations
        foreach (range(1, 200) as $i) {
            PhysicalRewardAllocationModel::create([
                'user_id' => $users->random()->id,
                'physical_reward_id' => $assets->random()->id,
                'team_volume_at_allocation' => rand(10000, 100000),
                'active_referrals_at_allocation' => rand(5, 50),
                'status' => ['allocated', 'delivered', 'ownership_transferred', 'forfeited'][rand(0, 3)],
                'allocated_at' => now()->subDays(rand(1, 365)),
                'maintenance_compliant' => rand(0, 1) === 1,
                'maintenance_months_completed' => rand(0, 12),
                'total_income_generated' => rand(0, 50000),
                'monthly_income_average' => rand(0, 5000)
            ]);
        }

        $startTime = microtime(true);
        
        $stats = $this->repository->getStatistics();
        $deliveredAllocations = $this->repository->findByStatus(AllocationStatus::delivered());
        $maintenanceDue = $this->repository->findRequiringMaintenanceCheck();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertNotEmpty($stats);
        $this->assertIsArray($deliveredAllocations);
        $this->assertIsArray($maintenanceDue);
        $this->assertLessThan(3.0, $executionTime, 'Allocation repository queries should complete within 3 seconds');
    }
}