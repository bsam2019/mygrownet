<?php

namespace Tests\Unit\Application\Services;

use Tests\TestCase;
use App\Application\Services\AssetAllocationService;
use App\Application\UseCases\Asset\ProcessAssetAllocationUseCase;
use App\Application\UseCases\Asset\ProcessAssetMaintenanceUseCase;
use App\Services\AssetIncomeTrackingService;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssetAllocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AssetAllocationService $service;
    private ProcessAssetAllocationUseCase $processAssetAllocationUseCase;
    private ProcessAssetMaintenanceUseCase $processAssetMaintenanceUseCase;
    private AssetIncomeTrackingService $assetIncomeTrackingService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->processAssetAllocationUseCase = $this->createMock(ProcessAssetAllocationUseCase::class);
        $this->processAssetMaintenanceUseCase = $this->createMock(ProcessAssetMaintenanceUseCase::class);
        $this->assetIncomeTrackingService = $this->createMock(AssetIncomeTrackingService::class);
        
        $this->service = new AssetAllocationService(
            $this->processAssetAllocationUseCase,
            $this->processAssetMaintenanceUseCase,
            $this->assetIncomeTrackingService
        );
    }

    public function test_process_user_asset_allocation_success()
    {
        // Arrange
        $userId = 1;
        $expectedResult = [
            'success' => true,
            'user_id' => $userId,
            'tier' => 'Silver Member',
            'allocations' => [
                [
                    'allocation_id' => 1,
                    'asset_type' => 'SMARTPHONE',
                    'asset_value' => 3000,
                    'maintenance_period' => 12,
                    'status' => 'pending'
                ]
            ],
            'total_allocated' => 1
        ];

        $this->processAssetAllocationUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($userId)
            ->willReturn($expectedResult);

        // Act
        $result = $this->service->processUserAssetAllocation($userId);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function test_process_user_asset_allocation_handles_exception()
    {
        // Arrange
        $userId = 1;
        $errorMessage = 'Asset allocation failed';

        $this->processAssetAllocationUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($userId)
            ->willThrowException(new \Exception($errorMessage));

        // Act
        $result = $this->service->processUserAssetAllocation($userId);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals($errorMessage, $result['error']);
    }

    public function test_process_all_eligible_allocations()
    {
        // Arrange
        $silverTier = InvestmentTier::factory()->create(['name' => 'Silver Member']);
        $goldTier = InvestmentTier::factory()->create(['name' => 'Gold Member']);
        
        $user1 = User::factory()->create([
            'investment_tier_id' => $silverTier->id,
            'monthly_team_volume' => 16000
        ]);
        
        $user2 = User::factory()->create([
            'investment_tier_id' => $goldTier->id,
            'monthly_team_volume' => 55000
        ]);

        // Create referrals to make users eligible
        User::factory()->count(3)->create(['referrer_id' => $user1->id]);
        User::factory()->count(12)->create(['referrer_id' => $user2->id]);

        $this->processAssetAllocationUseCase
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(
                [
                    'success' => true,
                    'user_id' => $user1->id,
                    'tier' => 'Silver Member',
                    'allocations' => [['allocation_id' => 1, 'asset_type' => 'SMARTPHONE']],
                    'total_allocated' => 1
                ],
                [
                    'success' => true,
                    'user_id' => $user2->id,
                    'tier' => 'Gold Member',
                    'allocations' => [['allocation_id' => 2, 'asset_type' => 'MOTORBIKE']],
                    'total_allocated' => 1
                ]
            );

        // Act
        $results = $this->service->processAllEligibleAllocations();

        // Assert
        $this->assertEquals(2, $results['processed']);
        $this->assertEquals(2, $results['allocated']);
        $this->assertEquals(0, $results['failed']);
        $this->assertCount(2, $results['allocations']);
    }

    public function test_get_user_asset_status()
    {
        // Arrange
        $silverTier = InvestmentTier::factory()->create(['name' => 'Silver Member']);
        $user = User::factory()->create(['investment_tier_id' => $silverTier->id]);
        
        $asset = PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'value' => 3000
        ]);
        
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'active'
        ]);

        $mockMaintenanceStatus = [
            [
                'allocation_id' => $allocation->id,
                'asset_type' => 'SMARTPHONE',
                'status' => 'active',
                'maintenance_status' => 'maintained'
            ]
        ];

        $mockIncomeTracking = [
            'total_income' => 500,
            'monthly_income' => 50
        ];

        $this->processAssetMaintenanceUseCase
            ->expects($this->once())
            ->method('getAssetMaintenanceStatus')
            ->with($user->id)
            ->willReturn($mockMaintenanceStatus);

        $this->assetIncomeTrackingService
            ->expects($this->once())
            ->method('getUserAssetIncome')
            ->with($user->id)
            ->willReturn($mockIncomeTracking);

        // Act
        $status = $this->service->getUserAssetStatus($user->id);

        // Assert
        $this->assertEquals($user->id, $status['user_id']);
        $this->assertEquals('Silver Member', $status['tier']);
        $this->assertEquals(1, $status['total_allocations']);
        $this->assertEquals(1, $status['active_allocations']);
        $this->assertEquals(0, $status['completed_allocations']);
        $this->assertEquals(3000, $status['total_asset_value']);
        $this->assertEquals($mockMaintenanceStatus, $status['maintenance_status']);
        $this->assertEquals($mockIncomeTracking, $status['income_tracking']);
    }

    public function test_get_available_assets()
    {
        // Arrange
        PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'model' => 'iPhone 14',
            'value' => 3000,
            'status' => 'available'
        ]);
        
        PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'model' => 'Samsung Galaxy',
            'value' => 2500,
            'status' => 'available'
        ]);
        
        PhysicalReward::factory()->create([
            'type' => 'MOTORBIKE',
            'model' => 'Honda CB150',
            'value' => 12000,
            'status' => 'available'
        ]);

        // Create an allocated asset (should not appear)
        PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'model' => 'Allocated Phone',
            'value' => 3500,
            'status' => 'allocated'
        ]);

        // Act
        $availableAssets = $this->service->getAvailableAssets();

        // Assert
        $this->assertArrayHasKey('SMARTPHONE', $availableAssets);
        $this->assertArrayHasKey('MOTORBIKE', $availableAssets);
        $this->assertArrayNotHasKey('ALLOCATED', $availableAssets);
        
        $smartphones = $availableAssets['SMARTPHONE'];
        $this->assertEquals('SMARTPHONE', $smartphones['type']);
        $this->assertEquals(2, $smartphones['count']);
        $this->assertEquals(2500, $smartphones['value_range']['min']);
        $this->assertEquals(3000, $smartphones['value_range']['max']);
        $this->assertCount(2, $smartphones['assets']);
    }

    public function test_create_buyback_offer_success()
    {
        // Arrange
        $user = User::factory()->create();
        $asset = PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'value' => 3000
        ]);
        
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'completed',
            'completed_at' => now()->subMonths(6)
        ]);

        $offerAmount = 2000; // Within acceptable range

        // Act
        $result = $this->service->createBuybackOffer($allocation->id, $offerAmount);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('buyback_offer', $result);
        $this->assertEquals($allocation->id, $result['buyback_offer']['allocation_id']);
        $this->assertEquals($offerAmount, $result['buyback_offer']['offer_amount']);
    }

    public function test_create_buyback_offer_not_completed()
    {
        // Arrange
        $user = User::factory()->create();
        $asset = PhysicalReward::factory()->create(['value' => 3000]);
        
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'active' // Not completed
        ]);

        // Act
        $result = $this->service->createBuybackOffer($allocation->id, 2000);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Asset must be completed to be eligible for buyback', $result['error']);
    }

    public function test_create_buyback_offer_amount_too_high()
    {
        // Arrange
        $user = User::factory()->create();
        $asset = PhysicalReward::factory()->create(['value' => 3000]);
        
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'completed',
            'completed_at' => now()->subMonths(6)
        ]);

        $offerAmount = 2800; // Too high (over 90% of market value)

        // Act
        $result = $this->service->createBuybackOffer($allocation->id, $offerAmount);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContains('exceeds maximum buyback value', $result['error']);
    }

    public function test_get_asset_allocation_statistics()
    {
        // Arrange
        $silverTier = InvestmentTier::factory()->create(['name' => 'Silver Member']);
        $goldTier = InvestmentTier::factory()->create(['name' => 'Gold Member']);
        
        $smartphone = PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'value' => 3000
        ]);
        
        $motorbike = PhysicalReward::factory()->create([
            'type' => 'MOTORBIKE',
            'value' => 12000
        ]);

        // Create allocations with different statuses
        PhysicalRewardAllocation::factory()->create([
            'physical_reward_id' => $smartphone->id,
            'tier_id' => $silverTier->id,
            'status' => 'active'
        ]);
        
        PhysicalRewardAllocation::factory()->create([
            'physical_reward_id' => $motorbike->id,
            'tier_id' => $goldTier->id,
            'status' => 'completed'
        ]);
        
        PhysicalRewardAllocation::factory()->create([
            'physical_reward_id' => $smartphone->id,
            'tier_id' => $silverTier->id,
            'status' => 'forfeited'
        ]);

        // Act
        $stats = $this->service->getAssetAllocationStatistics();

        // Assert
        $this->assertEquals(3, $stats['total_allocations']);
        $this->assertEquals(1, $stats['active_allocations']);
        $this->assertEquals(1, $stats['completed_allocations']);
        $this->assertEquals(1, $stats['forfeited_allocations']);
        $this->assertEquals(33.33, round($stats['completion_rate'], 2));
        $this->assertEquals(33.33, round($stats['forfeiture_rate'], 2));
        $this->assertEquals(18000, $stats['total_asset_value']); // 3000 + 12000 + 3000
    }

    public function test_process_asset_recovery()
    {
        // Arrange
        $user = User::factory()->create();
        $asset = PhysicalReward::factory()->create([
            'status' => 'allocated',
            'owner_id' => $user->id
        ]);
        
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'forfeited'
        ]);

        // Act
        $result = $this->service->processAssetRecovery($allocation->id);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Asset recovered and made available for reallocation', $result['message']);
        
        // Verify database updates
        $asset->refresh();
        $allocation->refresh();
        
        $this->assertEquals('available', $asset->status);
        $this->assertNull($asset->owner_id);
        $this->assertEquals('recovered', $allocation->status);
        $this->assertNotNull($allocation->recovered_at);
    }

    public function test_process_asset_recovery_not_forfeited()
    {
        // Arrange
        $allocation = PhysicalRewardAllocation::factory()->create([
            'status' => 'active' // Not forfeited
        ]);

        // Act
        $result = $this->service->processAssetRecovery($allocation->id);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Only forfeited assets can be recovered', $result['error']);
    }
}