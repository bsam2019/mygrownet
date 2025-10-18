<?php

namespace App\Domain\Reward\Repositories;

use App\Domain\Reward\Entities\PhysicalRewardAllocation;
use App\Domain\Reward\ValueObjects\RewardAllocationId;
use App\Domain\Reward\ValueObjects\RewardId;
use App\Domain\Reward\ValueObjects\AllocationStatus;
use App\Domain\MLM\ValueObjects\UserId;

interface PhysicalRewardAllocationRepository
{
    /**
     * Find allocation by ID
     */
    public function findById(RewardAllocationId $id): ?PhysicalRewardAllocation;

    /**
     * Find allocations by user ID
     */
    public function findByUserId(UserId $userId): array;

    /**
     * Find allocations by reward ID
     */
    public function findByRewardId(RewardId $rewardId): array;

    /**
     * Find allocations by status
     */
    public function findByStatus(AllocationStatus $status): array;

    /**
     * Find allocations requiring maintenance check
     */
    public function findRequiringMaintenanceCheck(): array;

    /**
     * Find allocations eligible for ownership transfer
     */
    public function findEligibleForOwnershipTransfer(): array;

    /**
     * Check if user has allocation for reward
     */
    public function userHasAllocationForReward(UserId $userId, RewardId $rewardId): bool;

    /**
     * Save allocation
     */
    public function save(PhysicalRewardAllocation $allocation): void;

    /**
     * Delete allocation
     */
    public function delete(RewardAllocationId $id): void;

    /**
     * Get allocation statistics
     */
    public function getStatistics(): array;

    /**
     * Get user's income report
     */
    public function getUserIncomeReport(UserId $userId): array;
}