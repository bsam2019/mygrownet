<?php

namespace App\Domain\Reward\Services;

use App\Domain\Reward\Entities\PhysicalRewardAllocation;
use App\Domain\Reward\ValueObjects\RewardAllocationId;
use App\Domain\Reward\ValueObjects\RewardId;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;

class PhysicalRewardAllocationService
{
    /**
     * Create a new physical reward allocation
     */
    public function allocateReward(
        RewardAllocationId $allocationId,
        UserId $userId,
        RewardId $rewardId,
        TeamVolumeAmount $teamVolume,
        int $activeReferrals,
        int $teamDepth
    ): PhysicalRewardAllocation {
        return PhysicalRewardAllocation::allocate(
            $allocationId,
            $userId,
            $rewardId,
            $teamVolume,
            $activeReferrals,
            $teamDepth
        );
    }

    /**
     * Process reward delivery
     */
    public function processDelivery(PhysicalRewardAllocation $allocation): void
    {
        $allocation->markAsDelivered();
    }

    /**
     * Process ownership transfer
     */
    public function processOwnershipTransfer(PhysicalRewardAllocation $allocation): void
    {
        $allocation->transferOwnership();
    }

    /**
     * Process maintenance compliance check
     */
    public function processMaintenanceCheck(
        PhysicalRewardAllocation $allocation,
        bool $isCompliant,
        int $monthsCompleted
    ): void {
        $allocation->updateMaintenanceStatus($isCompliant, $monthsCompleted);
    }

    /**
     * Process income recording
     */
    public function recordIncome(
        PhysicalRewardAllocation $allocation,
        float $incomeAmount
    ): void {
        if ($incomeAmount <= 0) {
            throw new \DomainException('Income amount must be positive');
        }

        $allocation->recordIncomeGenerated($incomeAmount);
    }

    /**
     * Process reward forfeiture
     */
    public function forfeitReward(
        PhysicalRewardAllocation $allocation,
        string $reason
    ): void {
        if (empty($reason)) {
            throw new \DomainException('Forfeiture reason is required');
        }

        $allocation->forfeit($reason);
    }

    /**
     * Calculate allocation progress
     */
    public function calculateAllocationProgress(
        PhysicalRewardAllocation $allocation,
        int $requiredMaintenanceMonths
    ): array {
        $maintenanceStatus = $allocation->getMaintenanceStatus();
        $incomeMetrics = $allocation->getIncomeMetrics();

        return [
            'status' => $allocation->getStatus()->value(),
            'allocated_at' => $allocation->getAllocatedAt(),
            'delivered_at' => $allocation->getDeliveredAt(),
            'ownership_transferred_at' => $allocation->getOwnershipTransferredAt(),
            'maintenance' => [
                'compliant' => $maintenanceStatus->isCompliant(),
                'months_completed' => $maintenanceStatus->getMonthsCompleted(),
                'months_required' => $requiredMaintenanceMonths,
                'completion_percentage' => $maintenanceStatus->getCompliancePercentage($requiredMaintenanceMonths),
                'eligible_for_ownership' => $allocation->isEligibleForOwnershipTransfer()
            ],
            'income' => [
                'total_generated' => $incomeMetrics['total_generated'],
                'monthly_average' => $incomeMetrics['monthly_average']
            ],
            'performance_at_allocation' => $allocation->getPerformanceAtAllocation()
        ];
    }

    /**
     * Validate allocation eligibility
     */
    public function validateAllocationEligibility(
        TeamVolumeAmount $teamVolume,
        int $activeReferrals,
        int $teamDepth,
        array $requirements
    ): bool {
        // Check team volume requirement
        if (isset($requirements['required_team_volume']) && 
            $teamVolume->value() < $requirements['required_team_volume']) {
            return false;
        }

        // Check referral requirement
        if (isset($requirements['required_referrals']) && 
            $activeReferrals < $requirements['required_referrals']) {
            return false;
        }

        // Check team depth requirement
        if (isset($requirements['required_team_depth']) && 
            $teamDepth < $requirements['required_team_depth']) {
            return false;
        }

        return true;
    }

    /**
     * Calculate potential income from allocation
     */
    public function calculatePotentialIncome(
        float $estimatedMonthlyIncome,
        int $maintenanceMonths
    ): array {
        $totalPotentialIncome = $estimatedMonthlyIncome * $maintenanceMonths;
        
        return [
            'estimated_monthly' => $estimatedMonthlyIncome,
            'maintenance_period_months' => $maintenanceMonths,
            'total_potential_income' => $totalPotentialIncome,
            'break_even_months' => $estimatedMonthlyIncome > 0 
                ? ceil($totalPotentialIncome / $estimatedMonthlyIncome) 
                : 0
        ];
    }
}