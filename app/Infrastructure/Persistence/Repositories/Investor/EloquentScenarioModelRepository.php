<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\ScenarioModel;
use App\Domain\Investor\Repositories\ScenarioModelRepositoryInterface;
use App\Models\ScenarioModel as ScenarioModelModel;
use DateTimeImmutable;

class EloquentScenarioModelRepository implements ScenarioModelRepositoryInterface
{
    public function findActive(): array
    {
        return ScenarioModelModel::getActiveScenarios()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    private function toDomainEntity(ScenarioModelModel $model): ScenarioModel
    {
        return ScenarioModel::fromPersistence(
            id: $model->id,
            name: $model->name,
            scenarioType: $model->scenario_type,
            projectedValuation1y: $model->projected_valuation_1y ? (float) $model->projected_valuation_1y : null,
            projectedRoi1y: $model->projected_roi_1y ? (float) $model->projected_roi_1y : null,
            projectedValuation3y: $model->projected_valuation_3y ? (float) $model->projected_valuation_3y : null,
            projectedRoi3y: $model->projected_roi_3y ? (float) $model->projected_roi_3y : null,
            projectedValuation5y: $model->projected_valuation_5y ? (float) $model->projected_valuation_5y : null,
            projectedRoi5y: $model->projected_roi_5y ? (float) $model->projected_roi_5y : null,
            assumptions: $model->assumptions,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
