<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\RecommendationEngine;
use App\Services\PredictiveAnalyticsService;

Route::get('/debug/analytics', function () {
    try {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }
        
        $analyticsService = new AnalyticsService();
        $recommendationEngine = new RecommendationEngine();
        $predictiveService = new PredictiveAnalyticsService();
        
        // Generate fresh recommendations
        $recommendationEngine->generateRecommendations($user);
        
        $performance = $analyticsService->getMemberPerformance($user);
        $recommendations = $recommendationEngine->getActiveRecommendations($user);
        $growthPotential = $predictiveService->calculateGrowthPotential($user);
        $nextMilestone = $predictiveService->getNextMilestone($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'performance' => $performance,
                'recommendations' => $recommendations,
                'growth_potential' => $growthPotential,
                'next_milestone' => $nextMilestone,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
})->middleware('auth');
