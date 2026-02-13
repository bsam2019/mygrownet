<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\AssetModel;
use App\Infrastructure\Persistence\Eloquent\CMS\AssetAssignmentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\AssetMaintenanceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\AssetDepreciationModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssetService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    public function createAsset(array $data): AssetModel
    {
        return DB::transaction(function () use ($data) {
            // Generate asset number if not provided
            if (empty($data['asset_number'])) {
                $lastAsset = AssetModel::where('company_id', $data['company_id'])
                    ->orderBy('id', 'desc')
                    ->first();
                
                $sequence = $lastAsset ? (int) substr($lastAsset->asset_number, 5) + 1 : 1;
                $data['asset_number'] = 'AUTO-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            }

            // Calculate warranty expiry if warranty months provided
            if (!empty($data['warranty_months']) && !empty($data['purchase_date'])) {
                $data['warranty_expiry'] = Carbon::parse($data['purchase_date'])
                    ->addMonths($data['warranty_months']);
            }

            // Set current value to purchase cost initially
            if (empty($data['current_value']) && !empty($data['purchase_cost'])) {
                $data['current_value'] = $data['purchase_cost'];
            }

            $asset = AssetModel::create($data);

            // Log audit trail
            $this->auditTrail->log(
                companyId: $asset->company_id,
                userId: $data['created_by'],
                entityType: 'asset',
                entityId: $asset->id,
                action: 'created',
                newValues: $asset->toArray()
            );

            return $asset;
        });
    }

    public function updateAsset(AssetModel $asset, array $data, int $userId): AssetModel
    {
        $oldValues = $asset->toArray();
        
        // Recalculate warranty expiry if warranty months changed
        if (isset($data['warranty_months']) && $asset->purchase_date) {
            $data['warranty_expiry'] = Carbon::parse($asset->purchase_date)
                ->addMonths($data['warranty_months']);
        }

        $asset->update($data);

        $this->auditTrail->log(
            companyId: $asset->company_id,
            userId: $userId,
            entityType: 'asset',
            entityId: $asset->id,
            action: 'updated',
            oldValues: $oldValues,
            newValues: $asset->fresh()->toArray()
        );

        return $asset->fresh();
    }

    public function assignAsset(array $data): AssetAssignmentModel
    {
        return DB::transaction(function () use ($data) {
            $asset = AssetModel::findOrFail($data['asset_id']);

            // Create assignment record
            $assignment = AssetAssignmentModel::create([
                'company_id' => $asset->company_id,
                'asset_id' => $asset->id,
                'assigned_to' => $data['assigned_to'],
                'assigned_date' => $data['assigned_date'] ?? now(),
                'condition_on_assignment' => $data['condition_on_assignment'] ?? $asset->condition,
                'assignment_notes' => $data['assignment_notes'] ?? null,
                'assigned_by' => $data['assigned_by'],
            ]);

            // Update asset status and assignment
            $asset->update([
                'assigned_to' => $data['assigned_to'],
                'assigned_date' => $assignment->assigned_date,
                'status' => 'in_use',
            ]);

            // Log audit trail
            $this->auditTrail->log(
                companyId: $asset->company_id,
                userId: $data['assigned_by'],
                entityType: 'asset_assignment',
                entityId: $assignment->id,
                action: 'created',
                newValues: $assignment->toArray()
            );

            return $assignment;
        });
    }

    public function returnAsset(AssetAssignmentModel $assignment, array $data): AssetAssignmentModel
    {
        return DB::transaction(function () use ($assignment, $data) {
            $asset = $assignment->asset;

            // Update assignment record
            $assignment->update([
                'returned_date' => $data['returned_date'] ?? now(),
                'condition_on_return' => $data['condition_on_return'],
                'return_notes' => $data['return_notes'] ?? null,
                'returned_by' => $data['returned_by'],
            ]);

            // Update asset status
            $asset->update([
                'assigned_to' => null,
                'assigned_date' => null,
                'status' => 'available',
                'condition' => $data['condition_on_return'],
            ]);

            // Log audit trail
            $this->auditTrail->log(
                companyId: $asset->company_id,
                userId: $data['returned_by'],
                entityType: 'asset_assignment',
                entityId: $assignment->id,
                action: 'returned',
                newValues: $assignment->fresh()->toArray()
            );

            return $assignment->fresh();
        });
    }

    public function scheduleMaintenance(array $data): AssetMaintenanceModel
    {
        $asset = AssetModel::findOrFail($data['asset_id']);

        $maintenance = AssetMaintenanceModel::create([
            'company_id' => $asset->company_id,
            'asset_id' => $asset->id,
            'maintenance_type' => $data['maintenance_type'],
            'description' => $data['description'],
            'scheduled_date' => $data['scheduled_date'],
            'cost' => $data['cost'] ?? 0,
            'performed_by' => $data['performed_by'] ?? null,
            'status' => 'scheduled',
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'],
        ]);

        // Update asset status if maintenance is immediate
        if (Carbon::parse($data['scheduled_date'])->isToday()) {
            $asset->update(['status' => 'maintenance']);
        }

        $this->auditTrail->log(
            companyId: $asset->company_id,
            userId: $data['created_by'],
            entityType: 'asset_maintenance',
            entityId: $maintenance->id,
            action: 'created',
            newValues: $maintenance->toArray()
        );

        return $maintenance;
    }

    public function completeMaintenance(AssetMaintenanceModel $maintenance, array $data): AssetMaintenanceModel
    {
        return DB::transaction(function () use ($maintenance, $data) {
            $maintenance->update([
                'completed_date' => $data['completed_date'] ?? now(),
                'cost' => $data['cost'] ?? $maintenance->cost,
                'status' => 'completed',
                'notes' => $data['notes'] ?? $maintenance->notes,
            ]);

            // Update asset status back to available if no other pending maintenance
            $asset = $maintenance->asset;
            $pendingMaintenance = $asset->maintenanceRecords()
                ->where('status', 'scheduled')
                ->where('id', '!=', $maintenance->id)
                ->count();

            if ($pendingMaintenance === 0 && $asset->status === 'maintenance') {
                $asset->update(['status' => $asset->assigned_to ? 'in_use' : 'available']);
            }

            $this->auditTrail->log(
                companyId: $maintenance->company_id,
                userId: $data['completed_by'] ?? $maintenance->created_by,
                entityType: 'asset_maintenance',
                entityId: $maintenance->id,
                action: 'completed',
                newValues: $maintenance->fresh()->toArray()
            );

            return $maintenance->fresh();
        });
    }

    public function calculateDepreciation(AssetModel $asset, array $data): AssetDepreciationModel
    {
        $purchaseCost = $asset->purchase_cost;
        $salvageValue = $data['salvage_value'] ?? 0;
        $usefulLifeYears = $data['useful_life_years'];

        // Calculate annual depreciation (straight line method)
        $annualDepreciation = ($purchaseCost - $salvageValue) / $usefulLifeYears;

        $depreciation = AssetDepreciationModel::create([
            'company_id' => $asset->company_id,
            'asset_id' => $asset->id,
            'method' => $data['method'] ?? 'straight_line',
            'useful_life_years' => $usefulLifeYears,
            'salvage_value' => $salvageValue,
            'annual_depreciation' => $annualDepreciation,
            'depreciation_start_date' => $data['depreciation_start_date'] ?? $asset->purchase_date,
        ]);

        // Update asset current value based on depreciation
        $this->updateAssetValue($asset);

        return $depreciation;
    }

    public function updateAssetValue(AssetModel $asset): void
    {
        $depreciation = $asset->depreciation()->first();
        
        if (!$depreciation) {
            return;
        }

        $startDate = Carbon::parse($depreciation->depreciation_start_date);
        $yearsElapsed = $startDate->diffInYears(now());

        $totalDepreciation = min(
            $depreciation->annual_depreciation * $yearsElapsed,
            $asset->purchase_cost - $depreciation->salvage_value
        );

        $currentValue = max(
            $asset->purchase_cost - $totalDepreciation,
            $depreciation->salvage_value
        );

        $asset->update(['current_value' => $currentValue]);
    }

    public function getUpcomingMaintenance(int $companyId, int $days = 7)
    {
        return AssetMaintenanceModel::forCompany($companyId)
            ->upcoming($days)
            ->with(['asset', 'createdBy.user'])
            ->orderBy('scheduled_date')
            ->get();
    }

    public function getOverdueMaintenance(int $companyId)
    {
        return AssetMaintenanceModel::forCompany($companyId)
            ->overdue()
            ->with(['asset', 'createdBy.user'])
            ->orderBy('scheduled_date')
            ->get();
    }

    public function getAssetHistory(int $assetId)
    {
        $asset = AssetModel::findOrFail($assetId);

        return [
            'assignments' => $asset->assignments()
                ->with(['assignedToUser.user', 'assignedByUser.user', 'returnedByUser.user'])
                ->orderBy('assigned_date', 'desc')
                ->get(),
            'maintenance' => $asset->maintenanceRecords()
                ->with('createdBy.user')
                ->orderBy('scheduled_date', 'desc')
                ->get(),
        ];
    }
}
