<?php

namespace App\Extensions\Manufacturing\Controllers;

use App\Http\Controllers\Controller;
use App\Extensions\Manufacturing\Services\ManufacturingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkOrderController extends Controller
{
    public function __construct(private ManufacturingService $service) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/Manufacturing/WorkOrders/Index', [
            'workOrders' => $this->service->getWorkOrders($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'sa_item_id' => 'required|integer',
            'order_number' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'sa_bom_id' => 'nullable|integer',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $this->service->createWorkOrder($companyId, $validated['sa_item_id'], $validated['order_number'], $validated['quantity'], $validated['sa_bom_id'] ?? null, $validated['due_date'] ?? null, $validated['notes'] ?? null);
        return redirect()->back()->with('success', 'Work order created.');
    }
}
