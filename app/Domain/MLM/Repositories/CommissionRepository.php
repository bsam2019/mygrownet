<?php

declare(strict_types=1);

namespace App\Domain\MLM\Repositories;

use App\Domain\MLM\Entities\Commission;
use App\Domain\MLM\ValueObjects\CommissionId;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\CommissionLevel;
use App\Domain\MLM\ValueObjects\CommissionType;
use App\Domain\MLM\ValueObjects\CommissionStatus;
use DateTimeImmutable;

interface CommissionRepository
{
    /**
     * Find commission by ID
     */
    public function findById(CommissionId $id): ?Commission;

    /**
     * Save commission entity
     */
    public function save(Commission $commission): void;

    /**
     * Find commissions by earner ID
     */
    public function findByEarnerId(UserId $earnerId): array;

    /**
     * Find commissions by source ID (who generated the commission)
     */
    public function findBySourceId(UserId $sourceId): array;

    /**
     * Find commissions by level
     */
    public function findByLevel(CommissionLevel $level): array;

    /**
     * Find commissions by type
     */
    public function findByType(CommissionType $type): array;

    /**
     * Find commissions by status
     */
    public function findByStatus(CommissionStatus $status): array;

    /**
     * Find pending commissions for payment processing
     */
    public function findPendingCommissions(): array;

    /**
     * Find commissions within date range
     */
    public function findByDateRange(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array;

    /**
     * Find commissions for specific earner within date range
     */
    public function findByEarnerAndDateRange(
        UserId $earnerId, 
        DateTimeImmutable $startDate, 
        DateTimeImmutable $endDate
    ): array;

    /**
     * Calculate total commission amount for earner within period
     */
    public function calculateTotalCommissions(
        UserId $earnerId, 
        DateTimeImmutable $startDate, 
        DateTimeImmutable $endDate
    ): float;

    /**
     * Get commission statistics by level for earner
     */
    public function getCommissionStatsByLevel(UserId $earnerId): array;

    /**
     * Get commission statistics by type for earner
     */
    public function getCommissionStatsByType(UserId $earnerId): array;

    /**
     * Find commissions for network analysis (efficient network queries)
     */
    public function findNetworkCommissions(UserId $userId, int $maxDepth = 5): array;

    /**
     * Bulk update commission status
     */
    public function bulkUpdateStatus(array $commissionIds, CommissionStatus $status): void;

    /**
     * Delete commission
     */
    public function delete(CommissionId $id): void;
}