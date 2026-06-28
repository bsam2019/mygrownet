<?php

namespace App\Domain\LoyaltyReward\Repositories;

use App\Domain\LoyaltyReward\Entities\LoyaltyActivity;
use DateTimeImmutable;

interface LoyaltyActivityRepository
{
    public function save(LoyaltyActivity $activity): void;

    public function findByCycleId(int $cycleId): array;

    public function findByUserIdAndDate(int $userId, DateTimeImmutable $date): array;

    public function hasActivityForDate(int $cycleId, DateTimeImmutable $date): bool;
}
