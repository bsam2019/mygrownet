<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\LotService;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaLotModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaPurchaseOrderItemModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaSaleItemModel;
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

    public function traceability(Request $request, int $lotId)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $lot = SaLotModel::with('item')->find($lotId);
        if (!$lot || $lot->item?->sa_company_id !== $companyId) {
            abort(404);
        }

        $purchases = SaPurchaseOrderItemModel::where('sa_lot_id', $lotId)
            ->whereHas('purchaseOrder', fn($q) => $q->where('sa_company_id', $companyId))
            ->with('purchaseOrder')
            ->get()
            ->map(fn($m) => [
                'id' => $m->purchaseOrder->id,
                'order_number' => $m->purchaseOrder->order_number,
                'order_date' => $m->purchaseOrder->order_date?->format('Y-m-d'),
                'quantity' => (float) $m->quantity_received,
            ]);

        $sales = SaSaleItemModel::where('sa_lot_id', $lotId)
            ->whereHas('sale', fn($q) => $q->where('sa_company_id', $companyId))
            ->with('sale')
            ->get()
            ->map(fn($m) => [
                'id' => $m->sale->id,
                'receipt_number' => $m->sale->receipt_number,
                'sale_date' => $m->sale->sale_date?->format('Y-m-d'),
                'quantity' => (float) $m->quantity,
                'customer' => null,
            ]);

        return Inertia::render('StockFlow/Lots/Traceability', [
            'lot' => [
                'id' => $lot->id,
                'lot_number' => $lot->lot_number,
                'item_name' => $lot->item?->name ?? 'Unknown',
                'expiry_date' => $lot->expiry_date?->format('Y-m-d'),
                'initial_quantity' => (float) $lot->initial_quantity,
                'current_quantity' => (float) $lot->current_quantity,
                'status' => $lot->status,
            ],
            'purchases' => $purchases,
            'sales' => $sales,
        ]);
    }
}
