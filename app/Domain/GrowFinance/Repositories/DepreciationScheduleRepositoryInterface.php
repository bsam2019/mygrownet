<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\DepreciationEntry;

interface DepreciationScheduleRepositoryInterface
{
    public function findById(int $id): ?DepreciationEntry;
    public function save(DepreciationEntry $entry): DepreciationEntry;
    public function findByAsset(int $assetId): array;
    public function findUnposted(int $assetId): array;
    public function findForPeriod(int $businessId, string $periodDate): array;
    public function deleteForAsset(int $assetId): void;
}
