<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Expense;

interface ExpenseRepositoryInterface
{
    public function findById(int $id): ?Expense;

    public function save(Expense $expense): Expense;

    public function findByCompany(int $companyId): array;

    public function findByCategory(int $categoryId): array;

    public function findByJob(int $jobId): array;

    public function findByApprovalStatus(int $companyId, string $status): array;

    public function getSummary(int $companyId): array;
}
