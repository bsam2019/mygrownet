<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorSurvey;
use App\Domain\Investor\Repositories\InvestorSurveyRepositoryInterface;
use App\Models\Investor\InvestorSurvey as InvestorSurveyModel;
use DateTimeImmutable;

class EloquentInvestorSurveyRepository implements InvestorSurveyRepositoryInterface
{
    public function save(InvestorSurvey $survey): InvestorSurvey
    {
        $data = [
            'title' => $survey->getTitle(),
            'description' => $survey->getDescription(),
            'survey_type' => $survey->getSurveyType(),
            'questions' => $survey->getQuestions(),
            'status' => $survey->getStatus(),
            'start_date' => $survey->getStartDate()->format('Y-m-d H:i:s'),
            'end_date' => $survey->getEndDate()->format('Y-m-d H:i:s'),
            'is_anonymous' => $survey->isAnonymous(),
        ];

        if ($survey->getId() > 0) {
            $model = InvestorSurveyModel::findOrFail($survey->getId());
            $model->update($data);
        } else {
            $model = InvestorSurveyModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorSurvey
    {
        $model = InvestorSurveyModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findActive(): array
    {
        return InvestorSurveyModel::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findAll(): array
    {
        return InvestorSurveyModel::all()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    private function toDomainEntity(InvestorSurveyModel $model): InvestorSurvey
    {
        return InvestorSurvey::fromPersistence(
            id: $model->id,
            title: $model->title,
            description: $model->description,
            surveyType: $model->survey_type ?? 'general',
            questions: $model->questions ?? [],
            status: $model->status,
            startDate: new DateTimeImmutable($model->start_date),
            endDate: new DateTimeImmutable($model->end_date),
            isAnonymous: $model->is_anonymous ?? false,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
