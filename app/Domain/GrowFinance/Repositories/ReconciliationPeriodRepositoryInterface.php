<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\ReconciliationPeriod;

interface ReconciliationPeriodRepositoryInterface
{
    public function findById(int $id): ?ReconciliationPeriod;

    public function save(ReconciliationPeriod $reconciliationPeriod): ReconciliationPeriod;

    public function findByBusiness(int $businessId): array;

    public function findByBankAccount(int $bankAccountId): array;
}
