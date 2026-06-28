<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\RecommendationEngine;
use App\Services\PredictiveAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService,
        protected RecommendationEngine $recommendationEngine,
        protected PredictiveAnalyticsService $predictiveService
    ) {}
    
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $performance = $this->analyticsService->getMemberPerformance($user);
        $recommendations = $this->recommendationEngine->getActiveRecommendations($user);
        $predictions = $this->predictiveService->predictEarnings($user, 6);
        $growthPotential = $this->predictiveService->calculateGrowthPotential($user);
        $nextMilestone = $this->predictiveService->getNextMilestone($user);
        
        return Inertia::render('MyGrowNet/Analytics/Dashboard', [
            'performance' => $performance,
            'recommendations' => $recommendations,
            'predictions' => $predictions,
            'growthPotential' => $growthPotential,
            'nextMilestone' => $nextMilestone,
        ]);
    }
    
    public function performance(Request $request)
    {
        $user = $request->user();
        
        try {
            // Generate fresh recommendations
            $this->recommendationEngine->generateRecommendations($user);
            
            return response()->json([
                'performance' => $this->analyticsService->getMemberPerformance($user),
                'recommendations' => $this->recommendationEngine->getActiveRecommendations($user),
                'growthPotential' => $this->predictiveService->calculateGrowthPotential($user),
                'nextMilestone' => $this->predictiveService->getNextMilestone($user),
            ]);
        } catch (\Exception $e) {
            \Log::error('Analytics Performance API Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'error' => 'Failed to load analytics data',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }
    
    public function recommendations(Request $request)
    {
        $user = $request->user();
        
        // Generate fresh recommendations
        $this->recommendationEngine->generateRecommendations($user);
        
        // Return active recommendations
        $recommendations = $this->recommendationEngine->getActiveRecommendations($user);
        
        return response()->json($recommendations);
    }
    
    public function dismissRecommendation(Request $request, int $id)
    {
        $user = $request->user();
        $success = $this->recommendationEngine->dismissRecommendation($id, $user);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Recommendation dismissed' : 'Failed to dismiss recommendation',
        ]);
    }
    
    public function predictions(Request $request)
    {
        $user = $request->user();
        $months = $request->input('months', 12);
        
        return response()->json(
            $this->predictiveService->predictEarnings($user, $months)
        );
    }
    
    public function growthPotential(Request $request)
    {
        $user = $request->user();
        
        return response()->json(
            $this->predictiveService->calculateGrowthPotential($user)
        );
    }
    
    public function churnRisk(Request $request)
    {
        $user = $request->user();
        
        return response()->json(
            $this->predictiveService->calculateChurnRisk($user)
        );
    }
}
