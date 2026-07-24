<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\QuarterlyReport;
use App\Domain\Investor\Repositories\QuarterlyReportRepositoryInterface;
use App\Models\QuarterlyReport as QuarterlyReportModel;
use DateTimeImmutable;

class EloquentQuarterlyReportRepository implements QuarterlyReportRepositoryInterface
{
    public function findLatest(): ?QuarterlyReport
    {
        $model = QuarterlyReportModel::orderBy('year', 'desc')
            ->orderBy('quarter', 'desc')
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findById(int $id): ?QuarterlyReport
    {
        $model = QuarterlyReportModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    private function toDomainEntity(QuarterlyReportModel $model): QuarterlyReport
    {
        return QuarterlyReport::fromPersistence(
            id: $model->id,
            title: $model->title,
            quarter: $model->quarter,
            year: $model->year,
            publishedAt: $model->published_at ? new DateTimeImmutable($model->published_at) : null
        );
    }
}
