<?php

namespace App\Http\Controllers;

use App\Services\PointService;
use App\Services\LevelAdvancementService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PointsController extends Controller
{
    public function __construct(
        protected PointService $pointService,
        protected LevelAdvancementService $levelService
    ) {}

    /**
     * Display points dashboard
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $user->load(['points', 'badges', 'currentMonthActivity']);

        $levelProgress = $this->levelService->getLevelProgress($user);
        
        // Get recent transactions
        $recentTransactions = $user->pointTransactions()
            ->latest()
            ->limit(20)
            ->get();

        // Get monthly history
        $monthlyHistory = $user->monthlyActivityStatuses()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return Inertia::render('Points/Dashboard', [
            'userPoints' => $user->points,
            'levelProgress' => $levelProgress,
            'recentTransactions' => $recentTransactions,
            'monthlyHistory' => $monthlyHistory,
            'badges' => $user->badges,
            'currentMonthActivity' => $user->currentMonthActivity,
            'isQualified' => $user->isQualifiedThisMonth(),
            'daysLeftInMonth' => now()->endOfMonth()->diffInDays(now()),
        ]);
    }

    /**
     * Get point transactions history
     */
    public function transactions(Request $request)
    {
        $user = $request->user();

        $transactions = $user->pointTransactions()
            ->when($request->source, fn($q) => $q->where('source', $request->source))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(50);

        return response()->json($transactions);
    }

    /**
     * Get level progress details
     */
    public function levelProgress(Request $request)
    {
        $user = $request->user();
        $progress = $this->levelService->getLevelProgress($user);

        return response()->json($progress);
    }

    /**
     * Get monthly qualification status
     */
    public function qualificationStatus(Request $request)
    {
        $user = $request->user();
        $user->load('points', 'currentMonthActivity');

        $required = $this->pointService->getRequiredMAP($user);
        $current = $user->points?->monthly_points ?? 0;
        $qualified = $this->pointService->checkMonthlyQualification($user);

        return response()->json([
            'qualified' => $qualified,
            'current_map' => $current,
            'required_map' => $required,
            'needed_map' => max(0, $required - $current),
            'performance_tier' => $this->pointService->getPerformanceTier($user),
            'commission_bonus' => $this->pointService->getCommissionBonus($user),
            'days_left' => now()->endOfMonth()->diffInDays(now()),
        ]);
    }

    /**
     * Award daily login points
     */
    public function dailyLogin(Request $request)
    {
        $user = $request->user();
        
        $transaction = $this->pointService->awardDailyLogin($user);

        if (!$transaction) {
            return response()->json([
                'message' => 'Daily login already recorded today',
                'already_awarded' => true,
            ]);
        }

        // Check for streak bonuses
        $this->pointService->checkStreakBonuses($user);

        return response()->json([
            'message' => 'Daily login points awarded!',
            'transaction' => $transaction,
            'total_map' => $user->points->monthly_points,
        ]);
    }

    /**
     * Get leaderboard data
     */
    public function leaderboard(Request $request)
    {
        $type = $request->get('type', 'monthly'); // monthly, lifetime, streak

        $query = \App\Models\User::with('points');

        $leaderboard = match($type) {
            'monthly' => $query->join('user_points', 'users.id', '=', 'user_points.user_id')
                ->orderBy('user_points.monthly_points', 'desc')
                ->select('users.*', 'user_points.monthly_points as score')
                ->limit(100)
                ->get(),
            
            'lifetime' => $query->join('user_points', 'users.id', '=', 'user_points.user_id')
                ->orderBy('user_points.lifetime_points', 'desc')
                ->select('users.*', 'user_points.lifetime_points as score')
                ->limit(100)
                ->get(),
            
            'streak' => $query->join('user_points', 'users.id', '=', 'user_points.user_id')
                ->orderBy('user_points.current_streak_months', 'desc')
                ->select('users.*', 'user_points.current_streak_months as score')
                ->limit(100)
                ->get(),
            
            default => collect(),
        };

        return response()->json([
            'type' => $type,
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * Get available badges
     */
    public function badges(Request $request)
    {
        $user = $request->user();
        $earnedBadges = $user->badges;
        $availableBadges = \App\Models\UserBadge::availableBadges();

        $badgesWithStatus = collect($availableBadges)->map(function ($badge, $code) use ($earnedBadges) {
            $earned = $earnedBadges->firstWhere('badge_code', $code);
            
            return [
                'code' => $code,
                'name' => $badge['name'],
                'description' => $badge['description'],
                'lp_reward' => $badge['lp_reward'],
                'icon' => $badge['icon'],
                'earned' => $earned !== null,
                'earned_at' => $earned?->earned_at,
            ];
        });

        return response()->json([
            'badges' => $badgesWithStatus,
            'total_earned' => $earnedBadges->count(),
            'total_available' => count($availableBadges),
        ]);
    }
}
