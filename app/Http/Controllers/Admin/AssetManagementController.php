<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AssetManagementAdministrationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;

class AssetManagementController extends Controller
{
    public function __construct(
        protected AssetManagementAdministrationService $assetAdminService
    ) {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display the asset management dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        
        return Inertia::render('Admin/Assets/Dashboard', [
            'overview' => $this->assetAdminService->getOverviewMetrics($period),
            'inventoryStats' => $this->assetAdminService->getInventoryStatistics(),
            'allocationMetrics' => $this->assetAdminService->getAllocationMetrics($period),
            'maintenanceAlerts' => $this->assetAdminService->getMaintenanceAlerts(),
            'performanceMetrics' => $this->assetAdminService->getPerformanceMetrics($period),
            'period' => $period
        ]);
    }

    /**
     * Display asset inventory management interface
     */
    public function inventory(Request $request)
    {
        $filters = $request->only(['type', 'status', 'search', 'value_min', 'value_max']);
        
        return Inertia::render('Admin/Assets/Inventory', [
            'assets' => $this->assetAdminService->getAssetsWithFilters($filters),
            'filters' => $filters,
            'statistics' => $this->assetAdminService->getInventoryStatistics(),
            'assetTypes' => $this->assetAdminService->getAssetTypes(),
        ]);
    }

    /**
     * Display asset allocation oversight interface
     */
    public function allocations(Request $request)
    {
        $filters = $request->only(['status', 'asset_type', 'user_search', 'date_from', 'date_to']);
        
        return Inertia::render('Admin/Assets/Allocations', [
            'allocations' => $this->assetAdminService->getAllocationsWithFilters($filters),
            'filters' => $filters,
            'statistics' => $this->assetAdminService->getAllocationStatistics(),
            'eligibilityMetrics' => $this->assetAdminService->getEligibilityMetrics(),
        ]);
    }

    /**
     * Display maintenance monitoring interface
     */
    public function maintenance(Request $request)
    {
        $period = $request->get('period', 'month');
        
        return Inertia::render('Admin/Assets/Maintenance', [
            'maintenanceData' => $this->assetAdminService->getMaintenanceData($period),
            'violationAlerts' => $this->assetAdminService->getViolationAlerts(),
            'complianceMetrics' => $this->assetAdminService->getMaintenanceCompliance(),
            'schedules' => $this->assetAdminService->getMaintenanceSchedules(),
        ]);
    }

    /**
     * Create new asset
     */
    public function createAsset(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:SMARTPHONE,TABLET,MOTORBIKE,CAR,PROPERTY',
            'model' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'serial_number' => 'nullable|string|max:255',
        ]);

        try {
            $asset = $this->assetAdminService->createAsset(
                $request->type,
                $request->model,
                $request->value,
                $request->description,
                $request->serial_number,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Asset created successfully',
                'data' => $asset
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create asset: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update asset information
     */
    public function updateAsset(Request $request, int $assetId): JsonResponse
    {
        $request->validate([
            'model' => 'sometimes|string|max:255',
            'value' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'serial_number' => 'nullable|string|max:255',
            'status' => 'sometimes|in:AVAILABLE,ALLOCATED,TRANSFERRED,MAINTENANCE',
        ]);

        try {
            $asset = $this->assetAdminService->updateAsset(
                $assetId,
                $request->only(['model', 'value', 'description', 'serial_number', 'status']),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Asset updated successfully',
                'data' => $asset
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update asset: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Manually allocate asset to user
     */
    public function allocateAsset(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id' => 'required|exists:physical_rewards,id',
            'user_id' => 'required|exists:users,id',
            'maintenance_period' => 'required|integer|min:1|max:24',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $allocation = $this->assetAdminService->manuallyAllocateAsset(
                $request->asset_id,
                $request->user_id,
                $request->maintenance_period,
                $request->reason,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Asset allocated successfully',
                'data' => $allocation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to allocate asset: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Process asset ownership transfer
     */
    public function transferOwnership(Request $request, int $allocationId): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $result = $this->assetAdminService->processOwnershipTransfer(
                $allocationId,
                $request->reason,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Ownership transferred successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer ownership: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Handle maintenance violation
     */
    public function handleViolation(Request $request, int $allocationId): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:warning,recovery,extension',
            'reason' => 'required|string|max:500',
            'extension_months' => 'required_if:action,extension|integer|min:1|max:12',
        ]);

        try {
            $result = $this->assetAdminService->handleMaintenanceViolation(
                $allocationId,
                $request->action,
                $request->reason,
                $request->extension_months,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Violation handled successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to handle violation: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get asset details for modal
     */
    public function getAssetDetails(int $assetId): JsonResponse
    {
        try {
            $details = $this->assetAdminService->getAssetDetails($assetId);

            return response()->json([
                'success' => true,
                'data' => $details
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Asset not found'
            ], 404);
        }
    }

    /**
     * Get allocation details for modal
     */
    public function getAllocationDetails(int $allocationId): JsonResponse
    {
        try {
            $details = $this->assetAdminService->getAllocationDetails($allocationId);

            return response()->json([
                'success' => true,
                'data' => $details
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Allocation not found'
            ], 404);
        }
    }

    /**
     * Bulk update asset status
     */
    public function bulkUpdateAssets(Request $request): JsonResponse
    {
        $request->validate([
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'exists:physical_rewards,id',
            'action' => 'required|in:mark_available,mark_maintenance,update_value',
            'value' => 'required_if:action,update_value|numeric|min:0',
        ]);

        try {
            $result = $this->assetAdminService->bulkUpdateAssets(
                $request->asset_ids,
                $request->action,
                $request->value,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully updated " . count($request->asset_ids) . " assets",
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assets: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Export asset inventory
     */
    public function exportInventory(Request $request)
    {
        $filters = $request->only(['type', 'status', 'value_min', 'value_max']);
        
        return $this->assetAdminService->exportInventory($filters);
    }

    /**
     * Export asset allocations
     */
    public function exportAllocations(Request $request)
    {
        $filters = $request->only(['status', 'asset_type', 'date_from', 'date_to']);
        
        return $this->assetAdminService->exportAllocations($filters);
    }

    /**
     * Export maintenance report
     */
    public function exportMaintenanceReport(Request $request)
    {
        $period = $request->get('period', 'month');
        
        return $this->assetAdminService->exportMaintenanceReport($period);
    }

    /**
     * Get real-time dashboard data
     */
    public function getDashboardData(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        return response()->json([
            'overview' => $this->assetAdminService->getOverviewMetrics($period),
            'alerts' => $this->assetAdminService->getMaintenanceAlerts(),
            'recentActivity' => $this->assetAdminService->getRecentActivity(),
        ]);
    }

    /**
     * Generate asset performance report
     */
    public function generatePerformanceReport(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'required|in:week,month,quarter,year',
            'asset_type' => 'nullable|in:SMARTPHONE,TABLET,MOTORBIKE,CAR,PROPERTY',
        ]);

        try {
            $report = $this->assetAdminService->generatePerformanceReport(
                $request->period,
                $request->asset_type
            );

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Check user eligibility for asset
     */
    public function checkUserEligibility(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'asset_type' => 'required|in:SMARTPHONE,TABLET,MOTORBIKE,CAR,PROPERTY',
        ]);

        try {
            $eligibility = $this->assetAdminService->checkUserEligibility(
                $request->user_id,
                $request->asset_type
            );

            return response()->json([
                'success' => true,
                'data' => $eligibility
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check eligibility: ' . $e->getMessage()
            ], 400);
        }
    }
}