<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\Profile;
use App\Domain\GrowFinance\Repositories\ProfileRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceProfileModel;

class EloquentProfileRepository implements ProfileRepositoryInterface
{
    public function findById(int $id): ?Profile
    {
        $model = GrowFinanceProfileModel::find($id);
        return $model ? Profile::reconstitute($model->toArray()) : null;
    }

    public function save(Profile $entity): Profile
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceProfileModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceProfileModel::create($data);
        return Profile::reconstitute($model->toArray());
    }

    public function findByUser(int $userId): ?Profile
    {
        $_ = GrowFinanceProfileModel::where('user_id', $userId)->first(); return $_ ? Profile::reconstitute($_->toArray()) : null;
    }

}
