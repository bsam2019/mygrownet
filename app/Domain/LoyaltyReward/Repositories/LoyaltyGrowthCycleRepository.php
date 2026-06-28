<?php

namespace App\Domain\LoyaltyReward\Repositories;

use App\Domain\LoyaltyReward\Entities\LoyaltyGrowthCycle;
use App\Domain\LoyaltyReward\ValueObjects\CycleId;
use DateTimeImmutable;

interface LoyaltyGrowthCycleRepository
{
    public function save(LoyaltyGrowthCycle $cycle): void;

    public function findById(CycleId $id): ?LoyaltyGrowthCycle;

    public function findActiveCycleByUserId(int $userId): ?LoyaltyGrowthCycle;

    public function findCompletedCyclesByUserId(int $userId): array;

    public function findExpiredActiveCycles(): array;

    public function hasActivityForDate(CycleId $cycleId, DateTimeImmutable $date): bool;

    public function findAllActiveCycles(): array;
}
