<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\StockMovementModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobInventoryModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LowStockAlertModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel;
use App\Notifications\CMS\LowStockAlertNotification;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    public function createItem(array $data): InventoryItemModel
    {
        return DB::transaction(function () use ($data) {
            // Generate item code if not provided
            if (empty($data['item_code'])) {
                $lastItem = InventoryItemModel::where('company_id', $data['company_id'])
                    ->orderBy('id', 'desc')
                    ->first();
                
                $sequence = $lastItem ? (int) substr($lastItem->item_code, 4) + 1 : 1;
                $data['item_code'] = 'INV-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
            }

            $item = InventoryItemModel::create($data);

            // Log audit trail
            $this->auditTrail->log(
                companyId: $item->company_id,
                userId: $data['created_by'],
                entityType: 'inventory_item',
                entityId: $item->id,
                action: 'created',
                newValues: $item->toArray()
            );

            // Check if low stock alert needed
            if ($item->current_stock <= $item->minimum_stock) {
                $this->createLowStockAlert($item);
            }

            return $item;
        });
    }

    public function updateItem(InventoryItemModel $item, array $data, int $userId): InventoryItemModel
    {
        $oldValues = $item->toArray();
        $item->update($data);

        $this->auditTrail->log(
            companyId: $item->company_id,
            userId: $userId,
            entityType: 'inventory_item',
            entityId: $item->id,
            action: 'updated',
            oldValues: $oldValues,
            newValues: $item->fresh()->toArray()
        );

        return $item->fresh();
    }

    public function recordStockMovement(array $data): StockMovementModel
    {
        return DB::transaction(function () use ($data) {
            $item = InventoryItemModel::findOrFail($data['inventory_item_id']);
            
            $stockBefore = $item->current_stock;
            $stockAfter = $stockBefore + $data['quantity'];

            // Create movement record
            $movement = StockMovementModel::create([
                'company_id' => $item->company_id,
                'inventory_item_id' => $item->id,
                'movement_type' => $data['movement_type'],
                'quantity' => $data['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'unit_cost' => $data['unit_cost'] ?? null,
                'job_id' => $data['job_id'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            // Update item stock
            $item->update(['current_stock' => $stockAfter]);

            // Update unit cost if provided (for purchases)
            if (isset($data['unit_cost']) && $data['movement_type'] === 'purchase') {
                $item->update(['unit_cost' => $data['unit_cost']]);
            }

            // Check for low stock
            if ($stockAfter <= $item->minimum_stock) {
                $this->createLowStockAlert($item);
            } else {
                // Resolve any existing alerts if stock is now above minimum
                $this->resolveLowStockAlerts($item);
            }

            // Log audit trail
            $this->auditTrail->log(
                companyId: $item->company_id,
                userId: $data['created_by'],
                entityType: 'stock_movement',
                entityId: $movement->id,
                action: 'created',
                newValues: $movement->toArray()
            );

            return $movement;
        });
    }

    public function useInventoryForJob(int $jobId, array $items, int $userId): array
    {
        return DB::transaction(function () use ($jobId, $items, $userId) {
            $jobInventory = [];

            foreach ($items as $itemData) {
                $inventoryItem = InventoryItemModel::findOrFail($itemData['inventory_item_id']);
                
                // Check if enough stock
                if ($inventoryItem->current_stock < $itemData['quantity']) {
                    throw new \DomainException(
                        "Insufficient stock for {$inventoryItem->name}. Available: {$inventoryItem->current_stock}, Required: {$itemData['quantity']}"
                    );
                }

                $totalCost = $itemData['quantity'] * $inventoryItem->unit_cost;

                // Create job inventory record
                $jobInv = JobInventoryModel::create([
                    'company_id' => $inventoryItem->company_id,
                    'job_id' => $jobId,
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity_used' => $itemData['quantity'],
                    'unit_cost' => $inventoryItem->unit_cost,
                    'total_cost' => $totalCost,
                    'created_by' => $userId,
                ]);

                // Record stock movement
                $this->recordStockMovement([
                    'inventory_item_id' => $inventoryItem->id,
                    'movement_type' => 'usage',
                    'quantity' => -$itemData['quantity'], // Negative for reduction
                    'job_id' => $jobId,
                    'notes' => "Used for job",
                    'created_by' => $userId,
                ]);

                $jobInventory[] = $jobInv;
            }

            return $jobInventory;
        });
    }

    private function createLowStockAlert(InventoryItemModel $item): void
    {
        // Check if there's already an unresolved alert
        $existingAlert = LowStockAlertModel::where('inventory_item_id', $item->id)
            ->where('is_resolved', false)
            ->first();

        if (!$existingAlert) {
            LowStockAlertModel::create([
                'company_id' => $item->company_id,
                'inventory_item_id' => $item->id,
                'current_stock' => $item->current_stock,
                'minimum_stock' => $item->minimum_stock,
            ]);

            // Notify managers/admins about low stock
            $managers = CmsUserModel::where('company_id', $item->company_id)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['owner', 'manager']);
                })
                ->with('user')
                ->get();

            foreach ($managers as $manager) {
                if ($manager->user) {
                    $manager->user->notify(new LowStockAlertNotification([
                        'id' => $item->id,
                        'item_code' => $item->item_code,
                        'name' => $item->name,
                        'current_stock' => $item->current_stock,
                        'minimum_stock' => $item->minimum_stock,
                        'unit' => $item->unit,
                    ]));
                }
            }
        }
    }

    private function resolveLowStockAlerts(InventoryItemModel $item): void
    {
        LowStockAlertModel::where('inventory_item_id', $item->id)
            ->where('is_resolved', false)
            ->update([
                'is_resolved' => true,
                'resolved_at' => now(),
            ]);
    }

    public function getLowStockItems(int $companyId): array
    {
        return InventoryItemModel::forCompany($companyId)
            ->lowStock()
            ->active()
            ->with('lowStockAlerts')
            ->get()
            ->toArray();
    }

    public function getStockMovementHistory(int $itemId, ?int $limit = null)
    {
        $query = StockMovementModel::where('inventory_item_id', $itemId)
            ->with(['createdBy.user', 'job'])
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
