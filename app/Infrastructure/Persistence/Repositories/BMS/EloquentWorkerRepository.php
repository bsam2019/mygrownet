<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Worker;
use App\Domain\BMS\Repositories\WorkerRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\WorkerModel;

class EloquentWorkerRepository implements WorkerRepositoryInterface
{
    public function findById(int $id): ?Worker
    {
        $model = WorkerModel::find($id);
        return $model ? Worker::reconstitute($model->toArray()) : null;
    }

    public function save(Worker $entity): Worker
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            WorkerModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = WorkerModel::create($data);
        return Worker::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return WorkerModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Worker::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $companyId): array
    {
        return WorkerModel::where('company_id', $companyId)->where('status', 'active')->get()
            ->map(fn($m) => Worker::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByDepartment(int $companyId, string $department): array
    {
        return WorkerModel::where('company_id', $companyId)->where('department', $department)->get()
            ->map(fn($m) => Worker::reconstitute($m->toArray()))
            ->toArray();
    }
}
