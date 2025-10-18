<?php

namespace Tests\Unit\Domain\Reward\Services;

use Tests\TestCase;
use App\Domain\Reward\Services\PhysicalRewardAllocationService;
use App\Domain\Reward\Entities\PhysicalRewardAllocation;
use App\Domain\Reward\ValueObjects\RewardAllocationId;
use App\Domain\Reward\ValueObjects\RewardId;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;

class PhysicalRewardAllocationServiceTest extends TestCase
{
    private PhysicalRewardAllocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PhysicalRewardAllocationService();
    }

    public function test_can_allocate_reward()
    {
        $allocation = $this->service->allocateReward(
            RewardAllocationId::fromInt(1),
            UserId::fromInt(1),
            RewardId::fromInt(1),
            TeamVolumeAmount::fromFloat(50000.0),
            10,
            3
        );

        $this->assertInstanceOf(PhysicalRewardAllocation::class, $allocation);
        $this->assertEquals(1, $allocation->getId()->value());
        $this->assertEquals(1, $allocation->getUserId()->value());
        $this->assertEquals(1, $allocation->getRewardId()->value());
        $this->assertTrue($allocation->getStatus()->isAllocated());
    }

    public function test_can_process_delivery()
    {
        $allocation = $this->service->allocateReward(
            RewardAllocationId::fromInt(1),
            UserId::fromInt(1),
            RewardId::fromInt(1),
            TeamVolumeAmount::fromFloat(50000.0),
            10,
            3
        );

        $this->service->processDelivery($allocation);

        $this->assertTrue($allocation->getStatus()->isDelivered());
        $this->assertNotNull($allocation->getDeliveredAt());
    }

    public function test_can_process_ownership_transfer()
    {
        $allocation = $this->service->allocateReward(
            RewardAllocationId::fromInt(1),
            UserId::fromInt(1),
            RewardId::fromInt(1),
            TeamVolumeAmount::fromFloat(50000.0),
            10,
            3
        );

        $this->service->processDelivery($allocation);
        $this->service->processMaintenanceCheck($allocation, true, 12);
        $this->service->processOwnershipTransfer($allocation);

        $this->assertTrue($allocation->getStatus()->isOwnershipTransferred());
        $this->assertNotNull($allocation->getOwnershipTransferredAt());
    }

    public function test_cannot_transfer_ownership_without_delivery()
    {
        $allocation = $this->service->allocateReward(
            RewardAllocationId::fromInt(1),
            UserId::fromInt(1),
            RewardId::fromInt(1),
            TeamVolumeAmount::fromFloat(50000.0),
            10,
            3
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can only transfer ownership of delivered rewards');

        $this->service->processOwnershipTransfer($allocation);
    }

    public function test_can_record_income()
    {
        $allocation = $this->service->allocateReward(
            RewardAllocationId::fromInt(1),
            UserId::fromInt(1),
            RewardId::fromInt(1),
            TeamVolumeAmount::fromFloat(50000.0),
            10,
            3
        );

        $this->service->processDelivery($allocation);
        $this->service->recordIncome($allocation, 800.0);

        $incomeMetrics = $allocation->getIncomeMetrics();
        $this->assertEquals(800.0, $incomeMetrics['total_generated']);
    }

    public function test_cannot_record_negative_income()
    {
        $allocation = $this->service->allocateReward(
            RewardAllocationId::fromInt(1),
            UserId::fromInt(1),
            RewardId::fromInt(1),
            TeamVolumeAmount::fromFloat(50000.0),
            10,
            3
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Income amount must be positive');

        $this->service->recordIncome($allocation, -100.0);
    }

    public function test_can_forfeit_reward()
    {
        $allocation = $this->service->allocateReward(
            RewardAllocationId::fromInt(1),
            UserId::fromInt(1),
            RewardId::fromInt(1),
            TeamVolumeAmount::fromFloat(50000.0),
            10,
            3
        );

        $this->service->forfeitReward($allocation, 'Performance requirements not met');

        $this->assertTrue($allocation->getStatus()->isForfeited());
    }

    public function test_cannot_forfeit_without_reason()
    {
        $allocation = $this->service->allocateReward(
            RewardAllocationId::fromInt(1),
            UserId::fromInt(1),
            RewardId::fromInt(1),
            TeamVolumeAmount::fromFloat(50000.0),
            10,
            3
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Forfeiture reason is required');

        $this->service->forfeitReward($allocation, '');
    }

    public function test_can_calculate_allocation_progress()
    {
        $allocation = $this->service->allocateReward(
            RewardAllocationId::fromInt(1),
            UserId::fromInt(1),
            RewardId::fromInt(1),
            TeamVolumeAmount::fromFloat(50000.0),
            10,
            3
        );

        $this->service->processDelivery($allocation);
        $this->service->processMaintenanceCheck($allocation, true, 6);
        $this->service->recordIncome($allocation, 1200.0);

        $progress = $this->service->calculateAllocationProgress($allocation, 12);

        $this->assertEquals('delivered', $progress['status']);
        $this->assertTrue($progress['maintenance']['compliant']);
        $this->assertEquals(6, $progress['maintenance']['months_completed']);
        $this->assertEquals(12, $progress['maintenance']['months_required']);
        $this->assertEquals(50.0, $progress['maintenance']['completion_percentage']);
        $this->assertEquals(1200.0, $progress['income']['total_generated']);
        $this->assertEquals(50000.0, $progress['performance_at_allocation']['team_volume']);
        $this->assertEquals(10, $progress['performance_at_allocation']['active_referrals']);
        $this->assertEquals(3, $progress['performance_at_allocation']['team_depth']);
    }

    public function test_can_validate_allocation_eligibility()
    {
        $teamVolume = TeamVolumeAmount::fromFloat(50000.0);
        $activeReferrals = 10;
        $teamDepth = 3;

        $requirements = [
            'required_team_volume' => 40000.0,
            'required_referrals' => 8,
            'required_team_depth' => 2
        ];

        $isEligible = $this->service->validateAllocationEligibility(
            $teamVolume,
            $activeReferrals,
            $teamDepth,
            $requirements
        );

        $this->assertTrue($isEligible);
    }

    public function test_validates_insufficient_team_volume()
    {
        $teamVolume = TeamVolumeAmount::fromFloat(30000.0);
        $activeReferrals = 10;
        $teamDepth = 3;

        $requirements = [
            'required_team_volume' => 40000.0,
            'required_referrals' => 8,
            'required_team_depth' => 2
        ];

        $isEligible = $this->service->validateAllocationEligibility(
            $teamVolume,
            $activeReferrals,
            $teamDepth,
            $requirements
        );

        $this->assertFalse($isEligible);
    }

    public function test_validates_insufficient_referrals()
    {
        $teamVolume = TeamVolumeAmount::fromFloat(50000.0);
        $activeReferrals = 5;
        $teamDepth = 3;

        $requirements = [
            'required_team_volume' => 40000.0,
            'required_referrals' => 8,
            'required_team_depth' => 2
        ];

        $isEligible = $this->service->validateAllocationEligibility(
            $teamVolume,
            $activeReferrals,
            $teamDepth,
            $requirements
        );

        $this->assertFalse($isEligible);
    }

    public function test_validates_insufficient_team_depth()
    {
        $teamVolume = TeamVolumeAmount::fromFloat(50000.0);
        $activeReferrals = 10;
        $teamDepth = 1;

        $requirements = [
            'required_team_volume' => 40000.0,
            'required_referrals' => 8,
            'required_team_depth' => 2
        ];

        $isEligible = $this->service->validateAllocationEligibility(
            $teamVolume,
            $activeReferrals,
            $teamDepth,
            $requirements
        );

        $this->assertFalse($isEligible);
    }

    public function test_can_calculate_potential_income()
    {
        $estimatedMonthlyIncome = 800.0;
        $maintenanceMonths = 12;

        $potentialIncome = $this->service->calculatePotentialIncome(
            $estimatedMonthlyIncome,
            $maintenanceMonths
        );

        $this->assertEquals(800.0, $potentialIncome['estimated_monthly']);
        $this->assertEquals(12, $potentialIncome['maintenance_period_months']);
        $this->assertEquals(9600.0, $potentialIncome['total_potential_income']);
        $this->assertEquals(12, $potentialIncome['break_even_months']);
    }
}