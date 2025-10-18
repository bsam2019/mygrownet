<?php

namespace Tests\Unit\Application\Services;

use Tests\TestCase;
use App\Application\Services\AssetMaintenanceService;
use App\Application\UseCases\Asset\ProcessAssetMaintenanceUseCase;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssetMaintenanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private AssetMaintenanceService $service;
    private ProcessAssetMaintenanceUseCase $processAssetMaintenanceUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->processAssetMaintenanceUseCase = $this->createMock(ProcessAssetMaintenanceUseCase::class);
        $this->service = new AssetMaintenanceService($this->processAssetMaintenanceUseCase);
    }

    public function test_monitor_asset_maintenance()
    {
        // Arrange
        $mockResults = [
            'processed' => 3,
            'maintained' => 2,
            'violated' => 1,
            'completed' => 0,
            'forfeited' => 0,
            'details' => [
                [
                    'action' => 'maintained',
                    'user_id' => 1,
                    'asset_type' => 'SMARTPHONE',
                    'message' => 'Asset maintenance requirements met'
                ],
                [
                    'action' => 'violated',
                    'user_id' => 2,
                    'asset_type' => 'MOTORBIKE',
                    'message' => 'Asset maintenance requirements not met - warning issued'
                ]
            ]
        ];

        $this->processAssetMaintenanceUseCase
            ->expects($this->once())
            ->method('execute')
            ->with(null)
            ->willReturn($mockResults);

        // Act
        $results = $this->service->monitorAssetMaintenance();

        // Assert
        $this->assertEquals($mockResults, $results);
    }

    public function test_check_allocation_maintenance()
    {
        // Arrange
        $allocationId = 1;
        $mockResult = [
            'processed' => 1,
            'maintained' => 1,
            'violated' => 0,
            'completed' => 0,
            'forfeited' => 0,
            'details' => [
                [
                    'action' => 'maintained',
                    'allocation_id' => $allocationId,
                    'user_id' => 1,
                    'asset_type' => 'SMARTPHONE'
                ]
            ]
        ];

        $this->processAssetMaintenanceUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($allocationId)
            ->willReturn($mockResult);

        // Act
        $result = $this->service->checkAllocationMaintenance($allocationId);

        // Assert
        $this->assertEquals($mockResult, $result);
    }

    public function test_get_maintenance_alerts_high_risk()
    {
        // Arrange
        $silverTier = InvestmentTier::factory()->create(['name' => 'Silver Member']);
        $bronzeTier = InvestmentTier::factory()->create(['name' => 'Bronze Member']);
        
        // User with insufficient referrals and team volume (high risk)
        $user = User::factory()->create([
            'investment_tier_id' => $bronzeTier->id, // Wrong tier for smartphone
            'monthly_team_volume' => 5000 // Below required 15000
        ]);

        // Create only 1 referral (needs 3 for smartphone)
        User::factory()->create(['referrer_id' => $user->id]);

        $asset = PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'value' => 3000
        ]);

        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'active',
            'allocated_at' => now()->subMonths(2)
        ]);

        // Act
        $alerts = $this->service->getMaintenanceAlerts();

        // Assert
        $this->assertEquals(1, $alerts['total_alerts']);
        $this->assertEquals(1, $alerts['high_risk']);
        $this->assertEquals(0, $alerts['medium_risk']);
        
        $alert = $alerts['alerts'][0];
        $this->assertEquals('high', $alert['risk_level']);
        $this->assertEquals($allocation->id, $alert['allocation_id']);
        $this->assertEquals($user->id, $alert['user_id']);
        $this->assertEquals('SMARTPHONE', $alert['asset_type']);
        
        // Check risk factors
        $this->assertEquals(2, $alert['risk_factors']['referral_deficit']); // Needs 3, has 1
        $this->assertEquals(10000, $alert['risk_factors']['volume_deficit']); // Needs 15000, has 5000
        $this->assertTrue($alert['risk_factors']['tier_mismatch']); // Has Bronze, needs Silver
    }

    public function test_get_maintenance_alerts_medium_risk()
    {
        // Arrange
        $silverTier = InvestmentTier::factory()->create(['name' => 'Silver Member']);
        
        // User with correct tier but insufficient referrals (medium risk)
        $user = User::factory()->create([
            'investment_tier_id' => $silverTier->id,
            'monthly_team_volume' => 16000 // Above required 15000
        ]);

        // Create only 1 referral (needs 3)
        User::factory()->create(['referrer_id' => $user->id]);

        $asset = PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'value' => 3000
        ]);

        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'active'
        ]);

        // Act
        $alerts = $this->service->getMaintenanceAlerts();

        // Assert
        $this->assertEquals(1, $alerts['total_alerts']);
        $this->assertEquals(0, $alerts['high_risk']);
        $this->assertEquals(1, $alerts['medium_risk']);
        
        $alert = $alerts['alerts'][0];
        $this->assertEquals('medium', $alert['risk_level']);
        $this->assertEquals(2, $alert['risk_factors']['referral_deficit']);
        $this->assertEquals(0, $alert['risk_factors']['volume_deficit']);
        $this->assertFalse($alert['risk_factors']['tier_mismatch']);
    }

    public function test_get_maintenance_alerts_low_risk_not_included()
    {
        // Arrange
        $silverTier = InvestmentTier::factory()->create(['name' => 'Silver Member']);
        
        // User meeting all requirements (low risk)
        $user = User::factory()->create([
            'investment_tier_id' => $silverTier->id,
            'monthly_team_volume' => 16000
        ]);

        // Create sufficient referrals
        User::factory()->count(4)->create(['referrer_id' => $user->id]);

        $asset = PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'value' => 3000
        ]);

        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'active'
        ]);

        // Act
        $alerts = $this->service->getMaintenanceAlerts();

        // Assert
        $this->assertEquals(0, $alerts['total_alerts']);
        $this->assertEquals(0, $alerts['high_risk']);
        $this->assertEquals(0, $alerts['medium_risk']);
        $this->assertEmpty($alerts['alerts']);
    }

    public function test_get_maintenance_schedule()
    {
        // Arrange
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);
        
        $asset1 = PhysicalReward::factory()->create([
            'type' => 'SMARTPHONE',
            'value' => 3000
        ]);
        
        $asset2 = PhysicalReward::factory()->create([
            'type' => 'MOTORBIKE',
            'value' => 12000
        ]);

        // Allocation that needs check (no previous check)
        $allocation1 = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user1->id,
            'physical_reward_id' => $asset1->id,
            'status' => 'active',
            'allocated_at' => now()->subMonths(10),
            'last_maintenance_check' => null
        ]);

        // Allocation with old check
        $allocation2 = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user2->id,
            'physical_reward_id' => $asset2->id,
            'status' => 'active',
            'allocated_at' => now()->subMonths(8),
            'last_maintenance_check' => now()->subDays(35)
        ]);

        // Recent allocation (should not appear)
        PhysicalRewardAllocation::factory()->create([
            'status' => 'active',
            'allocated_at' => now()->subMonths(1),
            'last_maintenance_check' => now()->subDays(5)
        ]);

        // Act
        $schedule = $this->service->getMaintenanceSchedule(30);

        // Assert
        $this->assertCount(2, $schedule);
        
        $item1 = collect($schedule)->firstWhere('allocation_id', $allocation1->id);
        $this->assertEquals('John Doe', $item1['user_name']);
        $this->assertEquals('SMARTPHONE', $item1['asset_type']);
        $this->assertEquals(10, $item1['months_elapsed']);
        $this->assertEquals(2, $item1['months_remaining']); // 12 - 10
        $this->assertEquals('high', $item1['priority']); // <= 3 months remaining
        
        $item2 = collect($schedule)->firstWhere('allocation_id', $allocation2->id);
        $this->assertEquals('Jane Smith', $item2['user_name']);
        $this->assertEquals('MOTORBIKE', $item2['asset_type']);
        $this->assertEquals(8, $item2['months_elapsed']);
        $this->assertEquals(4, $item2['months_remaining']); // 12 - 8
        $this->assertEquals('medium', $item2['priority']); // <= 6 months remaining
    }

    public function test_generate_maintenance_report()
    {
        // Arrange
        $user = User::factory()->create();
        $asset = PhysicalReward::factory()->create(['value' => 3000]);

        // Create allocations with different statuses
        PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'active'
        ]);
        
        PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'completed'
        ]);
        
        PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'forfeited'
        ]);

        // Create recent violation
        PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'active',
            'maintenance_status' => 'violated',
            'last_maintenance_check' => now()->subDays(15)
        ]);

        // Act
        $report = $this->service->generateMaintenanceReport();

        // Assert
        $summary = $report['summary'];
        $this->assertEquals(4, $summary['total_allocations']);
        $this->assertEquals(2, $summary['active_allocations']); // active + violated
        $this->assertEquals(1, $summary['completed_allocations']);
        $this->assertEquals(1, $summary['forfeited_allocations']);
        $this->assertEquals(25.0, $summary['completion_rate']); // 1/4 * 100
        $this->assertEquals(25.0, $summary['forfeiture_rate']); // 1/4 * 100
        $this->assertEquals(1, $summary['recent_violations']);
        
        $this->assertArrayHasKey('alerts', $report);
        $this->assertArrayHasKey('schedule', $report);
        $this->assertNotNull($report['generated_at']);
    }

    public function test_create_asset_payment_plan()
    {
        // Arrange
        $user = User::factory()->create();
        $asset = PhysicalReward::factory()->create(['value' => 10000]);
        
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'forfeited'
        ]);

        $paymentTerms = [
            'monthly_payment' => 1000,
            'start_date' => now()->addWeek()
        ];

        // Act
        $result = $this->service->createAssetPaymentPlan($allocation->id, $paymentTerms);

        // Assert
        $this->assertTrue($result['success']);
        
        $plan = $result['payment_plan'];
        $this->assertEquals($allocation->id, $plan['allocation_id']);
        $this->assertEquals($user->id, $plan['user_id']);
        $this->assertEquals(10000, $plan['asset_value']);
        $this->assertEquals(1000, $plan['monthly_payment']);
        $this->assertEquals(10, $plan['total_payments']); // 10000 / 1000
        $this->assertEquals(10000, $plan['total_amount']); // 1000 * 10
        $this->assertEquals('active', $plan['status']);
    }

    public function test_create_asset_payment_plan_not_forfeited()
    {
        // Arrange
        $allocation = PhysicalRewardAllocation::factory()->create([
            'status' => 'active' // Not forfeited
        ]);

        // Act
        $result = $this->service->createAssetPaymentPlan($allocation->id, []);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Payment plans are only available for forfeited assets', $result['error']);
    }

    public function test_create_asset_payment_plan_default_terms()
    {
        // Arrange
        $user = User::factory()->create();
        $asset = PhysicalReward::factory()->create(['value' => 5000]);
        
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'status' => 'forfeited'
        ]);

        // Act - no payment terms provided, should use defaults
        $result = $this->service->createAssetPaymentPlan($allocation->id, []);

        // Assert
        $this->assertTrue($result['success']);
        
        $plan = $result['payment_plan'];
        $this->assertEquals(500, $plan['monthly_payment']); // 10% of 5000
        $this->assertEquals(10, $plan['total_payments']); // 5000 / 500
        $this->assertEquals(5000, $plan['total_amount']);
    }
}