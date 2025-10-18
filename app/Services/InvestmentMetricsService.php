<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentMetric;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InvestmentMetricsService
{
    public function generateMetrics(string $period = 'month'): Collection
    {
        $startDate = $this->getStartDate($period);
        $this->ensureMetricsExist($startDate);

        return InvestmentMetric::where('date', '>=', $startDate)
            ->orderBy('date')
            ->get()
            ->map(fn($metric) => [
                'date' => $metric->date->format('Y-m-d'),
                'total_investments' => $metric->total_value,
                'average_roi' => $metric->average_roi,
                'success_rate' => $metric->success_rate,
                'active_investments' => $metric->total_count,
                'trend_value' => $this->calculateTrendValue($metric)
            ]);
    }

    private function ensureMetricsExist(Carbon $startDate): void
    {
        $dates = collect();
        $currentDate = $startDate->copy();
        $today = now()->startOfDay();

        while ($currentDate <= $today) {
            $dates->push($currentDate->format('Y-m-d'));
            $currentDate->addDay();
        }

        $existingDates = InvestmentMetric::whereBetween('date', [$startDate, $today])
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'));

        $missingDates = $dates->diff($existingDates);

        foreach ($missingDates as $date) {
            $this->recordMetricsForDate(Carbon::parse($date));
        }
    }

    private function recordMetricsForDate(Carbon $date): void
    {
        InvestmentMetric::updateOrCreate(
            ['date' => $date],
            [
                'total_value' => Investment::where('created_at', '<=', $date)->sum('amount'),
                'total_count' => Investment::where('status', 'active')
                    ->where('created_at', '<=', $date)
                    ->count(),
                'average_roi' => Investment::where('status', 'active')
                    ->where('created_at', '<=', $date)
                    ->avg('roi') ?? 0,
                'success_rate' => $this->calculateSuccessRateForDate($date)
            ]
        );
    }

    private function calculateSuccessRateForDate(Carbon $date): float
    {
        $total = Investment::where('created_at', '<=', $date)->count();
        if ($total === 0) return 0;

        $successful = Investment::where('created_at', '<=', $date)
            ->whereIn('status', ['completed', 'active'])
            ->count();

        return round(($successful / $total) * 100, 2);
    }

    private function calculateTrendValue(InvestmentMetric $metric): float
    {
        $previousMetric = InvestmentMetric::where('date', '<', $metric->date)
            ->orderBy('date', 'desc')
            ->first();

        if (!$previousMetric || $previousMetric->total_value == 0 || $previousMetric->total_value === null) {
            return 0;
        }

        // Additional safety check to prevent division by zero
        $previousValue = (float) $previousMetric->total_value;
        if ($previousValue == 0) {
            return 0;
        }

        return round(
            (($metric->total_value - $previousValue) / $previousValue) * 100,
            2
        );
    }

    private function getStartDate(string $period): Carbon
    {
        return match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };
    }
}