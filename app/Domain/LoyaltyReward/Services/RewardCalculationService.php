<?php

namespace App\Domain\LoyaltyReward\Services;

use App\Domain\LoyaltyReward\Entities\LoyaltyGrowthCycle;
use App\Domain\LoyaltyReward\Entities\RewardPool;
use App\Domain\LoyaltyReward\ValueObjects\LoyaltyAmount;

class RewardCalculationService
{
    public function calculateProportionalRewards(
        array $cycles,
        RewardPool $pool
    ): array {
        $totalRequested = LoyaltyAmount::zero();
        
        foreach ($cycles as $cycle) {
            $totalRequested = $totalRequested->add($cycle->getEarnedAmount());
        }

        $results = [];
        foreach ($cycles as $cycle) {
            $proportionalAmount = $pool->calculateProportionalAmount(
                $cycle->getEarnedAmount(),
                $totalRequested
            );

            $results[$cycle->getId()->toString()] = [
                'cycle' => $cycle,
                'requested' => $cycle->getEarnedAmount(),
                'allocated' => $proportionalAmount,
                'is_full_amount' => $proportionalAmount->equals($cycle->getEarnedAmount()),
            ];
        }

        return $results;
    }

    public function calculateDailyReward(LoyaltyGrowthCycle $cycle): LoyaltyAmount
    {
        if (!$cycle->canRecordActivity()) {
            return LoyaltyAmount::zero();
        }

        return LoyaltyAmount::fromKwacha(LoyaltyGrowthCycle::getDailyRate());
    }

    public function calculateProjectedEarnings(int $activeDays): LoyaltyAmount
    {
        $maxDays = min($activeDays, LoyaltyGrowthCycle::getCycleDurationDays());
        return LoyaltyAmount::fromKwacha($maxDays * LoyaltyGrowthCycle::getDailyRate());
    }

    public function calculateRemainingPotential(LoyaltyGrowthCycle $cycle): LoyaltyAmount
    {
        $remainingDays = $cycle->getRemainingDays();
        return LoyaltyAmount::fromKwacha($remainingDays * LoyaltyGrowthCycle::getDailyRate());
    }
}
