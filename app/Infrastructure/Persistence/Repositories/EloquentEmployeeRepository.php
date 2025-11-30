<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Models\Employee;
use Illuminate\Support\Collection;

class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function findById(EmployeeId $id): ?Employee
    {
        return Employee::with(['department', 'position', 'manager', 'user'])
            ->find($id->toInt());
    }

    public function findByUserId(int $userId): ?Employee
    {
        return Employee::with(['department', 'position', 'manager', 'user'])
            ->where('user_id', $userId)
            ->first();
    }

    public function findByDepartment(int $departmentId): Collection
    {
        return Employee::with(['position', 'user'])
            ->where('department_id', $departmentId)
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get();
    }

    public function findByManager(EmployeeId $managerId): Collection
    {
        return Employee::with(['department', 'position', 'user'])
            ->where('manager_id', $managerId->toInt())
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get();
    }

    public function getActiveEmployees(): Collection
    {
        return Employee::with(['department', 'position'])
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get();
    }

    public function save(object $employee): void
    {
        if ($employee instanceof Employee) {
            $employee->save();
        }
    }
}
