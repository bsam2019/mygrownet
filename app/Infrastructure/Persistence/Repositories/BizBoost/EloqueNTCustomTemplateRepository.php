<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\CustomTemplate;
use App\Domain\BizBoost\Repositories\CustomTemplateRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostCustomTemplateModel;

class EloquentCustomTemplateRepository implements CustomTemplateRepositoryInterface
{
    public function findById(int $id): ?CustomTemplate
    {
        $model = BizBoostCustomTemplateModel::find($id);
        return $model ? CustomTemplate::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId): array
    {
        return BizBoostCustomTemplateModel::where('business_id', $businessId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($m) => CustomTemplate::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(CustomTemplate $entity): CustomTemplate
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostCustomTemplateModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostCustomTemplateModel::create($data);
        return CustomTemplate::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostCustomTemplateModel::destroy($id);
    }
}