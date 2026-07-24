<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\Sale;

interface SaleRepositoryInterface
{
    public function findById(int $id): ?Sale;

    public function findByBusiness(int $businessId, array $filters = []): array;

    public function save(Sale $entity): Sale;

    public function delete(int $id): void;

    public function sumByBusiness(int $businessId, array $conditions = []): float;

    public function getSalesReport(int $businessId, string $startDate, string $endDate): array;
}