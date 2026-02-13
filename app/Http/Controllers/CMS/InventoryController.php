<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\InventoryService;
use App\Infrastructure\Persistence\Eloquent\CMS\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LowStockAlertModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $items = InventoryItemModel::forCompany($companyId)
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
            )
            ->when($request->category, fn($q) => $q->byCategory($request->category))
            ->when($request->low_stock, fn($q) => $q->lowStock())
            ->with('createdBy.user')
            ->orderBy('name')
            ->paginate(20);

        // Get categories for filter
        $categories = InventoryItemModel::forCompany($companyId)
            ->select('category')
            ->distinct()
            ->pluck('category');

        // Get low stock count
        $lowStockCount = InventoryItemModel::forCompany($companyId)
            ->lowStock()
            ->active()
            ->count();

        return Inertia::render('CMS/Inventory/Index', [
            'items' => $items,
            'categories' => $categories,
            'lowStockCount' => $lowStockCount,
            'filters' => $request->only(['search', 'category', 'low_stock']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CMS/Inventory/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $userId = $request->user()->cmsUser->id;

        $item = $this->inventoryService->createItem([
            ...$validated,
            'company_id' => $companyId,
            'created_by' => $userId,
        ]);

        return redirect()
            ->route('cms.inventory.show', $item->id)
            ->with('success', 'Inventory item created successfully');
    }

    public function show(InventoryItemModel $inventory): Response
    {
        $inventory->load(['createdBy.user', 'lowStockAlerts' => fn($q) => $q->unresolved()]);

        // Get stock movement history
        $movements = $this->inventoryService->getStockMovementHistory($inventory->id, 20);

        // Get job usage
        $jobUsage = $inventory->jobUsages()
            ->with('job.customer')
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('CMS/Inventory/Show', [
            'item' => $inventory,
            'movements' => $movements,
            'jobUsage' => $jobUsage,
        ]);
    }

    public function edit(InventoryItemModel $inventory): Response
    {
        return Inertia::render('CMS/Inventory/Edit', [
            'item' => $inventory,
        ]);
    }

    public function update(Request $request, InventoryItemModel $inventory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $userId = $request->user()->cmsUser->id;

        $this->inventoryService->updateItem($inventory, $validated, $userId);

        return redirect()
            ->route('cms.inventory.show', $inventory->id)
            ->with('success', 'Inventory item updated successfully');
    }

    public function recordMovement(Request $request, InventoryItemModel $inventory)
    {
        $validated = $request->validate([
            'movement_type' => 'required|in:purchase,usage,adjustment,return,damage,transfer',
            'quantity' => 'required|integer',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $userId = $request->user()->cmsUser->id;

        $this->inventoryService->recordStockMovement([
            'inventory_item_id' => $inventory->id,
            ...$validated,
            'created_by' => $userId,
        ]);

        return back()->with('success', 'Stock movement recorded successfully');
    }

    public function lowStockAlerts(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $alerts = LowStockAlertModel::forCompany($companyId)
            ->unresolved()
            ->with('inventoryItem')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('CMS/Inventory/LowStockAlerts', [
            'alerts' => $alerts,
        ]);
    }
}
