<?php

namespace App\Domain\CMS\Production\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\ProductionOrderModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CuttingListModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CuttingListItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ProductionTrackingModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WasteTrackingModel;
use App\Infrastructure\Persistence\Eloquent\CMS\QualityCheckpointModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ProductionMaterialsUsageModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CuttingPatternModel;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    public function createProductionOrder(int $companyId, array $data): ProductionOrderModel
    {
        return DB::transaction(function () use ($companyId, $data) {
            $data['company_id'] = $companyId;
            $data['order_number'] = $this->generateOrderNumber($companyId);
            
            $order = ProductionOrderModel::create($data);
            
            // Create default quality checkpoints
            $this->createDefaultQualityCheckpoints($order->id);
            
            return $order->load(['job', 'assignedUser']);
        });
    }

    public function updateProductionOrder(int $id, array $data): ProductionOrderModel
    {
        $order = ProductionOrderModel::findOrFail($id);
        $order->update($data);
        
        return $order->load(['job', 'assignedUser']);
    }

    public function deleteProductionOrder(int $id): bool
    {
        $order = ProductionOrderModel::findOrFail($id);
        return $order->delete();
    }

    public function generateCuttingList(int $productionOrderId, array $items): CuttingListModel
    {
        return DB::transaction(function () use ($productionOrderId, $items) {
            $order = ProductionOrderModel::findOrFail($productionOrderId);
            
            $cuttingList = CuttingListModel::create([
                'company_id' => $order->company_id,
                'production_order_id' => $productionOrderId,
                'list_number' => $this->generateCuttingListNumber($order->company_id),
                'generated_date' => now(),
                'status' => 'draft',
            ]);
            
            $totalRequired = 0;
            foreach ($items as $index => $item) {
                $totalLength = $item['required_length'] * $item['quantity'];
                $totalRequired += $totalLength;
                
                CuttingListItemModel::create([
                    'cutting_list_id' => $cuttingList->id,
                    'material_id' => $item['material_id'],
                    'item_code' => $item['item_code'] ?? null,
                    'description' => $item['description'],
                    'required_length' => $item['required_length'],
                    'quantity' => $item['quantity'],
                    'total_length' => $totalLength,
                    'sort_order' => $index,
                ]);
            }
            
            $cuttingList->update(['total_length_required' => $totalRequired]);
            
            return $cuttingList->load('items.material');
        });
    }

    public function optimizeCuttingList(int $cuttingListId, float $stockLength = 6.0): array
    {
        $cuttingList = CuttingListModel::with('items')->findOrFail($cuttingListId);
        
        // Group items by material
        $itemsByMaterial = $cuttingList->items->groupBy('material_id');
        
        $optimizedPatterns = [];
        $totalWaste = 0;
        $totalUsed = 0;
        
        foreach ($itemsByMaterial as $materialId => $items) {
            // Sort items by length (descending) for better optimization
            $sortedItems = $items->sortByDesc('required_length')->values();
            
            $patterns = $this->calculateOptimalCuts($sortedItems->toArray(), $stockLength);
            
            foreach ($patterns as $pattern) {
                $totalUsed += $pattern['used'];
                $totalWaste += $pattern['waste'];
                
                // Save pattern
                CuttingPatternModel::create([
                    'company_id' => $cuttingList->company_id,
                    'material_id' => $materialId,
                    'pattern_name' => "Auto-generated for {$cuttingList->list_number}",
                    'stock_length' => $stockLength,
                    'cuts' => $pattern['cuts'],
                    'total_used' => $pattern['used'],
                    'waste' => $pattern['waste'],
                    'efficiency_percentage' => ($pattern['used'] / $stockLength) * 100,
                ]);
            }
            
            $optimizedPatterns[$materialId] = $patterns;
        }
        
        // Update cutting list
        $wastePercentage = $totalUsed > 0 ? ($totalWaste / ($totalUsed + $totalWaste)) * 100 : 0;
        $cuttingList->update([
            'total_length_used' => $totalUsed,
            'waste_percentage' => $wastePercentage,
            'optimized' => true,
        ]);
        
        return [
            'patterns' => $optimizedPatterns,
            'total_used' => $totalUsed,
            'total_waste' => $totalWaste,
            'waste_percentage' => $wastePercentage,
            'efficiency' => 100 - $wastePercentage,
        ];
    }

    private function calculateOptimalCuts(array $items, float $stockLength): array
    {
        $patterns = [];
        $remainingItems = $items;
        
        while (!empty($remainingItems)) {
            $currentStock = $stockLength;
            $cuts = [];
            $used = 0;
            
            // First Fit Decreasing algorithm
            foreach ($remainingItems as $key => $item) {
                $length = $item['required_length'];
                $qty = $item['quantity'];
                
                while ($qty > 0 && $currentStock >= $length) {
                    $cuts[] = [
                        'length' => $length,
                        'description' => $item['description'],
                    ];
                    $used += $length;
                    $currentStock -= $length;
                    $qty--;
                }
                
                if ($qty == 0) {
                    unset($remainingItems[$key]);
                } else {
                    $remainingItems[$key]['quantity'] = $qty;
                }
            }
            
            if (empty($cuts)) {
                break; // No more cuts possible
            }
            
            $patterns[] = [
                'cuts' => $cuts,
                'used' => $used,
                'waste' => $stockLength - $used,
                'stock_length' => $stockLength,
            ];
        }
        
        return $patterns;
    }

    public function updateProductionTracking(int $productionOrderId, array $data): ProductionTrackingModel
    {
        $tracking = ProductionTrackingModel::updateOrCreate(
            [
                'production_order_id' => $productionOrderId,
                'stage' => $data['stage'],
            ],
            $data
        );
        
        // Update production order status based on tracking
        $this->updateProductionOrderStatus($productionOrderId);
        
        return $tracking;
    }

    public function recordWaste(int $companyId, array $data): WasteTrackingModel
    {
        $data['company_id'] = $companyId;
        return WasteTrackingModel::create($data);
    }

    public function updateQualityCheckpoint(int $checkpointId, array $data): QualityCheckpointModel
    {
        $checkpoint = QualityCheckpointModel::findOrFail($checkpointId);
        $checkpoint->update($data);
        
        return $checkpoint;
    }

    public function recordMaterialUsage(int $productionOrderId, array $materials): void
    {
        foreach ($materials as $material) {
            $usage = ProductionMaterialsUsageModel::updateOrCreate(
                [
                    'production_order_id' => $productionOrderId,
                    'material_id' => $material['material_id'],
                ],
                [
                    'planned_quantity' => $material['planned_quantity'] ?? 0,
                    'actual_quantity' => $material['actual_quantity'] ?? 0,
                    'variance' => ($material['actual_quantity'] ?? 0) - ($material['planned_quantity'] ?? 0),
                    'unit' => $material['unit'],
                    'unit_cost' => $material['unit_cost'] ?? null,
                    'total_cost' => ($material['actual_quantity'] ?? 0) * ($material['unit_cost'] ?? 0),
                    'notes' => $material['notes'] ?? null,
                ]
            );
        }
    }

    private function createDefaultQualityCheckpoints(int $productionOrderId): void
    {
        $checkpoints = [
            ['checkpoint_name' => 'Material Inspection', 'stage' => 'cutting'],
            ['checkpoint_name' => 'Cutting Accuracy', 'stage' => 'cutting'],
            ['checkpoint_name' => 'Assembly Check', 'stage' => 'assembly'],
            ['checkpoint_name' => 'Finishing Quality', 'stage' => 'finishing'],
            ['checkpoint_name' => 'Final Inspection', 'stage' => 'final_inspection'],
        ];
        
        foreach ($checkpoints as $checkpoint) {
            QualityCheckpointModel::create([
                'production_order_id' => $productionOrderId,
                'checkpoint_name' => $checkpoint['checkpoint_name'],
                'stage' => $checkpoint['stage'],
                'status' => 'pending',
            ]);
        }
    }

    private function updateProductionOrderStatus(int $productionOrderId): void
    {
        $order = ProductionOrderModel::findOrFail($productionOrderId);
        $tracking = $order->tracking;
        
        $allCompleted = $tracking->every(fn($t) => $t->status === 'completed');
        $anyInProgress = $tracking->contains(fn($t) => $t->status === 'in_progress');
        
        if ($allCompleted && $tracking->count() > 0) {
            $order->update(['status' => 'completed', 'completion_date' => now()]);
        } elseif ($anyInProgress) {
            $order->update(['status' => 'in_progress', 'start_date' => $order->start_date ?? now()]);
        }
    }

    private function generateOrderNumber(int $companyId): string
    {
        $prefix = 'PO';
        $date = now()->format('Ymd');
        $count = ProductionOrderModel::where('company_id', $companyId)
            ->whereDate('created_at', now())
            ->count() + 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }

    private function generateCuttingListNumber(int $companyId): string
    {
        $prefix = 'CL';
        $date = now()->format('Ymd');
        $count = CuttingListModel::where('company_id', $companyId)
            ->whereDate('created_at', now())
            ->count() + 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }

    public function getProductionStatistics(int $companyId, array $filters = []): array
    {
        $query = ProductionOrderModel::where('company_id', $companyId);
        
        if (isset($filters['start_date'])) {
            $query->where('order_date', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('order_date', '<=', $filters['end_date']);
        }
        
        $orders = $query->get();
        
        return [
            'total_orders' => $orders->count(),
            'pending' => $orders->where('status', 'pending')->count(),
            'in_progress' => $orders->where('status', 'in_progress')->count(),
            'completed' => $orders->where('status', 'completed')->count(),
            'cancelled' => $orders->where('status', 'cancelled')->count(),
            'total_estimated_hours' => $orders->sum('estimated_hours'),
            'total_actual_hours' => $orders->sum('actual_hours'),
            'efficiency' => $orders->sum('estimated_hours') > 0 
                ? ($orders->sum('actual_hours') / $orders->sum('estimated_hours')) * 100 
                : 0,
        ];
    }

    public function getWasteStatistics(int $companyId, array $filters = []): array
    {
        $query = WasteTrackingModel::where('company_id', $companyId);
        
        if (isset($filters['start_date'])) {
            $query->where('waste_date', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('waste_date', '<=', $filters['end_date']);
        }
        
        $waste = $query->get();
        
        return [
            'total_waste_records' => $waste->count(),
            'total_waste_value' => $waste->sum('value'),
            'by_type' => $waste->groupBy('waste_type')->map->count(),
            'by_material' => $waste->groupBy('material_id')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total_quantity' => $items->sum('quantity'),
                    'total_value' => $items->sum('value'),
                ];
            }),
        ];
    }
}
