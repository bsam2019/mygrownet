<?php

namespace App\Domain\ProfitSharing\Services;

use App\Domain\ProfitSharing\ValueObjects\ProfitAmount;

class ProfitDistributionCalculator
{
    // Professional level multipliers from docs
    private const LEVEL_MULTIPLIERS = [
        'Associate' => 1.0,
        'Professional' => 1.2,
        'Senior' => 1.5,
        'Manager' => 2.0,
        'Director' => 2.5,
        'Executive' => 3.0,
        'Ambassador' => 4.0,
    ];

    public function calculateBPBasedDistribution(
        ProfitAmount $memberShareAmount,
        array $activeMembers, // ['user_id' => bp_amount]
        float $totalBP
    ): array {
        if ($totalBP <= 0) {
            throw new \DomainException('Total BP must be greater than zero');
        }

        $distributions = [];
        $memberShare = $memberShareAmount->value();

        foreach ($activeMembers as $userId => $memberBP) {
            $sharePercentage = $memberBP / $totalBP;
            $distributions[$userId] = $memberShare * $sharePercentage;
        }

        return $distributions;
    }

    public function calculateLevelBasedDistribution(
        ProfitAmount $memberShareAmount,
        array $activeMembers // ['user_id' => 'level']
    ): array {
        $totalMultiplier = 0;
        foreach ($activeMembers as $userId => $level) {
            $totalMultiplier += self::LEVEL_MULTIPLIERS[$level] ?? 1.0;
        }

        if ($totalMultiplier <= 0) {
            throw new \DomainException('Total multiplier must be greater than zero');
        }

        $distributions = [];
        $memberShare = $memberShareAmount->value();

        foreach ($activeMembers as $userId => $level) {
            $multiplier = self::LEVEL_MULTIPLIERS[$level] ?? 1.0;
            $distributions[$userId] = ($multiplier / $totalMultiplier) * $memberShare;
        }

        return $distributions;
    }

    public function getLevelMultiplier(string $level): float
    {
        return self::LEVEL_MULTIPLIERS[$level] ?? 1.0;
    }
}
