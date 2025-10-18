<?php

namespace App\Domain\Reward\Repositories;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\MatrixPosition;
use Illuminate\Database\Eloquent\Collection;

interface ReferralRepositoryInterface
{
    /**
     * Build referral tree with configurable depth limits
     */
    public function buildReferralTree(User $user, int $maxLevel = 3): array;

    /**
     * Get referrals by specific level
     */
    public function getReferralsByLevel(User $user, int $level): Collection;

    /**
     * Get direct referrals for a user
     */
    public function getDirectReferrals(User $user): Collection;

    /**
     * Generate matrix structure data for frontend display
     */
    public function buildMatrixStructure(User $user, int $maxLevel = 3): array;

    /**
     * Get all referrals up to specified depth
     */
    public function getAllReferrals(User $user, int $maxDepth = 3): Collection;

    /**
     * Get referral commissions for a user
     */
    public function getReferralCommissions(User $user): Collection;

    /**
     * Get matrix position for a user
     */
    public function getMatrixPosition(User $user): ?MatrixPosition;

    /**
     * Get matrix downline count by level
     */
    public function getMatrixDownlineCount(User $user, int $maxLevel = 3): array;

    /**
     * Find next available matrix position for spillover
     */
    public function findNextAvailableMatrixPosition(User $sponsor, int $maxLevel = 3): ?array;

    /**
     * Get referral statistics for a user
     */
    public function getReferralStatistics(User $user): array;

    /**
     * Get users eligible for matrix commission at specific level
     */
    public function getEligibleForMatrixCommission(User $user, int $level): Collection;

    /**
     * Get referral tree with investment data
     */
    public function buildReferralTreeWithInvestments(User $user, int $maxLevel = 3): array;
}