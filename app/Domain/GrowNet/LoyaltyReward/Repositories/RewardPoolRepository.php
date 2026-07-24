<?php

namespace App\Domain\GrowNet\LoyaltyReward\Repositories;

use App\Domain\GrowNet\LoyaltyReward\Entities\RewardPool;

interface RewardPoolRepository
{
    public function save(RewardPool $pool): void;

    public function getCurrent(): RewardPool;

    public function findById(int $id): ?RewardPool;
}
