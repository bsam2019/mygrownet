<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Application\UseCases\Tools\CalculateUserROIUseCase;
use App\Application\UseCases\Tools\CreateBusinessPlanUseCase;
use App\Application\UseCases\Tools\GetUserBusinessPlanUseCase;
use App\Application\UseCases\Tools\UpdateBusinessPlanUseCase;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ToolsController extends Controller
{
    /**
     * Tools Index Page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        return Inertia::render('GrowNet/Tools/Index', [
            'userTier' => $user->starter_kit_tier ?? 'basic',
        ]);
    }
    
    /**
     * Commission Calculator Tool
     */
    public function commissionCalculator(Request $request)
    {
        $user = $request->user();
        
        // Check if user has starter kit
        if (!$user->has_starter_kit) {
            return redirect()
                ->route('mygrownet.starter-kit.purchase')
                ->with('error', 'You need to purchase a starter kit to access this tool.');
        }
        
        // Get commission rates from system
        $commissionRates = [
            'subscription' => [
                'level_1' => 10,
                'level_2' => 5,
                'level_3' => 3,
                'level_4' => 2,
                'level_5' => 1,
                'level_6' => 1,
                'level_7' => 1,
            ],
            'starter_kit' => [
                'level_1' => 10,
                'level_2' => 5,
                'level_3' => 3,
                'level_4' => 2,
                'level_5' => 1,
                'level_6' => 1,
                'level_7' => 1,
            ],
        ];
        
        // Get user's current network stats
        $networkStats = [
            'level_1' => $user->directReferrals()->count(),
            'level_2' => $this->getNetworkCountAtLevel($user, 2),
            'level_3' => $this->getNetworkCountAtLevel($user, 3),
            'level_4' => $this->getNetworkCountAtLevel($user, 4),
            'level_5' => $this->getNetworkCountAtLevel($user, 5),
            'level_6' => $this->getNetworkCountAtLevel($user, 6),
            'level_7' => $this->getNetworkCountAtLevel($user, 7),
        ];
        
        return Inertia::render('GrowNet/Tools/EarningsCalculator', [
            'currentNetwork' => $networkStats,
            'userTier' => $user->starter_kit_tier,
        ]);
    }
    
    /**
     * Goal Tracker Tool
     */
    public function goalTracker(Request $request)
    {
        $user = $request->user();
        
        // Get user's goals
        $goals = DB::table('user_goals')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get current earnings
        $currentEarnings = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'commission')
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
        
        return Inertia::render('GrowNet/Tools/GoalTracker', [
            'goals' => $goals,
            'currentEarnings' => $currentEarnings,
            'userTier' => $user->starter_kit_tier,
        ]);
    }
    
    /**
     * Store new goal
     */
    public function storeGoal(Request $request)
    {
        $validated = $request->validate([
            'goal_type' => 'required|in:monthly_income,team_size,total_earnings',
            'target_amount' => 'required|numeric|min:0',
            'target_date' => 'required|date|after:today',
            'description' => 'nullable|string|max:500',
        ]);
        
        $goalId = DB::table('user_goals')->insertGetId([
            'user_id' => $request->user()->id,
            'goal_type' => $validated['goal_type'],
            'target_amount' => $validated['target_amount'],
            'target_date' => $validated['target_date'],
            'description' => $validated['description'] ?? null,
            'current_progress' => 0,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Goal created successfully!');
    }
    
    /**
     * Update goal progress
     */
    public function updateGoalProgress(Request $request, int $goalId)
    {
        $validated = $request->validate([
            'current_progress' => 'required|numeric|min:0',
        ]);
        
        DB::table('user_goals')
            ->where('id', $goalId)
            ->where('user_id', $request->user()->id)
            ->update([
                'current_progress' => $validated['current_progress'],
                'updated_at' => now(),
            ]);
        
        return redirect()->back()->with('success', 'Progress updated!');
    }
    
    /**
     * Network Visualizer Tool
     */
    public function networkVisualizer(Request $request)
    {
        $user = $request->user();
        
        // Get network tree data (7 levels)
        $networkTree = $this->buildNetworkTree($user, 7);
        
        // Get network statistics
        $networkStats = [
            'total_members' => $this->getTotalNetworkCount($user),
            'active_members' => $this->getActiveNetworkCount($user),
            'total_volume' => $this->getTotalNetworkVolume($user),
            'this_month_volume' => $this->getMonthlyNetworkVolume($user),
        ];
        
        return Inertia::render('GrowNet/Tools/NetworkVisualizer', [
            'networkTree' => $networkTree,
            'networkStats' => $networkStats,
            'userTier' => $user->starter_kit_tier,
        ]);
    }
    
    /**
     * Business Plan Generator (Premium Only)
     */
    public function businessPlanGenerator(Request $request)
    {
        $user = $request->user();
        
        // Check premium access
        if ($user->starter_kit_tier !== 'premium') {
            return redirect()
                ->route('mygrownet.starter-kit.purchase')
                ->with('error', 'Business Plan Generator is a premium tool. Upgrade to access it.');
        }
        
        // Get user's existing business plan if any
        $existingPlan = DB::table('user_business_plans')
            ->where('user_id', $user->id)
            ->latest()
            ->first();
        
        return Inertia::render('GrowNet/Tools/BusinessPlanGenerator', [
            'existingPlan' => $existingPlan,
            'userProfile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'joined_at' => $user->created_at,
            ],
            'userTier' => $user->starter_kit_tier,
        ]);
    }
    
    /**
     * Generate business plan PDF
     */
    public function generateBusinessPlan(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'vision' => 'required|string|max:1000',
            'target_market' => 'required|string|max:1000',
            'income_goal_6months' => 'required|numeric|min:0',
            'income_goal_1year' => 'required|numeric|min:0',
            'team_size_goal' => 'required|integer|min:0',
            'marketing_strategy' => 'required|string|max:2000',
            'action_plan' => 'required|string|max:2000',
        ]);
        
        $user = $request->user();
        
        // Save business plan
        $planId = DB::table('user_business_plans')->insertGetId([
            'user_id' => $user->id,
            'business_name' => $validated['business_name'],
            'vision' => $validated['vision'],
            'target_market' => $validated['target_market'],
            'income_goal_6months' => $validated['income_goal_6months'],
            'income_goal_1year' => $validated['income_goal_1year'],
            'team_size_goal' => $validated['team_size_goal'],
            'marketing_strategy' => $validated['marketing_strategy'],
            'action_plan' => $validated['action_plan'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // TODO: Generate PDF using a service
        // For now, return success
        
        return redirect()->back()->with('success', 'Business plan saved successfully!');
    }
    
    /**
     * ROI Calculator (Premium Only)
     */
    public function roiCalculator(Request $request)
    {
        $user = $request->user();
        
        // Check premium access
        if ($user->starter_kit_tier !== 'premium') {
            return redirect()
                ->route('mygrownet.starter-kit.purchase')
                ->with('error', 'ROI Calculator is a premium tool. Upgrade to access it.');
        }
        
        // Get user's investment history
        $investments = [
            'starter_kit' => $user->has_starter_kit ? ($user->starter_kit_tier === 'premium' ? 1000 : 500) : 0,
            'total_earnings' => DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('amount', '>', 0)
                ->sum('amount'),
        ];
        
        return Inertia::render('GrowNet/Tools/ROICalculator', [
            'investments' => $investments,
            'userTier' => $user->starter_kit_tier,
        ]);
    }
    
    // Helper methods
    
    protected function getNetworkCountAtLevel(User $user, int $level): int
    {
        // Simplified - you may need to adjust based on your actual network structure
        if ($level === 1) {
            return $user->directReferrals()->count();
        }
        
        // Recursive count for deeper levels
        $count = 0;
        $currentLevel = $user->directReferrals;
        
        for ($i = 2; $i <= $level; $i++) {
            $nextLevel = collect();
            foreach ($currentLevel as $member) {
                $nextLevel = $nextLevel->merge($member->directReferrals);
            }
            $currentLevel = $nextLevel;
        }
        
        return $currentLevel->count();
    }
    
    protected function buildNetworkTree(User $user, int $maxDepth, int $currentDepth = 0): array
    {
        if ($currentDepth >= $maxDepth) {
            return [];
        }
        
        $children = $user->directReferrals()->get()->map(function ($child) use ($maxDepth, $currentDepth) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'tier' => $child->starter_kit_tier,
                'has_starter_kit' => $child->has_starter_kit,
                'joined_at' => $child->created_at->format('Y-m-d'),
                'children' => $this->buildNetworkTree($child, $maxDepth, $currentDepth + 1),
            ];
        });
        
        return $children->toArray();
    }
    
    protected function getTotalNetworkCount(User $user): int
    {
        $count = 0;
        for ($i = 1; $i <= 7; $i++) {
            $count += $this->getNetworkCountAtLevel($user, $i);
        }
        return $count;
    }
    
    protected function getActiveNetworkCount(User $user): int
    {
        // Members who have starter kit
        return User::whereIn('referrer_id', function ($query) use ($user) {
            $query->select('id')
                ->from('users')
                ->where('referrer_id', $user->id);
        })->where('has_starter_kit', true)->count();
    }
    
    protected function getTotalNetworkVolume(User $user): float
    {
        // Total purchases from network
        return 0; // Implement based on your commission tracking
    }
    
    protected function getMonthlyNetworkVolume(User $user): float
    {
        // This month's purchases from network
        return 0; // Implement based on your commission tracking
    }
}
