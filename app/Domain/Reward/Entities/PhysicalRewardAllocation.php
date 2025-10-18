<?php

namespace App\Domain\Reward\Entities;

use App\Domain\Reward\ValueObjects\RewardAllocationId;
use App\Domain\Reward\ValueObjects\RewardId;
use App\Domain\Reward\ValueObjects\AllocationStatus;
use App\Domain\Reward\ValueObjects\MaintenanceStatus;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;
use DateTimeImmutable;

class PhysicalRewardAllocation
{
    private function __construct(
        private RewardAllocationId $id,
        private UserId $userId,
        private RewardId $rewardId,
        private TeamVolumeAmount $teamVolumeAtAllocation,
        private int $activeReferralsAtAllocation,
        private int $teamDepthAtAllocation,
        private AllocationStatus $status,
        private DateTimeImmutable $allocatedAt,
        private ?DateTimeImmutable $deliveredAt = null,
        private ?DateTimeImmutable $ownershipTransferredAt = null,
        private MaintenanceStatus $maintenanceStatus = new MaintenanceStatus(true, 0),
        private float $totalIncomeGenerated = 0.0,
        private float $monthlyIncomeAverage = 0.0
    ) {}

    public static function allocate(
        RewardAllocationId $id,
        UserId $userId,
        RewardId $rewardId,
        TeamVolumeAmount $teamVolume,
        int $activeReferrals,
        int $teamDepth
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            rewardId: $rewardId,
            teamVolumeAtAllocation: $teamVolume,
            activeReferralsAtAllocation: $activeReferrals,
            teamDepthAtAllocation: $teamDepth,
            status: AllocationStatus::allocated(),
            allocatedAt: new DateTimeImmutable()
        );
    }

    public function markAsDelivered(): void
    {
        if (!$this->status->isAllocated()) {
            throw new \DomainException('Can only deliver allocated rewards');
        }

        $this->status = AllocationStatus::delivered();
        $this->deliveredAt = new DateTimeImmutable();
    }

    public function transferOwnership(): void
    {
        if (!$this->status->isDelivered()) {
            throw new \DomainException('Can only transfer ownership of delivered rewards');
        }

        if (!$this->isEligibleForOwnershipTransfer()) {
            throw new \DomainException('Allocation not eligible for ownership transfer');
        }

        $this->status = AllocationStatus::ownershipTransferred();
        $this->ownershipTransferredAt = new DateTimeImmutable();
    }

    public function forfeit(string $reason): void
    {
        if ($this->status->isForfeited()) {
            throw new \DomainException('Allocation already forfeited');
        }

        $this->status = AllocationStatus::forfeited();
    }

    public function updateMaintenanceStatus(bool $compliant, int $monthsCompleted): void
    {
        $this->maintenanceStatus = new MaintenanceStatus($compliant, $monthsCompleted);
    }

    public function recordIncomeGenerated(float $amount): void
    {
        if ($amount <= 0) {
            throw new \DomainException('Income amount must be positive');
        }

        $this->totalIncomeGenerated += $amount;
        $this->recalculateMonthlyAverage();
    }

    public function isEligibleForOwnershipTransfer(): bool
    {
        return $this->status->isDelivered() && 
               $this->maintenanceStatus->isCompliant();
    }

    public function getPerformanceAtAllocation(): array
    {
        return [
            'team_volume' => $this->teamVolumeAtAllocation->value(),
            'active_referrals' => $this->activeReferralsAtAllocation,
            'team_depth' => $this->teamDepthAtAllocation
        ];
    }

    public function getIncomeMetrics(): array
    {
        return [
            'total_generated' => $this->totalIncomeGenerated,
            'monthly_average' => $this->monthlyIncomeAverage
        ];
    }

    private function recalculateMonthlyAverage(): void
    {
        if (!$this->deliveredAt) {
            return;
        }

        $monthsSinceDelivery = $this->deliveredAt->diff(new DateTimeImmutable())->m + 1;
        $this->monthlyIncomeAverage = $this->totalIncomeGenerated / max(1, $monthsSinceDelivery);
    }

    // Getters
    public function getId(): RewardAllocationId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getRewardId(): RewardId
    {
        return $this->rewardId;
    }

    public function getStatus(): AllocationStatus
    {
        return $this->status;
    }

    public function getAllocatedAt(): DateTimeImmutable
    {
        return $this->allocatedAt;
    }

    public function getDeliveredAt(): ?DateTimeImmutable
    {
        return $this->deliveredAt;
    }

    public function getOwnershipTransferredAt(): ?DateTimeImmutable
    {
        return $this->ownershipTransferredAt;
    }

    public function getMaintenanceStatus(): MaintenanceStatus
    {
        return $this->maintenanceStatus;
    }

    public function getTotalIncomeGenerated(): float
    {
        return $this->totalIncomeGenerated;
    }

    public function getMonthlyIncomeAverage(): float
    {
        return $this->monthlyIncomeAverage;
    }
}