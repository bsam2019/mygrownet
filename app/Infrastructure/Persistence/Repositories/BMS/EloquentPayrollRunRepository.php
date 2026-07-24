<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\PayrollRun;
use App\Domain\BMS\Repositories\PayrollRunRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\PayrollRunModel;

class EloquentPayrollRunRepository implements PayrollRunRepositoryInterface
{
    public function findById(int $id): ?PayrollRun
    {
        $model = PayrollRunModel::find($id);
        return $model ? PayrollRun::reconstitute($model->toArray()) : null;
    }

    public function save(PayrollRun $entity): PayrollRun
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            PayrollRunModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = PayrollRunModel::create($data);
        return PayrollRun::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return PayrollRunModel::where('company_id', $companyId)->get()
            ->map(fn($m) => PayrollRun::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findLatest(int $companyId): ?PayrollRun
    {
        $model = PayrollRunModel::where('company_id', $companyId)->latest()->first();
        return $model ? PayrollRun::reconstitute($model->toArray()) : null;
    }
}
