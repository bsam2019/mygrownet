<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Repositories\ReportRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceReportSnapshotModel;

class EloquentReportRepository implements ReportRepositoryInterface
{
    public function saveSnapshot(int $businessId, string $reportType, string $asOfDate, array $data): int
    {
        $model = GrowFinanceReportSnapshotModel::updateOrCreate(
            [
                'business_id' => $businessId,
                'report_type' => $reportType,
                'as_of_date' => $asOfDate,
            ],
            [
                'report_data' => $data,
            ]
        );

        return $model->id;
    }

    public function findSnapshot(int $businessId, string $reportType, string $asOfDate): ?array
    {
        $model = GrowFinanceReportSnapshotModel::forBusiness($businessId)
            ->ofType($reportType)
            ->where('as_of_date', $asOfDate)
            ->first();

        return $model ? $model->toArray() : null;
    }

    public function findSnapshotsByType(int $businessId, string $reportType, int $limit = 10): array
    {
        return GrowFinanceReportSnapshotModel::forBusiness($businessId)
            ->ofType($reportType)
            ->orderBy('as_of_date', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function deleteSnapshotsOlderThan(int $businessId, string $date): int
    {
        return GrowFinanceReportSnapshotModel::forBusiness($businessId)
            ->where('as_of_date', '<', $date)
            ->delete();
    }

    public function findSnapshotById(int $id): ?array
    {
        $model = GrowFinanceReportSnapshotModel::find($id);
        return $model ? $model->toArray() : null;
    }

    public function updateSnapshotHash(int $id, string $hash): void
    {
        GrowFinanceReportSnapshotModel::where('id', $id)->update(['integrity_hash' => $hash]);
    }

    public function lockSnapshot(int $id): void
    {
        GrowFinanceReportSnapshotModel::where('id', $id)->update(['locked_at' => now()]);
    }
}
