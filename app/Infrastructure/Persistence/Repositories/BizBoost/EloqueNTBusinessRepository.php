<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\Business;
use App\Domain\BizBoost\Entities\BusinessProfile;
use App\Domain\BizBoost\Repositories\BusinessRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessProfileModel;

class EloquentBusinessRepository implements BusinessRepositoryInterface
{
    public function findById(int $id): ?Business
    {
        $model = BizBoostBusinessModel::find($id);
        return $model ? Business::reconstitute($model->toArray()) : null;
    }

    public function findByUserId(int $userId): ?Business
    {
        $model = BizBoostBusinessModel::where('user_id', $userId)->first();
        return $model ? Business::reconstitute($model->toArray()) : null;
    }

    public function findBySlug(string $slug): ?Business
    {
        $model = BizBoostBusinessModel::where('slug', $slug)->first();
        return $model ? Business::reconstitute($model->toArray()) : null;
    }

    public function save(Business $entity): Business
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostBusinessModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostBusinessModel::create($data);
        return Business::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostBusinessModel::destroy($id);
    }

    public function findProfile(int $businessId): ?BusinessProfile
    {
        $model = BizBoostBusinessProfileModel::where('business_id', $businessId)->first();
        return $model ? BusinessProfile::reconstitute($model->toArray()) : null;
    }

    public function saveProfile(BusinessProfile $profile): BusinessProfile
    {
        $data = $profile->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostBusinessProfileModel::where('id', $id)->update($data);
            $model = BizBoostBusinessProfileModel::find($id);
            return BusinessProfile::reconstitute($model->toArray());
        }

        $model = BizBoostBusinessProfileModel::create($data);
        return BusinessProfile::reconstitute($model->toArray());
    }

    public function getBusinessIdsByUserId(int $userId): array
    {
        return BizBoostBusinessModel::where('user_id', $userId)
            ->pluck('id')
            ->toArray();
    }
}