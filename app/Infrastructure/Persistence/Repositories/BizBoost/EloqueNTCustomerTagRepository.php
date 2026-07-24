<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\CustomerTag;
use App\Domain\BizBoost\Repositories\CustomerTagRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostCustomerTagModel;

class EloquentCustomerTagRepository implements CustomerTagRepositoryInterface
{
    public function findById(int $id): ?CustomerTag
    {
        $model = BizBoostCustomerTagModel::find($id);
        return $model ? CustomerTag::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId): array
    {
        return BizBoostCustomerTagModel::where('business_id', $businessId)->get()
            ->map(fn($m) => CustomerTag::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(CustomerTag $entity): CustomerTag
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostCustomerTagModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostCustomerTagModel::create($data);
        return CustomerTag::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostCustomerTagModel::destroy($id);
    }
}