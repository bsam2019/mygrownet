<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Employee;

use App\Domain\Employee\Repositories\DepartmentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use Illuminate\Support\Collection;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    public function findById(int $id): ?DepartmentModel
    {
        return DepartmentModel::find($id);
    }

    public function findByName(string $name): ?DepartmentModel
    {
        return DepartmentModel::where('name', $name)->first();
    }

    public function getAllActive(): Collection
    {
        return DepartmentModel::active()->orderBy('name')->get();
    }

    public function getAll(): Collection
    {
        return DepartmentModel::orderBy('name')->get();
    }

    public function query()
    {
        return DepartmentModel::query();
    }

    public function save(array $data): DepartmentModel
    {
        return DepartmentModel::create($data);
    }

    public function update(int $id, array $data): DepartmentModel
    {
        $model = DepartmentModel::findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(int $id): void
    {
        DepartmentModel::destroy($id);
    }
}
