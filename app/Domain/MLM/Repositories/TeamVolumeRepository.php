<?php

declare(strict_types=1);

namespace App\Domain\MLM\Repositories;

use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;
use DateTimeImmutable;

interface TeamVolumeRepository
{
    /**
     * Get current team volume for user
     */
    public function getCurrentTeamVolume(UserId $userId): ?TeamVolumeAmount;

    /**
     * Get team volume for specific period
     */
    public function getTeamVolumeForPeriod(
        UserId $userId, 
        DateTimeImmutable $startDate, 
        DateTimeImmutable $endDate
    ): ?TeamVolumeAmount;

    /**
     * Calculate and store team volume rollup
     */
    public function calculateTeamVolumeRollup(UserId $userId): TeamVolumeAmount;

    /**
     * Get team volume aggregation for network analysis
     */
    public function getNetworkVolumeAggregation(UserId $userId, int $maxDepth = 5): array;

    /**
     * Update team volume for user
     */
    public function updateTeamVolume(
        UserId $userId, 
        TeamVolumeAmount $personalVolume, 
        TeamVolumeAmount $teamVolume,
        int $activeReferralsCount,
        DateTimeImmutable $periodStart,
        DateTimeImmutable $periodEnd
    ): void;

    /**
     * Get users eligible for performance bonuses based on team volume
     */
    public function getUsersEligibleForPerformanceBonuses(TeamVolumeAmount $minimumVolume): array;

    /**
     * Get team volume statistics for period
     */
    public function getTeamVolumeStats(
        DateTimeImmutable $startDate, 
        DateTimeImmutable $endDate
    ): array;

    /**
     * Get team volume history for user
     */
    public function getTeamVolumeHistory(UserId $userId, int $months = 12): array;

    /**
     * Calculate team depth for user
     */
    public function calculateTeamDepth(UserId $userId): int;

    /**
     * Get active referrals count for user
     */
    public function getActiveReferralsCount(UserId $userId): int;

    /**
     * Bulk update team volumes for performance optimization
     */
    public function bulkUpdateTeamVolumes(array $volumeUpdates): void;

    /**
     * Get top performers by team volume for leaderboards
     */
    public function getTopPerformersByTeamVolume(int $limit = 10): array;

    /**
     * Check if user qualifies for tier upgrade based on team volume
     */
    public function checkTierUpgradeQualification(
        UserId $userId, 
        TeamVolumeAmount $requiredVolume, 
        int $requiredReferrals
    ): bool;
}