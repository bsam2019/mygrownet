<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\ExitProjection;
use App\Domain\Investor\Repositories\ExitProjectionRepositoryInterface;
use App\Models\ExitProjection as ExitProjectionModel;
use DateTimeImmutable;

class EloquentExitProjectionRepository implements ExitProjectionRepositoryInterface
{
    public function findAll(): array
    {
        return ExitProjectionModel::orderBy('probability_percentage', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    private function toDomainEntity(ExitProjectionModel $model): ExitProjection
    {
        return ExitProjection::fromPersistence(
            id: $model->id,
            exitType: $model->exit_type,
            title: $model->title,
            projectedDate: $model->projected_date ? new DateTimeImmutable($model->projected_date) : null,
            projectedValuation: $model->projected_valuation ? (float) $model->projected_valuation : null,
            projectedMultiple: $model->projected_multiple ? (float) $model->projected_multiple : null,
            probabilityPercentage: (float) $model->probability_percentage,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
