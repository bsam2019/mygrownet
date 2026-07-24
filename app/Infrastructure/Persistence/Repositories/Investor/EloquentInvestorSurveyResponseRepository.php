<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorSurveyResponse;
use App\Domain\Investor\Repositories\InvestorSurveyResponseRepositoryInterface;
use App\Models\Investor\InvestorSurveyResponse as InvestorSurveyResponseModel;
use DateTimeImmutable;

class EloquentInvestorSurveyResponseRepository implements InvestorSurveyResponseRepositoryInterface
{
    public function save(InvestorSurveyResponse $response): InvestorSurveyResponse
    {
        $data = [
            'survey_id' => $response->getSurveyId(),
            'investor_account_id' => $response->getInvestorAccountId(),
            'answers' => $response->getResponses(),
            'submitted_at' => $response->getSubmittedAt()->format('Y-m-d H:i:s'),
        ];

        if ($response->getId() > 0) {
            $model = InvestorSurveyResponseModel::findOrFail($response->getId());
            $model->update($data);
        } else {
            $model = InvestorSurveyResponseModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorSurveyResponse
    {
        $model = InvestorSurveyResponseModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySurvey(int $surveyId): array
    {
        return InvestorSurveyResponseModel::where('survey_id', $surveyId)
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findByInvestor(int $investorAccountId): array
    {
        return InvestorSurveyResponseModel::where('investor_account_id', $investorAccountId)
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function hasCompleted(int $surveyId, int $investorAccountId): bool
    {
        return InvestorSurveyResponseModel::where('survey_id', $surveyId)
            ->where('investor_account_id', $investorAccountId)
            ->exists();
    }

    public function findCompletedSurveyIds(int $investorAccountId): array
    {
        return InvestorSurveyResponseModel::where('investor_account_id', $investorAccountId)
            ->pluck('survey_id')
            ->toArray();
    }

    private function toDomainEntity(InvestorSurveyResponseModel $model): InvestorSurveyResponse
    {
        return InvestorSurveyResponse::fromPersistence(
            id: $model->id,
            surveyId: $model->survey_id,
            investorAccountId: $model->investor_account_id,
            responses: $model->answers ?? [],
            submittedAt: $model->submitted_at ? new DateTimeImmutable($model->submitted_at) : new DateTimeImmutable(),
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
