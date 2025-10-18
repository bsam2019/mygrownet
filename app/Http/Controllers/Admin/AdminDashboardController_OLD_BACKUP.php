<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Investment;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\ActivityLog;
use App\Models\InvestmentCategory;
use App\Models\InvestmentMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use App\Services\ActivityLogService;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    protected $dashboardService;
    protected $activityLogService;

    public function __construct(
        AdminDashboardService $dashboardService,
        ActivityLogService $activityLogService,
        protected \App\Services\EmployeeAnalyticsService $employeeAnalyticsService,
        protected \App\Services\RealTimeNotificationService $notificationService,
        protected \App\Services\EmployeeQueryOptimizationService $employeeQueryService
    ) {
        // Admin check will be done in the method
        $this->dashboardService = $dashboardService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }
        
        $period = $request->get('period', 'month');
        $reportData = $this->getReportData($period);

        // If it's an AJAX request, only return trend data
        if ($request->wantsJson()) {
            return [
                'trendData' => $this->getTrendData($period)
            ];
        }

        // Otherwise return full page data
        $statistics = $this->dashboardService->getStatistics();
        $recentActivities = $this->activityLogService->getRecentActivities([
            'type' => $request->get('activity_type'),
            'status' => $request->get('status')
        ], 10);

        return Inertia::render('Admin/Dashboard/Index', [
            'investmentMetrics' => $this->getInvestmentMetrics(),
            'trendData' => $this->getTrendData($period),
            'summary' => $this->getSummaryData(),
            'stats' => array_merge($this->getStatsData(), $statistics),
            'categoryDistribution' => $this->getCategoryDistribution(),
            'alerts' => $this->getAlertsData(),
            'recentInvestments' => $this->getRecentInvestments(),
            'recentUsers' => $this->dashboardService->getRecentUsers(),
            'recentTransactions' => $this->dashboardService->getRecentTransactions(),
            'reportData' => $reportData,
            'recentActivities' => $recentActivities,
            'employeeManagement' => $this->getEnhancedEmployeeManagementData(),
        ]);
    }

    public function activities(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }
        
        $activities = $this->activityLogService->getPaginatedActivities([
            'type' => $request->get('type'),
            'status' => $request->get('status'),
            'date_range' => $request->get('date_range')
        ]);

        return view('admin.activities', compact('activities'));
    }

    public function metrics(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }
        
        $period = $request->get('period', 'month');
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };

        // Record today's metrics if not already recorded
        InvestmentMetric::recordDailyMetrics();

        $metrics = InvestmentMetric::where('date', '>=', $startDate)
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

        return Inertia::render('Admin/Investments/Metrics', [
            'metrics' => $metrics
        ]);
    }

    private function calculateTrendValue($metric)
    {
        $previousMetric = InvestmentMetric::where('date', '<', $metric->date)
            ->orderBy('date', 'desc')
            ->first();

        if (!$previousMetric || $previousMetric->total_value == 0) return 0;

        return round((($metric->total_value - $previousMetric->total_value) / $previousMetric->total_value) * 100, 2);
    }

    private function getInvestmentMetrics()
    {
        $currentPeriod = now()->startOfMonth();
        $previousPeriod = now()->subMonth()->startOfMonth();

        $currentMetrics = Investment::where('created_at', '>=', $currentPeriod)->get();
        $previousMetrics = Investment::whereBetween('created_at', [$previousPeriod, $currentPeriod])->get();

        return [
            'totalValue' => $currentMetrics->sum('amount'),
            'valueChange' => $this->calculateGrowth(
                $previousMetrics->sum('amount'),
                $currentMetrics->sum('amount')
            ),
            'activeInvestors' => User::whereHas('investments', function($query) {
                $query->where('status', 'active');
            })->count(),
            'investorChange' => $this->calculateGrowth(
                User::whereHas('investments', function($query) use ($previousPeriod, $currentPeriod) {
                    $query->whereBetween('created_at', [$previousPeriod, $currentPeriod]);
                })->count(),
                User::whereHas('investments', function($query) use ($currentPeriod) {
                    $query->where('created_at', '>=', $currentPeriod);
                })->count()
            ),
            'averageRoi' => Investment::where('status', 'active')->avg('roi') ?? 0,
            'roiChange' => 0, // Calculate ROI change if needed
        ];
    }

    private function getTrendData($period)
    {
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };

        $investments = Investment::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('date')
            ->get();

        return [
            'labels' => $investments->pluck('date'),
            'amounts' => $investments->pluck('total'),
            'counts' => $investments->pluck('count'),
            'totals' => [
                'amount' => $investments->sum('total'),
                'count' => $investments->sum('count')
            ],
            'averages' => [
                'amount' => $investments->avg('total') ?? 0
            ],
            'rates' => [
                'success' => $this->calculateSuccessRate($startDate)
            ],
            'growth' => [
                'amount' => $this->calculateGrowthForPeriod('amount', $startDate),
                'count' => $this->calculateGrowthForPeriod('count', $startDate),
                'average' => $this->calculateGrowthForPeriod('average', $startDate),
                'success' => $this->calculateSuccessRateGrowth($startDate)
            ]
        ];
    }

    private function getStartDate($period)
    {
        return match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };
    }

    private function calculateGrowth($previous, $current)
    {
        if ($previous == 0) return 0;
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function calculateSuccessRate($startDate)
    {
        $total = Investment::where('created_at', '>=', $startDate)->count();
        if ($total === 0) return 0;

        $successful = Investment::where('created_at', '>=', $startDate)
            ->whereIn('status', ['completed', 'active'])
            ->count();

        return round(($successful / $total) * 100, 2);
    }

    private function calculateSuccessRateGrowth($startDate)
    {
        $previousRate = $this->calculateSuccessRate($startDate->copy()->subDays($startDate->diffInDays(now())));
        $currentRate = $this->calculateSuccessRate($startDate);

        return $this->calculateGrowth($previousRate, $currentRate);
    }

    private function calculateGrowthForPeriod($metric, $startDate)
    {
        $previousPeriod = Investment::where('created_at', '>=', $startDate->copy()->subDays($startDate->diffInDays(now())))
            ->where('created_at', '<', $startDate);

        $currentPeriod = Investment::where('created_at', '>=', $startDate);

        $previousValue = match($metric) {
            'amount' => $previousPeriod->sum('amount'),
            'count' => $previousPeriod->count(),
            'average' => $previousPeriod->avg('amount') ?? 0,
            default => 0
        };

        $currentValue = match($metric) {
            'amount' => $currentPeriod->sum('amount'),
            'count' => $currentPeriod->count(),
            'average' => $currentPeriod->avg('amount') ?? 0,
            default => 0
        };

        return $this->calculateGrowth($previousValue, $currentValue);
    }

    private function getSummaryData()
    {
        $platformFee = Investment::sum('platform_fee') ?? 0;

        return [
            'total_value' => Investment::sum('amount') ?? 0,
            'total_count' => User::count() ?? 0,
            'active_count' => Investment::where('status', 'active')->count() ?? 0,
            'active_value' => Investment::where('status', 'active')->sum('amount') ?? 0,
            'revenue' => $platformFee,
            'revenue_growth' => $this->calculateGrowthRate('platform_fee'),
            // Reward System Metrics
            'total_referrals' => DB::table('users')->whereNotNull('referrer_id')->count(),
            'total_commissions_paid' => DB::table('referral_commissions')->where('status', 'paid')->sum('amount'),
            'pending_commissions' => DB::table('referral_commissions')->where('status', 'pending')->sum('amount'),
            'active_matrix_positions' => DB::table('matrix_positions')->whereNotNull('user_id')->count(),
            'profit_distributions_this_month' => DB::table('profit_distributions')
                ->whereMonth('created_at', now()->month)
                ->sum('total_distributed')
        ];
    }

    private function getStatsData()
    {
        $investments = Investment::active()->get();
        $avgRoi = $investments->avg('roi') ?? 0;
        $avgVolatility = $investments->avg(function($investment) {
            return $investment->calculateVolatility();
        }) ?? 0;

        return [
            'monthly_investment' => Investment::whereMonth('created_at', now()->month)->sum('amount') ?? 0,
            'investment_growth' => $this->calculateGrowthRate('amount'),
            'new_users' => User::whereMonth('created_at', now()->month)->count() ?? 0,
            'user_growth' => $this->calculateUserGrowth(),
            'success_rate' => $this->calculateSuccessRate(now()->subMonth()),
            'completed_investments' => Investment::whereIn('status', ['completed', 'active'])->count() ?? 0,
            'average_roi' => round($avgRoi, 2),
            'roi_progress' => min(round(($avgRoi / 15) * 100, 0), 100), // Assuming 15% is target ROI
            'risk_score' => round($avgVolatility, 2),
            'risk_progress' => min(round((1 - $avgVolatility / 20) * 100, 0), 100), // Lower volatility is better
        ];
    }

    private function getCategoryDistribution()
    {
        return DB::table('investments')
            ->rightJoin('investment_categories', 'investments.category_id', '=', 'investment_categories.id')
            ->select(
                'investment_categories.id',
                'investment_categories.name',
                DB::raw('COUNT(investments.id) as count'),
                DB::raw('COALESCE(SUM(investments.amount), 0) as total_value')
            )
            ->groupBy('investment_categories.id', 'investment_categories.name')
            ->get()
            ->map(function ($category) {
                $totalInvestments = Investment::sum('amount') ?: 1;
                $percentage = round(($category->total_value / $totalInvestments) * 100, 1);

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'count' => $category->count,
                    'total_value' => $category->total_value,
                    'percentage' => $percentage,
                    'color' => $this->getCategoryColor($category->id)
                ];
            });
    }

    private function getAlertsData()
    {
        return [
            'pending_approvals' => Investment::where('status', 'pending')->count() ?? 0,
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count() ?? 0,
            'withdrawal_amount' => Withdrawal::where('status', 'pending')->sum('amount') ?? 0,
            'pending_commissions' => DB::table('referral_commissions')->where('status', 'pending')->count() ?? 0,
            'pending_commission_amount' => DB::table('referral_commissions')->where('status', 'pending')->sum('amount') ?? 0,
            'tier_upgrades_pending' => 0, // Tier upgrades are processed immediately, no pending status
            'matrix_spillovers' => $this->getMatrixSpilloverAlerts(),
            'system_alerts' => $this->getSystemAlerts()
        ];
    }

    private function getRecentInvestments()
    {
        return $this->activityLogService->getRecentActivities([
            'type' => request('type'),
            'status' => request('status')
        ]);
    }

    private function calculateGrowthRate($field)
    {
        $currentMonth = Investment::whereMonth('created_at', now()->month)
            ->sum($field);
        $lastMonth = Investment::whereMonth('created_at', now()->subMonth()->month)
            ->sum($field);

        return $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;
    }

    private function calculateUserGrowth()
    {
        $currentMonth = User::whereMonth('created_at', now()->month)->count();
        $lastMonth = User::whereMonth('created_at', now()->subMonth()->month)->count();

        return $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;
    }

    private function getMatrixSpilloverAlerts()
    {
        // Check for users who need matrix position assignment
        return DB::table('users')
            ->leftJoin('matrix_positions', 'users.id', '=', 'matrix_positions.user_id')
            ->whereNull('matrix_positions.user_id')
            ->whereNotNull('users.referrer_id')
            ->count();
    }

    private function getSystemAlerts()
    {
        $alerts = [];
        
        // Check for commission processing delays
        $delayedCommissions = DB::table('referral_commissions')
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subDays(7))
            ->count();
            
        if ($delayedCommissions > 0) {
            $alerts[] = [
                'title' => 'Delayed Commission Processing',
                'message' => "{$delayedCommissions} commissions pending for over 7 days",
                'severity' => 'warning'
            ];
        }
        
        // Check for profit distribution due
        $profitDistributionDue = now()->day === 1 && now()->hour >= 9; // First day of month, after 9 AM
        if ($profitDistributionDue) {
            $alerts[] = [
                'title' => 'Monthly Profit Distribution Due',
                'message' => 'Monthly profit distribution should be processed',
                'severity' => 'info'
            ];
        }
        
        return $alerts;
    }

    private function getCategoryColor($categoryId)
    {
        $colors = [
            1 => '#4F46E5', // Indigo
            2 => '#7C3AED', // Purple
            3 => '#EC4899', // Pink
            4 => '#F59E0B', // Amber
            5 => '#10B981', // Emerald
        ];

        return $colors[$categoryId] ?? '#6B7280'; // Gray default
    }

    private function getReportData($period)
    {
        $startDate = $this->getStartDate($period);
        $endDate = now();
        $dates = collect();

        // Generate all dates in the period
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates->push($date->format('Y-m-d'));
        }

        // Get users data with proper date filling
        $users = User::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date');

        // Get returns data
        $returns = Investment::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('AVG(roi) as average_roi')
            ->groupBy('date')
            ->get()
            ->pluck('average_roi', 'date');

        // Get investments data
        $investments = Investment::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        // Fill missing dates with zeros
        $filledData = $dates->mapWithKeys(function ($date) use ($users, $returns, $investments) {
            return [$date => [
                'users' => $users[$date] ?? 0,
                'returns' => $returns[$date] ?? 0,
                'investments' => $investments[$date] ?? 0
            ]];
        });

        return [
            'labels' => $dates->values(),
            'users' => $filledData->pluck('users')->values(),
            'returns' => $filledData->pluck('returns')->values(),
            'period_stats' => [
                'total_investments' => $filledData->pluck('investments')->values(),
                'active_users' => User::whereHas('investments', function($query) use ($startDate) {
                    $query->where('status', 'active')
                        ->where('created_at', '>=', $startDate);
                })->count(),
                'pending_withdrawals' => Withdrawal::where('status', 'pending')
                    ->where('created_at', '>=', $startDate)
                    ->sum('amount'),
                'success_rate' => $this->calculateSuccessRate($startDate)
            ]
        ];
    }

    /**
     * Get enhanced employee management data for admin dashboard
     */
    private function getEnhancedEmployeeManagementData(): array
    {
        // Get optimized employee statistics
        $stats = $this->employeeQueryService->getEmployeeStatistics();
        
        // Add growth trends
        $stats['employeeGrowth'] = $this->calculateEmployeeGrowth();
        $stats['activeGrowth'] = $this->calculateActiveEmployeeGrowth();
        $stats['avgEmployeesPerDept'] = $stats['departments_count'] > 0 
            ? round($stats['active_employees'] / $stats['departments_count'], 1) 
            : 0;
        $stats['turnoverRate'] = $this->calculateTurnoverRate();
        $stats['avgPerformanceScore'] = $this->getAveragePerformanceScore();
        $stats['totalCommissions'] = $this->getQuarterlyCommissions();

        // Get analytics data
        $turnoverAnalysis = $this->employeeAnalyticsService->getEmployeeTurnoverAnalysis(6);
        $performanceDistribution = $this->employeeAnalyticsService->getPerformanceDistribution();
        $commissionAnalytics = $this->employeeAnalyticsService->getCommissionAnalytics(3);
        $departmentEfficiency = $this->employeeAnalyticsService->getDepartmentEfficiencyMetrics();

        // Get recent activities with real-time updates
        $recentActivities = $this->getEnhancedRecentActivities();
        
        // Get department overview with performance metrics
        $departmentOverview = $this->getEnhancedDepartmentOverview();
        
        // Get performance statistics with trends
        $performanceStats = $this->getEnhancedPerformanceStats();

        return [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'departmentOverview' => $departmentOverview,
            'performanceStats' => $performanceStats,
            'analytics' => [
                'turnoverAnalysis' => $turnoverAnalysis,
                'performanceDistribution' => $performanceDistribution,
                'commissionAnalytics' => $commissionAnalytics,
                'departmentEfficiency' => $departmentEfficiency,
            ],
            'alerts' => $this->getEmployeeAlerts(),
            'quickActions' => $this->getEmployeeQuickActions(),
        ];
    }

    /**
     * Get employee management data for admin dashboard (legacy method)
     */
    private function getEmployeeManagementData(): array
    {
        $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;
        $departmentModel = \App\Infrastructure\Persistence\Eloquent\DepartmentModel::class;
        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;
        $commissionModel = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::class;

        // Employee statistics
        $stats = [
            'totalEmployees' => $employeeModel::count(),
            'activeEmployees' => $employeeModel::where('employment_status', 'active')->count(),
            'newHires' => $employeeModel::where('hire_date', '>=', now()->startOfMonth())->count(),
            'departments' => $departmentModel::where('is_active', true)->count()
        ];

        // Recent activities
        $recentActivities = collect();
        
        // Recent hires
        $recentHires = $employeeModel::where('hire_date', '>=', now()->subDays(30))
            ->with(['department', 'position'])
            ->latest('hire_date')
            ->take(5)
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'employeeName' => $employee->first_name . ' ' . $employee->last_name,
                    'description' => 'Joined as ' . ($employee->position->title ?? 'Employee'),
                    'type' => 'hire',
                    'date' => $employee->hire_date->toISOString()
                ];
            });

        // Recent performance reviews
        $recentReviews = $performanceModel::with('employee')
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'employeeName' => $review->employee->first_name . ' ' . $review->employee->last_name,
                    'description' => 'Performance review completed - Score: ' . $review->overall_score,
                    'type' => 'performance',
                    'date' => $review->created_at->toISOString()
                ];
            });

        $recentActivities = $recentHires->concat($recentReviews)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        // Department overview
        $departmentOverview = $departmentModel::with(['head', 'employees'])
            ->where('is_active', true)
            ->get()
            ->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'headName' => $department->head ? 
                        $department->head->first_name . ' ' . $department->head->last_name : null,
                    'employeeCount' => $department->employees()->where('employment_status', 'active')->count()
                ];
            });

        // Performance statistics
        $performanceStats = [
            'averageScore' => $performanceModel::avg('overall_score') ?? 0,
            'topPerformers' => $performanceModel::where('overall_score', '>=', 8.0)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'goalAchievementRate' => $performanceModel::selectRaw('AVG(JSON_EXTRACT(metrics, "$.goal_achievement_rate")) as avg_goal_rate')->value('avg_goal_rate') ?? 0,
            'totalCommissions' => $commissionModel::where('status', 'paid')
                ->whereMonth('calculation_date', now()->month)
                ->sum('commission_amount')
        ];

        return [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'departmentOverview' => $departmentOverview,
            'performanceStats' => $performanceStats
        ];
    }

    /**
     * API endpoint for employee management summary
     */
    public function employeeManagementSummary()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        return response()->json($this->getEmployeeManagementData());
    }

    /**
     * API endpoint for department overview
     */
    public function departmentOverview()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        $departmentModel = \App\Infrastructure\Persistence\Eloquent\DepartmentModel::class;
        $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;
        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;

        $stats = [
            'totalDepartments' => $departmentModel::where('is_active', true)->count(),
            'averageEmployeesPerDept' => round($employeeModel::where('employment_status', 'active')->count() / 
                max($departmentModel::where('is_active', true)->count(), 1), 1)
        ];

        $performance = $departmentModel::with(['employees.performanceReviews'])
            ->where('is_active', true)
            ->get()
            ->map(function ($department) {
                $avgScore = $department->employees()
                    ->join('employee_performance', 'employees.id', '=', 'employee_performance.employee_id')
                    ->avg('employee_performance.overall_score') ?? 0;

                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'employeeCount' => $department->employees()->where('employment_status', 'active')->count(),
                    'performanceScore' => round($avgScore, 1)
                ];
            });

        $heads = $departmentModel::with('head')
            ->where('is_active', true)
            ->whereNotNull('head_employee_id')
            ->get()
            ->filter(function ($department) {
                return $department->head !== null;
            })
            ->map(function ($department) {
                return [
                    'id' => $department->head->id,
                    'name' => $department->head->first_name . ' ' . $department->head->last_name,
                    'departmentName' => $department->name,
                    'teamSize' => $department->employees()->where('employment_status', 'active')->count()
                ];
            });

        $recentChanges = collect([
            [
                'id' => 1,
                'type' => 'new_department',
                'description' => 'New department created',
                'date' => now()->subDays(5)->toISOString()
            ]
        ]);

        return response()->json([
            'stats' => $stats,
            'performance' => $performance,
            'heads' => $heads,
            'recentChanges' => $recentChanges
        ]);
    }

    /**
     * API endpoint for performance statistics
     */
    public function performanceStats()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;
        $commissionModel = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::class;
        $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;

        $stats = [
            'averageScore' => $performanceModel::avg('overall_score') ?? 0,
            'topPerformers' => $performanceModel::where('overall_score', '>=', 8.0)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'goalAchievementRate' => $performanceModel::selectRaw('AVG(JSON_EXTRACT(metrics, "$.goal_achievement_rate")) as avg_goal_rate')->value('avg_goal_rate') ?? 0,
            'totalCommissions' => $commissionModel::where('status', 'paid')
                ->whereMonth('calculation_date', now()->month)
                ->sum('commission_amount')
        ];

        $distribution = [
            ['label' => 'Excellent (9-10)', 'count' => $performanceModel::whereBetween('overall_score', [9, 10])->count(), 'percentage' => 0, 'color' => '#10b981'],
            ['label' => 'Good (7-8)', 'count' => $performanceModel::whereBetween('overall_score', [7, 8.99])->count(), 'percentage' => 0, 'color' => '#3b82f6'],
            ['label' => 'Average (5-6)', 'count' => $performanceModel::whereBetween('overall_score', [5, 6.99])->count(), 'percentage' => 0, 'color' => '#f59e0b'],
            ['label' => 'Poor (0-4)', 'count' => $performanceModel::whereBetween('overall_score', [0, 4.99])->count(), 'percentage' => 0, 'color' => '#ef4444']
        ];

        $total = array_sum(array_column($distribution, 'count'));
        if ($total > 0) {
            foreach ($distribution as &$item) {
                $item['percentage'] = round(($item['count'] / $total) * 100, 1);
            }
        }

        $topPerformers = $performanceModel::with(['employee.department', 'employee.position'])
            ->whereMonth('created_at', now()->month)
            ->orderBy('overall_score', 'desc')
            ->take(5)
            ->get()
            ->map(function ($performance) {
                $commissions = $commissionModel::where('employee_id', $performance->employee_id)
                    ->whereMonth('calculation_date', now()->month)
                    ->where('status', 'paid')
                    ->sum('commission_amount');

                return [
                    'id' => $performance->employee->id,
                    'name' => $performance->employee->first_name . ' ' . $performance->employee->last_name,
                    'department' => $performance->employee->department->name ?? 'N/A',
                    'position' => $performance->employee->position->title ?? 'N/A',
                    'score' => $performance->overall_score,
                    'commissions' => $commissions
                ];
            });

        $trends = collect(range(5, 0))->map(function ($monthsAgo) use ($performanceModel) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M'),
                'score' => $performanceModel::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->avg('overall_score') ?? 0
            ];
        });

        $thisMonth = $commissionModel::where('status', 'paid')
            ->whereMonth('calculation_date', now()->month)
            ->sum('commission_amount');
        
        $lastMonth = $commissionModel::where('status', 'paid')
            ->whereMonth('calculation_date', now()->subMonth()->month)
            ->sum('commission_amount');

        return response()->json([
            'stats' => $stats,
            'distribution' => $distribution,
            'topPerformers' => $topPerformers,
            'trends' => $trends,
            'commissionGrowth' => $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0
        ]);
    }

    // Enhanced Employee Management Helper Methods

    private function calculateEmployeeGrowth(): float
    {
        $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;
        $currentMonth = $employeeModel::whereMonth('hire_date', now()->month)->count();
        $lastMonth = $employeeModel::whereMonth('hire_date', now()->subMonth()->month)->count();
        
        return $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;
    }

    private function calculateActiveEmployeeGrowth(): float
    {
        $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;
        $currentActive = $employeeModel::where('employment_status', 'active')->count();
        $lastMonthActive = $employeeModel::where('employment_status', 'active')
            ->where('hire_date', '<=', now()->subMonth()->endOfMonth())
            ->whereNull('termination_date')
            ->orWhere('termination_date', '>', now()->subMonth()->endOfMonth())
            ->count();
        
        return $lastMonthActive > 0 ? round((($currentActive - $lastMonthActive) / $lastMonthActive) * 100, 1) : 0;
    }

    private function calculateTurnoverRate(): float
    {
        $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;
        $totalEmployees = $employeeModel::count();
        $terminatedThisYear = $employeeModel::whereYear('termination_date', now()->year)->count();
        
        return $totalEmployees > 0 ? round(($terminatedThisYear / $totalEmployees) * 100, 1) : 0;
    }

    private function getAveragePerformanceScore(): float
    {
        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;
        return round($performanceModel::whereYear('created_at', now()->year)->avg('overall_score') ?? 0, 1);
    }

    private function getQuarterlyCommissions(): float
    {
        $commissionModel = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::class;
        return $commissionModel::where('status', 'paid')
            ->whereBetween('calculation_date', [now()->startOfQuarter(), now()->endOfQuarter()])
            ->sum('commission_amount');
    }

    private function getEnhancedRecentActivities(): array
    {
        $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;
        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;
        $commissionModel = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::class;

        $activities = collect();

        // Recent hires
        $recentHires = $employeeModel::where('hire_date', '>=', now()->subDays(30))
            ->with(['department', 'position'])
            ->latest('hire_date')
            ->take(3)
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => 'hire_' . $employee->id,
                    'employeeName' => $employee->first_name . ' ' . $employee->last_name,
                    'description' => 'Joined as ' . ($employee->position->title ?? 'Employee') . ' in ' . ($employee->department->name ?? 'Unknown Department'),
                    'type' => 'hire',
                    'date' => $employee->hire_date->toISOString(),
                    'priority' => 'normal'
                ];
            });

        // Recent performance reviews
        $recentReviews = $performanceModel::with('employee')
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => 'review_' . $review->id,
                    'employeeName' => $review->employee->first_name . ' ' . $review->employee->last_name,
                    'description' => 'Performance review completed - Score: ' . $review->overall_score . '/10',
                    'type' => 'performance',
                    'date' => $review->created_at->toISOString(),
                    'priority' => $review->overall_score >= 8 ? 'high' : 'normal'
                ];
            });

        // Recent promotions/status changes
        $recentPromotions = $employeeModel::where('updated_at', '>=', now()->subDays(30))
            ->where('created_at', '<', now()->subDays(30)) // Not new hires
            ->with(['department', 'position'])
            ->latest('updated_at')
            ->take(2)
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => 'promotion_' . $employee->id,
                    'employeeName' => $employee->first_name . ' ' . $employee->last_name,
                    'description' => 'Profile updated - ' . ($employee->position->title ?? 'Position updated'),
                    'type' => 'promotion',
                    'date' => $employee->updated_at->toISOString(),
                    'priority' => 'normal'
                ];
            });

        // High-value commissions
        $highCommissions = $commissionModel::with('employee')
            ->where('calculation_date', '>=', now()->subDays(30))
            ->where('commission_amount', '>=', 1000)
            ->latest('calculation_date')
            ->take(2)
            ->get()
            ->map(function ($commission) {
                return [
                    'id' => 'commission_' . $commission->id,
                    'employeeName' => $commission->employee->first_name . ' ' . $commission->employee->last_name,
                    'description' => 'High commission earned: K' . number_format($commission->commission_amount, 2),
                    'type' => 'commission',
                    'date' => $commission->calculation_date->toISOString(),
                    'priority' => 'high'
                ];
            });

        return $activities->concat($recentHires)
            ->concat($recentReviews)
            ->concat($recentPromotions)
            ->concat($highCommissions)
            ->sortByDesc('date')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function getEnhancedDepartmentOverview(): array
    {
        $departmentModel = \App\Infrastructure\Persistence\Eloquent\DepartmentModel::class;
        
        return $departmentModel::with(['head', 'employees'])
            ->where('is_active', true)
            ->get()
            ->map(function ($department) {
                $activeEmployees = $department->employees()->where('employment_status', 'active')->count();
                $avgPerformance = $department->employees()
                    ->join('employee_performance', 'employees.id', '=', 'employee_performance.employee_id')
                    ->whereYear('employee_performance.created_at', now()->year)
                    ->avg('employee_performance.overall_score') ?? 0;

                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'headName' => $department->head ? 
                        $department->head->first_name . ' ' . $department->head->last_name : null,
                    'employeeCount' => $activeEmployees,
                    'performanceScore' => round($avgPerformance, 1),
                    'efficiency' => $this->calculateDepartmentEfficiency($department),
                    'growth' => $this->calculateDepartmentGrowth($department->id),
                ];
            })
            ->sortByDesc('efficiency')
            ->values()
            ->toArray();
    }

    private function getEnhancedPerformanceStats(): array
    {
        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;
        $commissionModel = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::class;

        $currentYear = now()->year;
        $currentMonth = now()->month;

        return [
            'averageScore' => round($performanceModel::whereYear('created_at', $currentYear)->avg('overall_score') ?? 0, 1),
            'topPerformers' => $performanceModel::where('overall_score', '>=', 8.0)
                ->whereMonth('created_at', $currentMonth)
                ->count(),
            'goalAchievementRate' => $this->calculateGoalAchievementRate(),
            'totalCommissions' => $commissionModel::where('status', 'paid')
                ->whereMonth('calculation_date', $currentMonth)
                ->sum('commission_amount'),
            'performanceTrend' => $this->getPerformanceTrend(),
            'commissionTrend' => $this->getCommissionTrend(),
            'reviewsCompleted' => $performanceModel::whereMonth('created_at', $currentMonth)->count(),
            'reviewsPending' => $this->getPendingReviewsCount(),
        ];
    }

    private function getEmployeeAlerts(): array
    {
        $alerts = [];
        
        // Performance reviews due
        $reviewsDue = $this->getPendingReviewsCount();
        if ($reviewsDue > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Performance Reviews Due',
                'message' => "{$reviewsDue} employees need performance reviews",
                'action' => 'View Reviews',
                'url' => route('admin.performance.index')
            ];
        }

        // High turnover departments
        $highTurnoverDepts = $this->getHighTurnoverDepartments();
        if (!empty($highTurnoverDepts)) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'High Turnover Alert',
                'message' => count($highTurnoverDepts) . ' departments have high turnover rates',
                'action' => 'View Analytics',
                'url' => route('admin.employees.analytics')
            ];
        }

        // Pending commissions
        $commissionModel = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::class;
        $pendingCommissions = $commissionModel::where('status', 'pending')->count();
        if ($pendingCommissions > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Pending Commissions',
                'message' => "{$pendingCommissions} commission payments pending approval",
                'action' => 'Process Commissions',
                'url' => route('admin.commissions.index')
            ];
        }

        return $alerts;
    }

    private function getEmployeeQuickActions(): array
    {
        return [
            [
                'title' => 'Add Employee',
                'description' => 'Register a new employee',
                'icon' => 'user-plus',
                'url' => route('admin.employees.create'),
                'color' => 'blue'
            ],
            [
                'title' => 'Bulk Operations',
                'description' => 'Perform bulk employee actions',
                'icon' => 'users',
                'url' => route('admin.employees.bulk'),
                'color' => 'purple'
            ],
            [
                'title' => 'Analytics',
                'description' => 'View detailed analytics',
                'icon' => 'chart-bar',
                'url' => route('admin.employees.analytics'),
                'color' => 'green'
            ],
            [
                'title' => 'Export Data',
                'description' => 'Export employee data',
                'icon' => 'download',
                'url' => route('admin.employees.export'),
                'color' => 'gray'
            ]
        ];
    }

    // Additional helper methods
    private function calculateDepartmentEfficiency($department): float
    {
        // Simple efficiency calculation based on performance and retention
        $activeEmployees = $department->employees()->where('employment_status', 'active')->count();
        if ($activeEmployees === 0) return 0;

        $avgPerformance = $department->employees()
            ->join('employee_performance', 'employees.id', '=', 'employee_performance.employee_id')
            ->whereYear('employee_performance.created_at', now()->year)
            ->avg('employee_performance.overall_score') ?? 0;

        $turnoverRate = $this->calculateDepartmentTurnoverRate($department->id);
        $retentionScore = max(0, 100 - $turnoverRate);

        return round(($avgPerformance * 10 + $retentionScore) / 2, 1);
    }

    private function calculateDepartmentGrowth(int $departmentId): float
    {
        $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;
        $currentCount = $employeeModel::where('department_id', $departmentId)
            ->where('employment_status', 'active')
            ->count();
        
        $lastMonthCount = $employeeModel::where('department_id', $departmentId)
            ->where('hire_date', '<=', now()->subMonth()->endOfMonth())
            ->where(function($query) {
                $query->whereNull('termination_date')
                    ->orWhere('termination_date', '>', now()->subMonth()->endOfMonth());
            })
            ->count();

        return $lastMonthCount > 0 ? round((($currentCount - $lastMonthCount) / $lastMonthCount) * 100, 1) : 0;
    }

    private function calculateDepartmentTurnoverRate(int $departmentId): float
    {
        $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;
        $totalEmployees = $employeeModel::where('department_id', $departmentId)->count();
        $terminated = $employeeModel::where('department_id', $departmentId)
            ->whereYear('termination_date', now()->year)
            ->count();

        return $totalEmployees > 0 ? round(($terminated / $totalEmployees) * 100, 1) : 0;
    }

    private function calculateGoalAchievementRate(): float
    {
        // This would depend on how goals are stored - placeholder implementation
        return 75.5; // Example rate
    }

    private function getPerformanceTrend(): array
    {
        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;
        
        return collect(range(5, 0))->map(function ($monthsAgo) use ($performanceModel) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M'),
                'score' => round($performanceModel::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->avg('overall_score') ?? 0, 1)
            ];
        })->toArray();
    }

    private function getCommissionTrend(): array
    {
        $commissionModel = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::class;
        
        return collect(range(5, 0))->map(function ($monthsAgo) use ($commissionModel) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M'),
                'amount' => $commissionModel::whereYear('calculation_date', $date->year)
                    ->whereMonth('calculation_date', $date->month)
                    ->where('status', 'paid')
                    ->sum('commission_amount')
            ];
        })->toArray();
    }

    private function getPendingReviewsCount(): int
    {
        $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;
        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;
        
        return $employeeModel::where('employment_status', 'active')
            ->whereDoesntHave('performanceReviews', function ($query) {
                $query->where('created_at', '>=', now()->subMonths(6));
            })
            ->count();
    }

    private function getHighTurnoverDepartments(): array
    {
        $departmentModel = \App\Infrastructure\Persistence\Eloquent\DepartmentModel::class;
        
        return $departmentModel::where('is_active', true)
            ->get()
            ->filter(function ($department) {
                return $this->calculateDepartmentTurnoverRate($department->id) > 15; // 15% threshold
            })
            ->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'turnoverRate' => $this->calculateDepartmentTurnoverRate($department->id)
                ];
            })
            ->toArray();
    }

    private function getCommissionStats(): array
    {
        $commissionModel = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::class;
        
        $thisMonth = $commissionModel::where('status', 'paid')
            ->whereMonth('calculation_date', now()->month)
            ->sum('commission_amount');
        
        $lastMonth = $commissionModel::where('status', 'paid')
            ->whereMonth('calculation_date', now()->subMonth()->month)
            ->sum('commission_amount');

        $commissionStats = [
            'thisMonth' => $thisMonth,
            'lastMonth' => $lastMonth,
            'growth' => $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0
        ];

        return response()->json([
            'stats' => $stats,
            'distribution' => $distribution,
            'topPerformers' => $topPerformers,
            'trends' => $trends,
            'commissionStats' => $commissionStats
        ]);
    }
}
