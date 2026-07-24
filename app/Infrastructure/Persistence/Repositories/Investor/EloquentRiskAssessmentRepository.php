<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\RiskAssessment;
use App\Domain\Investor\Repositories\RiskAssessmentRepositoryInterface;
use App\Models\RiskAssessment as RiskAssessmentModel;
use DateTimeImmutable;

class EloquentRiskAssessmentRepository implements RiskAssessmentRepositoryInterface
{
    public function findLatest(): ?RiskAssessment
    {
        $model = RiskAssessmentModel::getLatest();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findHistory(int $limit = 12): array
    {
        return RiskAssessmentModel::orderBy('assessment_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    private function toDomainEntity(RiskAssessmentModel $model): RiskAssessment
    {
        return RiskAssessment::fromPersistence(
            id: $model->id,
            riskLevel: $model->risk_level,
            riskScore: (float) $model->overall_risk_score,
            assessmentDate: new DateTimeImmutable($model->assessment_date),
            factors: $model->risk_factors,
            notes: $model->summary,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
