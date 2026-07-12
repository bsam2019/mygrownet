<?php

namespace App\Http\Controllers\Manager;

use Inertia\Inertia;
use App\Models\Investment;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\ReferralCommission;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display manager dashboard with team oversight capabilities
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Simple manager access check
        if (!$this->isManager($user)) {
            abort(403, 'Access denied. Manager privileges required.');
        }
        $period = $request->get('period', 'month');

        // Manager-specific metrics
        $teamMetrics = $this->getTeamMetrics($user);
        $performanceData = $this->getPerformanceData($period);
        $pendingApprovals = $this->getPendingApprovals();
        $teamActivities = $this->getTeamActivities();

        return Inertia::render('Manager/Dashboard/Index', [
            'teamMetrics' => $teamMetrics,
            'performanceData' => $performanceData,
            'pendingApprovals' => $pendingApprovals,
            'teamActivities' => $teamActivities,
            'managedUsers' => $this->getManagedUsers($user),
            'recentInvestments' => $this->getRecentTeamInvestments($user),
            'commissionOverview' => $this->getCommissionOverview($user),
        ]);
    }

    /**
     * Get metrics for users under this manager's oversight
     */
    private function getTeamMetrics($manager): array
    {
        // Assuming managers oversee users in their referral network or assigned region
        $teamUsers = $this->getTeamUsers($manager);
        $teamUserIds = $teamUsers->pluck('id');

        $totalInvestments = Investment::whereIn('user_id', $teamUserIds)
            ->where('status', 'active')
            ->sum('amount');

        $monthlyGrowth = Investment::whereIn('user_id', $teamUserIds)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $activeInvestors = Investment::whereIn('user_id', $teamUserIds)
            ->where('status', 'active')
            ->distinct('user_id')
            ->count();

        $totalCommissions = ReferralCommission::whereIn('referrer_id', $teamUserIds)
            ->where('status', 'paid')
            ->sum('amount');

        return [
            'team_size' => $teamUsers->count(),
            'active_investors' => $activeInvestors,
            'total_investments' => $totalInvestments,
            'monthly_growth' => $monthlyGrowth,
            'total_commissions' => $totalCommissions,
            'average_investment' => $activeInvestors > 0 ? $totalInvestments / $activeInvestors : 0,
            'team_performance_score' => $this->calculateTeamPerformanceScore($teamUsers),
        ];
    }

    /**
     * Get performance data for the specified period
     */
    private function getPerformanceData($period): array
    {
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };

        $manager = auth()->user();
        $teamUserIds = $this->getTeamUsers($manager)->pluck('id');

        $investments = Investment::whereIn('user_id', $teamUserIds)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('date')
            ->get();

        return [
            'labels' => $investments->pluck('date'),
            'investments' => $investments->pluck('total'),
            'counts' => $investments->pluck('count'),
            'growth_rate' => $this->calculateGrowthRate($investments),
        ];
    }

    /**
     * Get pending approvals that require manager attention
     */
    private function getPendingApprovals(): array
    {
        $manager = auth()->user();
        $teamUserIds = $this->getTeamUsers($manager)->pluck('id');

        return [
            'withdrawals' => WithdrawalRequest::whereIn('user_id', $teamUserIds)
                ->where('status', 'pending')
                ->with('user')
                ->latest()
                ->take(10)
                ->get(),
            'tier_upgrades' => DB::table('tier_upgrades')
                ->whereIn('user_id', $teamUserIds)
                ->where('status', 'pending')
                ->count(),
            'commission_disputes' => DB::table('referral_commissions')
                ->whereIn('referrer_id', $teamUserIds)
                ->where('status', 'disputed')
                ->count(),
        ];
    }

    /**
     * Get recent team activities
     */
    private function getTeamActivities(): array
    {
        $manager = auth()->user();
        $teamUserIds = $this->getTeamUsers($manager)->pluck('id');

        return DB::table('activity_logs')
            ->whereIn('user_id', $teamUserIds)
            ->latest()
            ->take(20)
            ->get()
            ->toArray();
    }

    /**
     * Get users managed by this manager
     */
    private function getManagedUsers($manager)
    {
        return $this->getTeamUsers($manager)
            ->with(['investments' => function($query) {
                $query->where('status', 'active');
            }])
            ->take(20)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_investments' => $user->investments->sum('amount'),
                    'investment_count' => $user->investments->count(),
                    'last_activity' => $user->last_login_at,
                    'status' => $user->status,
                ];
            });
    }

    /**
     * Get recent investments from team members
     */
    private function getRecentTeamInvestments($manager)
    {
        $teamUserIds = $this->getTeamUsers($manager)->pluck('id');

        return Investment::whereIn('user_id', $teamUserIds)
            ->with(['user', 'tier'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function($investment) {
                return [
                    'id' => $investment->id,
                    'user_name' => $investment->user->name,
                    'amount' => $investment->amount,
                    'tier' => $investment->tier?->name,
                    'status' => $investment->status,
                    'created_at' => $investment->created_at,
                ];
            });
    }

    /**
     * Get commission overview for the team
     */
    private function getCommissionOverview($manager): array
    {
        $teamUserIds = $this->getTeamUsers($manager)->pluck('id');

        $totalPaid = ReferralCommission::whereIn('referrer_id', $teamUserIds)
            ->where('status', 'paid')
            ->sum('amount');

        $totalPending = ReferralCommission::whereIn('referrer_id', $teamUserIds)
            ->where('status', 'pending')
            ->sum('amount');

        $monthlyCommissions = ReferralCommission::whereIn('referrer_id', $teamUserIds)
            ->where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        return [
            'total_paid' => $totalPaid,
            'total_pending' => $totalPending,
            'monthly_commissions' => $monthlyCommissions,
            'commission_rate' => $this->calculateTeamCommissionRate($teamUserIds),
        ];
    }

    /**
     * Get team users based on manager's oversight scope
     */
    private function getTeamUsers($manager)
    {
        // This logic depends on your business rules for manager oversight
        // Option 1: Managers oversee their direct referrals and their network
        if ($manager->hasRole('regional_manager')) {
            // Regional managers might oversee users in specific regions
            return User::where('region', $manager->region)->get();
        }
        
        // Option 2: Managers oversee users in their referral network
        return User::where('referrer_id', $manager->id)
            ->orWhereIn('referrer_id', function($query) use ($manager) {
                $query->select('id')
                    ->from('users')
                    ->where('referrer_id', $manager->id);
            })
            ->get();
    }

    /**
     * Calculate team performance score
     */
    private function calculateTeamPerformanceScore($teamUsers): float
    {
        if ($teamUsers->isEmpty()) return 0;

        $activeUsers = $teamUsers->filter(function($user) {
            return $user->investments()->where('status', 'active')->exists();
        })->count();

        $totalInvestments = Investment::whereIn('user_id', $teamUsers->pluck('id'))
            ->where('status', 'active')
            ->sum('amount');

        $averageInvestment = $activeUsers > 0 ? $totalInvestments / $activeUsers : 0;
        
        // Score based on activity rate and average investment size
        $activityScore = ($activeUsers / $teamUsers->count()) * 50;
        $investmentScore = min(($averageInvestment / 5000) * 50, 50); // Assuming 5000 is a good benchmark
        
        return round($activityScore + $investmentScore, 1);
    }

    /**
     * Calculate growth rate for investments
     */
    private function calculateGrowthRate($investments): float
    {
        if ($investments->count() < 2) return 0;

        $first = $investments->first();
        $last = $investments->last();
        
        if ($first->total == 0) return 0;
        
        return round((($last->total - $first->total) / $first->total) * 100, 2);
    }

    /**
     * Calculate team commission rate
     */
    private function calculateTeamCommissionRate($teamUserIds): float
    {
        $totalInvestments = Investment::whereIn('user_id', $teamUserIds)->sum('amount');
        $totalCommissions = ReferralCommission::whereIn('referrer_id', $teamUserIds)
            ->where('status', 'paid')
            ->sum('amount');

        if ($totalInvestments == 0) return 0;

        return round(($totalCommissions / $totalInvestments) * 100, 2);
    }

    /**
     * Simple manager check - customize this logic based on your needs
     */
    private function isManager($user): bool
    {
        return in_array($user->rank, ['manager', 'regional_manager']) || 
               $user->email === 'manager@mygrownet.com' ||
               $user->hasRole('Investment Manager') ||
               $user->hasRole('Administrator') || // Admins can also access manager dashboard
               $user->hasRole('admin');
    }
}