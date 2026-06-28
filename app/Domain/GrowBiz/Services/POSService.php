<?php

namespace App\Domain\GrowBiz\Services;

use App\Infrastructure\Persistence\Eloquent\GrowBizPOSSaleModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizPOSSaleItemModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizPOSShiftModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizPOSSettingsModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizPOSQuickProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use Carbon\Carbon;
use Illuminate\Support\Str;

class POSService
{
    // Shifts
    public function getActiveShift(int $userId): ?array
    {
        $shift = GrowBizPOSShiftModel::where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        return $shift ? $this->mapShift($shift) : null;
    }

    public function openShift(int $userId, array $data): array
    {
        // Close any existing open shifts
        GrowBizPOSShiftModel::where('user_id', $userId)
            ->where('status', 'open')
            ->update(['status' => 'closed', 'ended_at' => now()]);

        $shift = GrowBizPOSShiftModel::create([
            'user_id' => $userId,
            'employee_id' => $data['employee_id'] ?? null,
            'shift_number' => $this->generateShiftNumber($userId),
            'opening_cash' => $data['opening_cash'] ?? 0,
            'opening_notes' => $data['notes'] ?? null,
            'started_at' => now(),
            'status' => 'open',
        ]);

        return $this->mapShift($shift);
    }

    public function closeShift(int $shiftId, int $userId, array $data): ?array
    {
        $shift = GrowBizPOSShiftModel::where('id', $shiftId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if (!$shift) return null;

        $closingCash = $data['closing_cash'] ?? 0;
        $expectedCash = $shift->opening_cash + $shift->total_cash_sales;
        $difference = $closingCash - $expectedCash;

        $shift->update([
            'closing_cash' => $closingCash,
            'expected_cash' => $expectedCash,
            'cash_difference' => $difference,
            'closing_notes' => $data['notes'] ?? null,
            'ended_at' => now(),
            'status' => 'closed',
        ]);

        return $this->mapShift($shift->fresh());
    }

    public function getShiftHistory(int $userId, int $limit = 10): array
    {
        return GrowBizPOSShiftModel::where('user_id', $userId)
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($s) => $this->mapShift($s))
            ->toArray();
    }

    // Sales
    public function createSale(int $userId, array $data): array
    {
        $activeShift = $this->getActiveShift($userId);
        
        // Calculate totals
        $subtotal = 0;
        $itemCount = 0;
        foreach ($data['items'] as $item) {
            $itemTotal = ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0) - ($item['discount'] ?? 0);
            $subtotal += $itemTotal;
            $itemCount += $item['quantity'] ?? 1;
        }

        $discountAmount = $data['discount_amount'] ?? 0;
        $taxAmount = $data['tax_amount'] ?? 0;
        $totalAmount = $subtotal - $discountAmount + $taxAmount;
        $amountPaid = $data['amount_paid'] ?? $totalAmount;
        $changeGiven = max(0, $amountPaid - $totalAmount);

        $sale = GrowBizPOSSaleModel::create([
            'user_id' => $userId,
            'shift_id' => $activeShift['id'] ?? null,
            'sale_number' => $this->generateSaleNumber($userId),
            'customer_id' => $data['customer_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'item_count' => $itemCount,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'discount_percentage' => $data['discount_percentage'] ?? 0,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'payment_reference' => $data['payment_reference'] ?? null,
            'amount_paid' => $amountPaid,
            'change_given' => $changeGiven,
            'notes' => $data['notes'] ?? null,
            'served_by' => $data['served_by'] ?? null,
            'completed_at' => now(),
            'status' => 'completed',
        ]);

        // Create sale items
        foreach ($data['items'] as $item) {
            $itemTotal = ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0) - ($item['discount'] ?? 0);
            
            GrowBizPOSSaleItemModel::create([
                'sale_id' => $sale->id,
                'inventory_item_id' => $item['inventory_item_id'] ?? null,
                'product_name' => $item['product_name'],
                'product_sku' => $item['product_sku'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'unit' => $item['unit'] ?? 'piece',
                'unit_price' => $item['unit_price'] ?? 0,
                'discount' => $item['discount'] ?? 0,
                'tax' => $item['tax'] ?? 0,
                'total' => $itemTotal,
            ]);

            // Deduct from inventory if tracking enabled
            if (!empty($item['inventory_item_id'])) {
                $this->deductInventory($item['inventory_item_id'], $item['quantity'] ?? 1, $sale->id);
            }
        }

        // Update shift totals
        if ($activeShift) {
            $this->updateShiftTotals($activeShift['id'], $totalAmount, $data['payment_method'] ?? 'cash');
        }

        return $this->mapSale($sale->fresh(['items']));
    }

    public function getSale(int $id, int $userId): ?array
    {
        $sale = GrowBizPOSSaleModel::where('id', $id)
            ->where('user_id', $userId)
            ->with(['items', 'shift'])
            ->first();

        return $sale ? $this->mapSale($sale) : null;
    }

    public function getSales(int $userId, array $filters = []): array
    {
        $query = GrowBizPOSSaleModel::where('user_id', $userId)
            ->with(['items']);

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['shift_id'])) {
            $query->where('shift_id', $filters['shift_id']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($filters['limit'] ?? 50)
            ->get()
            ->map(fn($s) => $this->mapSale($s))
            ->toArray();
    }

    public function voidSale(int $id, int $userId, ?string $reason = null): bool
    {
        $sale = GrowBizPOSSaleModel::where('id', $id)
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->first();

        if (!$sale) return false;

        // Restore inventory
        foreach ($sale->items as $item) {
            if ($item->inventory_item_id) {
                $this->restoreInventory($item->inventory_item_id, $item->quantity);
            }
        }

        // Update shift totals
        if ($sale->shift_id) {
            $this->updateShiftTotals($sale->shift_id, -$sale->total_amount, $sale->payment_method);
        }

        $sale->update([
            'status' => 'voided',
            'notes' => $sale->notes . "\n[VOIDED] " . ($reason ?? 'No reason provided'),
        ]);

        return true;
    }

    // Quick Products
    public function getQuickProducts(int $userId): array
    {
        return GrowBizPOSQuickProductModel::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'color' => $p->color,
                'inventory_item_id' => $p->inventory_item_id,
            ])
            ->toArray();
    }

    public function saveQuickProduct(int $userId, array $data): array
    {
        $product = GrowBizPOSQuickProductModel::updateOrCreate(
            ['id' => $data['id'] ?? null, 'user_id' => $userId],
            [
                'user_id' => $userId,
                'inventory_item_id' => $data['inventory_item_id'] ?? null,
                'name' => $data['name'],
                'price' => $data['price'] ?? 0,
                'color' => $data['color'] ?? '#3b82f6',
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]
        );

        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'color' => $product->color,
        ];
    }

    // Settings
    public function getSettings(int $userId): array
    {
        $settings = GrowBizPOSSettingsModel::firstOrCreate(
            ['user_id' => $userId],
            [
                'currency' => 'ZMW',
                'currency_symbol' => 'K',
                'default_tax_rate' => 0,
                'enable_tax' => false,
                'track_inventory' => true,
                'quick_amounts' => [10, 20, 50, 100, 200, 500],
                'payment_methods' => ['cash', 'mobile_money'],
            ]
        );

        return [
            'receipt_header' => $settings->receipt_header,
            'receipt_footer' => $settings->receipt_footer,
            'business_name' => $settings->business_name,
            'business_address' => $settings->business_address,
            'business_phone' => $settings->business_phone,
            'tax_id' => $settings->tax_id,
            'default_tax_rate' => (float) $settings->default_tax_rate,
            'enable_tax' => $settings->enable_tax,
            'require_customer' => $settings->require_customer,
            'allow_credit_sales' => $settings->allow_credit_sales,
            'auto_print_receipt' => $settings->auto_print_receipt,
            'track_inventory' => $settings->track_inventory,
            'currency' => $settings->currency,
            'currency_symbol' => $settings->currency_symbol,
            'payment_methods' => $settings->payment_methods ?? ['cash', 'mobile_money'],
            'quick_amounts' => $settings->quick_amounts ?? [10, 20, 50, 100, 200, 500],
        ];
    }

    public function saveSettings(int $userId, array $data): array
    {
        GrowBizPOSSettingsModel::updateOrCreate(
            ['user_id' => $userId],
            $data
        );

        return $this->getSettings($userId);
    }

    // Statistics
    public function getDailyStats(int $userId, ?string $date = null): array
    {
        $date = $date ? Carbon::parse($date) : now();

        $sales = GrowBizPOSSaleModel::where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->where('status', 'completed');

        $totalSales = (clone $sales)->sum('total_amount');
        $totalTransactions = (clone $sales)->count();
        $cashSales = (clone $sales)->where('payment_method', 'cash')->sum('total_amount');
        $mobileSales = (clone $sales)->where('payment_method', 'mobile_money')->sum('total_amount');
        $cardSales = (clone $sales)->where('payment_method', 'card')->sum('total_amount');
        $avgTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        return [
            'date' => $date->format('Y-m-d'),
            'total_sales' => (float) $totalSales,
            'total_transactions' => $totalTransactions,
            'cash_sales' => (float) $cashSales,
            'mobile_sales' => (float) $mobileSales,
            'card_sales' => (float) $cardSales,
            'average_transaction' => round($avgTransaction, 2),
        ];
    }

    // Helpers
    private function generateSaleNumber(int $userId): string
    {
        $today = now()->format('ymd');
        $count = GrowBizPOSSaleModel::where('user_id', $userId)
            ->whereDate('created_at', now())
            ->count() + 1;

        return "S{$today}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function generateShiftNumber(int $userId): string
    {
        $today = now()->format('ymd');
        $count = GrowBizPOSShiftModel::where('user_id', $userId)
            ->whereDate('created_at', now())
            ->count() + 1;

        return "SH{$today}-" . str_pad($count, 2, '0', STR_PAD_LEFT);
    }

    private function updateShiftTotals(int $shiftId, float $amount, string $paymentMethod): void
    {
        $shift = GrowBizPOSShiftModel::find($shiftId);
        if (!$shift) return;

        $shift->increment('total_sales', $amount);
        $shift->increment('transaction_count', $amount > 0 ? 1 : -1);

        if ($paymentMethod === 'cash') {
            $shift->increment('total_cash_sales', $amount);
        } elseif ($paymentMethod === 'mobile_money') {
            $shift->increment('total_mobile_sales', $amount);
        } elseif ($paymentMethod === 'card') {
            $shift->increment('total_card_sales', $amount);
        }
    }

    private function deductInventory(int $itemId, float $quantity, int $saleId): void
    {
        $item = InventoryItemModel::find($itemId);
        if ($item) {
            $item->decrement('current_stock', $quantity);
            // Could also create a stock movement record here
        }
    }

    private function restoreInventory(int $itemId, float $quantity): void
    {
        $item = InventoryItemModel::find($itemId);
        if ($item) {
            $item->increment('current_stock', $quantity);
        }
    }

    private function mapShift($shift): array
    {
        return [
            'id' => $shift->id,
            'shift_number' => $shift->shift_number,
            'opening_cash' => (float) $shift->opening_cash,
            'closing_cash' => $shift->closing_cash ? (float) $shift->closing_cash : null,
            'expected_cash' => $shift->expected_cash ? (float) $shift->expected_cash : null,
            'cash_difference' => $shift->cash_difference ? (float) $shift->cash_difference : null,
            'total_sales' => (float) $shift->total_sales,
            'total_cash_sales' => (float) $shift->total_cash_sales,
            'total_mobile_sales' => (float) $shift->total_mobile_sales,
            'total_card_sales' => (float) $shift->total_card_sales,
            'transaction_count' => $shift->transaction_count,
            'started_at' => $shift->started_at->format('Y-m-d H:i:s'),
            'ended_at' => $shift->ended_at?->format('Y-m-d H:i:s'),
            'status' => $shift->status,
            'duration' => $shift->ended_at 
                ? $shift->started_at->diffForHumans($shift->ended_at, true)
                : $shift->started_at->diffForHumans(now(), true),
        ];
    }

    private function mapSale($sale): array
    {
        return [
            'id' => $sale->id,
            'sale_number' => $sale->sale_number,
            'customer_name' => $sale->customer_name,
            'customer_phone' => $sale->customer_phone,
            'item_count' => $sale->item_count,
            'subtotal' => (float) $sale->subtotal,
            'discount_amount' => (float) $sale->discount_amount,
            'tax_amount' => (float) $sale->tax_amount,
            'total_amount' => (float) $sale->total_amount,
            'payment_method' => $sale->payment_method,
            'payment_method_label' => ucfirst(str_replace('_', ' ', $sale->payment_method)),
            'amount_paid' => (float) $sale->amount_paid,
            'change_given' => (float) $sale->change_given,
            'status' => $sale->status,
            'notes' => $sale->notes,
            'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
            'formatted_time' => $sale->created_at->format('g:i A'),
            'items' => $sale->items->map(fn($i) => [
                'id' => $i->id,
                'product_name' => $i->product_name,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
                'discount' => (float) $i->discount,
                'total' => (float) $i->total,
            ])->toArray(),
        ];
    }
}
