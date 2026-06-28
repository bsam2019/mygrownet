<?php

namespace App\Domain\POS\Services;

use App\Infrastructure\Persistence\Eloquent\POSShiftModel;
use App\Infrastructure\Persistence\Eloquent\POSSaleModel;
use App\Infrastructure\Persistence\Eloquent\POSSaleItemModel;
use App\Infrastructure\Persistence\Eloquent\POSSettingsModel;
use App\Infrastructure\Persistence\Eloquent\POSQuickProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Standalone POS Service
 * 
 * This service can be used by any module (GrowBiz, BizBoost, standalone POS)
 * The module_context parameter determines which module is using the service
 */
class POSService
{
    protected string $moduleContext = 'pos';
    protected ?int $userId = null;

    /**
     * Set the module context for all operations
     */
    public function forModule(string $module): self
    {
        $this->moduleContext = $module;
        return $this;
    }

    /**
     * Set the user for all operations
     */
    public function forUser(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Get current user ID
     */
    protected function getUserId(): int
    {
        return $this->userId ?? auth()->id();
    }

    // ==================== SHIFT MANAGEMENT ====================

    /**
     * Start a new shift
     */
    public function startShift(array $data): POSShiftModel
    {
        $userId = $this->getUserId();
        
        // Check for existing open shift
        $existingShift = POSShiftModel::where('user_id', $userId)
            ->where('module_context', $this->moduleContext)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            throw new \Exception('You already have an open shift. Please close it first.');
        }

        return POSShiftModel::create([
            'user_id' => $userId,
            'module_context' => $this->moduleContext,
            'shift_number' => $this->generateShiftNumber(),
            'opening_cash' => $data['opening_cash'] ?? 0,
            'opening_notes' => $data['opening_notes'] ?? null,
            'started_at' => now(),
            'status' => 'open',
            'operator_type' => $data['operator_type'] ?? null,
            'operator_id' => $data['operator_id'] ?? null,
        ]);
    }

    /**
     * Close current shift
     */
    public function closeShift(int $shiftId, array $data): POSShiftModel
    {
        $shift = POSShiftModel::where('id', $shiftId)
            ->where('user_id', $this->getUserId())
            ->where('status', 'open')
            ->firstOrFail();

        $closingCash = $data['closing_cash'] ?? 0;
        $expectedCash = $shift->opening_cash + $shift->total_cash_sales;

        $shift->update([
            'closing_cash' => $closingCash,
            'expected_cash' => $expectedCash,
            'cash_difference' => $closingCash - $expectedCash,
            'closing_notes' => $data['closing_notes'] ?? null,
            'ended_at' => now(),
            'status' => 'closed',
        ]);

        return $shift->fresh();
    }

    /**
     * Get current open shift
     */
    public function getCurrentShift(): ?POSShiftModel
    {
        return POSShiftModel::where('user_id', $this->getUserId())
            ->where('module_context', $this->moduleContext)
            ->where('status', 'open')
            ->first();
    }

    /**
     * Get shifts with optional filters
     */
    public function getShifts(array $filters = [])
    {
        $query = POSShiftModel::where('user_id', $this->getUserId())
            ->where('module_context', $this->moduleContext);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('started_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('started_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('started_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    // ==================== SALES MANAGEMENT ====================

    /**
     * Create a new sale
     */
    public function createSale(array $data): POSSaleModel
    {
        $userId = $this->getUserId();
        $shift = $this->getCurrentShift();

        return DB::transaction(function () use ($data, $userId, $shift) {
            // Create sale
            $sale = POSSaleModel::create([
                'user_id' => $userId,
                'shift_id' => $shift?->id,
                'module_context' => $this->moduleContext,
                'sale_number' => $this->generateSaleNumber(),
                'customer_type' => $data['customer_type'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_reference' => $data['payment_reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'served_by_type' => $data['served_by_type'] ?? null,
                'served_by_id' => $data['served_by_id'] ?? null,
                'currency' => $data['currency'] ?? 'ZMW',
            ]);

            // Add items
            $subtotal = 0;
            $itemCount = 0;

            foreach ($data['items'] as $item) {
                $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                
                POSSaleItemModel::create([
                    'sale_id' => $sale->id,
                    'inventory_item_id' => $item['inventory_item_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'piece',
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $item['cost_price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotal,
                ]);

                $subtotal += $itemTotal;
                $itemCount += $item['quantity'];

                // Update inventory if tracking enabled
                if (isset($item['inventory_item_id']) && $this->shouldTrackInventory()) {
                    $this->decrementStock($item['inventory_item_id'], $item['quantity'], $sale->id);
                }
            }

            // Calculate totals
            $discountAmount = $data['discount_amount'] ?? 0;
            $taxAmount = $data['tax_amount'] ?? 0;
            $totalAmount = $subtotal - $discountAmount + $taxAmount;
            $amountPaid = $data['amount_paid'] ?? $totalAmount;

            $sale->update([
                'item_count' => $itemCount,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'change_given' => max(0, $amountPaid - $totalAmount),
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Update shift totals
            if ($shift) {
                $this->updateShiftTotals($shift, $sale);
            }

            return $sale->fresh(['items']);
        });
    }

    /**
     * Get sales with optional filters
     */
    public function getSales(array $filters = [])
    {
        $query = POSSaleModel::where('user_id', $this->getUserId())
            ->where('module_context', $this->moduleContext)
            ->with(['items']);

        if (isset($filters['shift_id'])) {
            $query->where('shift_id', $filters['shift_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get sale by ID
     */
    public function getSale(int $saleId): ?POSSaleModel
    {
        return POSSaleModel::where('id', $saleId)
            ->where('user_id', $this->getUserId())
            ->with(['items'])
            ->first();
    }

    /**
     * Void a sale
     */
    public function voidSale(int $saleId, string $reason = null): POSSaleModel
    {
        $sale = POSSaleModel::where('id', $saleId)
            ->where('user_id', $this->getUserId())
            ->where('status', 'completed')
            ->firstOrFail();

        return DB::transaction(function () use ($sale, $reason) {
            // Restore inventory
            foreach ($sale->items as $item) {
                if ($item->inventory_item_id && $this->shouldTrackInventory()) {
                    $this->incrementStock($item->inventory_item_id, $item->quantity, $sale->id, 'void');
                }
            }

            // Update sale status
            $sale->update([
                'status' => 'voided',
                'notes' => $sale->notes . "\n[VOIDED] " . ($reason ?? 'No reason provided'),
            ]);

            // Update shift totals if applicable
            if ($sale->shift_id) {
                $shift = POSShiftModel::find($sale->shift_id);
                if ($shift && $shift->status === 'open') {
                    $this->recalculateShiftTotals($shift);
                }
            }

            return $sale->fresh();
        });
    }

    // ==================== SETTINGS ====================

    /**
     * Get POS settings
     */
    public function getSettings(): ?POSSettingsModel
    {
        return POSSettingsModel::firstOrCreate(
            [
                'user_id' => $this->getUserId(),
                'module_context' => $this->moduleContext,
            ],
            [
                'currency' => 'ZMW',
                'currency_symbol' => 'K',
                'track_inventory' => true,
            ]
        );
    }

    /**
     * Update POS settings
     */
    public function updateSettings(array $data): POSSettingsModel
    {
        $settings = $this->getSettings();
        $settings->update($data);
        return $settings->fresh();
    }

    // ==================== QUICK PRODUCTS ====================

    /**
     * Get quick products
     */
    public function getQuickProducts()
    {
        return POSQuickProductModel::where('user_id', $this->getUserId())
            ->where('module_context', $this->moduleContext)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Sync quick products with inventory
     */
    public function syncQuickProducts(array $productIds): void
    {
        $userId = $this->getUserId();

        // Remove existing
        POSQuickProductModel::where('user_id', $userId)
            ->where('module_context', $this->moduleContext)
            ->delete();

        // Add new
        $items = InventoryItemModel::whereIn('id', $productIds)
            ->where('user_id', $userId)
            ->get();

        foreach ($items as $index => $item) {
            POSQuickProductModel::create([
                'user_id' => $userId,
                'module_context' => $this->moduleContext,
                'inventory_item_id' => $item->id,
                'name' => $item->name,
                'price' => $item->selling_price,
                'sort_order' => $index,
            ]);
        }
    }

    // ==================== REPORTS ====================

    /**
     * Get daily report
     */
    public function getDailyReport(string $date = null): array
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $userId = $this->getUserId();

        $sales = POSSaleModel::where('user_id', $userId)
            ->where('module_context', $this->moduleContext)
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->get();

        return [
            'date' => $date->toDateString(),
            'total_sales' => $sales->sum('total_amount'),
            'transaction_count' => $sales->count(),
            'average_sale' => $sales->count() > 0 ? $sales->avg('total_amount') : 0,
            'cash_sales' => $sales->where('payment_method', 'cash')->sum('total_amount'),
            'mobile_sales' => $sales->where('payment_method', 'mobile_money')->sum('total_amount'),
            'card_sales' => $sales->where('payment_method', 'card')->sum('total_amount'),
            'items_sold' => $sales->sum('item_count'),
        ];
    }

    /**
     * Get weekly statistics
     */
    public function getWeeklyStats(): array
    {
        $userId = $this->getUserId();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $sales = POSSaleModel::where('user_id', $userId)
            ->where('module_context', $this->moduleContext)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('status', 'completed')
            ->get();

        // Daily breakdown
        $dailyStats = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $daySales = $sales->filter(fn($s) => Carbon::parse($s->created_at)->isSameDay($date));
            $dailyStats[] = [
                'date' => $date->toDateString(),
                'day' => $date->format('D'),
                'total' => $daySales->sum('total_amount'),
                'count' => $daySales->count(),
            ];
        }

        return [
            'total_sales' => $sales->sum('total_amount'),
            'transaction_count' => $sales->count(),
            'average_sale' => $sales->count() > 0 ? round($sales->avg('total_amount'), 2) : 0,
            'items_sold' => $sales->sum('item_count'),
            'daily_breakdown' => $dailyStats,
            'payment_breakdown' => [
                'cash' => $sales->where('payment_method', 'cash')->sum('total_amount'),
                'mobile_money' => $sales->where('payment_method', 'mobile_money')->sum('total_amount'),
                'card' => $sales->where('payment_method', 'card')->sum('total_amount'),
            ],
        ];
    }

    // ==================== HELPERS ====================

    protected function generateShiftNumber(): string
    {
        $prefix = strtoupper(substr($this->moduleContext, 0, 2));
        $date = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "SH-{$prefix}-{$date}-{$random}";
    }

    protected function generateSaleNumber(): string
    {
        $prefix = strtoupper(substr($this->moduleContext, 0, 2));
        $date = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "SL-{$prefix}-{$date}-{$random}";
    }

    protected function shouldTrackInventory(): bool
    {
        $settings = $this->getSettings();
        return $settings->track_inventory ?? true;
    }

    protected function updateShiftTotals(POSShiftModel $shift, POSSaleModel $sale): void
    {
        $shift->increment('total_sales', $sale->total_amount);
        $shift->increment('transaction_count');

        match ($sale->payment_method) {
            'cash' => $shift->increment('total_cash_sales', $sale->total_amount),
            'mobile_money' => $shift->increment('total_mobile_sales', $sale->total_amount),
            'card' => $shift->increment('total_card_sales', $sale->total_amount),
            default => null,
        };
    }

    protected function recalculateShiftTotals(POSShiftModel $shift): void
    {
        $sales = POSSaleModel::where('shift_id', $shift->id)
            ->where('status', 'completed')
            ->get();

        $shift->update([
            'total_sales' => $sales->sum('total_amount'),
            'total_cash_sales' => $sales->where('payment_method', 'cash')->sum('total_amount'),
            'total_mobile_sales' => $sales->where('payment_method', 'mobile_money')->sum('total_amount'),
            'total_card_sales' => $sales->where('payment_method', 'card')->sum('total_amount'),
            'transaction_count' => $sales->count(),
        ]);
    }

    protected function decrementStock(int $itemId, float $quantity, int $saleId): void
    {
        $item = InventoryItemModel::find($itemId);
        if ($item && $item->track_stock) {
            $item->decrement('current_stock', $quantity);
            // Stock movement is handled by inventory service
        }
    }

    protected function incrementStock(int $itemId, float $quantity, int $saleId, string $reason): void
    {
        $item = InventoryItemModel::find($itemId);
        if ($item && $item->track_stock) {
            $item->increment('current_stock', $quantity);
        }
    }
}
