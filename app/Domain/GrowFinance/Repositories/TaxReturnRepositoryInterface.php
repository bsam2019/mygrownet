<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\TaxReturn;

interface TaxReturnRepositoryInterface
{
    public function findById(int $id): ?TaxReturn;
    public function save(TaxReturn $taxReturn): TaxReturn;
    public function findByBusiness(int $businessId): array;
    public function findByTypeAndPeriod(int $businessId, string $returnType, string $periodStart, string $periodEnd): ?TaxReturn;
    public function findByType(int $businessId, string $returnType): array;
    public function delete(TaxReturn $taxReturn): void;
}
