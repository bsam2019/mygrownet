<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Employee;

use App\Domain\Employee\Repositories\EmployeePerformanceRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use Illuminate\Support\Collection;

class EloquentEmployeePerformanceRepository implements EmployeePerformanceRepositoryInterface
{
    public function findById(int $id): ?EmployeePerformanceModel
    {
        return EmployeePerformanceModel::find($id);
    }

    public function findByEmployee(int $employeeId, array $filters = []): Collection
    {
        $query = EmployeePerformanceModel::where('employee_id', $employeeId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['period'])) {
            $query->where('evaluation_period', 'like', '%' . $filters['period'] . '%');
        }

        return $query->orderBy('evaluation_period_end', 'desc')->get();
    }

    public function query()
    {
        return EmployeePerformanceModel::query();
    }

    public function save(array $data): EmployeePerformanceModel
    {
        return EmployeePerformanceModel::create($data);
    }

    public function update(int $id, array $data): EmployeePerformanceModel
    {
        $model = EmployeePerformanceModel::findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(int $id): void
    {
        EmployeePerformanceModel::destroy($id);
    }
}
