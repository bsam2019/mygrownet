<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        return Inertia::render('Admin/Dashboard/Index', [
            'memberMetrics' => $this->getMemberMetrics(),
            'subscriptionMetrics' => $this->getSubscriptionMetrics(),
            'pointsMetrics' => $this->getPointsMetrics(),
            'matrixMetrics' => $this->getMatrixMetrics(),
            'financialMetrics' => $this->getFinancialMetrics(),
            'professionalLevelDistribution' => $this->getProfessionalLevelDistribution(),
            'memberGrowthTrend' => $this->getMemberGrowthTrend(),
            'revenueGrowthTrend' => $this->getRevenueGrowthTrend(),
            'recentActivity' => $this->getRecentActivity(),
            'alerts' => $this->getAlerts(),
        ]);
    }

    /**
     * Get member metrics
     */
    private function getMemberMetrics(): array
    {
        $totalMembers = User::count();
        $activeMembers = User::where('status', 'active')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();
        $newMembersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $lastMonthMembers = User::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        $memberGrowth = $lastMonthMembers > 0 
            ? round((($newMembersThisMonth - $lastMonthMembers) / $lastMonthMembers) * 100, 1) 
            : 0;

        return [
            'total' => $totalMembers,
            'active' => $activeMembers,
            'new_this_month' => $newMembersThisMonth,
            'growth_rate' => $memberGrowth,
            'active_percentage' => $totalMembers > 0 ? round(($activeMembers / $totalMembers) * 100, 1) : 0,
        ];
    }

    /**
     * Get subscription metrics
     */
    private function getSubscriptionMetrics(): array
    {
        $activeSubscriptions = DB::table('package_subscriptions')
            ->where('status', 'active')
            ->count();
        
        $monthlyRevenue = DB::table('package_subscriptions')
            ->where('status', 'active')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        
        $lastMonthRevenue = DB::table('package_subscriptions')
            ->where('status', 'active')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');
        
        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) 
            : 0;

        $totalRevenue = DB::table('package_subscriptions')
            ->where('status', 'active')
            ->sum('amount');

        return [
            'active' => $activeSubscriptions,
            'monthly_revenue' => $monthlyRevenue,
            'total_revenue' => $totalRevenue,
            'growth_rate' => $revenueGrowth,
            'conversion_rate' => User::count() > 0 
                ? round(($activeSubscriptions / User::count()) * 100, 1) 
                : 0,
        ];
    }

    /**
     * Get points system metrics
     */
    private function getPointsMetrics(): array
    {
        $totalLP = DB::table('user_points')->sum('lifetime_points');
        $totalMAP = DB::table('user_points')->sum('monthly_points');
        
        $thisMonthLP = DB::table('point_transactions')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('lp_amount');
        
        $thisMonthMAP = DB::table('point_transactions')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('map_amount');

        $qualifiedUsers = DB::table('user_points')
            ->where('monthly_points', '>=', 100)
            ->count();
        
        $totalUsers = User::count();
        $qualificationRate = $totalUsers > 0 
            ? round(($qualifiedUsers / $totalUsers) * 100, 1) 
            : 0;

        return [
            'total_lp' => $totalLP,
            'total_map' => $totalMAP,
            'this_month_lp' => $thisMonthLP,
            'this_month_map' => $thisMonthMAP,
            'qualified_users' => $qualifiedUsers,
            'qualification_rate' => $qualificationRate,
        ];
    }

    /**
     * Get matrix network metrics
     */
    private function getMatrixMetrics(): array
    {
        $totalPositions = DB::table('matrix_positions')->count();
        $filledPositions = DB::table('matrix_positions')
            ->whereNotNull('user_id')
            ->count();
        
        $fillRate = $totalPositions > 0 
            ? round(($filledPositions / $totalPositions) * 100, 1) 
            : 0;

        $newPositionsThisMonth = DB::table('matrix_positions')
            ->whereNotNull('user_id')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'total_positions' => $totalPositions,
            'filled_positions' => $filledPositions,
            'fill_rate' => $fillRate,
            'new_this_month' => $newPositionsThisMonth,
            'capacity' => 3279, // 3×3 matrix, 7 levels deep
        ];
    }

    /**
     * Get financial metrics
     */
    private function getFinancialMetrics(): array
    {
        $totalRevenue = DB::table('package_subscriptions')
            ->where('status', 'active')
            ->sum('amount');
        
        $totalCommissions = DB::table('referral_commissions')
            ->where('status', 'paid')
            ->sum('amount');
        
        $profitDistributed = DB::table('profit_distributions')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_distributed');

        $netRevenue = $totalRevenue - $totalCommissions;

        return [
            'total_revenue' => $totalRevenue,
            'total_commissions' => $totalCommissions,
            'profit_distributed' => $profitDistributed,
            'net_revenue' => $netRevenue,
            'commission_ratio' => $totalRevenue > 0 
                ? round(($totalCommissions / $totalRevenue) * 100, 1) 
                : 0,
        ];
    }

    /**
     * Get professional level distribution
     */
    private function getProfessionalLevelDistribution(): array
    {
        $distribution = User::select('current_professional_level', DB::raw('count(*) as count'))
            ->groupBy('current_professional_level')
            ->get();

        $levelNames = [
            'associate' => 'Associate',
            'professional' => 'Professional',
            'senior' => 'Senior',
            'manager' => 'Manager',
            'director' => 'Director',
            'executive' => 'Executive',
            'ambassador' => 'Ambassador',
        ];

        return $distribution->map(function($item) use ($levelNames) {
            return [
                'level' => $item->current_professional_level,
                'name' => $levelNames[$item->current_professional_level] ?? ucfirst($item->current_professional_level),
                'count' => $item->count,
            ];
        })->toArray();
    }

    /**
     * Get member growth trend (last 12 months)
     */
    private function getMemberGrowthTrend(): array
    {
        $trend = User::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return $trend->map(function($item) {
            return [
                'date' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                'count' => $item->count,
            ];
        })->toArray();
    }

    /**
     * Get revenue growth trend (last 12 months)
     */
    private function getRevenueGrowthTrend(): array
    {
        $trend = DB::table('package_subscriptions')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as revenue')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->where('status', 'active')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return $trend->map(function($item) {
            return [
                'date' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                'revenue' => $item->revenue,
            ];
        })->toArray();
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        $activities = [];

        // Recent member registrations
        $newMembers = User::latest()
            ->limit(5)
            ->get()
            ->map(function($user) {
                return [
                    'type' => 'member_joined',
                    'description' => "{$user->name} joined the platform",
                    'timestamp' => $user->created_at->diffForHumans(),
                    'icon' => 'user-plus',
                ];
            });

        // Recent subscriptions
        $newSubscriptions = DB::table('package_subscriptions')
            ->join('users', 'package_subscriptions.user_id', '=', 'users.id')
            ->join('packages', 'package_subscriptions.package_id', '=', 'packages.id')
            ->select('users.name as user_name', 'packages.name as package_name', 'package_subscriptions.created_at')
            ->latest('package_subscriptions.created_at')
            ->limit(5)
            ->get()
            ->map(function($sub) {
                return [
                    'type' => 'subscription',
                    'description' => "{$sub->user_name} subscribed to {$sub->package_name}",
                    'timestamp' => Carbon::parse($sub->created_at)->diffForHumans(),
                    'icon' => 'credit-card',
                ];
            });

        return $newMembers->concat($newSubscriptions)
            ->sortByDesc('timestamp')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Get system alerts
     */
    private function getAlerts(): array
    {
        $alerts = [];

        // Low qualification rate alert
        $qualificationRate = $this->getPointsMetrics()['qualification_rate'];
        if ($qualificationRate < 50) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Qualification Rate',
                'message' => "Only {$qualificationRate}% of members are meeting MAP requirements",
            ];
        }

        // Pending commissions
        $pendingCommissions = DB::table('referral_commissions')
            ->where('status', 'pending')
            ->count();
        if ($pendingCommissions > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Pending Commissions',
                'message' => "{$pendingCommissions} commission payments pending",
            ];
        }

        // Matrix capacity warning
        $fillRate = $this->getMatrixMetrics()['fill_rate'];
        if ($fillRate > 80) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Matrix Capacity High',
                'message' => "Matrix is {$fillRate}% full - consider expansion",
            ];
        }

        // Profit distribution due (first day of month)
        if (now()->day === 1) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Profit Distribution Due',
                'message' => 'Monthly profit distribution should be processed',
            ];
        }

        return $alerts;
    }
}
