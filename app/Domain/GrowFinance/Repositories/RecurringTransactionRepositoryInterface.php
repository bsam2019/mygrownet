<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\RecurringTransaction;

interface RecurringTransactionRepositoryInterface
{
    public function findById(int $id): ?RecurringTransaction;

    public function save(RecurringTransaction $recurringTransaction): RecurringTransaction;

    public function findByBusiness(int $businessId): array;

    public function findActive(int $businessId): array;

    public function findDueToday(int $businessId): array;

    public function findByType(int $businessId, string $type): array;
}
