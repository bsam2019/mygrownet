<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\DepreciationEntry;
use App\Domain\GrowFinance\Repositories\DepreciationScheduleRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceDepreciationScheduleModel;

class EloquentDepreciationScheduleRepository implements DepreciationScheduleRepositoryInterface
{
    public function findById(int $id): ?DepreciationEntry
    {
        $model = GrowFinanceDepreciationScheduleModel::find($id);
        return $model ? DepreciationEntry::reconstitute($model->toArray()) : null;
    }

    public function save(DepreciationEntry $entry): DepreciationEntry
    {
        $data = $entry->toArray();
        if ($entry->id) {
            GrowFinanceDepreciationScheduleModel::where('id', $entry->id)->update($data);
            $model = GrowFinanceDepreciationScheduleModel::find($entry->id);
        } else {
            $model = GrowFinanceDepreciationScheduleModel::create($data);
        }
        return DepreciationEntry::reconstitute($model->toArray());
    }

    public function findByAsset(int $assetId): array
    {
        return GrowFinanceDepreciationScheduleModel::where('asset_id', $assetId)
            ->orderBy('period_date')
            ->get()
            ->map(fn($m) => DepreciationEntry::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findUnposted(int $assetId): array
    {
        return GrowFinanceDepreciationScheduleModel::where('asset_id', $assetId)
            ->whereNull('journal_entry_id')
            ->get()
            ->map(fn($m) => DepreciationEntry::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findForPeriod(int $businessId, string $periodDate): array
    {
        return GrowFinanceDepreciationScheduleModel::whereHas('asset', fn($q) => $q->where('business_id', $businessId))
            ->where('period_date', $periodDate)
            ->get()
            ->map(fn($m) => DepreciationEntry::reconstitute($m->toArray()))
            ->toArray();
    }

    public function deleteForAsset(int $assetId): void
    {
        GrowFinanceDepreciationScheduleModel::where('asset_id', $assetId)->delete();
    }
}
