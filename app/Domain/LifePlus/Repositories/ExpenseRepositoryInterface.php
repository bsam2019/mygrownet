<?php

declare(strict_types=1);

namespace App\Domain\LifePlus\Repositories;

use App\Domain\LifePlus\Entities\LifePlusExpense;

interface ExpenseRepositoryInterface
{
    public function findById(int $id): ?LifePlusExpense;

    public function save(LifePlusExpense $expense): LifePlusExpense;

    public function delete(int $id): bool;

    public function findByUser(int $userId, array $filters = []): array;

    public function findByLocalId(int $userId, string $localId): ?LifePlusExpense;

    public function getMonthSummary(int $userId, ?string $month = null): array;
}
