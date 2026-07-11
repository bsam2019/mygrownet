<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\InventoryService;
use App\Domain\StockFlow\Services\DepartmentBinService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
        private DepartmentBinService $departmentBinService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $items = $this->inventoryService->getItemsForCompany($companyId);

        return Inertia::render('StockAudit/Items/Index', [
            'items' => $items,
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $bins = $this->departmentBinService->getBins($companyId);

        return Inertia::render('StockAudit/Items/Create', [
            'bins' => $bins,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sa_bin_id' => 'nullable|exists:sa_bins,id',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'unit_price' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'system_quantity' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'is_expirable' => 'boolean',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $this->inventoryService->createItem($companyId, $validated);

        return redirect()->route('stock-audit.items.index');
    }

    public function show(int $itemId)
    {
        $companyId = session('stock_audit_company_id');
        $item = $this->inventoryService->getItemById($itemId, $companyId);

        if (!$item) {
            abort(404);
        }

        return Inertia::render('StockAudit/Items/Show', [
            'item' => $item->toArray(),
        ]);
    }

    public function update(Request $request, int $itemId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sa_bin_id' => 'nullable|exists:sa_bins,id',
            'sku' => 'nullable|string|max:100',
            'unit_price' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'system_quantity' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $this->inventoryService->updateItem($itemId, $companyId, $validated);

        return redirect()->route('stock-audit.items.index');
    }

    public function destroy(int $itemId)
    {
        $companyId = session('stock_audit_company_id');
        $this->inventoryService->deleteItem($itemId, $companyId);

        return redirect()->route('stock-audit.items.index');
    }

    public function import(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.sa_bin_id' => 'nullable|exists:sa_bins,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.system_quantity' => 'required|numeric|min:0',
        ]);

        foreach ($validated['items'] as $itemData) {
            $this->inventoryService->createItem($companyId, $itemData);
        }

        return redirect()->route('stock-audit.items.index');
    }

    public function adjustStock(Request $request, int $itemId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'new_quantity' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'type' => 'required|string|in:adjustment_in,adjustment_out,damage_out,expired_out,return_in',
        ]);

        $this->inventoryService->adjustStock(
            $itemId,
            $companyId,
            $validated['new_quantity'],
            $validated['type'],
            $validated['reason'],
            $request->user()->id,
        );

        return redirect()->route('stock-audit.items.show', $itemId);
    }
}
