<?php

namespace App\Domain\Investment\Services;

use App\Models\User;

class InvestmentTierService
{
    public function getTierUpgradeRecommendations(User $user): array
    {
        $eligibility = $user->checkTierUpgradeEligibility();

        if (!$eligibility['eligible'] || !$eligibility['next_tier']) {
            return [
                'recommendations' => [],
                'current_tier' => $eligibility['current_tier'],
                'next_tier' => $eligibility['next_tier'],
                'progress' => $user->getTierProgressPercentage(),
            ];
        }

        return [
            'recommendations' => [
                [
                    'from_tier' => $eligibility['current_tier']?->name ?? 'Current',
                    'to_tier' => $eligibility['next_tier']->name,
                    'required_amount' => $eligibility['required_amount'],
                    'current_amount' => $eligibility['current_amount'],
                    'remaining_amount' => $eligibility['remaining_amount'],
                    'progress' => $user->getTierProgressPercentage(),
                ]
            ],
            'current_tier' => $eligibility['current_tier'],
            'next_tier' => $eligibility['next_tier'],
            'progress' => $user->getTierProgressPercentage(),
        ];
    }
}
