<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\InventoryService;
use App\Domain\StockFlow\Services\DepartmentBinService;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $companyId = $request->session()->get('stockflow_company_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = SaItemModel::where('sa_company_id', $companyId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

        return Inertia::render('StockFlow/Items/Index', [
            'items' => $items,
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $bins = $this->departmentBinService->getBins($companyId);

        return Inertia::render('StockFlow/Items/Create', [
            'bins' => $bins,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

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

        return redirect()->sfRoute('stockflow.items.index')->with('success', 'Item created successfully.');
    }

    public function show(int $itemId)
    {
        $companyId = session('stockflow_company_id');
        $item = $this->inventoryService->getItemById($itemId, $companyId);

        if (!$item) {
            abort(404);
        }

        return Inertia::render('StockFlow/Items/Show', [
            'item' => $item->toArray(),
        ]);
    }

    public function update(Request $request, int $itemId)
    {
        $companyId = $request->session()->get('stockflow_company_id');

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

        return redirect()->sfRoute('stockflow.items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(int $itemId)
    {
        $companyId = session('stockflow_company_id');
        $this->inventoryService->deleteItem($itemId, $companyId);

        return redirect()->sfRoute('stockflow.items.index')->with('success', 'Item deleted successfully.');
    }

    public function import(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

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

        return redirect()->sfRoute('stockflow.items.index')->with('success', 'Items imported successfully.');
    }

    public function adjustStock(Request $request, int $itemId)
    {
        $companyId = $request->session()->get('stockflow_company_id');

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

        return redirect()->sfRoute('stockflow.items.show', $itemId)->with('success', 'Stock adjusted successfully.');
    }

    public function inventoryReport(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $items = $this->inventoryService->getItemsForCompany($companyId);

        $summary = [
            'total_items' => count($items),
            'total_value' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
        ];

        $itemArray = [];
        foreach ($items as $item) {
            $arr = $item->toArray();
            $summary['total_value'] += ($arr['unit_price'] ?? 0) * ($arr['system_quantity'] ?? 0);
            if (($arr['system_quantity'] ?? 0) <= 0) $summary['out_of_stock']++;
            elseif (($arr['reorder_level'] ?? null) !== null && ($arr['system_quantity'] ?? 0) <= ($arr['reorder_level'] ?? 0)) $summary['low_stock']++;
            $itemArray[] = $arr;
        }

        return Inertia::render('StockFlow/Inventory/Report', [
            'items' => $itemArray,
            'summary' => $summary,
            'reportDate' => now()->format('Y-m-d'),
        ]);
    }

    public function inventoryReportPdf()
    {
        $companyId = session('stockflow_company_id');
        $companyModel = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel::find($companyId);
        $companyName = $companyModel?->name ?? 'Company';

        $items = $this->inventoryService->getItemsForCompany($companyId);

        $summary = ['total_items' => count($items), 'total_value' => 0, 'low_stock' => 0, 'out_of_stock' => 0];
        $itemArray = [];
        foreach ($items as $item) {
            $arr = $item->toArray();
            $summary['total_value'] += ($arr['unit_price'] ?? 0) * ($arr['system_quantity'] ?? 0);
            if (($arr['system_quantity'] ?? 0) <= 0) $summary['out_of_stock']++;
            elseif (($arr['reorder_level'] ?? null) !== null && ($arr['system_quantity'] ?? 0) <= ($arr['reorder_level'] ?? 0)) $summary['low_stock']++;
            $itemArray[] = $arr;
        }

        $pdf = Pdf::loadView('pdf.stockflow.inventory-report', [
            'companyName' => $companyName,
            'items' => $itemArray,
            'summary' => $summary,
            'reportDate' => now()->format('Y-m-d'),
        ]);

        return $pdf->download("inventory-report-{$summary['total_items']}-items.pdf");
    }
}
