<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\SaleReturnService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleReturnController extends Controller
{
    public function __construct(private SaleReturnService $service) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/SaleReturns/Index', [
            'returns' => $this->service->getReturns($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'sa_sale_id' => 'required|integer|exists:sa_sales,id',
            'reason' => 'required|string',
            'total_refund' => 'required|numeric|min:0',
            'return_date' => 'nullable|date',
            'refund_method' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sa_item_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric',
            'items.*.subtotal' => 'nullable|numeric',
            'items.*.condition' => 'nullable|string',
        ]);
        $this->service->createReturn($companyId, $validated, $request->user()->id);
        return redirect()->back()->with('success', 'Sale return processed.');
    }
}
