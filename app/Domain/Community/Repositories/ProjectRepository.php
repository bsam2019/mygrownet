<?php

declare(strict_types=1);

namespace App\Domain\Community\Repositories;

use App\Domain\Community\ValueObjects\ProjectId;
use App\Domain\Community\ValueObjects\ProjectStatus;
use App\Domain\Community\ValueObjects\ContributionAmount;
use App\Domain\MLM\ValueObjects\UserId;
use DateTimeImmutable;

interface ProjectRepository
{
    /**
     * Find project by ID
     */
    public function findById(ProjectId $id): ?array;

    /**
     * Find projects by status
     */
    public function findByStatus(ProjectStatus $status): array;

    /**
     * Find active funding projects
     */
    public function findActiveFundingProjects(): array;

    /**
     * Find projects accessible by tier
     */
    public function findProjectsForTier(string $tierName): array;

    /**
     * Find featured projects
     */
    public function findFeaturedProjects(): array;

    /**
     * Find projects by type
     */
    public function findProjectsByType(string $type): array;

    /**
     * Find projects by risk level
     */
    public function findProjectsByRiskLevel(string $riskLevel): array;

    /**
     * Find projects user has contributed to
     */
    public function findUserProjects(UserId $userId): array;

    /**
     * Check if user can contribute to project
     */
    public function canUserContribute(UserId $userId, ProjectId $projectId): bool;

    /**
     * Get project funding statistics
     */
    public function getProjectFundingStats(ProjectId $projectId): array;

    /**
     * Get project contribution summary for user
     */
    public function getUserProjectContribution(UserId $userId, ProjectId $projectId): array;

    /**
     * Find projects requiring attention (deadlines, milestones)
     */
    public function findProjectsRequiringAttention(): array;

    /**
     * Get project performance metrics
     */
    public function getProjectPerformanceMetrics(ProjectId $projectId): array;

    /**
     * Find projects by funding progress range
     */
    public function findProjectsByFundingProgress(float $minProgress, float $maxProgress): array;

    /**
     * Get project timeline and milestones
     */
    public function getProjectTimeline(ProjectId $projectId): array;

    /**
     * Find projects with upcoming deadlines
     */
    public function findProjectsWithUpcomingDeadlines(int $days = 30): array;

    /**
     * Get project category statistics
     */
    public function getProjectCategoryStats(): array;

    /**
     * Find top performing projects
     */
    public function findTopPerformingProjects(int $limit = 10): array;

    /**
     * Update project funding amount
     */
    public function updateProjectFunding(ProjectId $projectId, ContributionAmount $amount): void;

    /**
     * Update project status
     */
    public function updateProjectStatus(ProjectId $projectId, ProjectStatus $status): void;

    /**
     * Get project search results
     */
    public function searchProjects(string $query, array $filters = []): array;
}