<?php

namespace Tests\Integration\Repositories;

use Tests\TestCase;
use App\Infrastructure\Persistence\Repositories\EloquentAssetRepository;
use App\Domain\Reward\ValueObjects\RewardId;
use App\Domain\Reward\ValueObjects\AssetType;
use App\Domain\Reward\ValueObjects\AssetValue;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;
use App\Models\User;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EloquentAssetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentAssetRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentAssetRepository();
    }

    public function test_can_find_asset_by_id()
    {
        $asset = PhysicalReward::create([
            'name' => 'Test Smartphone',
            'description' => 'High-end smartphone reward',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'required_membership_tiers' => ['Silver', 'Gold'],
            'required_referrals' => 5,
            'required_subscription_amount' => 300.00,
            'required_sustained_months' => 3,
            'required_team_volume' => 15000.00,
            'required_team_depth' => 2,
            'maintenance_period_months' => 12,
            'requires_performance_maintenance' => false,
            'income_generating' => false,
            'estimated_monthly_income' => 0,
            'available_quantity' => 10,
            'allocated_quantity' => 2,
            'is_active' => true
        ]);

        $found = $this->repository->findById(RewardId::fromInt($asset->id));

        $this->assertNotNull($found);
        $this->assertEquals($asset->id, $found['id']);
        $this->assertEquals('Test Smartphone', $found['name']);
        $this->assertEquals('SMARTPHONE', $found['category']);
        $this->assertEquals(3000.00, $found['estimated_value']);
        $this->assertEquals(8, $found['remaining_quantity']); // 10 - 2
        $this->assertTrue($found['is_available']);
    }

    public function test_can_find_assets_by_type()
    {
        PhysicalReward::create([
            'name' => 'iPhone 15',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3500.00,
            'required_membership_tiers' => ['Silver'],
            'required_referrals' => 5,
            'available_quantity' => 5,
            'allocated_quantity' => 1,
            'is_active' => true
        ]);

        PhysicalReward::create([
            'name' => 'Samsung Galaxy',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'required_membership_tiers' => ['Silver'],
            'required_referrals' => 5,
            'available_quantity' => 8,
            'allocated_quantity' => 2,
            'is_active' => true
        ]);

        PhysicalReward::create([
            'name' => 'Honda Motorbike',
            'category' => 'MOTORBIKE',
            'estimated_value' => 12000.00,
            'required_membership_tiers' => ['Gold'],
            'required_referrals' => 15,
            'available_quantity' => 3,
            'allocated_quantity' => 0,
            'is_active' => true
        ]);

        $smartphones = $this->repository->findByType(AssetType::smartphone());

        $this->assertCount(2, $smartphones);
        $this->assertEquals('iPhone 15', $smartphones[0]['name']);
        $this->assertEquals('Samsung Galaxy', $smartphones[1]['name']);
    }

    public function test_can_find_available_assets()
    {
        // Available asset
        PhysicalReward::create([
            'name' => 'Available Asset',
            'category' => 'SMARTPHONE',
            'estimated_value' => 2000.00,
            'required_membership_tiers' => ['Bronze'],
            'required_referrals' => 3,
            'available_quantity' => 10,
            'allocated_quantity' => 5,
            'is_active' => true
        ]);

        // Fully allocated asset
        PhysicalReward::create([
            'name' => 'Fully Allocated Asset',
            'category' => 'TABLET',
            'estimated_value' => 2500.00,
            'required_membership_tiers' => ['Bronze'],
            'required_referrals' => 3,
            'available_quantity' => 5,
            'allocated_quantity' => 5,
            'is_active' => true
        ]);

        // Inactive asset
        PhysicalReward::create([
            'name' => 'Inactive Asset',
            'category' => 'SMARTPHONE',
            'estimated_value' => 1500.00,
            'required_membership_tiers' => ['Bronze'],
            'required_referrals' => 3,
            'available_quantity' => 10,
            'allocated_quantity' => 2,
            'is_active' => false
        ]);

        $availableAssets = $this->repository->findAvailableAssets();

        $this->assertCount(1, $availableAssets);
        $this->assertEquals('Available Asset', $availableAssets[0]['name']);
        $this->assertTrue($availableAssets[0]['is_available']);
    }

    public function test_can_check_user_eligibility()
    {
        $tier = InvestmentTier::create([
            'name' => 'Silver',
            'minimum_amount' => 300,
            'monthly_fee' => 300.00,
            'profit_share_percentage' => 5.0,
            'is_active' => true
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'current_investment_tier_id' => $tier->id
        ]);

        TeamVolume::create([
            'user_id' => $user->id,
            'personal_volume' => 5000.00,
            'team_volume' => 20000.00,
            'team_depth' => 3,
            'active_referrals_count' => 8,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        $asset = PhysicalReward::create([
            'name' => 'Eligible Asset',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'required_membership_tiers' => ['Silver', 'Gold'],
            'required_referrals' => 5,
            'required_subscription_amount' => 300.00,
            'required_sustained_months' => 1,
            'required_team_volume' => 15000.00,
            'required_team_depth' => 2,
            'available_quantity' => 10,
            'allocated_quantity' => 2,
            'is_active' => true
        ]);

        $isEligible = $this->repository->checkUserEligibility(
            UserId::fromInt($user->id),
            RewardId::fromInt($asset->id),
            TeamVolumeAmount::fromFloat(20000.00),
            8,
            2
        );

        $this->assertTrue($isEligible);
    }

    public function test_can_get_eligible_assets_for_user()
    {
        $tier = InvestmentTier::create([
            'name' => 'Gold',
            'minimum_amount' => 500,
            'monthly_fee' => 500.00,
            'profit_share_percentage' => 8.0,
            'is_active' => true
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'current_investment_tier_id' => $tier->id
        ]);

        // Eligible asset
        PhysicalReward::create([
            'name' => 'Eligible Smartphone',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'required_membership_tiers' => ['Gold', 'Diamond'],
            'required_referrals' => 10,
            'required_subscription_amount' => 500.00,
            'required_sustained_months' => 3,
            'required_team_volume' => 30000.00,
            'required_team_depth' => 3,
            'available_quantity' => 5,
            'allocated_quantity' => 1,
            'is_active' => true
        ]);

        // Not eligible - too high requirements
        PhysicalReward::create([
            'name' => 'High Requirement Asset',
            'category' => 'CAR',
            'estimated_value' => 50000.00,
            'required_membership_tiers' => ['Elite'],
            'required_referrals' => 50,
            'required_subscription_amount' => 1500.00,
            'required_sustained_months' => 12,
            'required_team_volume' => 200000.00,
            'required_team_depth' => 5,
            'available_quantity' => 2,
            'allocated_quantity' => 0,
            'is_active' => true
        ]);

        $eligibleAssets = $this->repository->getEligibleAssetsForUser(
            UserId::fromInt($user->id),
            'Gold',
            TeamVolumeAmount::fromFloat(35000.00),
            15,
            6
        );

        $this->assertCount(1, $eligibleAssets);
        $this->assertEquals('Eligible Smartphone', $eligibleAssets[0]['name']);
    }

    public function test_can_reserve_and_release_asset()
    {
        $asset = PhysicalReward::create([
            'name' => 'Reservable Asset',
            'category' => 'TABLET',
            'estimated_value' => 2500.00,
            'required_membership_tiers' => ['Silver'],
            'required_referrals' => 5,
            'available_quantity' => 10,
            'allocated_quantity' => 3,
            'is_active' => true
        ]);

        // Reserve asset
        $reserved = $this->repository->reserveAsset(RewardId::fromInt($asset->id));
        $this->assertTrue($reserved);

        // Check allocation count increased
        $asset->refresh();
        $this->assertEquals(4, $asset->allocated_quantity);

        // Release reservation
        $released = $this->repository->releaseAssetReservation(RewardId::fromInt($asset->id));
        $this->assertTrue($released);

        // Check allocation count decreased
        $asset->refresh();
        $this->assertEquals(3, $asset->allocated_quantity);
    }

    public function test_can_get_asset_allocation_stats()
    {
        // Create assets with different categories
        PhysicalReward::create([
            'name' => 'Smartphone 1',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'available_quantity' => 10,
            'allocated_quantity' => 6,
            'is_active' => true
        ]);

        PhysicalReward::create([
            'name' => 'Smartphone 2',
            'category' => 'SMARTPHONE',
            'estimated_value' => 2500.00,
            'available_quantity' => 8,
            'allocated_quantity' => 4,
            'is_active' => true
        ]);

        PhysicalReward::create([
            'name' => 'Motorbike 1',
            'category' => 'MOTORBIKE',
            'estimated_value' => 12000.00,
            'available_quantity' => 5,
            'allocated_quantity' => 2,
            'is_active' => true
        ]);

        $stats = $this->repository->getAssetAllocationStats();

        $this->assertArrayHasKey('by_category', $stats);
        $this->assertArrayHasKey('overall', $stats);
        
        $this->assertArrayHasKey('SMARTPHONE', $stats['by_category']);
        $this->assertArrayHasKey('MOTORBIKE', $stats['by_category']);
        
        $this->assertEquals(2, $stats['by_category']['SMARTPHONE']['total_assets']);
        $this->assertEquals(18, $stats['by_category']['SMARTPHONE']['total_available']);
        $this->assertEquals(10, $stats['by_category']['SMARTPHONE']['total_allocated']);
        
        $this->assertEquals(3, $stats['overall']['total_assets']);
        $this->assertEquals(23, $stats['overall']['total_available']);
        $this->assertEquals(12, $stats['overall']['total_allocated']);
    }

    public function test_can_find_assets_by_value_range()
    {
        PhysicalReward::create([
            'name' => 'Low Value Asset',
            'category' => 'STARTER_KIT',
            'estimated_value' => 500.00,
            'available_quantity' => 20,
            'is_active' => true
        ]);

        PhysicalReward::create([
            'name' => 'Medium Value Asset',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'available_quantity' => 10,
            'is_active' => true
        ]);

        PhysicalReward::create([
            'name' => 'High Value Asset',
            'category' => 'CAR',
            'estimated_value' => 45000.00,
            'available_quantity' => 2,
            'is_active' => true
        ]);

        $mediumValueAssets = $this->repository->findByValueRange(
            AssetValue::fromFloat(2000.00),
            AssetValue::fromFloat(10000.00)
        );

        $this->assertCount(1, $mediumValueAssets);
        $this->assertEquals('Medium Value Asset', $mediumValueAssets[0]['name']);
        $this->assertEquals(3000.00, $mediumValueAssets[0]['estimated_value']);
    }

    public function test_can_get_low_stock_alerts()
    {
        // Low stock asset
        PhysicalReward::create([
            'name' => 'Low Stock Asset',
            'category' => 'SMARTPHONE',
            'estimated_value' => 3000.00,
            'available_quantity' => 10,
            'allocated_quantity' => 8, // Only 2 remaining
            'is_active' => true
        ]);

        // Critical stock asset
        PhysicalReward::create([
            'name' => 'Critical Stock Asset',
            'category' => 'TABLET',
            'estimated_value' => 2500.00,
            'available_quantity' => 5,
            'allocated_quantity' => 5, // 0 remaining
            'is_active' => true
        ]);

        // Good stock asset
        PhysicalReward::create([
            'name' => 'Good Stock Asset',
            'category' => 'MOTORBIKE',
            'estimated_value' => 12000.00,
            'available_quantity' => 20,
            'allocated_quantity' => 5, // 15 remaining
            'is_active' => true
        ]);

        $lowStockAlerts = $this->repository->getLowStockAlerts(5);

        $this->assertCount(2, $lowStockAlerts);
        
        // Should be ordered by remaining quantity (lowest first)
        $this->assertEquals('Critical Stock Asset', $lowStockAlerts[0]['name']);
        $this->assertEquals(0, $lowStockAlerts[0]['remaining_quantity']);
        $this->assertEquals('CRITICAL', $lowStockAlerts[0]['urgency_level']);
        
        $this->assertEquals('Low Stock Asset', $lowStockAlerts[1]['name']);
        $this->assertEquals(2, $lowStockAlerts[1]['remaining_quantity']);
        $this->assertEquals('HIGH', $lowStockAlerts[1]['urgency_level']);
    }

    public function test_can_get_asset_performance_metrics()
    {
        $asset = PhysicalReward::create([
            'name' => 'Performance Test Asset',
            'category' => 'PROPERTY',
            'estimated_value' => 100000.00,
            'income_generating' => true,
            'estimated_monthly_income' => 5000.00,
            'available_quantity' => 10,
            'allocated_quantity' => 5,
            'is_active' => true
        ]);

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

        // Create allocations with different statuses
        PhysicalRewardAllocation::create([
            'user_id' => $user1->id,
            'physical_reward_id' => $asset->id,
            'status' => 'delivered',
            'team_volume_at_allocation' => 50000.00,
            'active_referrals_at_allocation' => 20,
            'allocated_at' => now()->subMonths(6),
            'delivered_at' => now()->subMonths(5),
            'maintenance_compliant' => true,
            'maintenance_months_completed' => 5,
            'total_income_generated' => 25000.00,
            'monthly_income_average' => 5000.00
        ]);

        PhysicalRewardAllocation::create([
            'user_id' => $user2->id,
            'physical_reward_id' => $asset->id,
            'status' => 'forfeited',
            'team_volume_at_allocation' => 30000.00,
            'active_referrals_at_allocation' => 15,
            'allocated_at' => now()->subMonths(3),
            'forfeited_at' => now()->subMonth(),
            'maintenance_compliant' => false,
            'maintenance_months_completed' => 1,
            'total_income_generated' => 2000.00,
            'monthly_income_average' => 1000.00
        ]);

        $metrics = $this->repository->getAssetPerformanceMetrics(RewardId::fromInt($asset->id));

        $this->assertEquals($asset->id, $metrics['asset_id']);
        $this->assertEquals('Performance Test Asset', $metrics['asset_name']);
        $this->assertEquals(2, $metrics['total_allocations']);
        
        $this->assertEquals(50.0, $metrics['performance_metrics']['delivery_rate']); // 1 delivered out of 2
        $this->assertEquals(0.0, $metrics['performance_metrics']['ownership_transfer_rate']); // 0 transferred
        $this->assertEquals(50.0, $metrics['performance_metrics']['forfeiture_rate']); // 1 forfeited out of 2
        $this->assertEquals(27000.00, $metrics['performance_metrics']['total_income_generated']); // 25000 + 2000
        $this->assertEquals(3000.00, $metrics['performance_metrics']['avg_monthly_income']); // (5000 + 1000) / 2
    }

    public function test_repository_performance_with_large_dataset()
    {
        // Create 100 assets
        for ($i = 1; $i <= 100; $i++) {
            PhysicalReward::create([
                'name' => "Asset {$i}",
                'category' => ['SMARTPHONE', 'TABLET', 'MOTORBIKE', 'CAR', 'PROPERTY'][($i - 1) % 5],
                'estimated_value' => rand(1000, 50000),
                'required_membership_tiers' => [['Bronze'], ['Silver'], ['Gold'], ['Diamond'], ['Elite']][($i - 1) % 5],
                'required_referrals' => rand(3, 50),
                'available_quantity' => rand(5, 20),
                'allocated_quantity' => rand(0, 10),
                'is_active' => true
            ]);
        }

        $startTime = microtime(true);
        
        $availableAssets = $this->repository->findAvailableAssets();
        $stats = $this->repository->getAssetAllocationStats();
        $lowStockAlerts = $this->repository->getLowStockAlerts(3);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertNotEmpty($availableAssets);
        $this->assertNotEmpty($stats);
        $this->assertIsArray($lowStockAlerts);
        $this->assertLessThan(2.0, $executionTime, 'Asset repository queries should complete within 2 seconds');
    }
}