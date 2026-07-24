<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorQuestionAnswer;
use App\Domain\Investor\Repositories\InvestorQuestionAnswerRepositoryInterface;
use App\Models\Investor\InvestorQuestionAnswer as InvestorQuestionAnswerModel;
use DateTimeImmutable;

class EloquentInvestorQuestionAnswerRepository implements InvestorQuestionAnswerRepositoryInterface
{
    public function save(InvestorQuestionAnswer $answer): InvestorQuestionAnswer
    {
        $data = [
            'question_id' => $answer->getQuestionId(),
            'answered_by' => $answer->getAnsweredByUserId(),
            'answer' => $answer->getAnswer(),
            'answered_at' => $answer->getAnsweredAt()->format('Y-m-d H:i:s'),
        ];

        if ($answer->getId() > 0) {
            $model = InvestorQuestionAnswerModel::findOrFail($answer->getId());
            $model->update($data);
        } else {
            $model = InvestorQuestionAnswerModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorQuestionAnswer
    {
        $model = InvestorQuestionAnswerModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByQuestion(int $questionId): array
    {
        return InvestorQuestionAnswerModel::where('question_id', $questionId)
            ->orderBy('answered_at', 'asc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function delete(int $id): bool
    {
        return InvestorQuestionAnswerModel::destroy($id) > 0;
    }

    private function toDomainEntity(InvestorQuestionAnswerModel $model): InvestorQuestionAnswer
    {
        return InvestorQuestionAnswer::fromPersistence(
            id: $model->id,
            questionId: $model->question_id,
            answeredByUserId: $model->answered_by,
            answer: $model->answer,
            attachments: null,
            answeredAt: $model->answered_at ? new DateTimeImmutable($model->answered_at) : new DateTimeImmutable(),
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
