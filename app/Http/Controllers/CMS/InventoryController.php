<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Inventory\Services\InventoryService;
use App\Infrastructure\Persistence\Eloquent\CMS\StockLocationModel;
use App\Infrastructure\Persistence\Eloquent\CMS\StockLevelModel;
use App\Infrastructure\Persistence\Eloquent\CMS\StockTransferModel;
use App\Infrastructure\Persistence\Eloquent\CMS\StockAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\StockCountModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function locations(Request $request)
    {
        $locations = StockLocationModel::where('company_id', $request->user()->current_company_id)
            ->with('manager')
            ->get();

        return Inertia::render('CMS/Inventory/Locations', ['locations' => $locations]);
    }

    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'type' => 'required|in:warehouse,workshop,site,vehicle,other',
            'address' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $location = $this->inventoryService->createLocation($request->user()->current_company_id, $validated);
        return back()->with('success', 'Location created successfully');
    }

    public function stockLevels(Request $request)
    {
        $levels = StockLevelModel::whereHas('location', fn($q) => $q->where('company_id', $request->user()->current_company_id))
            ->with(['material', 'location'])
            ->paginate(50);

        return Inertia::render('CMS/Inventory/StockLevels', ['levels' => $levels]);
    }

    public function lowStockAlerts(Request $request)
    {
        $alerts = $this->inventoryService->getLowStockAlerts($request->user()->current_company_id);
        return response()->json($alerts);
    }

    public function transfers(Request $request)
    {
        $transfers = StockTransferModel::where('company_id', $request->user()->current_company_id)
            ->with(['fromLocation', 'toLocation', 'requestedBy'])
            ->latest()
            ->paginate(20);

        return Inertia::render('CMS/Inventory/Transfers', ['transfers' => $transfers]);
    }

    public function createTransfer(Request $request)
    {
        $validated = $request->validate([
            'from_location_id' => 'required|exists:cms_stock_locations,id',
            'to_location_id' => 'required|exists:cms_stock_locations,id|different:from_location_id',
            'transfer_date' => 'required|date',
            'requested_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:cms_materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string',
        ]);

        $transfer = $this->inventoryService->createTransfer($request->user()->current_company_id, $validated);
        return back()->with('success', 'Transfer created successfully');
    }

    public function approveTransfer(Request $request, int $id)
    {
        $transfer = $this->inventoryService->approveTransfer($id, $request->user()->id);
        return back()->with('success', 'Transfer approved');
    }

    public function receiveTransfer(Request $request, int $id)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*' => 'required|numeric|min:0',
        ]);

        $transfer = $this->inventoryService->receiveTransfer($id, $request->user()->id, $validated['items']);
        return back()->with('success', 'Transfer received');
    }

    public function adjustments(Request $request)
    {
        $adjustments = StockAdjustmentModel::where('company_id', $request->user()->current_company_id)
            ->with('createdBy')
            ->latest()
            ->paginate(20);

        return Inertia::render('CMS/Inventory/Adjustments', ['adjustments' => $adjustments]);
    }

    public function createAdjustment(Request $request)
    {
        $validated = $request->validate([
            'adjustment_date' => 'required|date',
            'adjustment_type' => 'required|in:increase,decrease,correction',
            'reason' => 'required|in:damaged,expired,found,lost,count_correction,other',
            'created_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:cms_materials,id',
            'items.*.location_id' => 'required|exists:cms_stock_locations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        $adjustment = $this->inventoryService->createAdjustment($request->user()->current_company_id, $validated);
        return back()->with('success', 'Adjustment created successfully');
    }

    public function approveAdjustment(Request $request, int $id)
    {
        $adjustment = $this->inventoryService->approveAdjustment($id, $request->user()->id);
        return back()->with('success', 'Adjustment approved and stock updated');
    }

    public function counts(Request $request)
    {
        $counts = StockCountModel::where('company_id', $request->user()->current_company_id)
            ->with(['location', 'countedBy'])
            ->latest()
            ->paginate(20);

        return Inertia::render('CMS/Inventory/Counts', ['counts' => $counts]);
    }

    public function createCount(Request $request)
    {
        $validated = $request->validate([
            'count_date' => 'required|date',
            'count_type' => 'required|in:full,partial,cycle',
            'location_id' => 'nullable|exists:cms_stock_locations,id',
            'counted_by' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:cms_materials,id',
            'items.*.location_id' => 'required|exists:cms_stock_locations,id',
            'items.*.counted_quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string',
        ]);

        $count = $this->inventoryService->createStockCount($request->user()->current_company_id, $validated);
        return back()->with('success', 'Stock count created successfully');
    }

    public function completeCount(Request $request, int $id)
    {
        $count = $this->inventoryService->completeStockCount($id, $request->user()->id);
        return back()->with('success', 'Stock count completed and variances adjusted');
    }
}
