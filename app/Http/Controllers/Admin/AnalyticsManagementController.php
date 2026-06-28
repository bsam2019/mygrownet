<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\RecommendationEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsManagementController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService,
        protected RecommendationEngine $recommendationEngine
    ) {}
    
    public function index(Request $request): Response
    {
        // Platform-wide analytics
        $platformStats = $this->getPlatformStats();
        $topPerformers = $this->getTopPerformers();
        $recentActivity = $this->getRecentActivity();
        $recommendationStats = $this->getRecommendationStats();
        
        return Inertia::render('Admin/Analytics/Index', [
            'platformStats' => $platformStats,
            'topPerformers' => $topPerformers,
            'recentActivity' => $recentActivity,
            'recommendationStats' => $recommendationStats,
        ]);
    }
    
    public function memberAnalytics(Request $request, int $userId): Response
    {
        $user = User::findOrFail($userId);
        
        $performance = $this->analyticsService->getMemberPerformance($user);
        $recommendations = $this->recommendationEngine->getActiveRecommendations($user);
        
        return Inertia::render('Admin/Analytics/MemberDetail', [
            'member' => $user,
            'performance' => $performance,
            'recommendations' => $recommendations,
        ]);
    }
    
    public function generateRecommendations(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        
        $user = User::findOrFail($request->user_id);
        $recommendations = $this->recommendationEngine->generateRecommendations($user);
        
        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
            'message' => 'Recommendations generated successfully',
        ]);
    }
    
    public function bulkGenerateRecommendations(Request $request)
    {
        $request->validate([
            'tier' => 'nullable|string',
            'active_only' => 'nullable|boolean',
        ]);
        
        $query = User::query();
        
        if ($request->tier) {
            $query->where('starter_kit_tier', $request->tier);
        }
        
        if ($request->active_only) {
            $query->where('is_currently_active', true);
        }
        
        $users = $query->get();
        $count = 0;
        
        foreach ($users as $user) {
            $this->recommendationEngine->generateRecommendations($user);
            $count++;
        }
        
        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "Generated recommendations for {$count} members",
        ]);
    }
    
    public function clearCache(Request $request)
    {
        DB::table('member_analytics_cache')->truncate();
        
        return response()->json([
            'success' => true,
            'message' => 'Analytics cache cleared successfully',
        ]);
    }
    
    protected function getPlatformStats(): array
    {
        $totalMembers = User::count();
        $activeMembers = User::where('is_currently_active', true)->count();
        $premiumMembers = User::where('starter_kit_tier', 'premium')->count();
        
        $totalEarnings = DB::table('transactions')
            ->where('type', 'credit')
            ->sum('amount');
        
        $monthlyEarnings = DB::table('transactions')
            ->where('type', 'credit')
            ->where('created_at', '>', now()->subDays(30))
            ->sum('amount');
        
        $totalRecommendations = DB::table('recommendations')
            ->where('is_dismissed', false)
            ->count();
        
        $activeRecommendations = DB::table('recommendations')
            ->where('is_dismissed', false)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->count();
        
        return [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'premium_members' => $premiumMembers,
            'active_percentage' => $totalMembers > 0 ? round(($activeMembers / $totalMembers) * 100, 1) : 0,
            'total_earnings' => $totalEarnings,
            'monthly_earnings' => $monthlyEarnings,
            'total_recommendations' => $totalRecommendations,
            'active_recommendations' => $activeRecommendations,
        ];
    }
    
    protected function getTopPerformers(): array
    {
        return User::select('users.*')
            ->selectRaw('(SELECT SUM(amount) FROM transactions WHERE user_id = users.id AND type = "credit") as total_earnings')
            ->orderByDesc('total_earnings')
            ->limit(10)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'tier' => $user->starter_kit_tier ?? 'none',
                    'total_earnings' => $user->total_earnings ?? 0,
                    'network_size' => $user->referral_count ?? 0,
                ];
            })
            ->toArray();
    }
    
    protected function getRecentActivity(): array
    {
        return DB::table('analytics_events')
            ->join('users', 'analytics_events.user_id', '=', 'users.id')
            ->select('analytics_events.*', 'users.name as user_name', 'users.email as user_email')
            ->orderBy('analytics_events.created_at', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }
    
    protected function getRecommendationStats(): array
    {
        $byType = DB::table('recommendations')
            ->select('recommendation_type', DB::raw('COUNT(*) as count'))
            ->where('is_dismissed', false)
            ->groupBy('recommendation_type')
            ->get()
            ->pluck('count', 'recommendation_type')
            ->toArray();
        
        $byPriority = DB::table('recommendations')
            ->select('priority', DB::raw('COUNT(*) as count'))
            ->where('is_dismissed', false)
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority')
            ->toArray();
        
        $dismissRate = DB::table('recommendations')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_dismissed = 1 THEN 1 ELSE 0 END) as dismissed
            ')
            ->first();
        
        $dismissPercentage = $dismissRate->total > 0 
            ? round(($dismissRate->dismissed / $dismissRate->total) * 100, 1) 
            : 0;
        
        return [
            'by_type' => $byType,
            'by_priority' => $byPriority,
            'dismiss_rate' => $dismissPercentage,
            'total' => $dismissRate->total,
            'dismissed' => $dismissRate->dismissed,
        ];
    }
}
