<?php

namespace App\Domain\LoyaltyReward\Repositories;

use App\Domain\LoyaltyReward\Entities\RewardPool;

interface RewardPoolRepository
{
    public function save(RewardPool $pool): void;

    public function getCurrent(): RewardPool;

    public function findById(int $id): ?RewardPool;
}
