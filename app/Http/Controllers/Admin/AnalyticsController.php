<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, UserPoints, PointTransaction, MatrixPosition, ReferralCommission};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Points System Analytics
     */
    public function points()
    {
        // Total points awarded
        $totalLP = UserPoints::sum('lifetime_points');
        $totalMAP = UserPoints::sum('monthly_points');
        
        // Points awarded this month
        $thisMonthLP = PointTransaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('lp_amount');
        $thisMonthMAP = PointTransaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('map_amount');
        
        // Level distribution
        $levelDistribution = User::select('current_professional_level', DB::raw('count(*) as count'))
            ->groupBy('current_professional_level')
            ->orderBy('current_professional_level')
            ->get()
            ->map(function($item) {
                $levelNames = [
                    'associate' => 'Associate',
                    'professional' => 'Professional', 
                    'senior' => 'Senior',
                    'manager' => 'Manager',
                    'director' => 'Director',
                    'executive' => 'Executive',
                    'ambassador' => 'Ambassador'
                ];
                return [
                    'level' => $item->current_professional_level,
                    'name' => $levelNames[$item->current_professional_level] ?? ucfirst($item->current_professional_level),
                    'count' => $item->count
                ];
            });
        
        // Monthly qualification rate
        $qualifiedUsers = User::whereHas('points', function($query) {
            $query->where('monthly_points', '>=', 100);
        })->count();
        $totalUsers = User::count();
        $qualificationRate = $totalUsers > 0 ? round(($qualifiedUsers / $totalUsers) * 100, 1) : 0;
        
        // Top point sources this month
        $topSources = PointTransaction::select('source', DB::raw('SUM(lp_amount + map_amount) as total_points'))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('source')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();
        
        // Recent transactions
        $recentTransactions = PointTransaction::with('user')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'user_name' => $transaction->user->name ?? 'Unknown',
                    'source' => $transaction->source,
                    'lp_amount' => $transaction->lp_amount,
                    'map_amount' => $transaction->map_amount,
                    'description' => $transaction->description,
                    'created_at' => $transaction->created_at->format('M j, Y H:i')
                ];
            });
        
        // Daily points trend (last 30 days)
        $dailyTrend = PointTransaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(lp_amount) as lp_total'),
                DB::raw('SUM(map_amount) as map_total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return Inertia::render('Admin/Analytics/Points', [
            'stats' => [
                'total_lp' => $totalLP,
                'total_map' => $totalMAP,
                'this_month_lp' => $thisMonthLP,
                'this_month_map' => $thisMonthMAP,
                'qualification_rate' => $qualificationRate,
                'qualified_users' => $qualifiedUsers,
                'total_users' => $totalUsers
            ],
            'level_distribution' => $levelDistribution,
            'top_sources' => $topSources,
            'recent_transactions' => $recentTransactions,
            'daily_trend' => $dailyTrend
        ]);
    }


    /**
     * Matrix System Analytics
     */
    public function matrix()
    {
        // Matrix statistics
        $totalPositions = MatrixPosition::count();
        $filledPositions = MatrixPosition::whereNotNull('user_id')->count();
        $fillRate = $totalPositions > 0 ? round(($filledPositions / $totalPositions) * 100, 1) : 0;
        
        // Positions by level
        $positionsByLevel = MatrixPosition::select('level', DB::raw('count(*) as total'), 
                DB::raw('count(user_id) as filled'))
            ->groupBy('level')
            ->orderBy('level')
            ->get()
            ->map(function($item) {
                return [
                    'level' => $item->level,
                    'total' => $item->total,
                    'filled' => $item->filled,
                    'fill_rate' => $item->total > 0 ? round(($item->filled / $item->total) * 100, 1) : 0
                ];
            });
        
        // Network growth (new positions filled per day)
        $networkGrowth = MatrixPosition::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as positions_created')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('user_id')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Top sponsors (users with most direct referrals)
        $topSponsors = User::select('users.id', 'users.name', DB::raw('COUNT(referrals.id) as referral_count'))
            ->leftJoin('users as referrals', 'users.id', '=', 'referrals.referrer_id')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('referral_count')
            ->limit(10)
            ->get();
        
        return Inertia::render('Admin/Analytics/Matrix', [
            'stats' => [
                'total_positions' => $totalPositions,
                'filled_positions' => $filledPositions,
                'fill_rate' => $fillRate,
                'empty_positions' => $totalPositions - $filledPositions
            ],
            'positions_by_level' => $positionsByLevel,
            'network_growth' => $networkGrowth,
            'top_sponsors' => $topSponsors
        ]);
    }


    /**
     * Member Analytics
     */
    public function members()
    {
        // Member statistics
        $totalMembers = User::count();
        $activeMembers = User::where('status', 'active')->count();
        $newMembersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Member growth trend (last 12 months)
        $memberGrowth = User::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as new_members')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'date' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                    'new_members' => $item->new_members
                ];
            });
        
        // Member activity levels
        $activityLevels = [
            'highly_active' => User::whereHas('points', function($query) {
                $query->where('monthly_points', '>=', 500);
            })->count(),
            'moderately_active' => User::whereHas('points', function($query) {
                $query->whereBetween('monthly_points', [200, 499]);
            })->count(),
            'low_active' => User::whereHas('points', function($query) {
                $query->whereBetween('monthly_points', [1, 199]);
            })->count(),
            'inactive' => User::whereDoesntHave('points')
                ->orWhereHas('points', function($query) {
                    $query->where('monthly_points', 0);
                })->count()
        ];
        
        // Professional level progression
        $levelProgression = User::select('current_professional_level as professional_level', DB::raw('count(*) as count'))
            ->groupBy('current_professional_level')
            ->orderBy('current_professional_level')
            ->get();
        
        return Inertia::render('Admin/Analytics/Members', [
            'stats' => [
                'total_members' => $totalMembers,
                'active_members' => $activeMembers,
                'new_this_month' => $newMembersThisMonth,
                'activity_rate' => $totalMembers > 0 ? round(($activeMembers / $totalMembers) * 100, 1) : 0
            ],
            'member_growth' => $memberGrowth,
            'activity_levels' => $activityLevels,
            'level_progression' => $levelProgression
        ]);
    }


    /**
     * Financial Analytics
     */
    public function financial()
    {
        // Subscription revenue (using package_subscriptions table)
        $totalSubscriptionRevenue = DB::table('package_subscriptions')->where('status', 'active')->sum('amount');
        $monthlySubscriptionRevenue = DB::table('package_subscriptions')
            ->where('status', 'active')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        
        // Commission payouts
        $totalCommissions = ReferralCommission::sum('amount');
        $monthlyCommissions = ReferralCommission::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        
        // Revenue by package
        $revenueByPackage = DB::table('package_subscriptions')
            ->join('packages', 'package_subscriptions.package_id', '=', 'packages.id')
            ->select('packages.name', DB::raw('SUM(package_subscriptions.amount) as revenue'), DB::raw('COUNT(*) as subscriptions'))
            ->where('package_subscriptions.status', 'active')
            ->groupBy('packages.name')
            ->orderByDesc('revenue')
            ->get();
        
        // Monthly revenue trend
        $revenueTrend = DB::table('package_subscriptions')
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
            ->get()
            ->map(function($item) {
                return [
                    'date' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                    'revenue' => $item->revenue
                ];
            });
        
        return Inertia::render('Admin/Analytics/Financial', [
            'stats' => [
                'total_subscription_revenue' => $totalSubscriptionRevenue,
                'monthly_subscription_revenue' => $monthlySubscriptionRevenue,
                'total_commissions' => $totalCommissions,
                'monthly_commissions' => $monthlyCommissions,
                'net_revenue' => $totalSubscriptionRevenue - $totalCommissions
            ],
            'revenue_by_package' => $revenueByPackage,
            'revenue_trend' => $revenueTrend
        ]);
    }


    /**
     * System Analytics
     */
    public function system()
    {
        // System statistics
        $totalUsers = User::count();
        $totalTransactions = PointTransaction::count();
        $totalCommissions = ReferralCommission::count();
        $totalSubscriptions = DB::table('package_subscriptions')->count();
        
        // Platform growth metrics
        $platformGrowth = [
            'users_growth' => User::whereMonth('created_at', now()->month)->count(),
            'transactions_growth' => PointTransaction::whereMonth('created_at', now()->month)->count(),
            'subscriptions_growth' => DB::table('package_subscriptions')->whereMonth('created_at', now()->month)->count()
        ];
        
        // System health metrics
        $systemHealth = [
            'active_users_percentage' => $totalUsers > 0 ? round((User::where('status', 'active')->count() / $totalUsers) * 100, 1) : 0,
            'qualified_users_percentage' => $totalUsers > 0 ? round((User::whereHas('points', function($q) { $q->where('monthly_points', '>=', 100); })->count() / $totalUsers) * 100, 1) : 0,
            'subscription_conversion' => $totalUsers > 0 ? round((DB::table('package_subscriptions')->where('status', 'active')->count() / $totalUsers) * 100, 1) : 0
        ];
        
        return Inertia::render('Admin/Analytics/System', [
            'stats' => [
                'total_users' => $totalUsers,
                'total_transactions' => $totalTransactions,
                'total_commissions' => $totalCommissions,
                'total_subscriptions' => $totalSubscriptions
            ],
            'platform_growth' => $platformGrowth,
            'system_health' => $systemHealth
        ]);
    }
}
