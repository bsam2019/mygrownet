<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\ExpenseCategory;

interface ExpenseCategoryRepositoryInterface
{
    public function findById(int $id): ?ExpenseCategory;

    public function save(ExpenseCategory $category): ExpenseCategory;

    public function findByCompany(int $companyId): array;

    public function findActive(int $companyId): array;
}
