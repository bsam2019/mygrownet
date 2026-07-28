<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\FiscalYear;

interface FiscalYearRepositoryInterface
{
    public function findById(int $id): ?FiscalYear;

    public function save(FiscalYear $fiscalYear): FiscalYear;

    public function findByBusiness(int $businessId): array;

    public function findCurrentForBusiness(int $businessId): ?FiscalYear;

    public function findByDate(int $businessId, \DateTimeImmutable $date): ?FiscalYear;
}
