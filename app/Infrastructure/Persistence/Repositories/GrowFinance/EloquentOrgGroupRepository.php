<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\OrgGroup;
use App\Domain\GrowFinance\Repositories\OrgGroupRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceOrgGroupModel;

class EloquentOrgGroupRepository implements OrgGroupRepositoryInterface
{
    public function findById(int $id): ?OrgGroup
    {
        $model = GrowFinanceOrgGroupModel::find($id);
        return $model ? OrgGroup::reconstitute($model->toArray()) : null;
    }

    public function findByParent(int $parentOrgId): array
    {
        return GrowFinanceOrgGroupModel::where('parent_org_id', $parentOrgId)->get()
            ->map(fn($m) => OrgGroup::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByChild(int $childOrgId): ?OrgGroup
    {
        $model = GrowFinanceOrgGroupModel::where('child_org_id', $childOrgId)->first();
        return $model ? OrgGroup::reconstitute($model->toArray()) : null;
    }

    public function findSubsidiaries(int $parentOrgId): array
    {
        return GrowFinanceOrgGroupModel::where('parent_org_id', $parentOrgId)
            ->where('is_active', true)
            ->get()
            ->map(fn($m) => OrgGroup::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(OrgGroup $entity): OrgGroup
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceOrgGroupModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceOrgGroupModel::create($data);
        return OrgGroup::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        GrowFinanceOrgGroupModel::where('id', $id)->delete();
    }
}
