<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

interface ReportRepositoryInterface
{
    public function saveSnapshot(int $businessId, string $reportType, string $asOfDate, array $data): int;

    public function findSnapshot(int $businessId, string $reportType, string $asOfDate): ?array;

    public function findSnapshotsByType(int $businessId, string $reportType, int $limit = 10): array;

    public function deleteSnapshotsOlderThan(int $businessId, string $date): int;

    public function findSnapshotById(int $id): ?array;

    public function updateSnapshotHash(int $id, string $hash): void;

    public function lockSnapshot(int $id): void;
}
