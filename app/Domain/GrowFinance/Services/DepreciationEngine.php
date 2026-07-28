<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\FixedAsset;
use App\Domain\GrowFinance\ValueObjects\DepreciationMethod;

class DepreciationEngine
{
    public function computePeriodDepreciation(FixedAsset $asset, \DateTimeImmutable $periodDate): float
    {
        if ($asset->status->value !== 'active' || $asset->isFullyDepreciated()) {
            return 0;
        }
        if ($periodDate < $asset->purchaseDate) {
            return 0;
        }

        return match ($asset->depreciationMethod) {
            DepreciationMethod::STRAIGHT_LINE => $this->straightLine($asset),
            DepreciationMethod::REDUCING_BALANCE => $this->reducingBalance($asset),
        };
    }

    public function straightLine(FixedAsset $asset): float
    {
        return $asset->getMonthlyStraightLineDepreciation();
    }

    public function reducingBalance(FixedAsset $asset): float
    {
        $rate = ($asset->depreciationRate ?? 25) / 100;
        $nbv = $asset->getNetBookValue();
        $monthly = ($nbv * $rate) / 12;
        $maxDepreciable = $asset->getDepreciableAmount() - $asset->accumulatedDepreciation;
        return min($monthly, max(0, $maxDepreciable));
    }

    public function generateSchedule(FixedAsset $asset): array
    {
        $schedule = [];
        $accumulated = $asset->accumulatedDepreciation;
        $depreciable = $asset->getDepreciableAmount();
        $remaining = $depreciable - $accumulated;

        if ($remaining <= 0) return $schedule;

        $months = min($asset->usefulLifeMonths, 60); // cap at 5 years to avoid huge schedules
        $period = new \DateTimeImmutable($asset->purchaseDate->format('Y-m-01'));
        $period = $period->modify('+1 month');

        for ($i = 0; $i < $months && $remaining > 0.01; $i++) {
            $amount = match ($asset->depreciationMethod) {
                DepreciationMethod::STRAIGHT_LINE => $this->straightLine(clone $asset),
                DepreciationMethod::REDUCING_BALANCE => min($this->reducingBalance(clone $asset), $remaining),
            };

            $amount = min($amount, $remaining);
            if ($amount <= 0.01) break;

            $accumulated += $amount;
            $remaining -= $amount;

            $schedule[] = [
                'period_date' => $period->format('Y-m-d'),
                'depreciation_amount' => round($amount, 2),
                'accumulated_depreciation' => round($accumulated, 2),
                'net_book_value' => max(0, round($asset->cost - $accumulated, 2)),
            ];

            $period = $period->modify('+1 month');
        }

        return $schedule;
    }
}
