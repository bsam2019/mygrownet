<?php

namespace App\Domain\CMS\Materials\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\MaterialPurchaseOrderModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PurchaseOrderItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobMaterialPlanModel;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function createPurchaseOrder(array $data): MaterialPurchaseOrderModel
    {
        return DB::transaction(function () use ($data) {
            $po = MaterialPurchaseOrderModel::create([
                'company_id' => $data['company_id'],
                'job_id' => $data['job_id'] ?? null,
                'po_number' => $this->generatePONumber($data['company_id']),
                'supplier_name' => $data['supplier_name'],
                'supplier_contact' => $data['supplier_contact'] ?? null,
                'supplier_address' => $data['supplier_address'] ?? null,
                'subtotal' => 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'total_amount' => 0,
                'status' => 'draft',
                'order_date' => $data['order_date'] ?? now(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            // Add items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $this->addItemToPurchaseOrder($po, $itemData);
                }
            }

            $po->calculateTotals();
            $po->save();

            return $po;
        });
    }

    public function addItemToPurchaseOrder(MaterialPurchaseOrderModel $po, array $data): PurchaseOrderItemModel
    {
        $item = PurchaseOrderItemModel::create([
            'purchase_order_id' => $po->id,
            'material_id' => $data['material_id'] ?? null,
            'job_material_plan_id' => $data['job_material_plan_id'] ?? null,
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'unit_price' => $data['unit_price'],
            'total_price' => 0,
            'received_quantity' => 0,
        ]);

        $item->calculateTotalPrice();
        $item->save();

        $po->calculateTotals();
        $po->save();

        return $item;
    }

    public function updatePurchaseOrderItem(PurchaseOrderItemModel $item, array $data): PurchaseOrderItemModel
    {
        $item->update($data);

        if (isset($data['quantity']) || isset($data['unit_price'])) {
            $item->calculateTotalPrice();
            $item->save();

            $item->purchaseOrder->calculateTotals();
            $item->purchaseOrder->save();
        }

        return $item->fresh();
    }

    public function removeItemFromPurchaseOrder(PurchaseOrderItemModel $item): bool
    {
        $po = $item->purchaseOrder;
        $deleted = $item->delete();

        if ($deleted) {
            $po->calculateTotals();
            $po->save();
        }

        return $deleted;
    }

    public function createPurchaseOrderFromJobMaterials(int $jobId, array $materialPlanIds, array $poData): MaterialPurchaseOrderModel
    {
        return DB::transaction(function () use ($jobId, $materialPlanIds, $poData) {
            $plans = JobMaterialPlanModel::with('material')
                ->whereIn('id', $materialPlanIds)
                ->where('job_id', $jobId)
                ->where('status', 'planned')
                ->get();

            if ($plans->isEmpty()) {
                throw new \Exception('No valid materials found to create purchase order');
            }

            // Group by supplier
            $supplier = $plans->first()->material->supplier ?? 'Unknown Supplier';

            $po = $this->createPurchaseOrder([
                'company_id' => $poData['company_id'],
                'job_id' => $jobId,
                'supplier_name' => $supplier,
                'supplier_contact' => $poData['supplier_contact'] ?? null,
                'supplier_address' => $poData['supplier_address'] ?? null,
                'order_date' => $poData['order_date'] ?? now(),
                'expected_delivery_date' => $poData['expected_delivery_date'] ?? null,
                'notes' => $poData['notes'] ?? null,
                'terms' => $poData['terms'] ?? null,
                'created_by' => $poData['created_by'],
            ]);

            foreach ($plans as $plan) {
                $this->addItemToPurchaseOrder($po, [
                    'material_id' => $plan->material_id,
                    'job_material_plan_id' => $plan->id,
                    'description' => $plan->material->name,
                    'quantity' => $plan->planned_quantity,
                    'unit' => $plan->material->unit,
                    'unit_price' => $plan->unit_price,
                ]);

                // Mark material plan as ordered
                $plan->markAsOrdered();
            }

            return $po->fresh('items');
        });
    }

    public function approvePurchaseOrder(MaterialPurchaseOrderModel $po, int $approvedBy): MaterialPurchaseOrderModel
    {
        if (!$po->isDraft()) {
            throw new \Exception('Only draft purchase orders can be approved');
        }

        $po->approve($approvedBy);
        return $po->fresh();
    }

    public function receivePurchaseOrder(MaterialPurchaseOrderModel $po, array $receivedItems): MaterialPurchaseOrderModel
    {
        return DB::transaction(function () use ($po, $receivedItems) {
            foreach ($receivedItems as $itemId => $receivedData) {
                $item = PurchaseOrderItemModel::findOrFail($itemId);
                
                $item->update([
                    'received_quantity' => $receivedData['received_quantity'],
                ]);

                // Update job material plan if linked
                if ($item->job_material_plan_id) {
                    $plan = JobMaterialPlanModel::find($item->job_material_plan_id);
                    if ($plan) {
                        $plan->markAsReceived(
                            $receivedData['received_quantity'],
                            $item->unit_price
                        );
                    }
                }
            }

            $po->markAsReceived();
            return $po->fresh();
        });
    }

    public function cancelPurchaseOrder(MaterialPurchaseOrderModel $po): MaterialPurchaseOrderModel
    {
        return DB::transaction(function () use ($po) {
            // Reset material plans back to planned status
            $items = $po->items()->whereNotNull('job_material_plan_id')->get();
            foreach ($items as $item) {
                if ($item->jobMaterialPlan) {
                    $item->jobMaterialPlan->update(['status' => 'planned', 'ordered_at' => null]);
                }
            }

            $po->cancel();
            return $po->fresh();
        });
    }

    private function generatePONumber(int $companyId): string
    {
        $prefix = 'PO';
        $date = now()->format('Ymd');
        
        $lastPO = MaterialPurchaseOrderModel::where('company_id', $companyId)
            ->where('po_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('po_number', 'desc')
            ->first();

        if ($lastPO) {
            $lastNumber = (int) substr($lastPO->po_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $newNumber);
    }
}
