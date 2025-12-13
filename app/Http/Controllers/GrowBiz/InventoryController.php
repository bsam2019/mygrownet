<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Domain\GrowBiz\Services\InventoryService;
use App\Domain\GrowBiz\ValueObjects\StockMovementType;
use App\Domain\GrowBiz\Exceptions\OperationFailedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = [
            'category_id' => $request->get('category'),
            'search' => $request->get('search'),
            'stock_status' => $request->get('stock_status'),
            'sort_by' => $request->get('sort_by', 'name'),
            'sort_dir' => $request->get('sort_dir', 'asc'),
        ];

        $items = $this->inventoryService->getItemsForUser($user->id, $filters);
        $stats = $this->inventoryService->getStatistics($user->id);
        $categories = $this->inventoryService->getCategories($user->id);

        $itemsArray = array_map(fn($item) => $item->toArray(), $items);

        return Inertia::render('GrowBiz/Inventory/Index', [
            'items' => $itemsArray,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => $filters,
            'movementTypes' => StockMovementType::all(),
        ]);
    }

    public function lowStock()
    {
        $user = Auth::user();
        
        $lowStockItems = $this->inventoryService->getLowStockItems($user->id);
        $outOfStockItems = $this->inventoryService->getOutOfStockItems($user->id);
        $stats = $this->inventoryService->getStatistics($user->id);

        return Inertia::render('GrowBiz/Inventory/LowStock', [
            'lowStockItems' => array_map(fn($i) => $i->toArray(), $lowStockItems),
            'outOfStockItems' => array_map(fn($i) => $i->toArray(), $outOfStockItems),
            'stats' => $stats,
        ]);
    }

    public function show(int $id)
    {
        $user = Auth::user();
        $item = $this->inventoryService->getItemById($id, $user->id);

        if (!$item) {
            abort(404, 'Item not found');
        }

        $movements = $this->inventoryService->getStockMovements($id);
        $categories = $this->inventoryService->getCategories($user->id);

        return Inertia::render('GrowBiz/Inventory/Show', [
            'item' => $item->toArray(),
            'movements' => $movements,
            'categories' => $categories,
            'movementTypes' => StockMovementType::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:inventory_categories,id',
            'unit' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'initial_stock' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();

        try {
            $item = $this->inventoryService->createItem(
                userId: $user->id,
                name: $validated['name'],
                sku: $validated['sku'] ?? null,
                description: $validated['description'] ?? null,
                categoryId: $validated['category_id'] ?? null,
                unit: $validated['unit'],
                costPrice: (float) $validated['cost_price'],
                sellingPrice: (float) $validated['selling_price'],
                initialStock: $validated['initial_stock'] ?? 0,
                lowStockThreshold: $validated['low_stock_threshold'],
                location: $validated['location'] ?? null,
                barcode: $validated['barcode'] ?? null
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'item' => $item->toArray(),
                    'message' => 'Item created successfully.',
                ]);
            }

            return redirect()->route('growbiz.inventory.show', $item->id())
                ->with('success', 'Item created successfully.');
        } catch (OperationFailedException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to create item.'], 500);
            }
            return back()->with('error', 'Failed to create item. Please try again.');
        }
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:inventory_categories,id',
            'unit' => 'sometimes|required|string|max:50',
            'cost_price' => 'sometimes|required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'low_stock_threshold' => 'sometimes|required|integer|min:0',
            'location' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();

        try {
            $item = $this->inventoryService->updateItem($id, $user->id, $validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'item' => $item->toArray(),
                    'message' => 'Item updated successfully.',
                ]);
            }

            return back()->with('success', 'Item updated successfully.');
        } catch (OperationFailedException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to update item.'], 500);
            }
            return back()->with('error', 'Failed to update item. Please try again.');
        }
    }

    public function adjustStock(Request $request, int $id)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:purchase,sale,adjustment,transfer,return,damage',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        try {
            $item = $this->inventoryService->adjustStock(
                itemId: $id,
                userId: $user->id,
                type: $validated['type'],
                quantity: $validated['quantity'],
                unitCost: $validated['unit_cost'] ?? null,
                notes: $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'item' => $item->toArray(),
                'message' => 'Stock adjusted successfully.',
            ]);
        } catch (OperationFailedException $e) {
            return response()->json(['success' => false, 'message' => 'Failed to adjust stock.'], 500);
        }
    }

    public function destroy(int $id)
    {
        $user = Auth::user();

        try {
            $this->inventoryService->deleteItem($id, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully.',
            ]);
        } catch (OperationFailedException $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete item.'], 500);
        }
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        $items = $this->inventoryService->searchItems($user->id, $query);

        return response()->json([
            'items' => array_map(fn($i) => $i->toArray(), $items),
        ]);
    }

    public function stats()
    {
        $user = Auth::user();
        $stats = $this->inventoryService->getStatistics($user->id);

        return response()->json($stats);
    }

    // Category Management
    public function categories()
    {
        $user = Auth::user();
        $categories = $this->inventoryService->getCategories($user->id);

        return response()->json(['categories' => $categories]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();

        try {
            $category = $this->inventoryService->createCategory(
                $user->id,
                $validated['name'],
                $validated['description'] ?? null,
                $validated['color'] ?? null
            );

            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => 'Category created successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create category.'], 500);
        }
    }

    public function updateCategory(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        try {
            $category = $this->inventoryService->updateCategory(
                $id,
                $validated['name'],
                $validated['description'] ?? null,
                $validated['color'] ?? null
            );

            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => 'Category updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update category.'], 500);
        }
    }

    public function destroyCategory(int $id)
    {
        try {
            $this->inventoryService->deleteCategory($id);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete category.'], 500);
        }
    }

    public function movements(int $id)
    {
        $user = Auth::user();
        $item = $this->inventoryService->getItemById($id, $user->id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found.'], 404);
        }

        $movements = $this->inventoryService->getStockMovements($id);

        return response()->json(['movements' => $movements]);
    }

    public function recentMovements()
    {
        $user = Auth::user();
        $movements = $this->inventoryService->getRecentMovements($user->id);

        return response()->json(['movements' => $movements]);
    }
}
