<?php

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Module\CheckFeatureAccessUseCase;
use App\Application\UseCases\Module\GetUsageSummaryUseCase;
use App\Application\UseCases\Module\TrackUsageUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Module Usage Controller
 * 
 * Handles API endpoints for module usage tracking and feature access.
 */
class ModuleUsageController extends Controller
{
    public function __construct(
        private GetUsageSummaryUseCase $getUsageSummaryUseCase,
        private TrackUsageUseCase $trackUsageUseCase,
        private CheckFeatureAccessUseCase $checkFeatureAccessUseCase
    ) {}

    /**
     * Get usage summary for a module
     * 
     * GET /api/modules/{moduleId}/usage
     */
    public function getUsage(Request $request, string $moduleId): JsonResponse
    {
        try {
            $summary = $this->getUsageSummaryUseCase->execute(
                $request->user(),
                $moduleId
            );

            return response()->json($summary);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Track usage for a module
     * 
     * POST /api/modules/{moduleId}/usage
     */
    public function trackUsage(Request $request, string $moduleId): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'count' => 'sometimes|integer|min:1',
        ]);

        try {
            $result = $this->trackUsageUseCase->execute(
                $request->user(),
                $moduleId,
                $validated['type'],
                $validated['count'] ?? 1
            );

            return response()->json($result);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'limit_exceeded' => true,
            ], 403);
        }
    }

    /**
     * Check feature access
     * 
     * GET /api/modules/{moduleId}/features/{feature}/access
     */
    public function checkFeatureAccess(Request $request, string $moduleId, string $feature): JsonResponse
    {
        try {
            $result = $this->checkFeatureAccessUseCase->execute(
                $request->user(),
                $moduleId,
                $feature
            );

            return response()->json($result);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get available features for user
     * 
     * GET /api/modules/{moduleId}/features
     */
    public function getFeatures(Request $request, string $moduleId): JsonResponse
    {
        try {
            $available = $this->checkFeatureAccessUseCase->getAvailableFeatures(
                $request->user(),
                $moduleId
            );

            $locked = $this->checkFeatureAccessUseCase->getLockedFeatures(
                $request->user(),
                $moduleId
            );

            return response()->json([
                'module_id' => $moduleId,
                'available_features' => $available,
                'locked_features' => $locked,
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
