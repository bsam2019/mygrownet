<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TransactionBasedFinancialReportingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;

/**
 * Transaction-Based Financial Reporting Controller
 * 
 * Provides financial reports using the transactions table as single source of truth.
 * Replaces the legacy FinancialReportingController with transaction-based reporting.
 */
class TransactionBasedFinancialReportingController extends Controller
{
    public function __construct(
        protected TransactionBasedFinancialReportingService $reportingService
    ) {
        // Middleware applied in routes
    }

    /**
     * Display the financial reporting dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        
        return Inertia::render('Admin/Financial/ReportsDashboard', [
            'initialPeriod' => $period,
        ]);
    }

    /**
     * Get financial overview (API endpoint)
     */
    public function getOverview(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        try {
            $overview = $this->reportingService->getFinancialOverview($period);

            return response()->json([
                'success' => true,
                'data' => $overview,
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get financial overview: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get transaction breakdown
     */
    public function getTransactionBreakdown(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        try {
            $breakdown = $this->reportingService->getTransactionBreakdown($period);

            return response()->json([
                'success' => true,
                'data' => $breakdown,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get transaction breakdown: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get revenue by module
     */
    public function getRevenueByModule(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        try {
            $revenue = $this->reportingService->getRevenueByModule($period);

            return response()->json([
                'success' => true,
                'data' => $revenue,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get revenue by module: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get transaction trends
     */
    public function getTransactionTrends(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        try {
            $trends = $this->reportingService->getTransactionTrends($period);

            return response()->json([
                'success' => true,
                'data' => $trends,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get transaction trends: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get user financial summary
     */
    public function getUserSummary(Request $request, int $userId): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        try {
            $summary = $this->reportingService->getUserFinancialSummary($userId, $period);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user summary: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get compliance metrics
     */
    public function getComplianceMetrics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        try {
            $metrics = $this->reportingService->getComplianceMetrics($period);

            return response()->json([
                'success' => true,
                'data' => $metrics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get compliance metrics: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get withdrawal statistics
     */
    public function getWithdrawalStatistics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        try {
            $stats = $this->reportingService->getWithdrawalStatistics($period);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get withdrawal statistics: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get top revenue sources
     */
    public function getTopRevenueSources(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $limit = $request->get('limit', 10);
        
        try {
            $sources = $this->reportingService->getTopRevenueSources($period, $limit);

            return response()->json([
                'success' => true,
                'data' => $sources,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get top revenue sources: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Clear reporting cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            $this->reportingService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Financial reporting cache cleared successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 400);
        }
    }
}
