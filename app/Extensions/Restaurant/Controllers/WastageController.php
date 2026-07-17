<?php

namespace App\Extensions\Restaurant\Controllers;

use App\Http\Controllers\Controller;
use App\Extensions\Restaurant\Services\RestaurantService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WastageController extends Controller
{
    public function __construct(private RestaurantService $service) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/Restaurant/Wastage/Index', [
            'records' => $this->service->getWastageRecords($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'sa_item_id' => 'required|integer',
            'quantity' => 'required|numeric|min:0',
            'reason' => 'required|in:spoilage,trim,overproduction,expiry,other',
            'unit_cost' => 'nullable|numeric|min:0',
            'occurred_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $this->service->recordWastage($companyId, $validated['sa_item_id'], $validated['quantity'], $validated['reason'], $validated['unit_cost'] ?? 0, $validated['notes'] ?? null, $validated['occurred_at'] ?? null);
        return redirect()->back()->with('success', 'Wastage recorded.');
    }
}
