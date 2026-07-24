<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use App\Domain\Employee\ValueObjects\EmployeeId;
use Illuminate\Support\Collection;

interface ExpenseRepositoryInterface
{
    public function findByEmployee(EmployeeId $employeeId, array $filters = []): Collection;

    public function findById(int $id): ?object;

    public function findForYear(EmployeeId $employeeId, int $year): Collection;

    public function create(array $data): object;

    public function update(int $id, array $data): ?object;

    public function delete(int $id): bool;
}