<?php

namespace App\Domain\CMS\Inventory\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\StockLocationModel;
use App\Infrastructure\Persistence\Eloquent\CMS\StockLevelModel;
use App\Infrastructure\Persistence\Eloquent\CMS\StockTransferModel;
use App\Infrastructure\Persistence\Eloquent\CMS\StockAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\StockCountModel;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function createLocation(int $companyId, array $data): StockLocationModel
    {
        return StockLocationModel::create([
            'company_id' => $companyId,
            'name' => $data['name'],
            'code' => $data['code'],
            'type' => $data['type'],
            'address' => $data['address'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function getLowStockAlerts(int $companyId): array
    {
        return StockLevelModel::whereHas('location', fn($q) => $q->where('company_id', $companyId))
            ->whereRaw('quantity <= reorder_level')
            ->with(['material', 'location'])
            ->get()
            ->toArray();
    }

    public function createTransfer(int $companyId, array $data): StockTransferModel
    {
        return DB::transaction(function () use ($companyId, $data) {
            $transfer = StockTransferModel::create([
                'company_id' => $companyId,
                'transfer_number' => $this->generateTransferNumber($companyId),
                'from_location_id' => $data['from_location_id'],
                'to_location_id' => $data['to_location_id'],
                'transfer_date' => $data['transfer_date'],
                'requested_by' => $data['requested_by'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $transfer->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $transfer->load('items.material', 'fromLocation', 'toLocation');
        });
    }

    public function approveTransfer(int $transferId, int $approvedBy): StockTransferModel
    {
        $transfer = StockTransferModel::findOrFail($transferId);
        $transfer->update([
            'status' => 'in_transit',
            'approved_by' => $approvedBy,
            'approved_date' => now(),
        ]);

        return $transfer;
    }

    public function receiveTransfer(int $transferId, int $receivedBy, array $items): StockTransferModel
    {
        return DB::transaction(function () use ($transferId, $receivedBy, $items) {
            $transfer = StockTransferModel::findOrFail($transferId);
            
            foreach ($items as $itemId => $receivedQty) {
                $item = $transfer->items()->findOrFail($itemId);
                $item->update(['received_quantity' => $receivedQty]);

                // Update stock levels
                $this->updateStockLevel($transfer->from_location_id, $item->material_id, -$receivedQty);
                $this->updateStockLevel($transfer->to_location_id, $item->material_id, $receivedQty);
            }

            $transfer->update([
                'status' => 'received',
                'received_by' => $receivedBy,
                'received_date' => now(),
            ]);

            return $transfer->load('items.material');
        });
    }

    public function createAdjustment(int $companyId, array $data): StockAdjustmentModel
    {
        return DB::transaction(function () use ($companyId, $data) {
            $adjustment = StockAdjustmentModel::create([
                'company_id' => $companyId,
                'adjustment_number' => $this->generateAdjustmentNumber($companyId),
                'adjustment_date' => $data['adjustment_date'],
                'adjustment_type' => $data['adjustment_type'],
                'reason' => $data['reason'],
                'created_by' => $data['created_by'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $adjustment->items()->create([
                    'material_id' => $item['material_id'],
                    'location_id' => $item['location_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $adjustment->load('items.material', 'items.location');
        });
    }

    public function approveAdjustment(int $adjustmentId, int $approvedBy): StockAdjustmentModel
    {
        return DB::transaction(function () use ($adjustmentId, $approvedBy) {
            $adjustment = StockAdjustmentModel::findOrFail($adjustmentId);
            
            foreach ($adjustment->items as $item) {
                $multiplier = $adjustment->adjustment_type === 'increase' ? 1 : -1;
                $this->updateStockLevel($item->location_id, $item->material_id, $item->quantity * $multiplier);
            }

            $adjustment->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_date' => now(),
            ]);

            return $adjustment;
        });
    }

    public function createStockCount(int $companyId, array $data): StockCountModel
    {
        return DB::transaction(function () use ($companyId, $data) {
            $count = StockCountModel::create([
                'company_id' => $companyId,
                'count_number' => $this->generateCountNumber($companyId),
                'count_date' => $data['count_date'],
                'count_type' => $data['count_type'],
                'location_id' => $data['location_id'] ?? null,
                'counted_by' => $data['counted_by'],
                'status' => 'in_progress',
            ]);

            foreach ($data['items'] as $item) {
                $stockLevel = StockLevelModel::where('location_id', $item['location_id'])
                    ->where('material_id', $item['material_id'])
                    ->first();

                $count->items()->create([
                    'material_id' => $item['material_id'],
                    'location_id' => $item['location_id'],
                    'system_quantity' => $stockLevel->quantity ?? 0,
                    'counted_quantity' => $item['counted_quantity'],
                    'unit' => $item['unit'],
                ]);
            }

            return $count->load('items.material', 'items.location');
        });
    }

    public function completeStockCount(int $countId, int $verifiedBy): StockCountModel
    {
        return DB::transaction(function () use ($countId, $verifiedBy) {
            $count = StockCountModel::findOrFail($countId);
            
            foreach ($count->items as $item) {
                $variance = $item->counted_quantity - $item->system_quantity;
                $item->update(['variance' => $variance]);

                if ($variance != 0) {
                    $this->updateStockLevel($item->location_id, $item->material_id, $variance);
                }
            }

            $count->update([
                'status' => 'completed',
                'verified_by' => $verifiedBy,
                'verified_date' => now(),
            ]);

            return $count;
        });
    }

    private function updateStockLevel(int $locationId, int $materialId, float $quantity): void
    {
        $stockLevel = StockLevelModel::firstOrCreate(
            ['location_id' => $locationId, 'material_id' => $materialId],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );

        $stockLevel->increment('quantity', $quantity);
    }

    private function generateTransferNumber(int $companyId): string
    {
        $year = date('Y');
        $last = StockTransferModel::where('company_id', $companyId)
            ->where('transfer_number', 'like', "TRF-{$year}-%")
            ->orderBy('transfer_number', 'desc')
            ->first();

        $num = $last ? ((int) substr($last->transfer_number, -4)) + 1 : 1;
        return sprintf('TRF-%s-%04d', $year, $num);
    }

    private function generateAdjustmentNumber(int $companyId): string
    {
        $year = date('Y');
        $last = StockAdjustmentModel::where('company_id', $companyId)
            ->where('adjustment_number', 'like', "ADJ-{$year}-%")
            ->orderBy('adjustment_number', 'desc')
            ->first();

        $num = $last ? ((int) substr($last->adjustment_number, -4)) + 1 : 1;
        return sprintf('ADJ-%s-%04d', $year, $num);
    }

    private function generateCountNumber(int $companyId): string
    {
        $year = date('Y');
        $last = StockCountModel::where('company_id', $companyId)
            ->where('count_number', 'like', "CNT-{$year}-%")
            ->orderBy('count_number', 'desc')
            ->first();

        $num = $last ? ((int) substr($last->count_number, -4)) + 1 : 1;
        return sprintf('CNT-%s-%04d', $year, $num);
    }
}
