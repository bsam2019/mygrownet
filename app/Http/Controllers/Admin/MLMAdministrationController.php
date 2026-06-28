<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MLMAdministrationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;

class MLMAdministrationController extends Controller
{
    public function __construct(
        protected MLMAdministrationService $mlmAdminService
    ) {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display the MLM administration dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        
        return Inertia::render('Admin/MLM/Dashboard', [
            'overview' => $this->mlmAdminService->getOverviewMetrics($period),
            'commissionStats' => $this->mlmAdminService->getCommissionStatistics($period),
            'networkAnalytics' => $this->mlmAdminService->getNetworkAnalytics(),
            'performanceMetrics' => $this->mlmAdminService->getPerformanceMetrics($period),
            'alerts' => $this->mlmAdminService->getSystemAlerts(),
            'period' => $period
        ]);
    }

    /**
     * Display commission oversight interface
     */
    public function commissions(Request $request)
    {
        $filters = $request->only(['status', 'type', 'level', 'search', 'date_from', 'date_to']);
        
        return Inertia::render('Admin/MLM/Commissions', [
            'commissions' => $this->mlmAdminService->getCommissionsWithFilters($filters),
            'filters' => $filters,
            'statistics' => $this->mlmAdminService->getCommissionStatistics(),
            'pendingCount' => $this->mlmAdminService->getPendingCommissionsCount(),
        ]);
    }

    /**
     * Display network analysis interface
     */
    public function networkAnalysis(Request $request)
    {
        $userId = $request->get('user_id');
        $depth = $request->get('depth', 5);
        
        return Inertia::render('Admin/MLM/NetworkAnalysis', [
            'networkData' => $userId ? $this->mlmAdminService->getNetworkStructure($userId, $depth) : null,
            'topPerformers' => $this->mlmAdminService->getTopPerformers(),
            'networkMetrics' => $this->mlmAdminService->getNetworkMetrics(),
            'growthTrends' => $this->mlmAdminService->getNetworkGrowthTrends(),
        ]);
    }

    /**
     * Display performance monitoring interface
     */
    public function performanceMonitoring(Request $request)
    {
        $period = $request->get('period', 'month');
        
        return Inertia::render('Admin/MLM/PerformanceMonitoring', [
            'performanceData' => $this->mlmAdminService->getPerformanceData($period),
            'tierDistribution' => $this->mlmAdminService->getTierDistribution(),
            'volumeAnalytics' => $this->mlmAdminService->getVolumeAnalytics($period),
            'complianceMetrics' => $this->mlmAdminService->getComplianceMetrics(),
        ]);
    }

    /**
     * Manually adjust commission
     */
    public function adjustCommission(Request $request): JsonResponse
    {
        $request->validate([
            'commission_id' => 'required|exists:referral_commissions,id',
            'new_amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $result = $this->mlmAdminService->adjustCommission(
                $request->commission_id,
                $request->new_amount,
                $request->reason,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Commission adjusted successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust commission: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Process pending commissions in bulk
     */
    public function processPendingCommissions(Request $request): JsonResponse
    {
        $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:referral_commissions,id',
            'action' => 'required|in:approve,reject',
        ]);

        try {
            $result = $this->mlmAdminService->processPendingCommissions(
                $request->commission_ids,
                $request->action,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully {$request->action}d " . count($request->commission_ids) . " commissions",
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process commissions: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Recalculate network structure
     */
    public function recalculateNetwork(): JsonResponse
    {
        try {
            $this->mlmAdminService->recalculateNetworkStructure();

            return response()->json([
                'success' => true,
                'message' => 'Network structure recalculation initiated'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to recalculate network: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get commission details for modal
     */
    public function getCommissionDetails(int $commissionId): JsonResponse
    {
        try {
            $details = $this->mlmAdminService->getCommissionDetails($commissionId);

            return response()->json([
                'success' => true,
                'data' => $details
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Commission not found'
            ], 404);
        }
    }

    /**
     * Export commission data
     */
    public function exportCommissions(Request $request)
    {
        $filters = $request->only(['status', 'type', 'level', 'date_from', 'date_to']);
        
        return $this->mlmAdminService->exportCommissions($filters);
    }

    /**
     * Export network analysis data
     */
    public function exportNetworkAnalysis(Request $request)
    {
        $userId = $request->get('user_id');
        $depth = $request->get('depth', 5);
        
        return $this->mlmAdminService->exportNetworkAnalysis($userId, $depth);
    }

    /**
     * Get real-time dashboard data
     */
    public function getDashboardData(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        return response()->json([
            'overview' => $this->mlmAdminService->getOverviewMetrics($period),
            'alerts' => $this->mlmAdminService->getSystemAlerts(),
            'recentActivity' => $this->mlmAdminService->getRecentActivity(),
        ]);
    }
}