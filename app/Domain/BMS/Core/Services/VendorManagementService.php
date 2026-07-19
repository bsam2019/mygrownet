<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\VendorModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PurchaseOrderModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PurchaseOrderItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\VendorRatingModel;
use Illuminate\Support\Facades\DB;

class VendorManagementService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    // Vendor Management
    public function createVendor(array $data): VendorModel
    {
        if (empty($data['vendor_number'])) {
            $lastVendor = VendorModel::where('company_id', $data['company_id'])
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastVendor ? (int) substr($lastVendor->vendor_number, 4) + 1 : 1;
            $data['vendor_number'] = 'VEN-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
        }

        $vendor = VendorModel::create($data);

        $this->auditTrail->log(
            companyId: $vendor->company_id,
            userId: $data['created_by'],
            entityType: 'vendor',
            entityId: $vendor->id,
            action: 'created',
            newValues: $vendor->toArray()
        );

        return $vendor;
    }

    // Purchase Order Management
    public function createPurchaseOrder(array $data): PurchaseOrderModel
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['po_number'])) {
                $year = date('Y');
                $lastPO = PurchaseOrderModel::where('company_id', $data['company_id'])
                    ->where('po_number', 'like', "PO-{$year}-%")
                    ->orderBy('id', 'desc')
                    ->first();
                
                $sequence = $lastPO ? (int) substr($lastPO->po_number, -4) + 1 : 1;
                $data['po_number'] = "PO-{$year}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            }

            $items = $data['items'] ?? [];
            unset($data['items']);

            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['total_price'];
            }

            $taxAmount = $subtotal * 0.16; // 16% VAT
            $totalAmount = $subtotal + $taxAmount;

            $po = PurchaseOrderModel::create([
                ...$data,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            foreach ($items as $item) {
                $item['quantity_pending'] = $item['quantity'];
                PurchaseOrderItemModel::create([
                    'purchase_order_id' => $po->id,
                    ...$item,
                ]);
            }

            $this->auditTrail->log(
                companyId: $po->company_id,
                userId: $data['created_by'],
                entityType: 'purchase_order',
                entityId: $po->id,
                action: 'created',
                newValues: $po->toArray()
            );

            return $po;
        });
    }

    public function approvePurchaseOrder(PurchaseOrderModel $po, int $userId): PurchaseOrderModel
    {
        $po->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        $this->auditTrail->log(
            companyId: $po->company_id,
            userId: $userId,
            entityType: 'purchase_order',
            entityId: $po->id,
            action: 'approved'
        );

        return $po;
    }

    public function receiveGoods(PurchaseOrderModel $po, array $receivedItems, int $userId): void
    {
        DB::transaction(function () use ($po, $receivedItems, $userId) {
            foreach ($receivedItems as $itemData) {
                $poItem = PurchaseOrderItemModel::findOrFail($itemData['po_item_id']);
                
                $quantityReceived = $itemData['quantity_received'];
                $poItem->quantity_received += $quantityReceived;
                $poItem->quantity_pending = $poItem->quantity - $poItem->quantity_received;
                $poItem->save();

                // Update inventory if linked
                if ($poItem->inventory_item_id) {
                    $inventoryItem = \App\Infrastructure\Persistence\Eloquent\CMS\InventoryItemModel::find($poItem->inventory_item_id);
                    if ($inventoryItem) {
                        $inventoryItem->quantity_in_stock += $quantityReceived;
                        $inventoryItem->save();
                    }
                }
            }

            // Update PO status
            $allReceived = $po->items()->where('quantity_pending', '>', 0)->count() === 0;
            $partiallyReceived = $po->items()->where('quantity_received', '>', 0)->count() > 0;

            if ($allReceived) {
                $po->update([
                    'status' => 'received',
                    'actual_delivery_date' => now(),
                ]);
            } elseif ($partiallyReceived) {
                $po->update(['status' => 'partially_received']);
            }

            $this->auditTrail->log(
                companyId: $po->company_id,
                userId: $userId,
                entityType: 'purchase_order',
                entityId: $po->id,
                action: 'goods_received'
            );
        });
    }

    // Vendor Performance
    public function rateVendor(array $data): VendorRatingModel
    {
        $overallRating = (
            $data['quality_rating'] +
            $data['delivery_rating'] +
            $data['communication_rating'] +
            $data['pricing_rating']
        ) / 4;

        $rating = VendorRatingModel::create([
            ...$data,
            'overall_rating' => $overallRating,
        ]);

        // Update vendor average rating
        $this->updateVendorAverageRating($rating->vendor_id);

        return $rating;
    }

    private function updateVendorAverageRating(int $vendorId): void
    {
        $avgRating = VendorRatingModel::where('vendor_id', $vendorId)
            ->avg('overall_rating');

        VendorModel::where('id', $vendorId)->update([
            'average_rating' => $avgRating ?? 0,
        ]);
    }

    public function updateVendorSpending(int $vendorId): void
    {
        $totalSpent = PurchaseOrderModel::where('vendor_id', $vendorId)
            ->whereIn('status', ['received', 'partially_received'])
            ->sum('total_amount');

        $totalOrders = PurchaseOrderModel::where('vendor_id', $vendorId)
            ->whereIn('status', ['received', 'partially_received'])
            ->count();

        VendorModel::where('id', $vendorId)->update([
            'total_spent' => $totalSpent,
            'total_orders' => $totalOrders,
        ]);
    }

    public function getVendorPerformance(int $companyId, int $vendorId): array
    {
        $vendor = VendorModel::findOrFail($vendorId);
        
        $orders = PurchaseOrderModel::where('vendor_id', $vendorId)
            ->whereIn('status', ['received', 'partially_received'])
            ->get();

        $onTimeDeliveries = $orders->filter(function ($po) {
            return $po->actual_delivery_date && $po->expected_delivery_date &&
                   $po->actual_delivery_date <= $po->expected_delivery_date;
        })->count();

        $onTimeRate = $orders->count() > 0 ? ($onTimeDeliveries / $orders->count()) * 100 : 0;

        return [
            'vendor' => $vendor,
            'total_orders' => $vendor->total_orders,
            'total_spent' => $vendor->total_spent,
            'average_rating' => $vendor->average_rating,
            'on_time_delivery_rate' => round($onTimeRate, 2),
            'recent_ratings' => $vendor->ratings()->latest()->take(5)->get(),
        ];
    }
}
