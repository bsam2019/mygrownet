<?php

namespace App\Domain\LoyaltyReward\Services;

use App\Domain\LoyaltyReward\Entities\LoyaltyGrowthCycle;
use App\Domain\LoyaltyReward\Repositories\LoyaltyGrowthCycleRepository;
use DateTimeImmutable;

class CycleManagementService
{
    public function __construct(
        private LoyaltyGrowthCycleRepository $cycleRepository,
        private QualificationService $qualificationService
    ) {}

    public function startCycleForUser(int $userId): LoyaltyGrowthCycle
    {
        // Check if user already has an active cycle
        $activeCycle = $this->cycleRepository->findActiveCycleByUserId($userId);
        if ($activeCycle) {
            throw new \DomainException('User already has an active loyalty cycle');
        }

        // Create new cycle
        $cycle = LoyaltyGrowthCycle::start($userId, new DateTimeImmutable());
        $this->cycleRepository->save($cycle);

        return $cycle;
    }

    public function recordDailyActivity(
        LoyaltyGrowthCycle $cycle,
        DateTimeImmutable $date
    ): void {
        // Check if activity already recorded for this date
        if ($this->cycleRepository->hasActivityForDate($cycle->getId(), $date)) {
            throw new \DomainException('Activity already recorded for this date');
        }

        $cycle->recordActiveDay();
        $this->cycleRepository->save($cycle);
    }

    public function completeCycle(LoyaltyGrowthCycle $cycle): void
    {
        $cycle->complete();
        $this->cycleRepository->save($cycle);
    }

    public function processExpiredCycles(): int
    {
        $expiredCycles = $this->cycleRepository->findExpiredActiveCycles();
        $count = 0;

        foreach ($expiredCycles as $cycle) {
            $cycle->complete();
            $this->cycleRepository->save($cycle);
            $count++;
        }

        return $count;
    }

    public function suspendCycle(LoyaltyGrowthCycle $cycle, string $reason): void
    {
        $cycle->suspend($reason);
        $this->cycleRepository->save($cycle);
    }

    public function terminateCycle(LoyaltyGrowthCycle $cycle, string $reason): void
    {
        $cycle->terminate($reason);
        $this->cycleRepository->save($cycle);
    }
}
