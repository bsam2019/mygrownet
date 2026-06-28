<?php

declare(strict_types=1);

namespace App\Domain\Community\Repositories;

use App\Domain\Community\ValueObjects\ProjectId;
use App\Domain\Community\ValueObjects\ContributionAmount;
use App\Domain\MLM\ValueObjects\UserId;
use DateTimeImmutable;

interface ProfitDistributionRepository
{
    /**
     * Find distribution by ID
     */
    public function findById(int $id): ?array;

    /**
     * Find distributions by project
     */
    public function findByProjectId(ProjectId $projectId): array;

    /**
     * Find distributions by user
     */
    public function findByUserId(UserId $userId): array;

    /**
     * Find distributions by status
     */
    public function findByStatus(string $status): array;

    /**
     * Find distributions by type
     */
    public function findByType(string $distributionType): array;

    /**
     * Find pending distributions for approval
     */
    public function findPendingDistributions(): array;

    /**
     * Find approved distributions for payment
     */
    public function findApprovedDistributions(): array;

    /**
     * Find distributions for specific period
     */
    public function findByPeriod(string $periodLabel): array;

    /**
     * Calculate profit distribution for contribution
     */
    public function calculateDistribution(
        int $contributionId,
        ContributionAmount $totalProfit,
        string $distributionType,
        string $periodLabel,
        DateTimeImmutable $periodStart,
        DateTimeImmutable $periodEnd
    ): array;

    /**
     * Calculate all distributions for project
     */
    public function calculateProjectDistributions(
        ProjectId $projectId,
        ContributionAmount $totalProfit,
        string $distributionType,
        string $periodLabel,
        DateTimeImmutable $periodStart,
        DateTimeImmutable $periodEnd
    ): array;

    /**
     * Get user's distribution summary
     */
    public function getUserDistributionSummary(UserId $userId): array;

    /**
     * Get project distribution summary
     */
    public function getProjectDistributionSummary(ProjectId $projectId): array;

    /**
     * Find distributions within date range
     */
    public function findByDateRange(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array;

    /**
     * Get distribution statistics by tier
     */
    public function getDistributionStatsByTier(): array;

    /**
     * Get distribution performance metrics
     */
    public function getDistributionPerformanceMetrics(): array;

    /**
     * Find top earning users from distributions
     */
    public function findTopEarningUsers(int $limit = 10): array;

    /**
     * Get tier bonus multipliers
     */
    public function getTierBonusMultipliers(): array;

    /**
     * Find distributions requiring attention
     */
    public function findDistributionsRequiringAttention(): array;

    /**
     * Save distribution
     */
    public function save(array $distributionData): int;

    /**
     * Update distribution status
     */
    public function updateStatus(int $distributionId, string $status): void;

    /**
     * Mark distribution as paid
     */
    public function markAsPaid(int $distributionId, array $paymentData): void;

    /**
     * Cancel distribution
     */
    public function cancel(int $distributionId, string $reason): void;

    /**
     * Delete distribution
     */
    public function delete(int $distributionId): void;
}