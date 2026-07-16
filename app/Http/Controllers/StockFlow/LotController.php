<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\LotService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LotController extends Controller
{
    public function __construct(private LotService $lotService) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $itemId = $request->get('item_id');
        return Inertia::render('StockFlow/Lots/Index', [
            'lots' => array_map(fn($l) => $l->toArray(), $this->lotService->getLots($companyId, $itemId ? (int) $itemId : null)),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'sa_item_id' => 'required|integer',
            'lot_number' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'received_date' => 'nullable|date',
        ]);
        $this->lotService->createLot($companyId, $validated);
        return redirect()->back()->with('success', 'Lot created.');
    }

    public function update(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate(['quantity' => 'required|numeric|min:0']);
        $this->lotService->adjustLotQuantity($id, $companyId, (float) $validated['quantity']);
        return redirect()->back()->with('success', 'Lot updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $this->lotService->deleteLot($id, $companyId);
        return redirect()->back()->with('success', 'Lot deleted.');
    }

    public function byItem(Request $request, int $itemId)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return response()->json(['lots' => array_map(fn($l) => $l->toArray(), $this->lotService->getLots($companyId, $itemId))]);
    }
}
