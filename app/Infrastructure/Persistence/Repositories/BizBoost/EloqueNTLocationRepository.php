<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\Location;
use App\Domain\BizBoost\Repositories\LocationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostLocationModel;
use Illuminate\Support\Facades\DB;

class EloquentLocationRepository implements LocationRepositoryInterface
{
    public function findById(int $id): ?Location
    {
        $model = BizBoostLocationModel::find($id);
        return $model ? Location::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId): array
    {
        return BizBoostLocationModel::where('business_id', $businessId)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get()
            ->map(fn($m) => Location::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findPrimary(int $businessId): ?Location
    {
        $model = BizBoostLocationModel::where('business_id', $businessId)
            ->where('is_primary', true)
            ->first();
        return $model ? Location::reconstitute($model->toArray()) : null;
    }

    public function save(Location $entity): Location
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($data['business_hours'] && is_array($data['business_hours'])) {
            $data['business_hours'] = json_encode($data['business_hours']);
        }

        if ($id) {
            BizBoostLocationModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostLocationModel::create($data);
        return Location::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostLocationModel::destroy($id);
    }

    public function countByBusiness(int $businessId): int
    {
        return BizBoostLocationModel::where('business_id', $businessId)->count();
    }
}