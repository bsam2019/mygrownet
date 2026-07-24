<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\CmsUser;
use App\Domain\BMS\Repositories\CmsUserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\CmsUserModel;

class EloquentCmsUserRepository implements CmsUserRepositoryInterface
{
    public function findById(int $id): ?CmsUser
    {
        $model = CmsUserModel::find($id);
        return $model ? CmsUser::reconstitute($model->toArray()) : null;
    }

    public function save(CmsUser $entity): CmsUser
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            CmsUserModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = CmsUserModel::create($data);
        return CmsUser::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return CmsUserModel::where('company_id', $companyId)->get()
            ->map(fn($m) => CmsUser::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByUser(int $userId): ?CmsUser
    {
        $model = CmsUserModel::where('user_id', $userId)->first();
        return $model ? CmsUser::reconstitute($model->toArray()) : null;
    }

    public function findActiveByCompany(int $companyId): array
    {
        return CmsUserModel::where('company_id', $companyId)->where('status', 'active')->get()
            ->map(fn($m) => CmsUser::reconstitute($m->toArray()))
            ->toArray();
    }
}
