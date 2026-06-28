<?php

namespace App\Domain\Tools\Entities;

use App\Domain\Tools\ValueObjects\MonetaryAmount;

class ROICalculation
{
    private function __construct(
        private readonly MonetaryAmount $totalInvestment,
        private readonly MonetaryAmount $totalEarnings,
        private readonly MonetaryAmount $netProfit,
        private readonly float $roiPercentage,
        private readonly int $daysActive,
        private readonly MonetaryAmount $dailyAverage,
        private readonly MonetaryAmount $monthlyProjection,
        private readonly int $breakEvenDays
    ) {}

    public static function calculate(
        MonetaryAmount $totalInvestment,
        MonetaryAmount $totalEarnings,
        int $daysActive
    ): self {
        $netProfit = $totalEarnings->subtract($totalInvestment);
        
        $roiPercentage = $totalInvestment->value() > 0
            ? (($totalEarnings->value() - $totalInvestment->value()) / $totalInvestment->value()) * 100
            : 0;

        $dailyAverage = $daysActive > 0
            ? MonetaryAmount::fromFloat($totalEarnings->value() / $daysActive)
            : MonetaryAmount::fromFloat(0);

        $monthlyProjection = MonetaryAmount::fromFloat($dailyAverage->value() * 30);

        $breakEvenDays = $dailyAverage->value() > 0
            ? (int) ceil($totalInvestment->value() / $dailyAverage->value())
            : 0;

        return new self(
            $totalInvestment,
            $totalEarnings,
            $netProfit,
            $roiPercentage,
            $daysActive,
            $dailyAverage,
            $monthlyProjection,
            $breakEvenDays
        );
    }

    public function totalInvestment(): MonetaryAmount
    {
        return $this->totalInvestment;
    }

    public function totalEarnings(): MonetaryAmount
    {
        return $this->totalEarnings;
    }

    public function netProfit(): MonetaryAmount
    {
        return $this->netProfit;
    }

    public function roiPercentage(): float
    {
        return $this->roiPercentage;
    }

    public function daysActive(): int
    {
        return $this->daysActive;
    }

    public function dailyAverage(): MonetaryAmount
    {
        return $this->dailyAverage;
    }

    public function monthlyProjection(): MonetaryAmount
    {
        return $this->monthlyProjection;
    }

    public function breakEvenDays(): int
    {
        return $this->breakEvenDays;
    }

    public function hasReachedBreakEven(): bool
    {
        return $this->totalEarnings->isGreaterThan($this->totalInvestment);
    }

    public function toArray(): array
    {
        return [
            'total_investment' => $this->totalInvestment->value(),
            'total_earnings' => $this->totalEarnings->value(),
            'net_profit' => $this->netProfit->value(),
            'roi_percentage' => round($this->roiPercentage, 2),
            'days_active' => $this->daysActive,
            'daily_average' => $this->dailyAverage->value(),
            'monthly_projection' => $this->monthlyProjection->value(),
            'break_even_days' => $this->breakEvenDays,
            'has_reached_break_even' => $this->hasReachedBreakEven(),
            'formatted' => [
                'total_investment' => $this->totalInvestment->formatted(),
                'total_earnings' => $this->totalEarnings->formatted(),
                'net_profit' => $this->netProfit->formatted(),
                'daily_average' => $this->dailyAverage->formatted(),
                'monthly_projection' => $this->monthlyProjection->formatted(),
            ],
        ];
    }
}
