<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\WarehouseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function __construct(private WarehouseService $warehouseService) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/Warehouses/Index', [
            'warehouses' => array_map(fn($w) => $w->toArray(), $this->warehouseService->getWarehouses($companyId)),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_default' => 'boolean',
        ]);
        $this->warehouseService->createWarehouse($companyId, $validated);
        return redirect()->back()->with('success', 'Warehouse created.');
    }

    public function update(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_default' => 'boolean',
        ]);
        $this->warehouseService->updateWarehouse($id, $companyId, $validated);
        return redirect()->back()->with('success', 'Warehouse updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $this->warehouseService->deleteWarehouse($id, $companyId);
        return redirect()->back()->with('success', 'Warehouse deleted.');
    }
}
