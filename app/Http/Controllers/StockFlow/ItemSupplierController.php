<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaSupplierModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemSupplierController extends Controller
{
    public function index(int $itemId)
    {
        $companyId = session('stockflow_company_id');
        $item = SaItemModel::where('id', $itemId)->where('sa_company_id', $companyId)->firstOrFail();

        $suppliers = $item->suppliers()->withPivot('supplier_sku', 'supplier_price', 'lead_time_days', 'is_preferred')->get();

        return response()->json(['suppliers' => $suppliers]);
    }

    public function store(Request $request, int $itemId)
    {
        $companyId = session('stockflow_company_id');
        $item = SaItemModel::where('id', $itemId)->where('sa_company_id', $companyId)->firstOrFail();

        $validated = $request->validate([
            'sa_supplier_id' => 'required|exists:sa_suppliers,id',
            'supplier_sku' => 'nullable|string|max:100',
            'supplier_price' => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'is_preferred' => 'boolean',
        ]);

        $supplier = SaSupplierModel::where('id', $validated['sa_supplier_id'])->where('sa_company_id', $companyId)->firstOrFail();

        $item->suppliers()->syncWithoutDetaching([
            $supplier->id => [
                'supplier_sku' => $validated['supplier_sku'] ?? null,
                'supplier_price' => $validated['supplier_price'] ?? null,
                'lead_time_days' => $validated['lead_time_days'] ?? null,
                'is_preferred' => $validated['is_preferred'] ?? false,
            ],
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(int $itemId, int $supplierId)
    {
        $companyId = session('stockflow_company_id');
        $item = SaItemModel::where('id', $itemId)->where('sa_company_id', $companyId)->firstOrFail();

        $item->suppliers()->detach($supplierId);

        return response()->json(['success' => true]);
    }
}
