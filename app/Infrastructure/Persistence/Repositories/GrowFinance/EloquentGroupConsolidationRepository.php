<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\GroupConsolidation;
use App\Domain\GrowFinance\Repositories\GroupConsolidationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceGroupConsolidationModel;

class EloquentGroupConsolidationRepository implements GroupConsolidationRepositoryInterface
{
    public function findById(int $id): ?GroupConsolidation
    {
        $model = GrowFinanceGroupConsolidationModel::find($id);
        return $model ? GroupConsolidation::reconstitute($model->toArray()) : null;
    }

    public function findByGroup(int $groupId): array
    {
        return GrowFinanceGroupConsolidationModel::where('group_id', $groupId)->get()
            ->map(fn($m) => GroupConsolidation::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByPeriod(int $businessId, string $period): array
    {
        return GrowFinanceGroupConsolidationModel::where('business_id', $businessId)
            ->where('period', $period)
            ->get()
            ->map(fn($m) => GroupConsolidation::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByBusinessAndPeriod(int $businessId, string $period): ?GroupConsolidation
    {
        $model = GrowFinanceGroupConsolidationModel::where('business_id', $businessId)
            ->where('period', $period)
            ->first();
        return $model ? GroupConsolidation::reconstitute($model->toArray()) : null;
    }

    public function save(GroupConsolidation $entity): GroupConsolidation
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceGroupConsolidationModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceGroupConsolidationModel::create($data);
        return GroupConsolidation::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        GrowFinanceGroupConsolidationModel::where('id', $id)->delete();
    }
}
