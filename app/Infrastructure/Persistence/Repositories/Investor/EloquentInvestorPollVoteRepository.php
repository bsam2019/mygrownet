<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorPollVote;
use App\Domain\Investor\Repositories\InvestorPollVoteRepositoryInterface;
use App\Models\Investor\InvestorPollVote as InvestorPollVoteModel;
use DateTimeImmutable;

class EloquentInvestorPollVoteRepository implements InvestorPollVoteRepositoryInterface
{
    public function save(InvestorPollVote $vote): InvestorPollVote
    {
        $data = [
            'poll_id' => $vote->getPollId(),
            'investor_account_id' => $vote->getInvestorAccountId(),
            'selected_options' => $vote->getSelectedOptions(),
            'voted_at' => $vote->getVotedAt()->format('Y-m-d H:i:s'),
        ];

        if ($vote->getId() > 0) {
            $model = InvestorPollVoteModel::findOrFail($vote->getId());
            $model->update($data);
        } else {
            $model = InvestorPollVoteModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorPollVote
    {
        $model = InvestorPollVoteModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByPollAndInvestor(int $pollId, int $investorAccountId): ?InvestorPollVote
    {
        $model = InvestorPollVoteModel::where('poll_id', $pollId)
            ->where('investor_account_id', $investorAccountId)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findVotedPollIds(int $investorAccountId): array
    {
        return InvestorPollVoteModel::where('investor_account_id', $investorAccountId)
            ->pluck('poll_id')
            ->toArray();
    }

    private function toDomainEntity(InvestorPollVoteModel $model): InvestorPollVote
    {
        return InvestorPollVote::fromPersistence(
            id: $model->id,
            pollId: $model->poll_id,
            investorAccountId: $model->investor_account_id,
            selectedOptions: $model->selected_options ?? [],
            votedAt: $model->voted_at ? new DateTimeImmutable($model->voted_at) : new DateTimeImmutable(),
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
