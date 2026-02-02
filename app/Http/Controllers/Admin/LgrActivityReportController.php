<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LgrActivityReportController extends Controller
{
    public function __construct()
    {
        // Middleware applied in routes/admin.php
    }

    /**
     * Display LGR activity performance report
     */
    public function index(Request $request): Response
    {
        $dateRange = $request->input('date_range', '30'); // days
        $activityType = $request->input('activity_type');
        $search = $request->input('search');

        // Get users with active LGR cycles
        $usersQuery = DB::table('users')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                DB::raw('COUNT(DISTINCT lgr_activities.id) as total_activities'),
                DB::raw('COUNT(DISTINCT DATE(lgr_activities.created_at)) as active_days'),
                DB::raw('SUM(lgr_activities.credits_awarded) as total_credits'),
                DB::raw('MAX(lgr_activities.created_at) as last_activity'),
            ])
            ->leftJoin('lgr_cycles', 'users.id', '=', 'lgr_cycles.user_id')
            ->leftJoin('lgr_activities', 'lgr_cycles.id', '=', 'lgr_activities.lgr_cycle_id')
            ->where('lgr_cycles.status', '!=', 'cancelled')
            ->whereNotNull('lgr_activities.id');

        // Apply date range filter
        if ($dateRange !== 'all') {
            $usersQuery->where('lgr_activities.created_at', '>=', now()->subDays((int)$dateRange));
        }

        // Apply activity type filter
        if ($activityType) {
            $usersQuery->where('lgr_activities.activity_type', $activityType);
        }

        // Apply search filter
        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.phone', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery
            ->groupBy('users.id', 'users.name', 'users.email', 'users.phone')
            ->orderByDesc('total_activities')
            ->paginate(20);

        // Get activity type breakdown
        $activityBreakdown = DB::table('lgr_activities')
            ->select([
                'activity_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users'),
                DB::raw('SUM(credits_awarded) as total_credits'),
            ])
            ->when($dateRange !== 'all', function ($query) use ($dateRange) {
                return $query->where('created_at', '>=', now()->subDays((int)$dateRange));
            })
            ->groupBy('activity_type')
            ->orderByDesc('count')
            ->get();

        // Get summary statistics
        $stats = [
            'total_users' => DB::table('lgr_cycles')
                ->where('status', '!=', 'cancelled')
                ->distinct('user_id')
                ->count('user_id'),
            'active_users' => DB::table('lgr_activities')
                ->when($dateRange !== 'all', function ($query) use ($dateRange) {
                    return $query->where('created_at', '>=', now()->subDays((int)$dateRange));
                })
                ->distinct('user_id')
                ->count('user_id'),
            'total_activities' => DB::table('lgr_activities')
                ->when($dateRange !== 'all', function ($query) use ($dateRange) {
                    return $query->where('created_at', '>=', now()->subDays((int)$dateRange));
                })
                ->count(),
            'total_credits' => DB::table('lgr_activities')
                ->when($dateRange !== 'all', function ($query) use ($dateRange) {
                    return $query->where('created_at', '>=', now()->subDays((int)$dateRange));
                })
                ->sum('credits_awarded'),
        ];

        // Get daily activity trend (last 30 days)
        $dailyTrend = DB::table('lgr_activities')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as activities'),
                DB::raw('COUNT(DISTINCT user_id) as users'),
                DB::raw('SUM(credits_awarded) as credits'),
            ])
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return Inertia::render('Admin/LGR/ActivityReport', [
            'users' => $users,
            'activityBreakdown' => $activityBreakdown,
            'stats' => $stats,
            'dailyTrend' => $dailyTrend,
            'filters' => [
                'date_range' => $dateRange,
                'activity_type' => $activityType,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Get detailed activity for a specific user
     */
    public function userDetails(Request $request, int $userId)
    {
        $dateRange = $request->input('date_range', '30');

        $activities = DB::table('lgr_activities')
            ->select([
                'lgr_activities.*',
                'lgr_cycles.cycle_number',
                'lgr_cycles.start_date',
                'lgr_cycles.end_date',
            ])
            ->join('lgr_cycles', 'lgr_activities.lgr_cycle_id', '=', 'lgr_cycles.id')
            ->where('lgr_activities.user_id', $userId)
            ->when($dateRange !== 'all', function ($query) use ($dateRange) {
                return $query->where('lgr_activities.created_at', '>=', now()->subDays((int)$dateRange));
            })
            ->orderByDesc('lgr_activities.created_at')
            ->paginate(50);

        $summary = DB::table('lgr_activities')
            ->select([
                'activity_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(credits_awarded) as credits'),
            ])
            ->where('user_id', $userId)
            ->when($dateRange !== 'all', function ($query) use ($dateRange) {
                return $query->where('created_at', '>=', now()->subDays((int)$dateRange));
            })
            ->groupBy('activity_type')
            ->get();

        return response()->json([
            'activities' => $activities,
            'summary' => $summary,
        ]);
    }
}
