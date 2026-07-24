<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\WhiteLabel;
use App\Domain\GrowFinance\Repositories\WhiteLabelRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceWhiteLabelModel;

class EloquentWhiteLabelRepository implements WhiteLabelRepositoryInterface
{
    public function findById(int $id): ?WhiteLabel
    {
        $model = GrowFinanceWhiteLabelModel::find($id);
        return $model ? WhiteLabel::reconstitute($model->toArray()) : null;
    }

    public function save(WhiteLabel $entity): WhiteLabel
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceWhiteLabelModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceWhiteLabelModel::create($data);
        return WhiteLabel::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): ?WhiteLabel
    {
        $_ = GrowFinanceWhiteLabelModel::forBusiness($businessId)->first(); return $_ ? WhiteLabel::reconstitute($_->toArray()) : null;
    }

}
