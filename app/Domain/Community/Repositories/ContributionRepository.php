<?php

declare(strict_types=1);

namespace App\Domain\Community\Repositories;

use App\Domain\Community\ValueObjects\ProjectId;
use App\Domain\Community\ValueObjects\ContributionAmount;
use App\Domain\MLM\ValueObjects\UserId;
use DateTimeImmutable;

interface ContributionRepository
{
    /**
     * Find contribution by ID
     */
    public function findById(int $id): ?array;

    /**
     * Find contributions by user
     */
    public function findByUserId(UserId $userId): array;

    /**
     * Find contributions by project
     */
    public function findByProjectId(ProjectId $projectId): array;

    /**
     * Find contributions by status
     */
    public function findByStatus(string $status): array;

    /**
     * Find pending contributions requiring approval
     */
    public function findPendingContributions(): array;

    /**
     * Get user's total contribution to project
     */
    public function getUserProjectContribution(UserId $userId, ProjectId $projectId): ContributionAmount;

    /**
     * Get user's total contributions across all projects
     */
    public function getUserTotalContributions(UserId $userId): ContributionAmount;

    /**
     * Find contributions within date range
     */
    public function findByDateRange(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array;

    /**
     * Get contribution statistics by tier
     */
    public function getContributionStatsByTier(): array;

    /**
     * Get contribution statistics by project
     */
    public function getContributionStatsByProject(): array;

    /**
     * Find top contributors
     */
    public function findTopContributors(int $limit = 10): array;

    /**
     * Find contributions eligible for returns
     */
    public function findContributionsEligibleForReturns(ProjectId $projectId): array;

    /**
     * Get user's contribution history
     */
    public function getUserContributionHistory(UserId $userId): array;

    /**
     * Calculate user's voting power for project
     */
    public function calculateUserVotingPower(UserId $userId, ProjectId $projectId): float;

    /**
     * Find contributions by tier
     */
    public function findContributionsByTier(string $tierName): array;

    /**
     * Get contribution performance metrics
     */
    public function getContributionPerformanceMetrics(): array;

    /**
     * Find contributions requiring attention (refunds, issues)
     */
    public function findContributionsRequiringAttention(): array;

    /**
     * Save contribution
     */
    public function save(array $contributionData): int;

    /**
     * Update contribution status
     */
    public function updateStatus(int $contributionId, string $status): void;

    /**
     * Delete contribution
     */
    public function delete(int $contributionId): void;
}