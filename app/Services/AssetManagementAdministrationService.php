<?php

namespace App\Services;

use App\Models\User;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetManagementAdministrationService
{
    /**
     * Get overview metrics for the dashboard
     */
    public function getOverviewMetrics(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);
        
        return [
            'total_assets' => $this->getTotalAssets(),
            'allocated_assets' => $this->getAllocatedAssets($startDate),
            'available_assets' => $this->getAvailableAssets(),
            'maintenance_violations' => $this->getMaintenanceViolations(),
            'asset_value' => $this->getTotalAssetValue(),
            'allocation_rate' => $this->getAllocationRate($startDate),
        ];
    }

    /**
     * Get inventory statistics
     */
    public function getInventoryStatistics(): array
    {
        $stats = PhysicalReward::selectRaw('
            type,
            status,
            COUNT(*) as count,
            SUM(value) as total_value,
            AVG(value) as avg_value
        ')
        ->groupBy(['type', 'status'])
        ->get();

        return [
            'by_type' => $this->groupStatsByType($stats),
            'by_status' => $this->groupStatsByStatus($stats),
            'total_inventory_value' => $stats->sum('total_value'),
            'asset_distribution' => $this->getAssetDistribution(),
        ];
    }

    /**
     * Get allocation metrics
     */
    public function getAllocationMetrics(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);
        
        return [
            'new_allocations' => $this->getNewAllocations($startDate),
            'completed_transfers' => $this->getCompletedTransfers($startDate),
            'violation_rate' => $this->getViolationRate($startDate),
            'average_maintenance_period' => $this->getAverageMaintenancePeriod(),
            'eligibility_metrics' => $this->getEligibilityMetrics(),
        ];
    }

    /**
     * Get maintenance alerts
     */
    public function getMaintenanceAlerts(): array
    {
        $alerts = [];
        
        // Assets nearing maintenance deadline
        $nearingDeadline = PhysicalRewardAllocation::with(['user', 'asset'])
            ->where('status', 'ACTIVE')
            ->whereRaw('DATE_ADD(allocated_at, INTERVAL maintenance_period MONTH) <= DATE_ADD(NOW(), INTERVAL 30 DAY)')
            ->count();
            
        if ($nearingDeadline > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Maintenance Deadlines Approaching',
                'message' => "{$nearingDeadline} assets have maintenance deadlines within 30 days",
                'action' => 'Review Maintenance',
                'url' => route('admin.assets.maintenance')
            ];
        }
        
        // Overdue maintenance
        $overdue = PhysicalRewardAllocation::where('status', 'ACTIVE')
            ->whereRaw('DATE_ADD(allocated_at, INTERVAL maintenance_period MONTH) < NOW()')
            ->count();
            
        if ($overdue > 0) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'Overdue Maintenance',
                'message' => "{$overdue} assets have overdue maintenance requirements",
                'action' => 'Handle Violations',
                'url' => route('admin.assets.maintenance')
            ];
        }
        
        // Low inventory
        $lowInventory = PhysicalReward::selectRaw('type, COUNT(*) as available_count')
            ->where('status', 'AVAILABLE')
            ->groupBy('type')
            ->having('available_count', '<', 5)
            ->get();
            
        if ($lowInventory->count() > 0) {
            $types = $lowInventory->pluck('type')->join(', ');
            $alerts[] = [
                'type' => 'info',
                'title' => 'Low Inventory Alert',
                'message' => "Low inventory for asset types: {$types}",
                'action' => 'Manage Inventory',
                'url' => route('admin.assets.inventory')
            ];
        }
        
        return $alerts;
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);
        
        return [
            'allocation_efficiency' => $this->getAllocationEfficiency($startDate),
            'maintenance_compliance' => $this->getMaintenanceCompliance(),
            'asset_utilization' => $this->getAssetUtilization(),
            'cost_per_allocation' => $this->getCostPerAllocation($startDate),
        ];
    }

    /**
     * Get assets with filters
     */
    public function getAssetsWithFilters(array $filters): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = PhysicalReward::with(['currentAllocation.user'])
            ->orderBy('created_at', 'desc');
        
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('model', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('serial_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        if (!empty($filters['value_min'])) {
            $query->where('value', '>=', $filters['value_min']);
        }
        
        if (!empty($filters['value_max'])) {
            $query->where('value', '<=', $filters['value_max']);
        }
        
        return $query->paginate(25);
    }

    /**
     * Get allocations with filters
     */
    public function getAllocationsWithFilters(array $filters): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = PhysicalRewardAllocation::with(['user', 'asset'])
            ->orderBy('allocated_at', 'desc');
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['asset_type'])) {
            $query->whereHas('asset', function ($q) use ($filters) {
                $q->where('type', $filters['asset_type']);
            });
        }
        
        if (!empty($filters['user_search'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['user_search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['user_search'] . '%');
            });
        }
        
        if (!empty($filters['date_from'])) {
            $query->where('allocated_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('allocated_at', '<=', $filters['date_to']);
        }
        
        return $query->paginate(25);
    }

    /**
     * Create new asset
     */
    public function createAsset(
        string $type,
        string $model,
        float $value,
        ?string $description,
        ?string $serialNumber,
        int $adminId
    ): PhysicalReward {
        DB::beginTransaction();
        
        try {
            $asset = PhysicalReward::create([
                'type' => $type,
                'model' => $model,
                'value' => $value,
                'description' => $description,
                'serial_number' => $serialNumber,
                'status' => 'AVAILABLE',
                'created_by' => $adminId,
            ]);
            
            Log::info('Asset created', [
                'asset_id' => $asset->id,
                'type' => $type,
                'model' => $model,
                'value' => $value,
                'admin_id' => $adminId,
            ]);
            
            DB::commit();
            
            return $asset;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update asset information
     */
    public function updateAsset(int $assetId, array $data, int $adminId): PhysicalReward
    {
        $asset = PhysicalReward::findOrFail($assetId);
        
        DB::beginTransaction();
        
        try {
            $originalData = $asset->toArray();
            
            $asset->update(array_filter($data));
            
            Log::info('Asset updated', [
                'asset_id' => $assetId,
                'changes' => array_diff_assoc($data, $originalData),
                'admin_id' => $adminId,
            ]);
            
            DB::commit();
            
            return $asset->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Manually allocate asset to user
     */
    public function manuallyAllocateAsset(
        int $assetId,
        int $userId,
        int $maintenancePeriod,
        string $reason,
        int $adminId
    ): PhysicalRewardAllocation {
        $asset = PhysicalReward::findOrFail($assetId);
        $user = User::findOrFail($userId);
        
        if ($asset->status !== 'AVAILABLE') {
            throw new \Exception('Asset is not available for allocation');
        }
        
        DB::beginTransaction();
        
        try {
            // Update asset status
            $asset->update([
                'status' => 'ALLOCATED',
                'owner_id' => $userId,
                'allocated_at' => now(),
            ]);
            
            // Create allocation record
            $allocation = PhysicalRewardAllocation::create([
                'user_id' => $userId,
                'physical_reward_id' => $assetId,
                'qualifying_tier_id' => $user->current_tier_id,
                'qualifying_volume' => $user->current_team_volume ?? 0,
                'maintenance_period' => $maintenancePeriod,
                'status' => 'ACTIVE',
                'allocated_at' => now(),
                'allocated_by' => $adminId,
                'allocation_reason' => $reason,
            ]);
            
            Log::info('Asset manually allocated', [
                'asset_id' => $assetId,
                'user_id' => $userId,
                'maintenance_period' => $maintenancePeriod,
                'reason' => $reason,
                'admin_id' => $adminId,
            ]);
            
            DB::commit();
            
            return $allocation;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process ownership transfer
     */
    public function processOwnershipTransfer(int $allocationId, string $reason, int $adminId): array
    {
        $allocation = PhysicalRewardAllocation::with(['asset', 'user'])->findOrFail($allocationId);
        
        if ($allocation->status !== 'ACTIVE') {
            throw new \Exception('Allocation is not active');
        }
        
        // Check if maintenance period is completed
        $maintenanceDeadline = $allocation->allocated_at->addMonths($allocation->maintenance_period);
        if (now()->lt($maintenanceDeadline)) {
            throw new \Exception('Maintenance period not yet completed');
        }
        
        DB::beginTransaction();
        
        try {
            // Update allocation status
            $allocation->update([
                'status' => 'COMPLETED',
                'completed_at' => now(),
                'transfer_reason' => $reason,
                'transferred_by' => $adminId,
            ]);
            
            // Update asset status
            $allocation->asset->update([
                'status' => 'TRANSFERRED',
                'transferred_at' => now(),
            ]);
            
            Log::info('Asset ownership transferred', [
                'allocation_id' => $allocationId,
                'asset_id' => $allocation->physical_reward_id,
                'user_id' => $allocation->user_id,
                'reason' => $reason,
                'admin_id' => $adminId,
            ]);
            
            DB::commit();
            
            return [
                'allocation' => $allocation->fresh(),
                'asset' => $allocation->asset->fresh(),
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle maintenance violation
     */
    public function handleMaintenanceViolation(
        int $allocationId,
        string $action,
        string $reason,
        ?int $extensionMonths,
        int $adminId
    ): array {
        $allocation = PhysicalRewardAllocation::with(['asset', 'user'])->findOrFail($allocationId);
        
        DB::beginTransaction();
        
        try {
            $result = [];
            
            switch ($action) {
                case 'warning':
                    $allocation->update([
                        'violation_warnings' => ($allocation->violation_warnings ?? 0) + 1,
                        'last_warning_at' => now(),
                        'warning_reason' => $reason,
                        'warned_by' => $adminId,
                    ]);
                    $result['action'] = 'Warning issued';
                    break;
                    
                case 'recovery':
                    $allocation->update([
                        'status' => 'FORFEITED',
                        'forfeited_at' => now(),
                        'forfeit_reason' => $reason,
                        'forfeited_by' => $adminId,
                    ]);
                    
                    $allocation->asset->update([
                        'status' => 'AVAILABLE',
                        'owner_id' => null,
                        'allocated_at' => null,
                    ]);
                    
                    $result['action'] = 'Asset recovered';
                    break;
                    
                case 'extension':
                    $allocation->update([
                        'maintenance_period' => $allocation->maintenance_period + $extensionMonths,
                        'extension_granted' => $extensionMonths,
                        'extension_reason' => $reason,
                        'extended_by' => $adminId,
                        'extended_at' => now(),
                    ]);
                    $result['action'] = "Maintenance period extended by {$extensionMonths} months";
                    break;
            }
            
            Log::info('Maintenance violation handled', [
                'allocation_id' => $allocationId,
                'action' => $action,
                'reason' => $reason,
                'extension_months' => $extensionMonths,
                'admin_id' => $adminId,
            ]);
            
            DB::commit();
            
            $result['allocation'] = $allocation->fresh();
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get asset details
     */
    public function getAssetDetails(int $assetId): array
    {
        $asset = PhysicalReward::with(['currentAllocation.user', 'allocationHistory.user'])
            ->findOrFail($assetId);
        
        return [
            'asset' => $asset,
            'current_allocation' => $asset->currentAllocation,
            'allocation_history' => $asset->allocationHistory,
            'maintenance_status' => $this->getAssetMaintenanceStatus($asset),
            'performance_metrics' => $this->getAssetPerformanceMetrics($asset),
        ];
    }

    /**
     * Get allocation details
     */
    public function getAllocationDetails(int $allocationId): array
    {
        $allocation = PhysicalRewardAllocation::with(['user', 'asset', 'qualifyingTier'])
            ->findOrFail($allocationId);
        
        return [
            'allocation' => $allocation,
            'user_stats' => $this->getUserAssetStats($allocation->user_id),
            'maintenance_timeline' => $this->getMaintenanceTimeline($allocation),
            'eligibility_check' => $this->checkAllocationEligibility($allocation),
        ];
    }

    /**
     * Bulk update assets
     */
    public function bulkUpdateAssets(array $assetIds, string $action, ?float $value, int $adminId): array
    {
        $assets = PhysicalReward::whereIn('id', $assetIds)->get();
        $processed = [];
        $failed = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($assets as $asset) {
                try {
                    switch ($action) {
                        case 'mark_available':
                            if ($asset->status === 'ALLOCATED') {
                                throw new \Exception('Cannot mark allocated asset as available');
                            }
                            $asset->update(['status' => 'AVAILABLE']);
                            break;
                            
                        case 'mark_maintenance':
                            $asset->update(['status' => 'MAINTENANCE']);
                            break;
                            
                        case 'update_value':
                            $asset->update(['value' => $value]);
                            break;
                    }
                    
                    $processed[] = $asset->id;
                } catch (\Exception $e) {
                    $failed[] = [
                        'asset_id' => $asset->id,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            Log::info('Bulk asset update completed', [
                'action' => $action,
                'processed_count' => count($processed),
                'failed_count' => count($failed),
                'admin_id' => $adminId,
            ]);
            
            DB::commit();
            
            return [
                'processed' => $processed,
                'failed' => $failed,
                'summary' => [
                    'total' => count($assetIds),
                    'processed' => count($processed),
                    'failed' => count($failed),
                ]
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Export inventory data
     */
    public function exportInventory(array $filters): StreamedResponse
    {
        $filename = 'asset_inventory_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return new StreamedResponse(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($handle, [
                'ID', 'Type', 'Model', 'Serial Number', 'Value', 'Status', 
                'Owner', 'Allocated At', 'Created At', 'Description'
            ]);
            
            // Get assets in chunks
            PhysicalReward::with(['currentAllocation.user'])
                ->when(!empty($filters['type']), fn($q) => $q->where('type', $filters['type']))
                ->when(!empty($filters['status']), fn($q) => $q->where('status', $filters['status']))
                ->when(!empty($filters['value_min']), fn($q) => $q->where('value', '>=', $filters['value_min']))
                ->when(!empty($filters['value_max']), fn($q) => $q->where('value', '<=', $filters['value_max']))
                ->chunk(1000, function ($assets) use ($handle) {
                    foreach ($assets as $asset) {
                        fputcsv($handle, [
                            $asset->id,
                            $asset->type,
                            $asset->model,
                            $asset->serial_number ?? 'N/A',
                            $asset->value,
                            $asset->status,
                            $asset->currentAllocation?->user?->name ?? 'N/A',
                            $asset->allocated_at?->format('Y-m-d H:i:s') ?? 'N/A',
                            $asset->created_at->format('Y-m-d H:i:s'),
                            $asset->description ?? 'N/A',
                        ]);
                    }
                });
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export allocations data
     */
    public function exportAllocations(array $filters): StreamedResponse
    {
        $filename = 'asset_allocations_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return new StreamedResponse(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($handle, [
                'ID', 'User', 'Asset Type', 'Asset Model', 'Asset Value', 'Status',
                'Maintenance Period', 'Allocated At', 'Completed At', 'Qualifying Tier', 'Team Volume'
            ]);
            
            // Get allocations in chunks
            PhysicalRewardAllocation::with(['user', 'asset', 'qualifyingTier'])
                ->when(!empty($filters['status']), fn($q) => $q->where('status', $filters['status']))
                ->when(!empty($filters['asset_type']), fn($q) => $q->whereHas('asset', fn($sq) => $sq->where('type', $filters['asset_type'])))
                ->when(!empty($filters['date_from']), fn($q) => $q->where('allocated_at', '>=', $filters['date_from']))
                ->when(!empty($filters['date_to']), fn($q) => $q->where('allocated_at', '<=', $filters['date_to']))
                ->chunk(1000, function ($allocations) use ($handle) {
                    foreach ($allocations as $allocation) {
                        fputcsv($handle, [
                            $allocation->id,
                            $allocation->user->name,
                            $allocation->asset->type,
                            $allocation->asset->model,
                            $allocation->asset->value,
                            $allocation->status,
                            $allocation->maintenance_period . ' months',
                            $allocation->allocated_at->format('Y-m-d H:i:s'),
                            $allocation->completed_at?->format('Y-m-d H:i:s') ?? 'N/A',
                            $allocation->qualifyingTier?->name ?? 'N/A',
                            $allocation->qualifying_volume,
                        ]);
                    }
                });
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // Helper methods
    
    private function getPeriodStartDate(string $period): Carbon
    {
        return match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };
    }

    private function getTotalAssets(): array
    {
        $current = PhysicalReward::count();
        $previous = PhysicalReward::where('created_at', '<', now()->startOfMonth())->count();
        
        return [
            'current' => $current,
            'previous' => $previous,
            'change_percent' => $previous > 0 ? (($current - $previous) / $previous) * 100 : 0,
        ];
    }

    private function getAllocatedAssets(Carbon $startDate): array
    {
        $current = PhysicalRewardAllocation::where('allocated_at', '>=', $startDate)->count();
        
        return [
            'current' => $current,
            'total' => PhysicalRewardAllocation::count(),
        ];
    }

    private function getAvailableAssets(): array
    {
        $available = PhysicalReward::where('status', 'AVAILABLE')->count();
        $total = PhysicalReward::count();
        
        return [
            'count' => $available,
            'percentage' => $total > 0 ? ($available / $total) * 100 : 0,
        ];
    }

    private function getMaintenanceViolations(): array
    {
        $violations = PhysicalRewardAllocation::where('status', 'ACTIVE')
            ->whereRaw('DATE_ADD(allocated_at, INTERVAL maintenance_period MONTH) < NOW()')
            ->count();
        
        return [
            'count' => $violations,
            'severity' => $violations > 10 ? 'high' : ($violations > 5 ? 'medium' : 'low'),
        ];
    }

    private function getTotalAssetValue(): array
    {
        $totalValue = PhysicalReward::sum('value');
        $allocatedValue = PhysicalReward::where('status', 'ALLOCATED')->sum('value');
        
        return [
            'total' => $totalValue,
            'allocated' => $allocatedValue,
            'available' => $totalValue - $allocatedValue,
        ];
    }

    private function getAllocationRate(Carbon $startDate): float
    {
        $totalAssets = PhysicalReward::count();
        $allocatedAssets = PhysicalReward::where('status', 'ALLOCATED')->count();
        
        return $totalAssets > 0 ? ($allocatedAssets / $totalAssets) * 100 : 0;
    }

    private function groupStatsByType(Collection $stats): array
    {
        return $stats->groupBy('type')->map(function ($group) {
            return [
                'count' => $group->sum('count'),
                'total_value' => $group->sum('total_value'),
                'avg_value' => $group->avg('avg_value'),
            ];
        })->toArray();
    }

    private function groupStatsByStatus(Collection $stats): array
    {
        return $stats->groupBy('status')->map(function ($group) {
            return [
                'count' => $group->sum('count'),
                'total_value' => $group->sum('total_value'),
                'avg_value' => $group->avg('avg_value'),
            ];
        })->toArray();
    }

    private function getAssetDistribution(): array
    {
        return PhysicalReward::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();
    }

    private function getNewAllocations(Carbon $startDate): int
    {
        return PhysicalRewardAllocation::where('allocated_at', '>=', $startDate)->count();
    }

    private function getCompletedTransfers(Carbon $startDate): int
    {
        return PhysicalRewardAllocation::where('completed_at', '>=', $startDate)->count();
    }

    private function getViolationRate(Carbon $startDate): float
    {
        $totalAllocations = PhysicalRewardAllocation::where('allocated_at', '>=', $startDate)->count();
        $violations = PhysicalRewardAllocation::where('allocated_at', '>=', $startDate)
            ->whereRaw('DATE_ADD(allocated_at, INTERVAL maintenance_period MONTH) < NOW()')
            ->count();
        
        return $totalAllocations > 0 ? ($violations / $totalAllocations) * 100 : 0;
    }

    private function getAverageMaintenancePeriod(): float
    {
        return PhysicalRewardAllocation::avg('maintenance_period') ?? 0;
    }

    public function getEligibilityMetrics(): array
    {
        // Calculate eligibility metrics based on tier requirements
        $tiers = InvestmentTier::all();
        $metrics = [];
        
        foreach ($tiers as $tier) {
            $eligibleUsers = User::where('current_tier_id', $tier->id)
                ->where('current_team_volume', '>=', $tier->minimum_volume ?? 0)
                ->count();
            
            $metrics[$tier->name] = [
                'eligible_users' => $eligibleUsers,
                'allocated_assets' => PhysicalRewardAllocation::whereHas('user', function ($q) use ($tier) {
                    $q->where('current_tier_id', $tier->id);
                })->count(),
            ];
        }
        
        return $metrics;
    }

    private function getAllocationEfficiency(Carbon $startDate): float
    {
        $availableAssets = PhysicalReward::where('status', 'AVAILABLE')->count();
        $totalAssets = PhysicalReward::count();
        
        return $totalAssets > 0 ? (($totalAssets - $availableAssets) / $totalAssets) * 100 : 0;
    }

    public function getMaintenanceCompliance(): float
    {
        $totalAllocations = PhysicalRewardAllocation::where('status', 'ACTIVE')->count();
        $compliantAllocations = PhysicalRewardAllocation::where('status', 'ACTIVE')
            ->whereRaw('DATE_ADD(allocated_at, INTERVAL maintenance_period MONTH) >= NOW()')
            ->count();
        
        return $totalAllocations > 0 ? ($compliantAllocations / $totalAllocations) * 100 : 100;
    }

    private function getAssetUtilization(): array
    {
        $total = PhysicalReward::count();
        $allocated = PhysicalReward::where('status', 'ALLOCATED')->count();
        $available = PhysicalReward::where('status', 'AVAILABLE')->count();
        $maintenance = PhysicalReward::where('status', 'MAINTENANCE')->count();
        
        return [
            'allocated_percentage' => $total > 0 ? ($allocated / $total) * 100 : 0,
            'available_percentage' => $total > 0 ? ($available / $total) * 100 : 0,
            'maintenance_percentage' => $total > 0 ? ($maintenance / $total) * 100 : 0,
        ];
    }

    private function getCostPerAllocation(Carbon $startDate): float
    {
        $allocations = PhysicalRewardAllocation::where('allocated_at', '>=', $startDate)->count();
        $totalValue = PhysicalRewardAllocation::where('allocated_at', '>=', $startDate)
            ->join('physical_rewards', 'physical_reward_allocations.physical_reward_id', '=', 'physical_rewards.id')
            ->sum('physical_rewards.value');
        
        return $allocations > 0 ? $totalValue / $allocations : 0;
    }

    public function getAssetTypes(): array
    {
        return [
            'SMARTPHONE' => 'Smartphone',
            'TABLET' => 'Tablet',
            'MOTORBIKE' => 'Motorbike',
            'CAR' => 'Car',
            'PROPERTY' => 'Property',
        ];
    }

    public function getAllocationStatistics(): array
    {
        return [
            'total_allocations' => PhysicalRewardAllocation::count(),
            'active_allocations' => PhysicalRewardAllocation::where('status', 'ACTIVE')->count(),
            'completed_allocations' => PhysicalRewardAllocation::where('status', 'COMPLETED')->count(),
            'forfeited_allocations' => PhysicalRewardAllocation::where('status', 'FORFEITED')->count(),
        ];
    }

    public function getMaintenanceData(string $period): array
    {
        $startDate = $this->getPeriodStartDate($period);
        
        return [
            'compliance_rate' => $this->getMaintenanceCompliance(),
            'violations_count' => $this->getMaintenanceViolations()['count'],
            'upcoming_deadlines' => $this->getUpcomingDeadlines(),
            'maintenance_trends' => $this->getMaintenanceTrends($startDate),
        ];
    }

    public function getViolationAlerts(): array
    {
        return PhysicalRewardAllocation::with(['user', 'asset'])
            ->where('status', 'ACTIVE')
            ->whereRaw('DATE_ADD(allocated_at, INTERVAL maintenance_period MONTH) < NOW()')
            ->orderBy('allocated_at')
            ->limit(10)
            ->get()
            ->map(function ($allocation) {
                return [
                    'allocation_id' => $allocation->id,
                    'user_name' => $allocation->user->name,
                    'asset_type' => $allocation->asset->type,
                    'asset_model' => $allocation->asset->model,
                    'days_overdue' => now()->diffInDays($allocation->allocated_at->addMonths($allocation->maintenance_period)),
                    'maintenance_deadline' => $allocation->allocated_at->addMonths($allocation->maintenance_period),
                ];
            })
            ->toArray();
    }

    public function getMaintenanceSchedules(): array
    {
        return PhysicalRewardAllocation::with(['user', 'asset'])
            ->where('status', 'ACTIVE')
            ->whereRaw('DATE_ADD(allocated_at, INTERVAL maintenance_period MONTH) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 90 DAY)')
            ->orderBy(DB::raw('DATE_ADD(allocated_at, INTERVAL maintenance_period MONTH)'))
            ->get()
            ->map(function ($allocation) {
                return [
                    'allocation_id' => $allocation->id,
                    'user_name' => $allocation->user->name,
                    'asset_type' => $allocation->asset->type,
                    'asset_model' => $allocation->asset->model,
                    'maintenance_deadline' => $allocation->allocated_at->addMonths($allocation->maintenance_period),
                    'days_remaining' => now()->diffInDays($allocation->allocated_at->addMonths($allocation->maintenance_period), false),
                ];
            })
            ->toArray();
    }

    private function getUpcomingDeadlines(): int
    {
        return PhysicalRewardAllocation::where('status', 'ACTIVE')
            ->whereRaw('DATE_ADD(allocated_at, INTERVAL maintenance_period MONTH) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)')
            ->count();
    }

    private function getMaintenanceTrends(Carbon $startDate): array
    {
        // Implementation for maintenance trends analysis
        return [
            'compliance_trend' => 'stable', // Placeholder
            'violation_trend' => 'decreasing', // Placeholder
        ];
    }

    private function getAssetMaintenanceStatus(PhysicalReward $asset): array
    {
        if (!$asset->currentAllocation) {
            return ['status' => 'not_allocated'];
        }
        
        $allocation = $asset->currentAllocation;
        $deadline = $allocation->allocated_at->addMonths($allocation->maintenance_period);
        
        if (now()->gt($deadline)) {
            return [
                'status' => 'overdue',
                'days_overdue' => now()->diffInDays($deadline),
            ];
        } elseif (now()->addDays(30)->gt($deadline)) {
            return [
                'status' => 'approaching',
                'days_remaining' => now()->diffInDays($deadline, false),
            ];
        } else {
            return [
                'status' => 'compliant',
                'days_remaining' => now()->diffInDays($deadline, false),
            ];
        }
    }

    private function getAssetPerformanceMetrics(PhysicalReward $asset): array
    {
        return [
            'total_allocations' => $asset->allocationHistory->count(),
            'successful_transfers' => $asset->allocationHistory->where('status', 'COMPLETED')->count(),
            'violations' => $asset->allocationHistory->where('status', 'FORFEITED')->count(),
            'average_maintenance_period' => $asset->allocationHistory->avg('maintenance_period'),
        ];
    }

    private function getUserAssetStats(int $userId): array
    {
        return [
            'total_allocations' => PhysicalRewardAllocation::where('user_id', $userId)->count(),
            'active_allocations' => PhysicalRewardAllocation::where('user_id', $userId)->where('status', 'ACTIVE')->count(),
            'completed_transfers' => PhysicalRewardAllocation::where('user_id', $userId)->where('status', 'COMPLETED')->count(),
            'violations' => PhysicalRewardAllocation::where('user_id', $userId)->where('status', 'FORFEITED')->count(),
        ];
    }

    private function getMaintenanceTimeline(PhysicalRewardAllocation $allocation): array
    {
        $timeline = [];
        
        $timeline[] = [
            'event' => 'Asset Allocated',
            'date' => $allocation->allocated_at,
            'description' => "Asset allocated for {$allocation->maintenance_period} months maintenance period",
        ];
        
        if ($allocation->last_warning_at) {
            $timeline[] = [
                'event' => 'Warning Issued',
                'date' => $allocation->last_warning_at,
                'description' => $allocation->warning_reason,
            ];
        }
        
        if ($allocation->extended_at) {
            $timeline[] = [
                'event' => 'Period Extended',
                'date' => $allocation->extended_at,
                'description' => "Extended by {$allocation->extension_granted} months: {$allocation->extension_reason}",
            ];
        }
        
        $deadline = $allocation->allocated_at->addMonths($allocation->maintenance_period);
        $timeline[] = [
            'event' => 'Maintenance Deadline',
            'date' => $deadline,
            'description' => 'Asset maintenance period ends',
            'is_future' => now()->lt($deadline),
        ];
        
        return $timeline;
    }

    private function checkAllocationEligibility(PhysicalRewardAllocation $allocation): array
    {
        $user = $allocation->user;
        $currentVolume = $user->current_team_volume ?? 0;
        $requiredVolume = $allocation->qualifying_volume;
        
        return [
            'current_tier' => $user->currentTier?->name,
            'qualifying_tier' => $allocation->qualifyingTier?->name,
            'current_volume' => $currentVolume,
            'required_volume' => $requiredVolume,
            'volume_maintained' => $currentVolume >= $requiredVolume,
            'tier_maintained' => $user->current_tier_id === $allocation->qualifying_tier_id,
        ];
    }

    public function checkUserEligibility(int $userId, string $assetType): array
    {
        $user = User::with('currentTier')->findOrFail($userId);
        
        // Define asset eligibility requirements
        $requirements = [
            'SMARTPHONE' => ['tier' => 'Silver', 'volume' => 15000, 'months' => 3],
            'TABLET' => ['tier' => 'Silver', 'volume' => 15000, 'months' => 3],
            'MOTORBIKE' => ['tier' => 'Gold', 'volume' => 50000, 'months' => 6],
            'CAR' => ['tier' => 'Diamond', 'volume' => 150000, 'months' => 9],
            'PROPERTY' => ['tier' => 'Elite', 'volume' => 500000, 'months' => 12],
        ];
        
        $requirement = $requirements[$assetType] ?? null;
        if (!$requirement) {
            return ['eligible' => false, 'reason' => 'Invalid asset type'];
        }
        
        $currentVolume = $user->current_team_volume ?? 0;
        $tierMet = $user->currentTier && $user->currentTier->name >= $requirement['tier'];
        $volumeMet = $currentVolume >= $requirement['volume'];
        
        // Check if user already has an active allocation of this type
        $hasActiveAllocation = PhysicalRewardAllocation::where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->whereHas('asset', function ($q) use ($assetType) {
                $q->where('type', $assetType);
            })
            ->exists();
        
        return [
            'eligible' => $tierMet && $volumeMet && !$hasActiveAllocation,
            'tier_met' => $tierMet,
            'volume_met' => $volumeMet,
            'has_active_allocation' => $hasActiveAllocation,
            'current_tier' => $user->currentTier?->name,
            'required_tier' => $requirement['tier'],
            'current_volume' => $currentVolume,
            'required_volume' => $requirement['volume'],
        ];
    }

    public function generatePerformanceReport(string $period, ?string $assetType): array
    {
        $startDate = $this->getPeriodStartDate($period);
        
        $query = PhysicalRewardAllocation::with(['asset', 'user'])
            ->where('allocated_at', '>=', $startDate);
        
        if ($assetType) {
            $query->whereHas('asset', function ($q) use ($assetType) {
                $q->where('type', $assetType);
            });
        }
        
        $allocations = $query->get();
        
        return [
            'period' => $period,
            'asset_type' => $assetType,
            'total_allocations' => $allocations->count(),
            'successful_transfers' => $allocations->where('status', 'COMPLETED')->count(),
            'active_allocations' => $allocations->where('status', 'ACTIVE')->count(),
            'violations' => $allocations->where('status', 'FORFEITED')->count(),
            'average_maintenance_period' => $allocations->avg('maintenance_period'),
            'total_asset_value' => $allocations->sum('asset.value'),
            'compliance_rate' => $this->calculateComplianceRate($allocations),
        ];
    }

    private function calculateComplianceRate(Collection $allocations): float
    {
        $activeAllocations = $allocations->where('status', 'ACTIVE');
        if ($activeAllocations->isEmpty()) {
            return 100;
        }
        
        $compliant = $activeAllocations->filter(function ($allocation) {
            $deadline = $allocation->allocated_at->addMonths($allocation->maintenance_period);
            return now()->lte($deadline);
        });
        
        return ($compliant->count() / $activeAllocations->count()) * 100;
    }

    public function exportMaintenanceReport(string $period): StreamedResponse
    {
        $filename = 'maintenance_report_' . $period . '_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return new StreamedResponse(function () use ($period) {
            $handle = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($handle, [
                'Allocation ID', 'User', 'Asset Type', 'Asset Model', 'Status',
                'Allocated At', 'Maintenance Period', 'Deadline', 'Days Remaining/Overdue',
                'Warnings', 'Extensions'
            ]);
            
            $startDate = $this->getPeriodStartDate($period);
            
            PhysicalRewardAllocation::with(['user', 'asset'])
                ->where('allocated_at', '>=', $startDate)
                ->chunk(1000, function ($allocations) use ($handle) {
                    foreach ($allocations as $allocation) {
                        $deadline = $allocation->allocated_at->addMonths($allocation->maintenance_period);
                        $daysRemaining = now()->diffInDays($deadline, false);
                        
                        fputcsv($handle, [
                            $allocation->id,
                            $allocation->user->name,
                            $allocation->asset->type,
                            $allocation->asset->model,
                            $allocation->status,
                            $allocation->allocated_at->format('Y-m-d'),
                            $allocation->maintenance_period . ' months',
                            $deadline->format('Y-m-d'),
                            $daysRemaining >= 0 ? $daysRemaining . ' days remaining' : abs($daysRemaining) . ' days overdue',
                            $allocation->violation_warnings ?? 0,
                            $allocation->extension_granted ?? 0,
                        ]);
                    }
                });
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function getRecentActivity(): array
    {
        $recentAllocations = PhysicalRewardAllocation::with(['user', 'asset'])
            ->orderBy('allocated_at', 'desc')
            ->limit(5)
            ->get();
        
        $recentTransfers = PhysicalRewardAllocation::with(['user', 'asset'])
            ->where('status', 'COMPLETED')
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();
        
        $activity = [];
        
        foreach ($recentAllocations as $allocation) {
            $activity[] = [
                'type' => 'allocation',
                'description' => "{$allocation->asset->type} allocated to {$allocation->user->name}",
                'timestamp' => $allocation->allocated_at,
                'asset_value' => $allocation->asset->value,
            ];
        }
        
        foreach ($recentTransfers as $transfer) {
            $activity[] = [
                'type' => 'transfer',
                'description' => "{$transfer->asset->type} ownership transferred to {$transfer->user->name}",
                'timestamp' => $transfer->completed_at,
                'asset_value' => $transfer->asset->value,
            ];
        }
        
        return collect($activity)->sortByDesc('timestamp')->take(10)->values()->toArray();
    }
}