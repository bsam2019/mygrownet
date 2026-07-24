<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Employee;

use App\Domain\Employee\Repositories\EmployeeCommissionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use Illuminate\Support\Collection;

class EloquentEmployeeCommissionRepository implements EmployeeCommissionRepositoryInterface
{
    public function findById(int $id): ?EmployeeCommissionModel
    {
        return EmployeeCommissionModel::find($id);
    }

    public function findByEmployee(int $employeeId): Collection
    {
        return EmployeeCommissionModel::where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function query()
    {
        return EmployeeCommissionModel::query();
    }

    public function save(array $data): EmployeeCommissionModel
    {
        return EmployeeCommissionModel::create($data);
    }

    public function update(int $id, array $data): EmployeeCommissionModel
    {
        $model = EmployeeCommissionModel::findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(int $id): void
    {
        EmployeeCommissionModel::destroy($id);
    }
}
