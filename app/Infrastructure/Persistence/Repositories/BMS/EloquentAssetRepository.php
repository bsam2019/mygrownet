<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Asset;
use App\Domain\BMS\Repositories\AssetRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\AssetModel;

class EloquentAssetRepository implements AssetRepositoryInterface
{
    public function findById(int $id): ?Asset
    {
        $model = AssetModel::find($id);
        return $model ? Asset::reconstitute($model->toArray()) : null;
    }

    public function save(Asset $entity): Asset
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            AssetModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = AssetModel::create($data);
        return Asset::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return AssetModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Asset::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByType(int $companyId, string $type): array
    {
        return AssetModel::where('company_id', $companyId)->where('asset_type', $type)->get()
            ->map(fn($m) => Asset::reconstitute($m->toArray()))
            ->toArray();
    }
}
