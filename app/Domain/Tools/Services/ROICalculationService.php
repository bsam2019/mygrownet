<?php

namespace App\Domain\Tools\Services;

use App\Domain\Tools\Entities\ROICalculation;
use App\Domain\Tools\ValueObjects\MonetaryAmount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ROICalculationService
{
    public function calculateForUser(User $user): ROICalculation
    {
        $totalInvestment = $this->calculateTotalInvestment($user);
        $totalEarnings = $this->calculateTotalEarnings($user);
        $daysActive = $this->calculateDaysActive($user);

        return ROICalculation::calculate(
            $totalInvestment,
            $totalEarnings,
            $daysActive
        );
    }

    private function calculateTotalInvestment(User $user): MonetaryAmount
    {
        $starterKitCost = 0;
        
        if ($user->has_starter_kit) {
            $starterKitCost = $user->starter_kit_tier === 'premium' ? 1000 : 500;
        }

        // Add any additional investments (subscriptions, etc.)
        $additionalInvestments = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'purchase')
            ->where('status', 'completed')
            ->sum('amount');

        return MonetaryAmount::fromFloat($starterKitCost + $additionalInvestments);
    }

    private function calculateTotalEarnings(User $user): MonetaryAmount
    {
        $earnings = DB::table('transactions')
            ->where('user_id', $user->id)
            ->whereIn('transaction_type', ['commission', 'bonus', 'referral_bonus'])
            ->where('status', 'completed')
            ->sum('amount');

        return MonetaryAmount::fromFloat($earnings);
    }

    private function calculateDaysActive(User $user): int
    {
        $joinedAt = $user->created_at;
        $now = now();
        
        return max(1, $joinedAt->diffInDays($now));
    }

    public function projectFutureROI(
        MonetaryAmount $currentInvestment,
        MonetaryAmount $currentEarnings,
        int $daysActive,
        int $projectionDays
    ): array {
        $dailyAverage = $daysActive > 0
            ? $currentEarnings->value() / $daysActive
            : 0;

        $projectedEarnings = MonetaryAmount::fromFloat(
            $currentEarnings->value() + ($dailyAverage * $projectionDays)
        );

        $projectedROI = ROICalculation::calculate(
            $currentInvestment,
            $projectedEarnings,
            $daysActive + $projectionDays
        );

        return [
            'projection_days' => $projectionDays,
            'projected_earnings' => $projectedEarnings->value(),
            'projected_roi_percentage' => $projectedROI->roiPercentage(),
            'projected_net_profit' => $projectedROI->netProfit()->value(),
        ];
    }
}
