<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use App\Domain\LoyaltyReward\Entities\RewardPool;
use App\Domain\LoyaltyReward\Repositories\RewardPoolRepository;
use App\Domain\LoyaltyReward\ValueObjects\LoyaltyAmount;

class EloquentRewardPoolRepository implements RewardPoolRepository
{
    public function save(RewardPool $pool): void
    {
        $model = RewardPoolModel::find($pool->getId()) ?? new RewardPoolModel();

        $model->fill([
            'total_balance' => $pool->getTotalBalance()->toKwacha(),
            'available_balance' => $pool->getAvailableBalance()->toKwacha(),
            'reserved_balance' => $pool->getReservedBalance()->toKwacha(),
            'last_updated' => $pool->getLastUpdated(),
        ]);

        $model->save();
    }

    public function getCurrent(): RewardPool
    {
        $model = RewardPoolModel::latest()->first();

        if (!$model) {
            // Create initial pool
            $pool = RewardPool::create();
            $this->save($pool);
            return $pool;
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?RewardPool
    {
        $model = RewardPoolModel::find($id);

        return $model ? $this->toDomainEntity($model) : null;
    }

    private function toDomainEntity(RewardPoolModel $model): RewardPool
    {
        return new RewardPool(
            $model->id,
            LoyaltyAmount::fromKwacha($model->total_balance),
            LoyaltyAmount::fromKwacha($model->available_balance),
            LoyaltyAmount::fromKwacha($model->reserved_balance),
            $model->last_updated
        );
    }
}
