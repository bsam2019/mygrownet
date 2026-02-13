<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdvancedAnalyticsService
{
    // Trend Analysis
    public function calculateTrend(int $companyId, string $metric, string $periodType = 'monthly', int $periods = 12): array
    {
        $data = [];
        $startDate = Carbon::now()->sub($periods, $periodType === 'monthly' ? 'months' : 'weeks');

        for ($i = 0; $i < $periods; $i++) {
            $periodStart = $startDate->copy()->add($i, $periodType === 'monthly' ? 'months' : 'weeks');
            $periodEnd = $periodStart->copy()->add(1, $periodType === 'monthly' ? 'month' : 'week');

            $value = $this->getMetricValue($companyId, $metric, $periodStart, $periodEnd);
            
            $data[] = [
                'period' => $periodStart->format('Y-m-d'),
                'value' => $value,
            ];
        }

        // Calculate trend direction
        $recentAvg = collect($data)->slice(-3)->avg('value');
        $previousAvg = collect($data)->slice(-6, 3)->avg('value');
        $trendDirection = $recentAvg > $previousAvg ? 'up' : ($recentAvg < $previousAvg ? 'down' : 'stable');

        return [
            'data' => $data,
            'trend_direction' => $trendDirection,
            'change_percentage' => $previousAvg > 0 ? (($recentAvg - $previousAvg) / $previousAvg) * 100 : 0,
        ];
    }

    // Forecasting (Simple Linear Regression)
    public function generateForecast(int $companyId, string $metric, int $forecastPeriods = 3): array
    {
        // Get historical data (last 12 months)
        $historical = $this->calculateTrend($companyId, $metric, 'monthly', 12);
        $data = $historical['data'];

        // Simple linear regression
        $n = count($data);
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($data as $i => $point) {
            $x = $i;
            $y = $point['value'];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        // Generate forecasts
        $forecasts = [];
        for ($i = 0; $i < $forecastPeriods; $i++) {
            $x = $n + $i;
            $forecastedValue = $slope * $x + $intercept;
            $forecastDate = Carbon::now()->add($i + 1, 'months');

            $forecasts[] = [
                'period' => $forecastDate->format('Y-m-d'),
                'forecasted_value' => max(0, round($forecastedValue, 2)),
                'confidence_lower' => max(0, round($forecastedValue * 0.9, 2)),
                'confidence_upper' => round($forecastedValue * 1.1, 2),
            ];
        }

        return [
            'historical' => $data,
            'forecasts' => $forecasts,
            'model' => [
                'type' => 'linear_regression',
                'slope' => round($slope, 4),
                'intercept' => round($intercept, 2),
            ],
        ];
    }

    // KPI Calculation
    public function calculateKPI(int $companyId, string $kpiType, Carbon $startDate, Carbon $endDate): array
    {
        $value = $this->getMetricValue($companyId, $kpiType, $startDate, $endDate);
        
        // Get previous period for comparison
        $periodLength = $startDate->diffInDays($endDate);
        $prevStart = $startDate->copy()->subDays($periodLength);
        $prevEnd = $startDate->copy();
        $previousValue = $this->getMetricValue($companyId, $kpiType, $prevStart, $prevEnd);

        $variance = $value - $previousValue;
        $variancePercentage = $previousValue > 0 ? ($variance / $previousValue) * 100 : 0;

        return [
            'value' => $value,
            'previous_value' => $previousValue,
            'variance' => $variance,
            'variance_percentage' => round($variancePercentage, 2),
            'trend' => $variance > 0 ? 'up' : ($variance < 0 ? 'down' : 'stable'),
        ];
    }

    // Goal Progress Tracking
    public function calculateGoalProgress(int $goalId): array
    {
        $goal = DB::table('cms_goals')->find($goalId);
        
        if (!$goal) {
            return [];
        }

        $progress = $goal->target_value > 0 ? ($goal->current_value / $goal->target_value) * 100 : 0;
        $daysTotal = Carbon::parse($goal->start_date)->diffInDays(Carbon::parse($goal->end_date));
        $daysElapsed = Carbon::parse($goal->start_date)->diffInDays(Carbon::now());
        $daysRemaining = max(0, Carbon::now()->diffInDays(Carbon::parse($goal->end_date)));
        
        $expectedProgress = $daysTotal > 0 ? ($daysElapsed / $daysTotal) * 100 : 0;
        $onTrack = $progress >= $expectedProgress;

        return [
            'current_value' => $goal->current_value,
            'target_value' => $goal->target_value,
            'progress_percentage' => round($progress, 2),
            'expected_progress' => round($expectedProgress, 2),
            'on_track' => $onTrack,
            'days_remaining' => $daysRemaining,
            'status' => $this->determineGoalStatus($goal, $progress),
        ];
    }

    // Helper Methods
    private function getMetricValue(int $companyId, string $metric, Carbon $startDate, Carbon $endDate): float
    {
        return match($metric) {
            'revenue' => InvoiceModel::where('company_id', $companyId)
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->where('status', 'paid')
                ->sum('total_amount'),
            
            'profit' => JobModel::where('company_id', $companyId)
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('profit_amount'),
            
            'expenses' => ExpenseModel::where('company_id', $companyId)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount'),
            
            'customers' => CustomerModel::where('company_id', $companyId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            
            'jobs' => JobModel::where('company_id', $companyId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            
            'payments' => PaymentModel::where('company_id', $companyId)
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->sum('amount'),
            
            default => 0,
        };
    }

    private function determineGoalStatus($goal, float $progress): string
    {
        $now = Carbon::now();
        $endDate = Carbon::parse($goal->end_date);

        if ($progress >= 100) {
            return 'achieved';
        }

        if ($now->gt($endDate)) {
            return 'failed';
        }

        if ($now->lt(Carbon::parse($goal->start_date))) {
            return 'not_started';
        }

        return 'in_progress';
    }

    // Dashboard Data Aggregation
    public function getDashboardData(int $companyId, string $period = 'month'): array
    {
        $startDate = Carbon::now()->startOf($period);
        $endDate = Carbon::now()->endOf($period);

        return [
            'revenue' => $this->calculateKPI($companyId, 'revenue', $startDate, $endDate),
            'profit' => $this->calculateKPI($companyId, 'profit', $startDate, $endDate),
            'expenses' => $this->calculateKPI($companyId, 'expenses', $startDate, $endDate),
            'customers' => $this->calculateKPI($companyId, 'customers', $startDate, $endDate),
            'jobs' => $this->calculateKPI($companyId, 'jobs', $startDate, $endDate),
            'revenue_trend' => $this->calculateTrend($companyId, 'revenue', 'monthly', 6),
            'profit_trend' => $this->calculateTrend($companyId, 'profit', 'monthly', 6),
        ];
    }
}
