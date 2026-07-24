<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\Budget;

interface BudgetRepositoryInterface
{
    public function findById(int $id): ?Budget;

    public function save(Budget $budget): Budget;

    public function findByBusiness(int $businessId): array;

    public function findActive(int $businessId): array;

    public function findCurrent(int $businessId): array;

    public function findByCategory(int $businessId, string $category): array;
}
