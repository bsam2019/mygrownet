<?php

declare(strict_types=1);

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class EmployeeAnalyticsService
{
    private const CACHE_TTL = 1800; // 30 minutes

    public function getEmployeeTurnoverAnalysis(int $months = 12): array
    {
        $cacheKey = "employee_turnover_analysis_{$months}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($months) {
            $startDate = now()->subMonths($months);
            
            $hires = EmployeeModel::where('hire_date', '>=', $startDate)
                ->selectRaw('DATE_FORMAT(hire_date, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy('month')
                ->map->count;

            $terminations = EmployeeModel::where('termination_date', '>=', $startDate)
                ->whereNotNull('termination_date')
                ->selectRaw('DATE_FORMAT(termination_date, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy('month')
                ->map->count;

            $monthlyData = [];
            $currentDate = $startDate->copy()->startOfMonth();
            
            while ($currentDate <= now()->startOfMonth()) {
                $monthKey = $currentDate->format('Y-m');
                $monthlyData[] = [
                    'month' => $currentDate->format('M Y'),
                    'hires' => $hires[$monthKey] ?? 0,
                    'terminations' => $terminations[$monthKey] ?? 0,
                    'net_change' => ($hires[$monthKey] ?? 0) - ($terminations[$monthKey] ?? 0),
                ];
                $currentDate->addMonth();
            }

            $totalEmployees = EmployeeModel::where('employment_status', 'active')->count();
            $totalTerminations = array_sum($terminations->toArray());
            $turnoverRate = $totalEmployees > 0 ? ($totalTerminations / $totalEmployees) * 100 : 0;

            return [
                'monthly_data' => $monthlyData,
                'summary' => [
                    'total_hires' => array_sum($hires->toArray()),
                    'total_terminations' => $totalTerminations,
                    'turnover_rate' => round($turnoverRate, 2),
                    'average_tenure_months' => $this->getAverageTenure(),
                ],
            ];
        });
    }

    public function getPerformanceDistribution(): array
    {
        $cacheKey = 'performance_distribution';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $currentYear = now()->year;
            
            $performanceData = EmployeePerformanceModel::whereYear('period_end', $currentYear)
                ->selectRaw('
                    CASE 
                        WHEN overall_score >= 4.5 THEN "Excellent"
                        WHEN overall_score >= 3.5 THEN "Good"
                        WHEN overall_score >= 2.5 THEN "Satisfactory"
                        WHEN overall_score >= 1.5 THEN "Needs Improvement"
                        ELSE "Poor"
                    END as performance_category,
                    COUNT(*) as count,
                    AVG(overall_score) as avg_score
                ')
                ->groupBy('performance_category')
                ->get();

            $departmentPerformance = EmployeePerformanceModel::whereYear('period_end', $currentYear)
                ->join('employees', 'employee_performance.employee_id', '=', 'employees.id')
                ->join('departments', 'employees.department_id', '=', 'departments.id')
                ->selectRaw('
                    departments.name as department_name,
                    AVG(overall_score) as avg_score,
                    COUNT(*) as review_count,
                    MAX(overall_score) as max_score,
                    MIN(overall_score) as min_score
                ')
                ->groupBy('departments.id', 'departments.name')
                ->orderByDesc('avg_score')
                ->get();

            return [
                'overall_distribution' => $performanceData->map(function ($item) {
                    return [
                        'category' => $item->performance_category,
                        'count' => $item->count,
                        'percentage' => 0, // Will be calculated in frontend
                        'avg_score' => round($item->avg_score, 2),
                    ];
                }),
                'department_performance' => $departmentPerformance->map(function ($item) {
                    return [
                        'department' => $item->department_name,
                        'avg_score' => round($item->avg_score, 2),
                        'review_count' => $item->review_count,
                        'score_range' => [
                            'min' => round($item->min_score, 2),
                            'max' => round($item->max_score, 2),
                        ],
                    ];
                }),
            ];
        });
    }

    public function getCommissionAnalytics(int $months = 6): array
    {
        $cacheKey = "commission_analytics_{$months}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($months) {
            $startDate = now()->subMonths($months);
            
            $monthlyCommissions = EmployeeCommissionModel::where('calculation_date', '>=', $startDate)
                ->selectRaw('
                    DATE_FORMAT(calculation_date, "%Y-%m") as month,
                    SUM(commission_amount) as total_amount,
                    COUNT(*) as transaction_count,
                    AVG(commission_amount) as avg_amount,
                    commission_type
                ')
                ->groupBy('month', 'commission_type')
                ->orderBy('month')
                ->get();

            $topEarners = EmployeeCommissionModel::where('calculation_date', '>=', $startDate)
                ->where('status', 'paid')
                ->join('employees', 'employee_commissions.employee_id', '=', 'employees.id')
                ->selectRaw('
                    employees.id,
                    CONCAT(employees.first_name, " ", employees.last_name) as name,
                    employees.employee_id,
                    SUM(commission_amount) as total_earned,
                    COUNT(*) as commission_count,
                    AVG(commission_amount) as avg_commission
                ')
                ->groupBy('employees.id', 'employees.first_name', 'employees.last_name', 'employees.employee_id')
                ->orderByDesc('total_earned')
                ->limit(10)
                ->get();

            $commissionByType = EmployeeCommissionModel::where('calculation_date', '>=', $startDate)
                ->selectRaw('
                    commission_type,
                    SUM(commission_amount) as total_amount,
                    COUNT(*) as count,
                    AVG(commission_amount) as avg_amount
                ')
                ->groupBy('commission_type')
                ->orderByDesc('total_amount')
                ->get();

            return [
                'monthly_trends' => $this->formatMonthlyCommissionData($monthlyCommissions, $months),
                'top_earners' => $topEarners->map(function ($earner) {
                    return [
                        'employee_id' => $earner->id,
                        'name' => $earner->name,
                        'employee_number' => $earner->employee_number,
                        'total_earned' => round($earner->total_earned, 2),
                        'commission_count' => $earner->commission_count,
                        'avg_commission' => round($earner->avg_commission, 2),
                    ];
                }),
                'commission_by_type' => $commissionByType->map(function ($type) {
                    return [
                        'type' => $type->commission_type,
                        'total_amount' => round($type->total_amount, 2),
                        'count' => $type->count,
                        'avg_amount' => round($type->avg_amount, 2),
                    ];
                }),
                'summary' => [
                    'total_commissions' => $commissionByType->sum('total_amount'),
                    'total_transactions' => $commissionByType->sum('count'),
                    'avg_commission' => $commissionByType->avg('avg_amount'),
                ],
            ];
        });
    }

    public function getDepartmentEfficiencyMetrics(): array
    {
        $cacheKey = 'department_efficiency_metrics';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $departments = DepartmentModel::with(['employees' => function ($query) {
                $query->where('employment_status', 'active');
            }])
            ->withCount(['employees as active_employee_count' => function ($query) {
                $query->where('employment_status', 'active');
            }])
            ->get();

            $departmentMetrics = [];
            
            foreach ($departments as $department) {
                $employeeIds = $department->employees->pluck('id');
                
                // Performance metrics
                $avgPerformance = EmployeePerformanceModel::whereIn('employee_id', $employeeIds)
                    ->whereYear('period_end', now()->year)
                    ->avg('overall_score') ?? 0;

                // Commission metrics
                $totalCommissions = EmployeeCommissionModel::whereIn('employee_id', $employeeIds)
                    ->where('status', 'paid')
                    ->whereYear('calculation_date', now()->year)
                    ->sum('commission_amount') ?? 0;

                // Turnover rate
                $terminations = EmployeeModel::where('department_id', $department->id)
                    ->whereYear('termination_date', now()->year)
                    ->count();
                
                $turnoverRate = $department->active_employee_count > 0 
                    ? ($terminations / $department->active_employee_count) * 100 
                    : 0;

                $departmentMetrics[] = [
                    'department_id' => $department->id,
                    'department_name' => $department->name,
                    'employee_count' => $department->active_employee_count,
                    'avg_performance_score' => round($avgPerformance, 2),
                    'total_commissions' => round($totalCommissions, 2),
                    'avg_commission_per_employee' => $department->active_employee_count > 0 
                        ? round($totalCommissions / $department->active_employee_count, 2) 
                        : 0,
                    'turnover_rate' => round($turnoverRate, 2),
                    'efficiency_score' => $this->calculateEfficiencyScore($avgPerformance, $turnoverRate, $totalCommissions),
                ];
            }

            return [
                'departments' => collect($departmentMetrics)->sortByDesc('efficiency_score')->values(),
                'company_averages' => [
                    'avg_performance' => collect($departmentMetrics)->avg('avg_performance_score'),
                    'avg_turnover' => collect($departmentMetrics)->avg('turnover_rate'),
                    'total_commissions' => collect($departmentMetrics)->sum('total_commissions'),
                ],
            ];
        });
    }

    public function getEmployeeGrowthPredictions(): array
    {
        $cacheKey = 'employee_growth_predictions';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            // Historical hiring data for the last 24 months
            $historicalData = EmployeeModel::where('hire_date', '>=', now()->subMonths(24))
                ->selectRaw('
                    DATE_FORMAT(hire_date, "%Y-%m") as month,
                    COUNT(*) as hires
                ')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Simple linear regression for prediction
            $predictions = $this->calculateGrowthPredictions($historicalData);

            return [
                'historical_data' => $historicalData,
                'predictions' => $predictions,
                'growth_rate' => $this->calculateGrowthRate($historicalData),
                'seasonal_patterns' => $this->identifySeasonalPatterns($historicalData),
            ];
        });
    }

    private function getAverageTenure(): float
    {
        return EmployeeModel::where('employment_status', 'active')
            ->selectRaw('AVG(DATEDIFF(CURDATE(), hire_date) / 365.25) as avg_tenure')
            ->value('avg_tenure') ?? 0;
    }

    private function formatMonthlyCommissionData($monthlyCommissions, int $months): array
    {
        $formatted = [];
        $currentDate = now()->subMonths($months)->startOfMonth();
        
        while ($currentDate <= now()->startOfMonth()) {
            $monthKey = $currentDate->format('Y-m');
            $monthData = $monthlyCommissions->where('month', $monthKey);
            
            $formatted[] = [
                'month' => $currentDate->format('M Y'),
                'total_amount' => $monthData->sum('total_amount'),
                'transaction_count' => $monthData->sum('transaction_count'),
                'avg_amount' => $monthData->avg('avg_amount') ?? 0,
                'by_type' => $monthData->groupBy('commission_type')->map(function ($items, $type) {
                    return [
                        'type' => $type,
                        'amount' => $items->sum('total_amount'),
                        'count' => $items->sum('transaction_count'),
                    ];
                })->values(),
            ];
            
            $currentDate->addMonth();
        }
        
        return $formatted;
    }

    private function calculateEfficiencyScore(float $performance, float $turnover, float $commissions): float
    {
        // Weighted efficiency score (0-100)
        $performanceScore = ($performance / 5) * 40; // 40% weight
        $turnoverScore = max(0, (100 - $turnover) / 100) * 30; // 30% weight (lower turnover is better)
        $commissionScore = min(30, ($commissions / 10000) * 30); // 30% weight (capped at 30)
        
        return round($performanceScore + $turnoverScore + $commissionScore, 2);
    }

    private function calculateGrowthPredictions($historicalData): array
    {
        // Simple moving average prediction for next 6 months
        $recentData = $historicalData->take(-6);
        $avgGrowth = $recentData->avg('hires');
        
        $predictions = [];
        $currentDate = now()->addMonth()->startOfMonth();
        
        for ($i = 0; $i < 6; $i++) {
            $predictions[] = [
                'month' => $currentDate->format('M Y'),
                'predicted_hires' => round($avgGrowth * (1 + ($i * 0.05))), // 5% growth assumption
                'confidence' => max(60, 90 - ($i * 5)), // Decreasing confidence
            ];
            $currentDate->addMonth();
        }
        
        return $predictions;
    }

    private function calculateGrowthRate($historicalData): float
    {
        if ($historicalData->count() < 2) return 0;
        
        $first = $historicalData->first()->hires;
        $last = $historicalData->last()->hires;
        
        return $first > 0 ? (($last - $first) / $first) * 100 : 0;
    }

    private function identifySeasonalPatterns($historicalData): array
    {
        $monthlyAverages = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthData = $historicalData->filter(function ($item) use ($month) {
                return (int) date('n', strtotime($item->month . '-01')) === $month;
            });
            
            $monthlyAverages[] = [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'avg_hires' => $monthData->avg('hires') ?? 0,
                'data_points' => $monthData->count(),
            ];
        }
        
        return $monthlyAverages;
    }
}