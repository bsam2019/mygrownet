<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Services\DashboardService;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\InvestmentCategory;
use App\Models\InvestmentOpportunity;
use App\Models\InvestmentTier;
use App\Models\ActivityLog;
use App\Models\ReferralCommission;
use App\Models\ProfitShare;
use App\Models\WithdrawalRequest;
use App\Domain\Financial\Services\WithdrawalPolicyService;
use App\Domain\Investment\Services\InvestmentTierService;
use App\Domain\Reward\Services\ReferralMatrixService;
use App\Infrastructure\Persistence\Repositories\EloquentReferralRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $dashboardService;
    protected $withdrawalPolicyService;
    protected $investmentTierService;
    protected $referralMatrixService;
    protected $referralRepository;

    public function __construct(
        DashboardService $dashboardService,
        WithdrawalPolicyService $withdrawalPolicyService,
        InvestmentTierService $investmentTierService,
        ReferralMatrixService $referralMatrixService,
        EloquentReferralRepository $referralRepository
    ) {
        $this->dashboardService = $dashboardService;
        $this->withdrawalPolicyService = $withdrawalPolicyService;
        $this->investmentTierService = $investmentTierService;
        $this->referralMatrixService = $referralMatrixService;
        $this->referralRepository = $referralRepository;
    }

    /**
     * Display role-based dashboard with VBIF-specific data aggregation
     */
    public function index()
    {
        $user = auth()->user();
        
        // Route to appropriate dashboard based on user role
        // Check for Administrator role (Spatie)
        if ($user->hasRole('Administrator') || $user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        
        // Check if user has manager role (using a simple field or method)
        if ($user->rank === 'manager' || $this->isManager($user)) {
            return redirect()->route('manager.dashboard');
        }
        
        // Default: Redirect to appropriate dashboard based on preference
        $preference = $user->preferred_dashboard ?? 'mobile';
        
        if ($preference === 'classic' || $preference === 'desktop') {
            // User prefers classic dashboard
            return redirect()->route('mygrownet.classic-dashboard');
        }
        
        // Default to mobile dashboard for all regular users
        return redirect()->route('mygrownet.dashboard');
    }

    /**
     * Simple manager check - you can customize this logic
     */
    private function isManager($user): bool
    {
        // Simple check based on rank or other criteria
        return in_array($user->rank, ['manager', 'regional_manager']) || 
               $user->email === 'manager@vbif.com';
    }

    /**
     * Display investor dashboard
     */
    public function investorDashboard()
    {
        $user = auth()->user();
        
        // MyGrowNet Dashboard Data
        $dashboardData = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'referral_code' => $user->referral_code,
                'joined_at' => $user->created_at->format('M d, Y'),
            ],
            'points' => [
                'lifetime_points' => (int) \DB::table('point_transactions')->where('user_id', $user->id)->sum('lp_amount'),
                'bonus_points' => (int) \DB::table('point_transactions')->where('user_id', $user->id)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('bp_amount'),
                'monthly_bp_target' => $this->getMonthlyBPTarget($user),
                'bp_progress_percentage' => $this->calculateBPProgress($user),
            ],
            'professionalLevel' => [
                'current' => $user->professional_level ?? 'Associate',
                'level_number' => $this->getLevelNumber($user->professional_level ?? 'Associate'),
                'next_level' => $this->getNextLevel($user->professional_level ?? 'Associate'),
                'lp_required_for_next' => $this->getLPRequiredForNextLevel($user),
                'lp_progress_percentage' => $this->calculateLPProgress($user),
            ],
            'earnings' => [
                'current_month' => $this->getCurrentMonthEarnings($user),
                'last_month' => $this->getLastMonthEarnings($user),
                'total_lifetime' => $this->getTotalLifetimeEarnings($user),
                'pending' => $this->getPendingEarnings($user),
            ],
            'network' => [
                'direct_referrals' => $user->directReferrals()->count(),
                'total_network' => $this->getTotalNetworkSize($user),
                'active_members' => $this->getActiveNetworkMembers($user),
                'level_breakdown' => $this->getNetworkLevelBreakdown($user),
            ],
            'wallet' => [
                'balance' => $this->getWalletBalance($user),
                'total_earnings' => $this->getTotalLifetimeEarnings($user),
                'total_withdrawals' => $user->withdrawals()->where('status', 'approved')->sum('amount') ?? 0,
            ],
            'recentActivities' => $this->getRecentActivities($user),
        ];

        return Inertia::render('Dashboard/MyGrowNetDashboard', $dashboardData);
    }
    
    // Helper methods for MyGrowNet dashboard
    private function getMonthlyBPTarget($user)
    {
        $levels = [
            'Associate' => 100,
            'Professional' => 200,
            'Senior' => 300,
            'Manager' => 400,
            'Director' => 500,
            'Executive' => 600,
            'Ambassador' => 800,
        ];
        return $levels[$user->professional_level ?? 'Associate'] ?? 100;
    }
    
    private function calculateBPProgress($user)
    {
        $target = $this->getMonthlyBPTarget($user);
        $current = $user->bonus_points ?? 0;
        return $target > 0 ? min(100, ($current / $target) * 100) : 0;
    }
    
    private function getLevelNumber($level)
    {
        $levels = [
            'Associate' => 1,
            'Professional' => 2,
            'Senior' => 3,
            'Manager' => 4,
            'Director' => 5,
            'Executive' => 6,
            'Ambassador' => 7,
        ];
        return $levels[$level] ?? 1;
    }
    
    private function getNextLevel($currentLevel)
    {
        $progression = [
            'Associate' => 'Professional',
            'Professional' => 'Senior',
            'Senior' => 'Manager',
            'Manager' => 'Director',
            'Director' => 'Executive',
            'Executive' => 'Ambassador',
            'Ambassador' => null,
        ];
        return $progression[$currentLevel] ?? null;
    }
    
    private function getLPRequiredForNextLevel($user)
    {
        $requirements = [
            'Associate' => 1000,
            'Professional' => 2500,
            'Senior' => 5000,
            'Manager' => 10000,
            'Director' => 20000,
            'Executive' => 40000,
            'Ambassador' => null,
        ];
        $nextLevel = $this->getNextLevel($user->professional_level ?? 'Associate');
        return $nextLevel ? $requirements[$user->professional_level ?? 'Associate'] : null;
    }
    
    private function calculateLPProgress($user)
    {
        $required = $this->getLPRequiredForNextLevel($user);
        if (!$required) return 100;
        
        $current = $user->lifetime_points ?? 0;
        return min(100, ($current / $required) * 100);
    }
    
    private function getCurrentMonthEarnings($user)
    {
        return $user->referralCommissions()
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->where('status', 'paid')
            ->sum('amount') ?? 0;
    }
    
    private function getLastMonthEarnings($user)
    {
        $lastMonth = now()->subMonth();
        return $user->referralCommissions()
            ->whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->where('status', 'paid')
            ->sum('amount') ?? 0;
    }
    
    private function getTotalLifetimeEarnings($user)
    {
        $commissions = $user->referralCommissions()->where('status', 'paid')->sum('amount') ?? 0;
        $profitShares = $user->profitShares()->sum('amount') ?? 0;
        return $commissions + $profitShares;
    }
    
    private function getPendingEarnings($user)
    {
        return $user->referralCommissions()->where('status', 'pending')->sum('amount') ?? 0;
    }
    
    private function getTotalNetworkSize($user)
    {
        return $user->directReferrals()->count() + 
               $user->directReferrals()->with('directReferrals')->get()->sum(fn($r) => $r->directReferrals->count());
    }
    
    private function getActiveNetworkMembers($user)
    {
        return $user->directReferrals()
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();
    }
    
    private function getNetworkLevelBreakdown($user)
    {
        $breakdown = [];
        for ($level = 1; $level <= 7; $level++) {
            $breakdown[] = [
                'level' => $level,
                'count' => $level === 1 ? $user->directReferrals()->count() : 0,
            ];
        }
        return $breakdown;
    }
    
    private function getWalletBalance($user)
    {
        // Use centralized WalletService for consistent calculation
        $walletService = app(\App\Services\WalletService::class);
        return $walletService->calculateBalance($user);
    }
    
    private function getRecentActivities($user)
    {
        return $user->referralCommissions()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($commission) {
                return [
                    'id' => $commission->id,
                    'description' => 'Earned commission from Level ' . ($commission->level ?? '1') . ' referral',
                    'points_earned' => 10, // Simplified
                    'created_at' => $commission->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }

    /**
     * Get real-time earnings and investment performance data
     */
    public function realTimeEarnings(): JsonResponse
    {
        $user = auth()->user();
        
        // Get real-time earnings breakdown
        $earnings = $user->calculateTotalEarningsDetailed();
        
        // Get current investment performance
        $investmentPerformance = $this->calculateRealTimeInvestmentPerformance($user);
        
        // Get recent earnings activity
        $recentEarnings = $this->getRecentEarningsActivity($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'earnings' => $earnings,
                'investment_performance' => $investmentPerformance,
                'recent_earnings' => $recentEarnings,
                'last_updated' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get withdrawal eligibility and penalty preview
     */
    public function withdrawalEligibility(Request $request): JsonResponse
    {
        $user = auth()->user();
        $investmentId = $request->get('investment_id');
        $withdrawalType = $request->get('withdrawal_type', 'full');
        $amount = $request->get('amount');

        if ($investmentId) {
            $investment = Investment::where('id', $investmentId)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$investment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Investment not found'
                ], 404);
            }

            $eligibility = $investment->isEligibleForWithdrawal($withdrawalType);
            $penalties = $investment->calculateWithdrawalPenalties();
            $scenarios = $investment->simulateWithdrawalScenarios();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'investment' => [
                        'id' => $investment->id,
                        'amount' => $investment->amount,
                        'current_value' => $investment->getCurrentValue(),
                        'tier' => $investment->tier?->name
                    ],
                    'eligibility' => $eligibility,
                    'penalties' => $penalties,
                    'scenarios' => $scenarios,
                    'withdrawable_amount' => $investment->getWithdrawableAmount($withdrawalType)
                ]
            ]);
        }

        // General withdrawal eligibility for user
        $generalEligibility = $this->withdrawalPolicyService->validateWithdrawal($user, $amount ?? 0);
        
        return response()->json([
            'success' => true,
            'data' => [
                'general_eligibility' => $generalEligibility,
                'user_investments' => $user->investments()
                    ->where('status', 'active')
                    ->with('tier')
                    ->get()
                    ->map(function ($investment) {
                        return [
                            'id' => $investment->id,
                            'amount' => $investment->amount,
                            'current_value' => $investment->getCurrentValue(),
                            'tier' => $investment->tier?->name,
                            'can_withdraw' => $investment->canWithdraw(),
                            'penalties' => $investment->calculateWithdrawalPenalties()
                        ];
                    })
            ]
        ]);
    }

    /**
     * Get penalty preview for specific withdrawal scenario
     */
    public function penaltyPreview(Request $request): JsonResponse
    {
        $request->validate([
            'investment_id' => 'required|exists:investments,id',
            'withdrawal_type' => 'required|in:full,partial,emergency,profits_only',
            'amount' => 'nullable|numeric|min:1',
            'withdrawal_date' => 'nullable|date'
        ]);

        $user = auth()->user();
        $investment = Investment::where('id', $request->investment_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$investment) {
            return response()->json([
                'success' => false,
                'message' => 'Investment not found'
            ], 404);
        }

        $withdrawalDate = $request->withdrawal_date 
            ? Carbon::parse($request->withdrawal_date)
            : now();

        $penaltyDetails = $investment->calculateTimedWithdrawalPenalty($withdrawalDate);
        $eligibility = $investment->isEligibleForWithdrawal($request->withdrawal_type);
        
        return response()->json([
            'success' => true,
            'data' => [
                'investment' => [
                    'id' => $investment->id,
                    'amount' => $investment->amount,
                    'current_value' => $investment->getCurrentValue(),
                    'tier' => $investment->tier?->name
                ],
                'withdrawal_details' => [
                    'type' => $request->withdrawal_type,
                    'requested_amount' => $request->amount,
                    'withdrawal_date' => $withdrawalDate,
                    'withdrawable_amount' => $investment->getWithdrawableAmount($request->withdrawal_type)
                ],
                'penalty_details' => $penaltyDetails,
                'eligibility' => $eligibility,
                'net_amount' => $penaltyDetails['penalty_applicable'] 
                    ? $penaltyDetails['net_withdrawable_amount']
                    : $investment->getWithdrawableAmount($request->withdrawal_type)
            ]
        ]);
    }

    /**
     * Get notifications and activity feed
     */
    public function notificationsAndActivity(Request $request): JsonResponse
    {
        $user = auth()->user();
        $limit = $request->get('limit', 20);
        $type = $request->get('type', 'all'); // all, notifications, activities
        
        $data = [];
        
        if ($type === 'all' || $type === 'notifications') {
            $data['notifications'] = $this->getUserNotifications($user, $limit);
        }
        
        if ($type === 'all' || $type === 'activities') {
            $data['activities'] = $this->getUserActivities($user, $limit);
        }
        
        if ($type === 'all') {
            $data['combined_feed'] = $this->getCombinedActivityFeed($user, $limit);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get comprehensive dashboard metrics
     */
    public function dashboardMetrics(): JsonResponse
    {
        $user = auth()->user();
        
        $metrics = [
            'portfolio_overview' => $this->getPortfolioOverview($user),
            'earnings_breakdown' => $user->calculateTotalEarningsDetailed(),
            'investment_performance' => $this->getInvestmentPerformanceMetrics($user),
            'referral_metrics' => $this->getReferralMetrics($user),
            'tier_information' => $this->getTierInformation($user),
            'matrix_performance' => $this->getMatrixPerformance($user),
            'withdrawal_summary' => $this->getWithdrawalSummary($user),
            'growth_trends' => $this->getGrowthTrends($user)
        ];

        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Get investment performance trends
     */
    public function investmentTrends(Request $request): JsonResponse
    {
        $user = auth()->user();
        $period = $request->get('period', 'month'); // day, week, month, quarter, year
        $investmentId = $request->get('investment_id');

        if ($investmentId) {
            $investment = Investment::where('id', $investmentId)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$investment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Investment not found'
                ], 404);
            }

            $trends = $this->getInvestmentSpecificTrends($investment, $period);
        } else {
            $trends = $this->getPortfolioTrends($user, $period);
        }

        return response()->json([
            'success' => true,
            'data' => $trends
        ]);
    }

    /**
     * Get tier upgrade recommendations
     */
    public function tierUpgradeRecommendations(): JsonResponse
    {
        $user = auth()->user();
        
        $eligibility = $user->checkTierUpgradeEligibility();
        $recommendations = $this->investmentTierService->getTierUpgradeRecommendations($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'eligibility' => $eligibility,
                'recommendations' => $recommendations,
                'tier_comparison' => $this->getTierComparisonData($user),
                'upgrade_benefits' => $this->calculateUpgradeBenefits($user)
            ]
        ]);
    }

    /**
     * Get matrix position and performance data
     */
    public function matrixData(): JsonResponse
    {
        $user = auth()->user();
        
        $matrixData = [
            'structure' => $user->buildMatrixStructure(3),
            'position_details' => $user->getMatrixPosition(),
            'downline_counts' => $user->getMatrixDownlineCount(),
            'performance_metrics' => $this->referralMatrixService->getMatrixPerformanceMetrics($user),
            'spillover_opportunities' => $this->identifySpilloverOpportunities($user),
            'commission_potential' => $this->calculateMatrixCommissionPotential($user)
        ];

        return response()->json([
            'success' => true,
            'data' => $matrixData
        ]);
    }

    /**
     * Private helper methods for data aggregation
     */
    private function aggregateDashboardData($user, $now): array
    {
        // VBIF-specific earnings breakdown
        $earnings = $user->calculateTotalEarningsDetailed();
        
        // VBIF tier upgrade information
        $tierInfo = $user->checkTierUpgradeEligibility();
        $tierProgress = $user->getTierProgressPercentage();
        
        // VBIF matrix information
        $matrixStructure = $user->buildMatrixStructure(3);
        $downlineCounts = $user->getMatrixDownlineCount();
        
        // Enhanced referral stats
        $referralStats = $user->getReferralStats();
        
        // Employee-specific data if user is an employee
        $employeeData = $this->getEmployeeData($user);

        // Get portfolio data (enhanced with VBIF calculations)
        $portfolio = [
            'total_investment' => $user->total_investment_amount ?? 0,
            'total_earnings' => $earnings['total_earnings'],
            'referral_earnings' => $earnings['referral_commissions'],
            'profit_earnings' => $earnings['profit_shares'],
            'matrix_commissions' => $earnings['matrix_commissions'],
            'pending_earnings' => $earnings['pending_earnings'],
            'active_referrals' => $referralStats['active_referrals'],
            'total_referrals' => $referralStats['total_referrals']
        ];
        
        // Get investment performance metrics for active investments
        $activeInvestments = $user->investments()->where('status', 'active')->with('tier')->get();
        $investmentMetrics = $activeInvestments->map(function ($investment) {
            return [
                'id' => $investment->id,
                'amount' => $investment->amount,
                'tier' => $investment->tier?->name,
                'metrics' => $investment->getInvestmentPerformanceMetrics(),
                'withdrawal_eligibility' => $investment->canWithdraw()
            ];
        });
        
        // Get recent transactions
        $recentTransactions = $user->transactions()
            ->select('id', 'reference_number', 'amount as investment_amount', 'transaction_type', 'status', 'created_at')
            ->latest()
            ->take(5)
            ->get();
        
        // Get investment opportunities
        $investmentOpportunities = InvestmentOpportunity::select('id', 'name', 'description', 'minimum_investment', 'expected_returns')
            ->where('status', 'active')
            ->take(3)
            ->get();

        // Get withdrawal eligibility summary
        $withdrawalSummary = $this->getWithdrawalSummary($user);
        
        // Get recent activities and notifications
        $recentActivities = $this->getUserActivities($user, 10);
        $notifications = $this->getUserNotifications($user, 5);

        return [
            'portfolio' => $portfolio,
            'earnings' => $earnings,
            'tierInfo' => $tierInfo,
            'tierProgress' => $tierProgress,
            'matrixStructure' => $matrixStructure,
            'downlineCounts' => $downlineCounts,
            'referralStats' => $referralStats,
            'investmentMetrics' => $investmentMetrics,
            'recent_transactions' => $recentTransactions,
            'investment_opportunities' => $investmentOpportunities,
            'withdrawal_summary' => $withdrawalSummary,
            'recent_activities' => $recentActivities,
            'notifications' => $notifications,
            'statistics' => $this->dashboardService->getUserStatistics($user),
            'recent_investments' => $this->dashboardService->getRecentInvestments($user),
            'employee_data' => $employeeData,
        ];
    }

    private function calculateRealTimeInvestmentPerformance($user): array
    {
        $activeInvestments = $user->investments()->where('status', 'active')->with('tier')->get();
        
        $totalInvested = $activeInvestments->sum('amount');
        $totalCurrentValue = $activeInvestments->sum(function ($investment) {
            return $investment->getCurrentValue();
        });
        
        $totalProfit = $totalCurrentValue - $totalInvested;
        $averageROI = $totalInvested > 0 ? ($totalProfit / $totalInvested) * 100 : 0;
        
        return [
            'total_invested' => $totalInvested,
            'current_value' => $totalCurrentValue,
            'total_profit' => $totalProfit,
            'average_roi' => $averageROI,
            'investment_count' => $activeInvestments->count(),
            'performance_by_tier' => $this->getPerformanceByTier($activeInvestments),
            'best_performing' => $activeInvestments->sortByDesc(function ($investment) {
                return $investment->getRoiAttribute();
            })->first(),
            'growth_rate' => $this->calculatePortfolioGrowthRate($activeInvestments)
        ];
    }

    private function getRecentEarningsActivity($user): array
    {
        $recentCommissions = ReferralCommission::where('referrer_id', $user->id)
            ->with(['referee', 'investment'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($commission) {
                return [
                    'type' => 'referral_commission',
                    'amount' => $commission->amount,
                    'level' => $commission->level,
                    'referee' => $commission->referee?->name,
                    'date' => $commission->created_at,
                    'status' => $commission->status
                ];
            });

        $recentProfitShares = ProfitShare::where('user_id', $user->id)
            ->with('investment')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($share) {
                return [
                    'type' => 'profit_share',
                    'amount' => $share->amount,
                    'period' => $share->period_type,
                    'investment_id' => $share->investment_id,
                    'date' => $share->created_at,
                    'status' => $share->status
                ];
            });

        return [
            'commissions' => $recentCommissions,
            'profit_shares' => $recentProfitShares,
            'combined' => $recentCommissions->concat($recentProfitShares)
                ->sortByDesc('date')
                ->take(15)
                ->values()
        ];
    }

    private function getUserNotifications($user, $limit): array
    {
        // This would integrate with Laravel's notification system
        // For now, we'll create notifications based on user activities
        $notifications = [];
        
        // Check for tier upgrade opportunities
        $tierEligibility = $user->checkTierUpgradeEligibility();
        if ($tierEligibility['eligible']) {
            $notifications[] = [
                'type' => 'tier_upgrade_available',
                'title' => 'Tier Upgrade Available',
                'message' => "You're eligible to upgrade to {$tierEligibility['next_tier']->name} tier!",
                'action_url' => route('investments.tier-upgrade'),
                'created_at' => now(),
                'priority' => 'high'
            ];
        }
        
        // Check for withdrawal eligibility
        $withdrawalEligible = $user->investments()
            ->where('status', 'active')
            ->get()
            ->filter(function ($investment) {
                return !$investment->isWithinLockInPeriod();
            });
            
        if ($withdrawalEligible->count() > 0) {
            $notifications[] = [
                'type' => 'withdrawal_available',
                'title' => 'Withdrawals Available',
                'message' => "You have {$withdrawalEligible->count()} investment(s) eligible for withdrawal",
                'action_url' => route('withdrawals.index'),
                'created_at' => now(),
                'priority' => 'medium'
            ];
        }
        
        // Check for pending commissions
        $pendingCommissions = $user->referralCommissions()
            ->where('status', 'pending')
            ->sum('amount');
            
        if ($pendingCommissions > 0) {
            $notifications[] = [
                'type' => 'pending_commissions',
                'title' => 'Pending Commissions',
                'message' => "You have K{$pendingCommissions} in pending referral commissions",
                'action_url' => route('referrals.commissions'),
                'created_at' => now(),
                'priority' => 'low'
            ];
        }

        return array_slice($notifications, 0, $limit);
    }

    private function getUserActivities($user, $limit): array
    {
        return ActivityLog::where('user_id', $user->id)
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'description' => $activity->description,
                    'created_at' => $activity->created_at,
                    'loggable_type' => $activity->loggable_type,
                    'loggable_id' => $activity->loggable_id,
                    'properties' => $activity->properties
                ];
            })
            ->toArray();
    }

    private function getCombinedActivityFeed($user, $limit): array
    {
        $activities = $this->getUserActivities($user, $limit);
        $notifications = $this->getUserNotifications($user, $limit);
        
        $combined = collect($activities)->concat($notifications)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
            
        return $combined->toArray();
    }

    private function getPortfolioOverview($user): array
    {
        $activeInvestments = $user->investments()->where('status', 'active')->with('tier')->get();
        $totalInvested = $activeInvestments->sum('amount');
        $currentValue = $activeInvestments->sum(function ($inv) { return $inv->getCurrentValue(); });
        
        return [
            'total_invested' => $totalInvested,
            'current_value' => $currentValue,
            'total_profit' => $currentValue - $totalInvested,
            'roi_percentage' => $totalInvested > 0 ? (($currentValue - $totalInvested) / $totalInvested) * 100 : 0,
            'investment_count' => $activeInvestments->count(),
            'tier_distribution' => $activeInvestments->groupBy('tier.name')->map->count(),
            'average_investment' => $activeInvestments->count() > 0 ? $totalInvested / $activeInvestments->count() : 0
        ];
    }

    private function getInvestmentPerformanceMetrics($user): array
    {
        $investments = $user->investments()->where('status', 'active')->with('tier')->get();
        
        return [
            'total_count' => $investments->count(),
            'performance_summary' => $investments->map(function ($investment) {
                return $investment->getInvestmentPerformanceMetrics();
            }),
            'tier_performance' => $this->getPerformanceByTier($investments),
            'growth_trends' => $this->calculateInvestmentGrowthTrends($investments)
        ];
    }

    private function getReferralMetrics($user): array
    {
        return [
            'statistics' => $this->referralRepository->getReferralStatistics($user),
            'matrix_performance' => $this->referralMatrixService->getMatrixPerformanceMetrics($user),
            'commission_breakdown' => $this->getCommissionBreakdown($user),
            'growth_metrics' => $this->calculateReferralGrowthMetrics($user)
        ];
    }

    private function getTierInformation($user): array
    {
        $eligibility = $user->checkTierUpgradeEligibility();
        $currentTier = $user->getCurrentInvestmentTier();
        
        return [
            'current_tier' => $currentTier,
            'upgrade_eligibility' => $eligibility,
            'progress_percentage' => $user->getTierProgressPercentage(),
            'tier_benefits' => $currentTier?->getTierSpecificBenefits(),
            'upgrade_benefits' => $currentTier?->getUpgradeBenefits()
        ];
    }

    private function getMatrixPerformance($user): array
    {
        return [
            'position' => $user->getMatrixPosition(),
            'structure' => $user->buildMatrixStructure(3),
            'downline_counts' => $user->getMatrixDownlineCount(),
            'performance_metrics' => $this->referralMatrixService->getMatrixPerformanceMetrics($user),
            'spillover_opportunities' => $this->identifySpilloverOpportunities($user)
        ];
    }

    private function getWithdrawalSummary($user): array
    {
        $activeInvestments = $user->investments()->where('status', 'active')->get();
        $eligibleForWithdrawal = $activeInvestments->filter(function ($investment) {
            return !$investment->isWithinLockInPeriod();
        });
        
        $totalWithdrawable = $eligibleForWithdrawal->sum(function ($investment) {
            return $investment->getWithdrawableAmount('full');
        });
        
        return [
            'total_investments' => $activeInvestments->count(),
            'eligible_for_withdrawal' => $eligibleForWithdrawal->count(),
            'total_withdrawable_amount' => $totalWithdrawable,
            'pending_withdrawals' => WithdrawalRequest::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'pending_approval'])
                ->count(),
            'withdrawal_restrictions' => $activeInvestments->filter(function ($investment) {
                return $investment->isWithinLockInPeriod();
            })->count()
        ];
    }

    private function getGrowthTrends($user): array
    {
        $monthlyData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $monthlyInvestments = $user->investments()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');
                
            $monthlyCommissions = $user->referralCommissions()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('status', 'paid')
                ->sum('amount');
                
            $monthlyData[] = [
                'month' => $month->format('Y-m'),
                'investments' => $monthlyInvestments,
                'commissions' => $monthlyCommissions,
                'total_growth' => $monthlyInvestments + $monthlyCommissions
            ];
        }
        
        return [
            'monthly_data' => $monthlyData,
            'total_growth_rate' => $this->calculateTotalGrowthRate($monthlyData),
            'investment_growth_rate' => $this->calculateInvestmentGrowthRate($monthlyData),
            'commission_growth_rate' => $this->calculateCommissionGrowthRate($monthlyData)
        ];
    }

    // Additional helper methods would continue here...
    private function getPerformanceByTier($investments): array
    {
        return $investments->groupBy('tier.name')->map(function ($tierInvestments, $tierName) {
            $totalInvested = $tierInvestments->sum('amount');
            $totalCurrentValue = $tierInvestments->sum(function ($inv) { return $inv->getCurrentValue(); });
            
            return [
                'tier' => $tierName,
                'count' => $tierInvestments->count(),
                'total_invested' => $totalInvested,
                'current_value' => $totalCurrentValue,
                'profit' => $totalCurrentValue - $totalInvested,
                'roi_percentage' => $totalInvested > 0 ? (($totalCurrentValue - $totalInvested) / $totalInvested) * 100 : 0
            ];
        })->values()->toArray();
    }

    private function calculatePortfolioGrowthRate($investments): float
    {
        if ($investments->isEmpty()) return 0;
        
        $oldestInvestment = $investments->sortBy('created_at')->first();
        $daysSinceOldest = $oldestInvestment->created_at->diffInDays(now());
        
        if ($daysSinceOldest <= 0) return 0;
        
        $totalInvested = $investments->sum('amount');
        $currentValue = $investments->sum(function ($inv) { return $inv->getCurrentValue(); });
        
        $totalReturn = (($currentValue - $totalInvested) / $totalInvested) * 100;
        
        // Annualized growth rate
        return $totalReturn * (365 / $daysSinceOldest);
    }

    /**
     * Get employee-specific data for user dashboard
     */
    private function getEmployeeData($user): ?array
    {
        $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;
        $employee = $employeeModel::where('user_id', $user->id)->with(['department', 'position', 'manager'])->first();
        
        if (!$employee) {
            return null;
        }

        // Get performance data
        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;
        $latestPerformance = $performanceModel::where('employee_id', $employee->id)
            ->latest('period_end')
            ->first();

        // Get commission data
        $commissionModel = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::class;
        $commissionStats = [
            'total_earned' => $commissionModel::where('employee_id', $employee->id)
                ->where('status', 'paid')
                ->sum('commission_amount'),
            'pending_amount' => $commissionModel::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->sum('commission_amount'),
            'ytd_earnings' => $commissionModel::where('employee_id', $employee->id)
                ->where('status', 'paid')
                ->whereYear('calculation_date', now()->year)
                ->sum('commission_amount'),
            'this_month' => $commissionModel::where('employee_id', $employee->id)
                ->where('status', 'paid')
                ->whereMonth('calculation_date', now()->month)
                ->sum('commission_amount'),
        ];

        // Get recent activities
        $recentActivities = $this->getEmployeeRecentActivities($employee);

        // Get goals (placeholder - would need goals table)
        $goals = $this->getEmployeeGoals($employee);

        // Get upcoming events
        $upcomingEvents = $this->getEmployeeUpcomingEvents($employee);

        return [
            'employee' => [
                'id' => $employee->id,
                'employeeNumber' => $employee->employee_number ?? $employee->employee_id,
                'firstName' => $employee->first_name,
                'lastName' => $employee->last_name,
                'fullName' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $employee->email,
                'employmentStatus' => $employee->employment_status,
                'hireDate' => $employee->hire_date?->toISOString(),
                'yearsOfService' => $employee->hire_date ? now()->diffInYears($employee->hire_date) : 0,
                'department' => $employee->department ? [
                    'id' => $employee->department->id,
                    'name' => $employee->department->name,
                ] : null,
                'position' => $employee->position ? [
                    'id' => $employee->position->id,
                    'title' => $employee->position->title,
                ] : null,
                'manager' => $employee->manager ? [
                    'id' => $employee->manager->id,
                    'fullName' => $employee->manager->first_name . ' ' . $employee->manager->last_name,
                ] : null,
            ],
            'performance' => [
                'currentScore' => $latestPerformance?->overall_score ?? 0,
                'lastReviewDate' => $latestPerformance?->period_end?->toISOString(),
                'goalsCompleted' => $this->getCompletedGoalsCount($employee),
                'reviewsDue' => $this->getReviewsDueCount($employee),
            ],
            'commissions' => $commissionStats,
            'recentActivities' => $recentActivities,
            'goals' => $goals,
            'upcomingEvents' => $upcomingEvents,
            'quickActions' => $this->getEmployeeQuickActions(),
        ];
    }

    private function getEmployeeRecentActivities($employee): array
    {
        $activities = [];
        
        // Recent commissions
        $commissionModel = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::class;
        $recentCommissions = $commissionModel::where('employee_id', $employee->id)
            ->latest('calculation_date')
            ->take(3)
            ->get();

        foreach ($recentCommissions as $commission) {
            $activities[] = [
                'id' => 'commission_' . $commission->id,
                'title' => 'Commission Earned',
                'description' => 'K' . number_format($commission->commission_amount, 2) . ' commission from ' . $commission->commission_type,
                'type' => 'commission',
                'createdAt' => $commission->calculation_date->toISOString(),
            ];
        }

        // Recent performance reviews
        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;
        $recentReviews = $performanceModel::where('employee_id', $employee->id)
            ->latest('created_at')
            ->take(2)
            ->get();

        foreach ($recentReviews as $review) {
            $activities[] = [
                'id' => 'review_' . $review->id,
                'title' => 'Performance Review',
                'description' => 'Performance review completed with score: ' . $review->overall_score . '/10',
                'type' => 'performance',
                'createdAt' => $review->created_at->toISOString(),
            ];
        }

        // Sort by date and return latest 5
        usort($activities, function ($a, $b) {
            return strtotime($b['createdAt']) - strtotime($a['createdAt']);
        });

        return array_slice($activities, 0, 5);
    }

    private function getEmployeeGoals($employee): array
    {
        // Placeholder implementation - would need goals table
        return [
            [
                'id' => 1,
                'title' => 'Complete Q1 Performance Review',
                'description' => 'Submit self-assessment and meet with manager',
                'status' => 'in-progress',
                'progress' => 75,
                'dueDate' => now()->addDays(15)->toISOString(),
            ],
            [
                'id' => 2,
                'title' => 'Client Portfolio Growth',
                'description' => 'Increase client portfolio by 20%',
                'status' => 'in-progress',
                'progress' => 45,
                'dueDate' => now()->addMonths(2)->toISOString(),
            ],
        ];
    }

    private function getEmployeeUpcomingEvents($employee): array
    {
        $events = [];
        
        // Birthday reminder
        if ($employee->date_of_birth) {
            $birthday = $employee->date_of_birth->setYear(now()->year);
            if ($birthday->isFuture() && $birthday->diffInDays(now()) <= 30) {
                $events[] = [
                    'id' => 'birthday_' . $employee->id,
                    'title' => 'Your Birthday',
                    'date' => $birthday->toISOString(),
                ];
            }
        }

        // Work anniversary
        if ($employee->hire_date) {
            $anniversary = $employee->hire_date->setYear(now()->year);
            if ($anniversary->isFuture() && $anniversary->diffInDays(now()) <= 30) {
                $years = now()->year - $employee->hire_date->year;
                $events[] = [
                    'id' => 'anniversary_' . $employee->id,
                    'title' => "Work Anniversary - {$years} Years",
                    'date' => $anniversary->toISOString(),
                ];
            }
        }

        // Performance review due
        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;
        $lastReview = $performanceModel::where('employee_id', $employee->id)
            ->latest('period_end')
            ->first();

        if (!$lastReview || $lastReview->period_end->addMonths(6)->isPast()) {
            $events[] = [
                'id' => 'review_due_' . $employee->id,
                'title' => 'Performance Review Due',
                'date' => now()->addDays(7)->toISOString(),
            ];
        }

        return $events;
    }

    private function getCompletedGoalsCount($employee): int
    {
        // Placeholder - would query goals table
        return 3;
    }

    private function getReviewsDueCount($employee): int
    {
        // Check if performance review is due (every 6 months)
        $performanceModel = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::class;
        $lastReview = $performanceModel::where('employee_id', $employee->id)
            ->latest('period_end')
            ->first();

        if (!$lastReview || $lastReview->period_end->addMonths(6)->isPast()) {
            return 1;
        }

        return 0;
    }

    private function getEmployeeQuickActions(): array
    {
        return [
            [
                'title' => 'Request Time Off',
                'description' => 'Submit a time-off request',
                'icon' => 'calendar',
                'url' => route('employee.time-off.request'),
            ],
            [
                'title' => 'Submit Expense',
                'description' => 'Submit an expense report',
                'icon' => 'currency-dollar',
                'url' => route('employee.expenses.create'),
            ],
            [
                'title' => 'View Documents',
                'description' => 'Access your documents',
                'icon' => 'document',
                'url' => route('employee.documents'),
            ],
            [
                'title' => 'Give Feedback',
                'description' => 'Provide feedback or suggestions',
                'icon' => 'chat-bubble-left',
                'url' => route('employee.feedback.create'),
            ],
        ];
    }

    private function identifySpilloverOpportunities($user): array
    {
        $position = $this->referralMatrixService->findNextAvailablePosition($user);
        
        return [
            'next_available_position' => $position,
            'spillover_potential' => $this->referralMatrixService->calculateSpilloverPotential($user),
            'recommended_actions' => $this->referralMatrixService->getSpilloverRecommendations($user)
        ];
    }



    /**
     * Get employee performance summary
     */
    private function getEmployeePerformanceSummary($employee): array
    {
        $latestPerformance = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::where('employee_id', $employee->id)
            ->latest('evaluation_period_end')
            ->first();

        if (!$latestPerformance) {
            return [
                'overallScore' => 0,
                'goalsAchieved' => 0,
                'clientRetentionRate' => 0,
                'newClientsAcquired' => 0,
                'revenueGenerated' => 0
            ];
        }

        return [
            'overallScore' => $latestPerformance->overall_score ?? 0,
            'goalsAchieved' => $latestPerformance->goal_achievement_rate ?? 0,
            'clientRetentionRate' => $latestPerformance->client_retention_rate ?? 0,
            'newClientsAcquired' => $latestPerformance->new_client_acquisitions ?? 0,
            'revenueGenerated' => $latestPerformance->commission_generated ?? 0
        ];
    }

    /**
     * Get employee client portfolio summary
     */
    private function getEmployeeClientPortfolioSummary($employee): array
    {
        $clientAssignments = \App\Infrastructure\Persistence\Eloquent\EmployeeClientAssignmentModel::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->with('user')
            ->get();

        $totalClients = $clientAssignments->count();
        $activeInvestments = 0;
        $totalValue = 0;

        foreach ($clientAssignments as $assignment) {
            if ($assignment->user) {
                $userInvestments = $assignment->user->investments()->where('status', 'active')->get();
                $activeInvestments += $userInvestments->count();
                $totalValue += $userInvestments->sum('amount');
            }
        }

        // Get commission summary for current month
        $currentMonth = now()->startOfMonth();
        $commissions = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::where('employee_id', $employee->id)
            ->where('calculation_date', '>=', $currentMonth)
            ->get();

        return [
            'totalClients' => $totalClients,
            'activeInvestments' => $activeInvestments,
            'totalValue' => $totalValue,
            'commissionSummary' => [
                'earned' => $commissions->where('status', 'paid')->sum('commission_amount'),
                'pending' => $commissions->where('status', 'pending')->sum('commission_amount')
            ]
        ];
    }



    private function calculateMatrixCommissionPotential($user): array
    {
        $tier = $user->getCurrentInvestmentTier();
        if (!$tier) return [];
        
        $structure = $tier->getMatrixCommissionStructure();
        $potential = [];
        
        foreach ($structure as $level => $config) {
            $potential[$level] = [
                'max_positions' => $config['positions'],
                'current_filled' => $user->getMatrixDownlineCount()["level_" . substr($level, -1)] ?? 0,
                'commission_rate' => $config['effective_rate'],
                'potential_per_k1000' => ($config['effective_rate'] / 100) * 1000
            ];
        }
        
        return $potential;
    }

    private function getCommissionBreakdown($user): array
    {
        $commissions = $user->referralCommissions()->where('status', 'paid')->get();
        
        return [
            'by_level' => $commissions->groupBy('level')->map(function ($levelCommissions) {
                return [
                    'count' => $levelCommissions->count(),
                    'total_amount' => $levelCommissions->sum('amount'),
                    'average_amount' => $levelCommissions->avg('amount')
                ];
            }),
            'total_earned' => $commissions->sum('amount'),
            'average_commission' => $commissions->avg('amount'),
            'highest_commission' => $commissions->max('amount'),
            'recent_trend' => $this->getRecentCommissionTrend($user)
        ];
    }

    private function calculateReferralGrowthMetrics($user): array
    {
        $referrals = $user->directReferrals()->get();
        $monthlyGrowth = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $monthlyReferrals = $referrals->whereBetween('created_at', $monthStart, $monthEnd)->count();
            $monthlyGrowth[] = [
                'month' => $month->format('Y-m'),
                'new_referrals' => $monthlyReferrals
            ];
        }
        
        return [
            'monthly_growth' => $monthlyGrowth,
            'total_referrals' => $referrals->count(),
            'active_referrals' => $referrals->filter(function ($referral) {
                return $referral->investments()->where('status', 'active')->exists();
            })->count(),
            'growth_rate' => $this->calculateReferralGrowthRate($monthlyGrowth)
        ];
    }

    private function calculateInvestmentGrowthTrends($investments): array
    {
        return $investments->map(function ($investment) {
            $projections = $investment->projectFutureValue(12);
            return [
                'investment_id' => $investment->id,
                'current_trend' => $investment->getGrowthRate(),
                'projections' => $projections
            ];
        })->toArray();
    }

    private function getTierComparisonData($user): array
    {
        $currentTier = $user->getCurrentInvestmentTier();
        if (!$currentTier) return [];
        
        $nextTier = $currentTier->getNextTier();
        if (!$nextTier) return [];
        
        return $currentTier->compareWith($nextTier);
    }

    private function calculateUpgradeBenefits($user): array
    {
        $currentTier = $user->getCurrentInvestmentTier();
        if (!$currentTier) return [];
        
        return $currentTier->getUpgradeBenefits();
    }

    private function getInvestmentSpecificTrends($investment, $period): array
    {
        return [
            'investment_id' => $investment->id,
            'performance_metrics' => $investment->getInvestmentPerformanceMetrics(),
            'projections' => $investment->projectFutureValue(12),
            'historical_performance' => $this->getHistoricalPerformance($investment, $period)
        ];
    }

    private function getPortfolioTrends($user, $period): array
    {
        $investments = $user->investments()->where('status', 'active')->get();
        
        return [
            'portfolio_performance' => $this->calculateRealTimeInvestmentPerformance($user),
            'trend_analysis' => $this->analyzeTrends($investments, $period),
            'growth_projections' => $this->projectPortfolioGrowth($investments)
        ];
    }

    private function getHistoricalPerformance($investment, $period): array
    {
        // This would calculate historical performance based on the period
        // For now, return basic metrics
        return [
            'period' => $period,
            'start_value' => $investment->amount,
            'current_value' => $investment->getCurrentValue(),
            'growth_rate' => $investment->getGrowthRate()
        ];
    }

    private function analyzeTrends($investments, $period): array
    {
        return [
            'period' => $period,
            'total_investments' => $investments->count(),
            'average_performance' => $investments->avg(function ($inv) {
                return $inv->getRoiAttribute();
            }),
            'best_performer' => $investments->sortByDesc(function ($inv) {
                return $inv->getRoiAttribute();
            })->first(),
            'trend_direction' => 'positive' // This would be calculated based on actual data
        ];
    }

    private function projectPortfolioGrowth($investments): array
    {
        $projections = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $totalProjectedValue = $investments->sum(function ($investment) use ($month) {
                $projections = $investment->projectFutureValue($month);
                return $projections[$month - 1]['projected_value'] ?? $investment->getCurrentValue();
            });
            
            $projections[] = [
                'month' => $month,
                'projected_portfolio_value' => $totalProjectedValue
            ];
        }
        
        return $projections;
    }

    private function calculateTotalGrowthRate($monthlyData): float
    {
        if (count($monthlyData) < 2) return 0;
        
        $firstMonth = $monthlyData[0]['total_growth'];
        $lastMonth = end($monthlyData)['total_growth'];
        
        if ($firstMonth <= 0) return 0;
        
        return (($lastMonth - $firstMonth) / $firstMonth) * 100;
    }

    private function calculateInvestmentGrowthRate($monthlyData): float
    {
        if (count($monthlyData) < 2) return 0;
        
        $firstMonth = $monthlyData[0]['investments'];
        $lastMonth = end($monthlyData)['investments'];
        
        if ($firstMonth <= 0) return 0;
        
        return (($lastMonth - $firstMonth) / $firstMonth) * 100;
    }

    private function calculateCommissionGrowthRate($monthlyData): float
    {
        if (count($monthlyData) < 2) return 0;
        
        $firstMonth = $monthlyData[0]['commissions'];
        $lastMonth = end($monthlyData)['commissions'];
        
        if ($firstMonth <= 0) return 0;
        
        return (($lastMonth - $firstMonth) / $firstMonth) * 100;
    }

    private function getRecentCommissionTrend($user): array
    {
        $recentCommissions = $user->referralCommissions()
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at')
            ->get();
            
        return [
            'last_30_days' => $recentCommissions->sum('amount'),
            'count' => $recentCommissions->count(),
            'average' => $recentCommissions->avg('amount'),
            'trend' => $recentCommissions->count() > 0 ? 'increasing' : 'stable'
        ];
    }

    private function calculateReferralGrowthRate($monthlyGrowth): float
    {
        if (count($monthlyGrowth) < 2) return 0;
        
        $firstMonth = $monthlyGrowth[0]['new_referrals'];
        $lastMonth = end($monthlyGrowth)['new_referrals'];
        
        if ($firstMonth <= 0) return 0;
        
        return (($lastMonth - $firstMonth) / $firstMonth) * 100;
    }

    /**
     * Display reports dashboard
     */
    public function reports()
    {
        $user = auth()->user();
        
        $reportData = [
            'investmentSummary' => $this->getInvestmentReportSummary($user),
            'earningsSummary' => $this->getEarningsReportSummary($user),
            'referralSummary' => $this->getReferralReportSummary($user),
            'withdrawalSummary' => $this->getWithdrawalReportSummary($user),
            'availableReports' => $this->getAvailableReports()
        ];

        return Inertia::render('Reports/Index', $reportData);
    }

    /**
     * Display investment reports
     */
    public function investmentReports(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'year');
        
        $reportData = [
            'investment_performance' => $this->getInvestmentPerformanceReport($user, $period),
            'tier_analysis' => $this->getTierAnalysisReport($user),
            'growth_trends' => $this->getInvestmentGrowthReport($user, $period),
            'roi_analysis' => $this->getROIAnalysisReport($user)
        ];

        return Inertia::render('Reports/Investments', $reportData);
    }

    /**
     * Display transaction reports
     */
    public function transactionReports(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'year');
        
        $reportData = [
            'transaction_summary' => $this->getTransactionSummaryReport($user, $period),
            'withdrawal_history' => $this->getWithdrawalHistoryReport($user),
            'commission_history' => $this->getCommissionHistoryReport($user),
            'profit_distribution_history' => $this->getProfitDistributionReport($user)
        ];

        return Inertia::render('Reports/Transactions', $reportData);
    }

    /**
     * Display referral reports
     */
    public function referralReports(Request $request)
    {
        $user = auth()->user();
        
        $reportData = [
            'referral_performance' => $this->getReferralPerformanceReport($user),
            'matrix_analysis' => $this->getMatrixAnalysisReport($user),
            'commission_breakdown' => $this->getCommissionBreakdownReport($user),
            'growth_analysis' => $this->getReferralGrowthAnalysisReport($user)
        ];

        return Inertia::render('Reports/Referrals', $reportData);
    }

    // Report helper methods
    private function getInvestmentReportSummary($user): array
    {
        $investments = $user->investments()->where('status', 'active')->get();
        
        // Safe ROI accessor
        $roiOf = function ($inv) {
            if (method_exists($inv, 'getRoiAttribute')) {
                return (float) $inv->getRoiAttribute();
            }
            // Try property or default to 0
            return isset($inv->roi) ? (float) $inv->roi : 0.0;
        };

        $totalInvested = $investments->sum('amount');
        $currentValue = $investments->sum(function ($inv) { return $inv->getCurrentValue(); });

        $averageRoi = $investments->count() > 0
            ? $investments->avg(function ($inv) use ($roiOf) { return $roiOf($inv); })
            : 0.0;

        $best = $investments->sortByDesc(function ($inv) use ($roiOf) { return $roiOf($inv); })->first();
        $bestSummary = $best ? [
            'id' => $best->id,
            'amount' => $best->amount,
            'current_value' => $best->getCurrentValue(),
            'roi' => $roiOf($best),
            'tier' => $best->tier?->name,
        ] : null;

        $tierDistribution = $investments
            ->groupBy(function ($inv) { return $inv->tier?->name ?? 'Unknown'; })
            ->map->count();

        return [
            'total_investments' => $investments->count(),
            'total_invested' => $totalInvested,
            'current_value' => $currentValue,
            'average_roi' => (float) $averageRoi,
            'best_performing' => $bestSummary,
            'tier_distribution' => $tierDistribution,
        ];
    }

    private function getEarningsReportSummary($user): array
    {
        $earnings = $user->calculateTotalEarningsDetailed();
        
        return [
            'total_earnings' => $earnings['total_earnings'],
            'referral_earnings' => $earnings['referral_commissions'],
            'profit_earnings' => $earnings['profit_shares'],
            'matrix_earnings' => $earnings['matrix_commissions'],
            'pending_earnings' => $earnings['pending_earnings'],
            'monthly_average' => $earnings['total_earnings'] / 12,
            'growth_rate' => $this->calculateEarningsGrowthRate($user)
        ];
    }

    private function getReferralReportSummary($user): array
    {
        $referralStats = $user->getReferralStats();
        
        return [
            'total_referrals' => $referralStats['total_referrals'],
            'active_referrals' => $referralStats['active_referrals'],
            'total_commission' => $referralStats['total_commission'],
            'pending_commission' => $referralStats['pending_commission'],
            'matrix_position' => $user->getMatrixPosition(),
            'downline_counts' => $user->getMatrixDownlineCount()
        ];
    }

    private function getWithdrawalReportSummary($user): array
    {
        $withdrawals = $user->withdrawalRequests()->get();
        
        return [
            'total_withdrawals' => $withdrawals->count(),
            'approved_withdrawals' => $withdrawals->where('status', 'approved')->count(),
            'pending_withdrawals' => $withdrawals->where('status', 'pending')->count(),
            'total_withdrawn' => $withdrawals->where('status', 'approved')->sum('amount'),
            'total_penalties' => $withdrawals->sum('penalty_amount'),
            'average_withdrawal' => $withdrawals->where('status', 'approved')->avg('amount')
        ];
    }

    private function getAvailableReports(): array
    {
        return [
            'investment_performance' => 'Investment Performance Analysis',
            'earnings_breakdown' => 'Earnings and Commission Breakdown',
            'referral_analysis' => 'Referral Network Analysis',
            'withdrawal_history' => 'Withdrawal History and Penalties',
            'tier_progression' => 'Investment Tier Progression',
            'matrix_performance' => 'Matrix Position Performance'
        ];
    }

    private function getInvestmentPerformanceReport($user, $period): array
    {
        $investments = $user->investments()->where('status', 'active')->get();
        
        return [
            'period' => $period,
            'performance_metrics' => $investments->map(function ($investment) {
                return $investment->getInvestmentPerformanceMetrics();
            }),
            'tier_performance' => $this->getPerformanceByTier($investments),
            'growth_projections' => $investments->map(function ($investment) {
                return $investment->projectFutureValue(12);
            })
        ];
    }

    private function getTierAnalysisReport($user): array
    {
        $currentTier = $user->getCurrentInvestmentTier();
        $tierHistory = $user->getTierHistory();
        
        return [
            'current_tier' => $currentTier,
            'tier_history' => $tierHistory,
            'upgrade_eligibility' => $user->checkTierUpgradeEligibility(),
            'tier_benefits_analysis' => $currentTier?->getTierSpecificBenefits(),
            'upgrade_recommendations' => $currentTier?->getUpgradeBenefits()
        ];
    }

    private function getInvestmentGrowthReport($user, $period): array
    {
        return $this->getGrowthTrends($user);
    }

    private function getROIAnalysisReport($user): array
    {
        $investments = $user->investments()->where('status', 'active')->get();
        
        return [
            'average_roi' => $investments->avg(function ($inv) { return $inv->getRoiAttribute(); }),
            'roi_by_tier' => $this->getPerformanceByTier($investments),
            'roi_trends' => $this->calculateROITrends($investments),
            'projected_returns' => $this->calculateProjectedReturns($investments)
        ];
    }

    private function getTransactionSummaryReport($user, $period): array
    {
        $transactions = $user->transactions()->get();
        
        return [
            'period' => $period,
            'total_transactions' => $transactions->count(),
            'transaction_types' => $transactions->groupBy('transaction_type')->map->count(),
            'transaction_amounts' => $transactions->groupBy('transaction_type')->map(function ($group) {
                return $group->sum('amount');
            }),
            'monthly_breakdown' => $this->getMonthlyTransactionBreakdown($transactions)
        ];
    }

    private function getWithdrawalHistoryReport($user): array
    {
        $withdrawals = $user->withdrawalRequests()->with('investment')->get();
        
        return [
            'withdrawal_history' => $withdrawals->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'type' => $withdrawal->type,
                    'status' => $withdrawal->status,
                    'penalty_amount' => $withdrawal->penalty_amount,
                    'net_amount' => $withdrawal->net_amount,
                    'created_at' => $withdrawal->created_at,
                    'investment' => $withdrawal->investment
                ];
            }),
            'withdrawal_statistics' => $this->getWithdrawalReportSummary($user)
        ];
    }

    private function getCommissionHistoryReport($user): array
    {
        $commissions = $user->referralCommissions()->with(['referee', 'investment'])->get();
        
        return [
            'commission_history' => $commissions->map(function ($commission) {
                return [
                    'id' => $commission->id,
                    'amount' => $commission->amount,
                    'level' => $commission->level,
                    'status' => $commission->status,
                    'referee' => $commission->referee?->name,
                    'investment_amount' => $commission->investment?->amount,
                    'created_at' => $commission->created_at
                ];
            }),
            'commission_statistics' => $this->getCommissionBreakdown($user)
        ];
    }

    private function getProfitDistributionReport($user): array
    {
        $profitShares = $user->profitShares()->with('investment')->get();
        
        return [
            'profit_distributions' => $profitShares->map(function ($share) {
                return [
                    'id' => $share->id,
                    'amount' => $share->amount,
                    'period_type' => $share->period_type,
                    'status' => $share->status,
                    'investment' => $share->investment,
                    'created_at' => $share->created_at
                ];
            }),
            'distribution_summary' => [
                'total_received' => $profitShares->where('status', 'paid')->sum('amount'),
                'pending_distributions' => $profitShares->where('status', 'pending')->sum('amount'),
                'average_distribution' => $profitShares->where('status', 'paid')->avg('amount')
            ]
        ];
    }

    private function getReferralPerformanceReport($user): array
    {
        return [
            'referral_statistics' => $this->referralRepository->getReferralStatistics($user),
            'performance_metrics' => $this->referralMatrixService->getMatrixPerformanceMetrics($user),
            'growth_analysis' => $this->calculateReferralGrowthMetrics($user)
        ];
    }

    private function getMatrixAnalysisReport($user): array
    {
        return [
            'matrix_structure' => $user->buildMatrixStructure(3),
            'position_analysis' => $user->getMatrixPosition(),
            'downline_performance' => $user->getMatrixDownlineCount(),
            'spillover_analysis' => $this->identifySpilloverOpportunities($user),
            'commission_potential' => $this->calculateMatrixCommissionPotential($user)
        ];
    }

    private function getCommissionBreakdownReport($user): array
    {
        return $this->getCommissionBreakdown($user);
    }

    private function getReferralGrowthAnalysisReport($user): array
    {
        return $this->calculateReferralGrowthMetrics($user);
    }

    // Additional helper methods for reports
    private function calculateEarningsGrowthRate($user): float
    {
        $monthlyEarnings = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $monthlyCommissions = $user->referralCommissions()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('status', 'paid')
                ->sum('amount');
                
            $monthlyProfits = $user->profitShares()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('status', 'paid')
                ->sum('amount');
                
            $monthlyEarnings[] = $monthlyCommissions + $monthlyProfits;
        }
        
        if (count($monthlyEarnings) < 2) return 0;
        
        $firstMonth = $monthlyEarnings[0];
        $lastMonth = end($monthlyEarnings);
        
        if ($firstMonth <= 0) return 0;
        
        return (($lastMonth - $firstMonth) / $firstMonth) * 100;
    }

    private function calculateROITrends($investments): array
    {
        return $investments->map(function ($investment) {
            return [
                'investment_id' => $investment->id,
                'current_roi' => $investment->getRoiAttribute(),
                'growth_rate' => $investment->getGrowthRate(),
                'tier' => $investment->tier?->name,
                'investment_age' => $investment->created_at->diffInDays(now())
            ];
        })->toArray();
    }

    private function calculateProjectedReturns($investments): array
    {
        return $investments->map(function ($investment) {
            return [
                'investment_id' => $investment->id,
                'projections' => $investment->projectFutureValue(12),
                'tier' => $investment->tier?->name
            ];
        })->toArray();
    }

    private function getMonthlyTransactionBreakdown($transactions): array
    {
        $monthlyData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $monthTransactions = $transactions->whereBetween('created_at', $monthStart, $monthEnd);
            
            $monthlyData[] = [
                'month' => $month->format('Y-m'),
                'count' => $monthTransactions->count(),
                'total_amount' => $monthTransactions->sum('amount'),
                'by_type' => $monthTransactions->groupBy('transaction_type')->map->count()
            ];
        }
        
        return $monthlyData;
    }
}
