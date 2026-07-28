<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\TaxRate;

interface TaxRateRepositoryInterface
{
    public function findById(int $id): ?TaxRate;
    public function save(TaxRate $rate): TaxRate;
    public function findByBusiness(int $businessId): array;
    public function findByType(int $businessId, string $taxType): array;
    public function findDefault(int $businessId, string $taxType): ?TaxRate;
    public function findEffective(int $businessId, string $taxType, \DateTimeImmutable $date): ?TaxRate;
    public function delete(TaxRate $rate): void;
}
