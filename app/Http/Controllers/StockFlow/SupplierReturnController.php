<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\SupplierReturnService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierReturnController extends Controller
{
    public function __construct(private SupplierReturnService $service) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/SupplierReturns/Index', [
            'returns' => $this->service->getReturns($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'sa_supplier_id' => 'required|integer|exists:sa_suppliers,id',
            'sa_purchase_order_id' => 'nullable|integer|exists:sa_purchase_orders,id',
            'reason' => 'required|string',
            'total_refund' => 'required|numeric|min:0',
            'return_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sa_item_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'nullable|numeric',
            'items.*.subtotal' => 'nullable|numeric',
        ]);
        $this->service->createReturn($companyId, $validated, $request->user()->id);
        return redirect()->back()->with('success', 'Supplier return processed.');
    }
}
