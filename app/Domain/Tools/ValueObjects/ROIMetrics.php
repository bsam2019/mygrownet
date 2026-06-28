<?php

namespace App\Domain\Tools\ValueObjects;

class ROIMetrics
{
    public function __construct(
        private readonly float $totalInvestment,
        private readonly float $totalEarnings,
        private readonly float $netProfit,
        private readonly float $roiPercentage,
        private readonly int $daysActive
    ) {}

    public static function calculate(
        float $totalInvestment,
        float $totalEarnings,
        int $daysActive
    ): self {
        $netProfit = $totalEarnings - $totalInvestment;
        $roiPercentage = $totalInvestment > 0 
            ? ($netProfit / $totalInvestment) * 100 
            : 0;

        return new self(
            $totalInvestment,
            $totalEarnings,
            $netProfit,
            $roiPercentage,
            $daysActive
        );
    }

    public function totalInvestment(): float
    {
        return $this->totalInvestment;
    }

    public function totalEarnings(): float
    {
        return $this->totalEarnings;
    }

    public function netProfit(): float
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

    public function monthlyAverage(): float
    {
        if ($this->daysActive === 0) {
            return 0;
        }
        return ($this->totalEarnings / $this->daysActive) * 30;
    }

    public function breakEvenDays(): ?int
    {
        if ($this->netProfit >= 0) {
            return $this->daysActive;
        }
        
        $dailyAverage = $this->daysActive > 0 
            ? $this->totalEarnings / $this->daysActive 
            : 0;
            
        if ($dailyAverage <= 0) {
            return null;
        }
        
        return (int) ceil($this->totalInvestment / $dailyAverage);
    }

    public function toArray(): array
    {
        return [
            'total_investment' => $this->totalInvestment,
            'total_earnings' => $this->totalEarnings,
            'net_profit' => $this->netProfit,
            'roi_percentage' => round($this->roiPercentage, 2),
            'days_active' => $this->daysActive,
            'monthly_average' => round($this->monthlyAverage(), 2),
            'break_even_days' => $this->breakEvenDays(),
        ];
    }
}
