<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Employee;

use App\Domain\Employee\Repositories\PositionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Support\Collection;

class EloquentPositionRepository implements PositionRepositoryInterface
{
    public function findById(int $id): ?PositionModel
    {
        return PositionModel::find($id);
    }

    public function findByDepartment(int $departmentId): Collection
    {
        return PositionModel::where('department_id', $departmentId)
            ->active()
            ->orderBy('title')
            ->get();
    }

    public function getAllActive(): Collection
    {
        return PositionModel::active()->orderBy('title')->get();
    }

    public function getAll(): Collection
    {
        return PositionModel::orderBy('title')->get();
    }

    public function query()
    {
        return PositionModel::query();
    }

    public function save(array $data): PositionModel
    {
        return PositionModel::create($data);
    }

    public function update(int $id, array $data): PositionModel
    {
        $model = PositionModel::findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(int $id): void
    {
        PositionModel::destroy($id);
    }
}
