<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorPoll;
use App\Domain\Investor\Repositories\InvestorPollRepositoryInterface;
use App\Models\Investor\InvestorPoll as InvestorPollModel;
use DateTimeImmutable;

class EloquentInvestorPollRepository implements InvestorPollRepositoryInterface
{
    public function save(InvestorPoll $poll): InvestorPoll
    {
        $data = [
            'question' => $poll->getQuestion(),
            'options' => $poll->getOptions(),
            'poll_type' => $poll->getPollType(),
            'end_date' => $poll->getEndDate()->format('Y-m-d'),
            'allow_multiple' => $poll->allowMultiple(),
            'status' => 'active',
        ];

        if ($poll->getId() > 0) {
            $model = InvestorPollModel::findOrFail($poll->getId());
            $model->update($data);
        } else {
            $model = InvestorPollModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorPoll
    {
        $model = InvestorPollModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findActive(): array
    {
        return InvestorPollModel::active()
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    private function toDomainEntity(InvestorPollModel $model): InvestorPoll
    {
        return InvestorPoll::fromPersistence(
            id: $model->id,
            question: $model->question,
            options: $model->options ?? [],
            pollType: $model->poll_type ?? 'single',
            endDate: new DateTimeImmutable($model->end_date),
            allowMultiple: $model->allow_multiple ?? false,
            voteCount: $model->votes()->count(),
            results: $model->results,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
