<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\FixedAsset;
use App\Domain\GrowFinance\Repositories\FixedAssetRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceFixedAssetModel;

class EloquentFixedAssetRepository implements FixedAssetRepositoryInterface
{
    public function findById(int $id): ?FixedAsset
    {
        $model = GrowFinanceFixedAssetModel::find($id);
        return $model ? FixedAsset::reconstitute($model->toArray()) : null;
    }

    public function save(FixedAsset $asset): FixedAsset
    {
        $data = $asset->toArray();
        unset($data['net_book_value']);

        if ($asset->id) {
            GrowFinanceFixedAssetModel::where('id', $asset->id)->update($data);
            $model = GrowFinanceFixedAssetModel::find($asset->id);
        } else {
            $model = GrowFinanceFixedAssetModel::create($data);
        }

        return FixedAsset::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceFixedAssetModel::forBusiness($businessId)
            ->orderBy('purchase_date', 'desc')
            ->get()
            ->map(fn($m) => FixedAsset::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $businessId): array
    {
        return GrowFinanceFixedAssetModel::forBusiness($businessId)
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn($m) => FixedAsset::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByCategory(int $businessId, string $category): array
    {
        return GrowFinanceFixedAssetModel::forBusiness($businessId)
            ->where('category', $category)
            ->get()
            ->map(fn($m) => FixedAsset::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findFullyDepreciated(int $businessId): array
    {
        return GrowFinanceFixedAssetModel::forBusiness($businessId)
            ->where('status', 'fully_depreciated')
            ->get()
            ->map(fn($m) => FixedAsset::reconstitute($m->toArray()))
            ->toArray();
    }

    public function delete(FixedAsset $asset): void
    {
        GrowFinanceFixedAssetModel::where('id', $asset->id)->delete();
    }
}
