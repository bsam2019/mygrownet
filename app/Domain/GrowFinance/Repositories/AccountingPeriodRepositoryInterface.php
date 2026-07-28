<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\AccountingPeriod;

interface AccountingPeriodRepositoryInterface
{
    public function findById(int $id): ?AccountingPeriod;

    public function save(AccountingPeriod $period): AccountingPeriod;

    public function findByBusiness(int $businessId): array;

    public function findByFiscalYear(int $fiscalYearId): array;

    public function findCurrent(int $businessId): ?AccountingPeriod;

    public function findByStatus(int $businessId, string $status): array;

    public function findByDate(int $businessId, \DateTimeImmutable $date): ?AccountingPeriod;

    public function findRange(int $businessId, \DateTimeImmutable $start, \DateTimeImmutable $end): array;
}
