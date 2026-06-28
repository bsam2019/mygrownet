<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPoints;
use App\Models\PointTransaction;
use App\Models\MonthlyActivityStatus;
use App\Services\PointService;
use App\Services\LevelAdvancementService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPointsController extends Controller
{
    public function __construct(
        protected PointService $pointService,
        protected LevelAdvancementService $levelService
    ) {
        // Middleware applied in routes
    }

    /**
     * Display points management dashboard
     */
    public function index(Request $request): Response
    {
        $stats = [
            'total_lp_awarded' => PointTransaction::sum('lp_amount'),
            'total_map_awarded' => PointTransaction::sum('bp_amount'),
            'qualified_users_this_month' => MonthlyActivityStatus::currentMonth()->qualified()->count(),
            'total_users_with_points' => UserPoints::count(),
            'average_lp' => UserPoints::avg('lifetime_points'),
            'average_map' => UserPoints::avg('monthly_points'),
        ];

        // Level distribution
        $levelDistribution = User::selectRaw('current_professional_level, COUNT(*) as count')
            ->groupBy('current_professional_level')
            ->get()
            ->pluck('count', 'current_professional_level');

        // Recent transactions
        $recentTransactions = PointTransaction::with('user')
            ->latest()
            ->limit(50)
            ->get();

        return Inertia::render('Admin/Points/Index', [
            'stats' => $stats,
            'levelDistribution' => $levelDistribution,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Display user points management
     */
    public function users(Request $request): Response
    {
        $users = User::with(['points', 'currentMonthActivity'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->level, function ($query, $level) {
                $query->where('current_professional_level', $level);
            })
            ->when($request->qualified !== null, function ($query) use ($request) {
                if ($request->qualified === 'true') {
                    $query->whereHas('points', function ($q) {
                        $q->whereRaw('monthly_points >= (
                            CASE current_professional_level
                                WHEN "associate" THEN 100
                                WHEN "professional" THEN 200
                                WHEN "senior" THEN 300
                                WHEN "manager" THEN 400
                                WHEN "director" THEN 500
                                WHEN "executive" THEN 600
                                WHEN "ambassador" THEN 800
                                ELSE 100
                            END
                        )');
                    });
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return Inertia::render('Admin/Points/Users', [
            'users' => $users,
            'filters' => $request->only(['search', 'level', 'qualified']),
        ]);
    }

    /**
     * Show user points details
     */
    public function show(User $user): Response
    {
        $user->load(['points', 'pointTransactions', 'badges', 'monthlyActivityStatuses']);

        $levelProgress = $this->levelService->getLevelProgress($user);

        $transactions = $user->pointTransactions()
            ->latest()
            ->paginate(50);

        return Inertia::render('Admin/Points/Show', [
            'user' => $user,
            'userPoints' => $user->points,
            'levelProgress' => $levelProgress,
            'transactions' => $transactions,
            'badges' => $user->badges,
            'monthlyHistory' => $user->monthlyActivityStatuses()
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
        ]);
    }

    /**
     * Award points to user (admin action)
     */
    public function awardPoints(Request $request, User $user)
    {
        $validated = $request->validate([
            'lp_amount' => 'required|integer|min:0|max:10000',
            'bp_amount' => 'required|integer|min:0|max:10000',
            'reason' => 'required|string|max:500',
        ]);

        $transaction = $this->pointService->awardPoints(
            $user,
            'admin_award',
            $validated['lp_amount'],
            $validated['bp_amount'],
            "Admin award: {$validated['reason']}"
        );

        return back()->with('success', 'Points awarded successfully!');
    }

    /**
     * Deduct points from user (admin action)
     */
    public function deductPoints(Request $request, User $user)
    {
        $validated = $request->validate([
            'lp_amount' => 'required|integer|min:0',
            'bp_amount' => 'required|integer|min:0',
            'reason' => 'required|string|max:500',
        ]);

        if (!$user->points) {
            return back()->withErrors(['error' => 'User has no points record']);
        }

        // Deduct points (negative amounts)
        $transaction = PointTransaction::create([
            'user_id' => $user->id,
            'point_type' => 'both',
            'lp_amount' => -$validated['lp_amount'],
            'bp_amount' => -$validated['bp_amount'],
            'source' => 'admin_deduction',
            'description' => "Admin deduction: {$validated['reason']}",
            'multiplier_applied' => 1.00,
        ]);

        // Update user points
        $user->points->decrement('lifetime_points', $validated['lp_amount']);
        $user->points->decrement('monthly_points', $validated['bp_amount']);

        return back()->with('success', 'Points deducted successfully!');
    }

    /**
     * Manually set user points
     */
    public function setPoints(Request $request, User $user)
    {
        $validated = $request->validate([
            'lifetime_points' => 'required|integer|min:0',
            'monthly_points' => 'required|integer|min:0',
            'reason' => 'required|string|max:500',
        ]);

        if (!$user->points) {
            $user->points()->create([
                'lifetime_points' => $validated['lifetime_points'],
                'monthly_points' => $validated['monthly_points'],
            ]);
        } else {
            $oldLP = $user->points->lifetime_points;
            $oldMAP = $user->points->monthly_points;

            $user->points->update([
                'lifetime_points' => $validated['lifetime_points'],
                'monthly_points' => $validated['monthly_points'],
            ]);

            // Create transaction record
            PointTransaction::create([
                'user_id' => $user->id,
                'point_type' => 'both',
                'lp_amount' => $validated['lifetime_points'] - $oldLP,
                'bp_amount' => $validated['monthly_points'] - $oldMAP,
                'source' => 'admin_adjustment',
                'description' => "Admin adjustment: {$validated['reason']}",
                'multiplier_applied' => 1.00,
            ]);
        }

        return back()->with('success', 'Points updated successfully!');
    }

    /**
     * Manually advance user level
     */
    public function advanceLevel(Request $request, User $user)
    {
        $validated = $request->validate([
            'new_level' => 'required|in:associate,professional,senior,manager,director,executive,ambassador',
            'reason' => 'required|string|max:500',
        ]);

        $oldLevel = $user->current_professional_level;

        $user->update([
            'current_professional_level' => $validated['new_level'],
            'level_achieved_at' => now(),
        ]);

        // Log the manual advancement
        $user->recordActivity(
            'admin_level_advancement',
            "Admin manually advanced user from {$oldLevel} to {$validated['new_level']}. Reason: {$validated['reason']}"
        );

        return back()->with('success', "User advanced to {$validated['new_level']} level!");
    }

    /**
     * Reset user's monthly points
     */
    public function resetMonthlyPoints(User $user)
    {
        if (!$user->points) {
            return back()->withErrors(['error' => 'User has no points record']);
        }

        $user->points->update([
            'last_month_points' => $user->points->monthly_points,
            'monthly_points' => 0,
        ]);

        return back()->with('success', 'Monthly points reset successfully!');
    }

    /**
     * Bulk operations
     */
    public function bulkAwardPoints(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'lp_amount' => 'required|integer|min:0|max:10000',
            'bp_amount' => 'required|integer|min:0|max:10000',
            'reason' => 'required|string|max:500',
        ]);

        $count = 0;
        foreach ($validated['user_ids'] as $userId) {
            $user = User::find($userId);
            if ($user) {
                $this->pointService->awardPoints(
                    $user,
                    'admin_bulk_award',
                    $validated['lp_amount'],
                    $validated['bp_amount'],
                    "Bulk award: {$validated['reason']}"
                );
                $count++;
            }
        }

        return back()->with('success', "Points awarded to {$count} users!");
    }

    /**
     * Get points statistics
     */
    public function statistics(Request $request): Response
    {
        $period = $request->get('period', 'month'); // day, week, month, year

        $dateFrom = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $stats = [
            'lp_awarded' => PointTransaction::where('created_at', '>=', $dateFrom)->sum('lp_amount'),
            'map_awarded' => PointTransaction::where('created_at', '>=', $dateFrom)->sum('bp_amount'),
            'transactions_count' => PointTransaction::where('created_at', '>=', $dateFrom)->count(),
            'unique_users' => PointTransaction::where('created_at', '>=', $dateFrom)->distinct('user_id')->count(),
        ];

        // Points by source
        $bySource = PointTransaction::where('created_at', '>=', $dateFrom)
            ->selectRaw('source, SUM(lp_amount) as total_lp, SUM(bp_amount) as total_map, COUNT(*) as count')
            ->groupBy('source')
            ->get();

        // Daily trend for the period
        $dailyTrend = PointTransaction::where('created_at', '>=', $dateFrom)
            ->selectRaw('DATE(created_at) as date, SUM(lp_amount) as lp, SUM(bp_amount) as map')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top users by points awarded
        $topUsers = PointTransaction::where('created_at', '>=', $dateFrom)
            ->selectRaw('user_id, SUM(lp_amount) as total_lp, SUM(bp_amount) as total_map')
            ->groupBy('user_id')
            ->orderByDesc('total_lp')
            ->limit(10)
            ->with('user:id,name,email')
            ->get();

        return Inertia::render('Admin/Points/Statistics', [
            'period' => $period,
            'stats' => $stats,
            'bySource' => $bySource,
            'dailyTrend' => $dailyTrend,
            'topUsers' => $topUsers,
        ]);
    }
}
