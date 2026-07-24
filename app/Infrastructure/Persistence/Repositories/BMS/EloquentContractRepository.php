<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Contract;
use App\Domain\BMS\Repositories\ContractRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\ContractModel;

class EloquentContractRepository implements ContractRepositoryInterface
{
    public function findById(int $id): ?Contract
    {
        $model = ContractModel::find($id);
        return $model ? Contract::reconstitute($model->toArray()) : null;
    }

    public function save(Contract $entity): Contract
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            ContractModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = ContractModel::create($data);
        return Contract::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return ContractModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Contract::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByStatus(int $companyId, string $status): array
    {
        return ContractModel::where('company_id', $companyId)->where('status', $status)->get()
            ->map(fn($m) => Contract::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findExpiring(int $companyId): array
    {
        return ContractModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '>=', now()->toDateString())
            ->where('end_date', '<=', now()->addDays(30)->toDateString())
            ->get()
            ->map(fn($m) => Contract::reconstitute($m->toArray()))
            ->toArray();
    }
}
