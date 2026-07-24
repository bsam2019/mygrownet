<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Illuminate\Support\Collection;

class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function findById(EmployeeId $id): ?EmployeeModel
    {
        return EmployeeModel::with(['department', 'position', 'manager', 'user'])
            ->find($id->toInt());
    }

    public function findByUserId(int $userId): ?EmployeeModel
    {
        return EmployeeModel::with(['department', 'position', 'manager', 'user'])
            ->where('user_id', $userId)
            ->first();
    }

    public function findByDepartment(int $departmentId): Collection
    {
        return EmployeeModel::with(['position', 'user'])
            ->where('department_id', $departmentId)
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get();
    }

    public function findByManager(EmployeeId $managerId): Collection
    {
        return EmployeeModel::with(['department', 'position', 'user'])
            ->where('manager_id', $managerId->toInt())
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get();
    }

    public function getActiveEmployees(): Collection
    {
        return EmployeeModel::with(['department', 'position'])
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get();
    }

    public function query()
    {
        return EmployeeModel::query();
    }

    public function save(object $employee): void
    {
        if ($employee instanceof EmployeeModel) {
            $employee->save();
        }
    }
}
