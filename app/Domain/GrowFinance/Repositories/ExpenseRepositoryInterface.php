<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\Expense;

interface ExpenseRepositoryInterface
{
    public function findById(int $id): ?Expense;

    public function save(Expense $expense): Expense;

    public function findByBusiness(int $businessId): array;

    public function findByVendor(int $vendorId): array;

    public function findByAccount(int $accountId): array;

    public function findByCategory(int $businessId, string $category): array;

    public function findInDateRange(int $businessId, string $startDate, string $endDate): array;
}
