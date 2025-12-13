<?php

namespace App\Domain\Inventory\Services;

use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\InventoryCategoryModel;
use App\Infrastructure\Persistence\Eloquent\StockMovementModel;
use App\Infrastructure\Persistence\Eloquent\InventoryAlertModel;
use Illuminate\Support\Facades\DB;

/**
 * Standalone Inventory Service
 * 
 * This service can be used by any module (GrowBiz, BizBoost, POS, standalone)
 * The module_context parameter determines which module is using the service
 */
class InventoryService
{
    protected string $moduleContext = 'inventory';
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

    // ==================== CATEGORIES ====================

    /**
     * Get all categories
     */
    public function getCategories()
    {
        return InventoryCategoryModel::where('user_id', $this->getUserId())
            ->withCount('items')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Create a category
     */
    public function createCategory(array $data): InventoryCategoryModel
    {
        return InventoryCategoryModel::create([
            'user_id' => $this->getUserId(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#6b7280',
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    /**
     * Update a category
     */
    public function updateCategory(int $categoryId, array $data): InventoryCategoryModel
    {
        $category = InventoryCategoryModel::where('id', $categoryId)
            ->where('user_id', $this->getUserId())
            ->firstOrFail();

        $category->update($data);
        return $category->fresh();
    }

    /**
     * Delete a category
     */
    public function deleteCategory(int $categoryId): bool
    {
        return InventoryCategoryModel::where('id', $categoryId)
            ->where('user_id', $this->getUserId())
            ->delete() > 0;
    }

    // ==================== ITEMS ====================

    /**
     * Get items with optional filters
     */
    public function getItems(array $filters = [])
    {
        $query = InventoryItemModel::where('user_id', $this->getUserId())
            ->with('category');

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->whereColumn('current_stock', '<=', 'low_stock_threshold');
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $query->orderBy($sortBy, $sortDir);

        return isset($filters['per_page']) 
            ? $query->paginate($filters['per_page'])
            : $query->get();
    }

    /**
     * Get a single item
     */
    public function getItem(int $itemId): ?InventoryItemModel
    {
        return InventoryItemModel::where('id', $itemId)
            ->where('user_id', $this->getUserId())
            ->with('category')
            ->first();
    }

    /**
     * Find item by SKU or barcode
     */
    public function findBySkuOrBarcode(string $code): ?InventoryItemModel
    {
        return InventoryItemModel::where('user_id', $this->getUserId())
            ->where(function ($q) use ($code) {
                $q->where('sku', $code)->orWhere('barcode', $code);
            })
            ->first();
    }

    /**
     * Create an item
     */
    public function createItem(array $data): InventoryItemModel
    {
        $item = InventoryItemModel::create([
            'user_id' => $this->getUserId(),
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'unit' => $data['unit'] ?? 'piece',
            'cost_price' => $data['cost_price'] ?? 0,
            'selling_price' => $data['selling_price'] ?? 0,
            'current_stock' => $data['current_stock'] ?? 0,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 10,
            'location' => $data['location'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'track_stock' => $data['track_stock'] ?? true,
        ]);

        // Record initial stock movement
        if ($item->current_stock > 0) {
            $this->recordMovement($item->id, 'initial', $item->current_stock, 'Initial stock');
        }

        return $item;
    }

    /**
     * Update an item
     */
    public function updateItem(int $itemId, array $data): InventoryItemModel
    {
        $item = InventoryItemModel::where('id', $itemId)
            ->where('user_id', $this->getUserId())
            ->firstOrFail();

        $item->update($data);
        return $item->fresh();
    }

    /**
     * Delete an item (soft delete)
     */
    public function deleteItem(int $itemId): bool
    {
        return InventoryItemModel::where('id', $itemId)
            ->where('user_id', $this->getUserId())
            ->delete() > 0;
    }

    // ==================== STOCK MANAGEMENT ====================

    /**
     * Adjust stock level
     */
    public function adjustStock(int $itemId, int $quantity, string $type, string $notes = null, $reference = null): InventoryItemModel
    {
        $item = InventoryItemModel::where('id', $itemId)
            ->where('user_id', $this->getUserId())
            ->firstOrFail();

        if (!$item->track_stock) {
            return $item;
        }

        return DB::transaction(function () use ($item, $quantity, $type, $notes, $reference) {
            $stockBefore = $item->current_stock;
            $stockAfter = $stockBefore + $quantity;

            // Update stock
            $item->update(['current_stock' => $stockAfter]);

            // Record movement
            $this->recordMovement(
                $item->id,
                $type,
                $quantity,
                $notes,
                $stockBefore,
                $stockAfter,
                $reference
            );

            // Check for alerts
            $this->checkStockAlerts($item->fresh());

            return $item->fresh();
        });
    }

    /**
     * Add stock (purchase, return, etc.)
     */
    public function addStock(int $itemId, int $quantity, string $type = 'purchase', string $notes = null): InventoryItemModel
    {
        return $this->adjustStock($itemId, abs($quantity), $type, $notes);
    }

    /**
     * Remove stock (sale, damage, etc.)
     */
    public function removeStock(int $itemId, int $quantity, string $type = 'sale', string $notes = null, $reference = null): InventoryItemModel
    {
        return $this->adjustStock($itemId, -abs($quantity), $type, $notes, $reference);
    }

    /**
     * Record a stock movement
     */
    protected function recordMovement(
        int $itemId,
        string $type,
        int $quantity,
        string $notes = null,
        int $stockBefore = null,
        int $stockAfter = null,
        $reference = null
    ): StockMovementModel {
        $item = InventoryItemModel::find($itemId);
        
        return StockMovementModel::create([
            'item_id' => $itemId,
            'user_id' => $this->getUserId(),
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore ?? $item->current_stock,
            'stock_after' => $stockAfter ?? ($item->current_stock + $quantity),
            'unit_cost' => $item->cost_price,
            'total_value' => abs($quantity) * $item->cost_price,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'notes' => $notes,
            'movement_date' => now(),
        ]);
    }

    /**
     * Get stock movements for an item
     */
    public function getStockMovements(int $itemId, array $filters = [])
    {
        $query = StockMovementModel::where('item_id', $itemId)
            ->where('user_id', $this->getUserId());

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('movement_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('movement_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('movement_date', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    // ==================== ALERTS ====================

    /**
     * Check and create stock alerts
     */
    protected function checkStockAlerts(InventoryItemModel $item): void
    {
        $userId = $this->getUserId();

        // Clear existing unacknowledged alerts for this item
        InventoryAlertModel::where('item_id', $item->id)
            ->where('user_id', $userId)
            ->where('is_acknowledged', false)
            ->delete();

        // Check for out of stock
        if ($item->current_stock <= 0) {
            InventoryAlertModel::create([
                'item_id' => $item->id,
                'user_id' => $userId,
                'type' => 'out_of_stock',
                'threshold_value' => 0,
                'current_value' => $item->current_stock,
            ]);
        }
        // Check for low stock
        elseif ($item->current_stock <= $item->low_stock_threshold) {
            InventoryAlertModel::create([
                'item_id' => $item->id,
                'user_id' => $userId,
                'type' => 'low_stock',
                'threshold_value' => $item->low_stock_threshold,
                'current_value' => $item->current_stock,
            ]);
        }
    }

    /**
     * Get active alerts
     */
    public function getAlerts(bool $unacknowledgedOnly = true)
    {
        $query = InventoryAlertModel::where('user_id', $this->getUserId())
            ->with('item');

        if ($unacknowledgedOnly) {
            $query->where('is_acknowledged', false);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Acknowledge an alert
     */
    public function acknowledgeAlert(int $alertId): bool
    {
        return InventoryAlertModel::where('id', $alertId)
            ->where('user_id', $this->getUserId())
            ->update([
                'is_acknowledged' => true,
                'acknowledged_at' => now(),
            ]) > 0;
    }

    // ==================== REPORTS ====================

    /**
     * Get inventory summary
     */
    public function getSummary(): array
    {
        $userId = $this->getUserId();
        $items = InventoryItemModel::where('user_id', $userId)->get();
        $recentMovementsCount = StockMovementModel::where('user_id', $userId)
            ->where('movement_date', '>=', now()->subDays(7))
            ->count();

        return [
            'total_items' => $items->count(),
            'active_items' => $items->where('is_active', true)->count(),
            'total_stock_value' => $items->sum(fn($i) => $i->current_stock * $i->cost_price),
            'total_retail_value' => $items->sum(fn($i) => $i->current_stock * $i->selling_price),
            'low_stock_count' => $items->filter(fn($i) => $i->current_stock <= $i->low_stock_threshold)->count(),
            'out_of_stock_count' => $items->where('current_stock', '<=', 0)->count(),
            'categories_count' => InventoryCategoryModel::where('user_id', $userId)->count(),
            'recent_movements_count' => $recentMovementsCount,
        ];
    }
    
    /**
     * Get recent stock movements across all items
     */
    public function getRecentMovements(int $limit = 10)
    {
        return StockMovementModel::where('user_id', $this->getUserId())
            ->with('item')
            ->orderBy('movement_date', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get all movements with filters
     */
    public function getAllMovements(array $filters = [])
    {
        $query = StockMovementModel::where('user_id', $this->getUserId())
            ->with('item');

        if (isset($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('movement_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('movement_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('movement_date', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems()
    {
        return InventoryItemModel::where('user_id', $this->getUserId())
            ->where('is_active', true)
            ->where('track_stock', true)
            ->whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->with('category')
            ->orderBy('current_stock')
            ->get();
    }
}
