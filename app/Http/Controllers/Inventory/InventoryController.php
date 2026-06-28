<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Domain\Inventory\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Standalone Inventory Controller
 * 
 * Handles inventory operations for standalone module access
 * For integrated access (via GrowBiz, etc.), use the respective module's controller
 */
class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService->forModule('inventory');
    }

    /**
     * Inventory Dashboard
     */
    public function index()
    {
        $summary = $this->inventoryService->getSummary();
        $lowStockItems = $this->inventoryService->getLowStockItems();
        $recentMovements = $this->inventoryService->getRecentMovements(5);

        return Inertia::render('Inventory/Dashboard', [
            'stats' => [
                'total_items' => $summary['total_items'] ?? 0,
                'total_categories' => $summary['categories_count'] ?? 0,
                'low_stock_count' => $summary['low_stock_count'] ?? 0,
                'out_of_stock_count' => $summary['out_of_stock_count'] ?? 0,
                'total_value' => $summary['total_stock_value'] ?? 0,
                'recent_movements' => $summary['recent_movements_count'] ?? 0,
            ],
            'lowStockItems' => $lowStockItems,
            'recentMovements' => $recentMovements,
        ]);
    }
    
    /**
     * Settings page
     */
    public function settings()
    {
        return Inertia::render('Inventory/Settings', [
            'settings' => [], // Placeholder for future settings
        ]);
    }

    /**
     * List items
     */
    public function items(Request $request)
    {
        $filters = $request->only(['category_id', 'is_active', 'low_stock', 'search', 'sort_by', 'sort_dir', 'per_page']);
        $items = $this->inventoryService->getItems(array_merge($filters, ['per_page' => $filters['per_page'] ?? 20]));
        $categories = $this->inventoryService->getCategories();

        return Inertia::render('Inventory/Items', [
            'items' => $items,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Show item details
     */
    public function showItem(int $itemId)
    {
        $item = $this->inventoryService->getItem($itemId);
        
        if (!$item) {
            abort(404, 'Item not found');
        }

        $movements = $this->inventoryService->getStockMovements($itemId, ['per_page' => 20]);

        return Inertia::render('Inventory/ItemShow', [
            'item' => $item,
            'movements' => $movements,
        ]);
    }

    /**
     * Create item form
     */
    public function createItem()
    {
        $categories = $this->inventoryService->getCategories();

        return Inertia::render('Inventory/ItemCreate', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store new item
     */
    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:inventory_categories,id',
            'unit' => 'nullable|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'current_stock' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
        ]);

        $item = $this->inventoryService->createItem($validated);
        return redirect()->route('inventory.items')->with('success', 'Item created successfully');
    }

    /**
     * Edit item form
     */
    public function editItem(int $itemId)
    {
        $item = $this->inventoryService->getItem($itemId);
        
        if (!$item) {
            abort(404, 'Item not found');
        }

        $categories = $this->inventoryService->getCategories();

        return Inertia::render('Inventory/ItemEdit', [
            'item' => $item,
            'categories' => $categories,
        ]);
    }

    /**
     * Update item
     */
    public function updateItem(Request $request, int $itemId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:inventory_categories,id',
            'unit' => 'nullable|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
        ]);

        $item = $this->inventoryService->updateItem($itemId, $validated);
        return redirect()->route('inventory.items.show', $itemId)->with('success', 'Item updated successfully');
    }

    /**
     * Delete item
     */
    public function deleteItem(int $itemId)
    {
        $this->inventoryService->deleteItem($itemId);
        return redirect()->route('inventory.items')->with('success', 'Item deleted successfully');
    }

    /**
     * Adjust stock
     */
    public function adjustStock(Request $request, int $itemId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|in:purchase,sale,adjustment,transfer,return,damage',
            'notes' => 'nullable|string|max:500',
        ]);

        $item = $this->inventoryService->adjustStock(
            $itemId,
            $validated['quantity'],
            $validated['type'],
            $validated['notes']
        );

        return back()->with('success', 'Stock adjusted successfully');
    }

    /**
     * Categories management
     */
    public function categories()
    {
        $categories = $this->inventoryService->getCategories();

        return Inertia::render('Inventory/Categories', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store category
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $category = $this->inventoryService->createCategory($validated);
        return back()->with('success', 'Category created successfully');
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, int $categoryId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $category = $this->inventoryService->updateCategory($categoryId, $validated);
        return back()->with('success', 'Category updated successfully');
    }

    /**
     * Delete category
     */
    public function deleteCategory(int $categoryId)
    {
        $this->inventoryService->deleteCategory($categoryId);
        return back()->with('success', 'Category deleted successfully');
    }

    /**
     * Stock movements history
     */
    public function movements(Request $request)
    {
        $filters = $request->only(['item_id', 'type', 'date_from', 'date_to', 'per_page']);
        $movements = $this->inventoryService->getAllMovements($filters);
        $items = $this->inventoryService->getItems(['is_active' => true]);

        return Inertia::render('Inventory/Movements', [
            'movements' => $movements,
            'items' => $items,
            'filters' => $filters,
        ]);
    }

    /**
     * Alerts
     */
    public function alerts()
    {
        $alerts = $this->inventoryService->getAlerts(false);

        return Inertia::render('Inventory/Alerts', [
            'alerts' => $alerts,
        ]);
    }

    /**
     * Acknowledge alert
     */
    public function acknowledgeAlert(int $alertId)
    {
        $this->inventoryService->acknowledgeAlert($alertId);
        return back()->with('success', 'Alert acknowledged');
    }

    /**
     * Find item by SKU or barcode (API)
     */
    public function findByCode(Request $request)
    {
        $code = $request->get('code');
        $item = $this->inventoryService->findBySkuOrBarcode($code);

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json($item);
    }

    /**
     * Get items for API (used by POS, etc.)
     */
    public function apiItems(Request $request)
    {
        $filters = $request->only(['category_id', 'search', 'is_active']);
        $filters['is_active'] = $filters['is_active'] ?? true;
        
        $items = $this->inventoryService->getItems($filters);

        return response()->json($items);
    }
}
