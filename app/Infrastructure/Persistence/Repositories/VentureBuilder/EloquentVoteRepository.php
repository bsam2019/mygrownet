<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\VentureBuilder;

use App\Domain\VentureBuilder\Entities\Vote;
use App\Domain\VentureBuilder\Repositories\VoteRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureVoteModel;

class EloquentVoteRepository implements VoteRepositoryInterface
{
    public function findByResolutionAndShareholder(int $resolutionId, int $shareholderId): ?Vote
    {
        $model = VentureVoteModel::where('resolution_id', $resolutionId)
            ->where('shareholder_id', $shareholderId)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(Vote $vote): Vote
    {
        $data = $vote->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id) {
            VentureVoteModel::where('id', $id)->update($data);
            return $vote;
        }

        $model = VentureVoteModel::create($data);
        return $this->toDomainEntity($model);
    }

    private function toDomainEntity(VentureVoteModel $model): Vote
    {
        return Vote::reconstitute($model->toArray());
    }
}
