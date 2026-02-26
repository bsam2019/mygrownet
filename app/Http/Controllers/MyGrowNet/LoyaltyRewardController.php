<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Application\Services\LoyaltyReward\LgrQualificationService;
use App\Application\Services\LoyaltyReward\LgrCycleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoyaltyRewardController extends Controller
{
    public function __construct(
        private LgrQualificationService $qualificationService,
        private LgrCycleService $cycleService
    ) {}

    public function index(): Response
    {
        $userId = auth()->id();
        
        $qualification = $this->qualificationService->checkQualification($userId);
        $cycleStats = $this->cycleService->getCycleStats($userId);
        
        // Get available LGR packages
        $packages = \App\Models\LgrPackage::getActive();
        
        // Get user's current package if they have one
        $userPackage = auth()->user()->lgrPackage ?? null;
        
        return Inertia::render('GrowNet/LoyaltyReward/Dashboard', [
            'qualification' => $qualification,
            'cycle' => $cycleStats,
            'packages' => $packages,
            'userPackage' => $userPackage,
            'user' => [
                'loyalty_points' => auth()->user()->loyalty_points,
                'bonus_balance' => auth()->user()->bonus_balance,
            ],
        ]);
    }

    public function startCycle(Request $request)
    {
        try {
            $cycle = $this->cycleService->startCycle(auth()->id());
            
            return back()->with('success', 'Your 70-day Loyalty Growth Reward cycle has started! Complete daily activities to earn up to K1,750 in Loyalty Credits.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function recordActivity(Request $request)
    {
        $request->validate([
            'activity_type' => 'required|string',
            'description' => 'required|string',
            'metadata' => 'nullable|array',
        ]);

        $lgcEarned = $this->cycleService->recordActivity(
            auth()->id(),
            $request->activity_type,
            $request->description,
            $request->metadata ?? []
        );

        if ($lgcEarned === null) {
            return response()->json([
                'success' => false,
                'message' => 'Activity could not be recorded. You may have already earned today or your cycle is not active.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => "Activity recorded! You earned K{$lgcEarned} in Loyalty Credits.",
            'lgc_earned' => $lgcEarned,
        ]);
    }

    public function qualification(): Response
    {
        $qualification = $this->qualificationService->checkQualification(auth()->id());
        
        return Inertia::render('GrowNet/LoyaltyReward/Qualification', [
            'qualification' => $qualification,
        ]);
    }

    public function activities(): Response
    {
        $activities = $this->cycleService->getRecentActivities(auth()->id(), 30);
        $cycleStats = $this->cycleService->getCycleStats(auth()->id());
        
        return Inertia::render('GrowNet/LoyaltyReward/Activities', [
            'activities' => $activities,
            'cycle' => $cycleStats,
        ]);
    }
}
