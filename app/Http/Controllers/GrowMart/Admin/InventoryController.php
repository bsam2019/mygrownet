<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrowMart\GrowMartInventory;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartWarehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = GrowMartInventory::with(['product.category', 'warehouse']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('product', fn($qry) => $qry->where('name', 'like', "%{$q}%"));
        }

        if ($request->filled('low_stock')) {
            $query->whereColumn('quantity', '<=', 'low_stock_threshold');
        }

        $inventory = $query->orderBy('quantity')->paginate(30)->withQueryString();

        $formatted = $inventory->through(fn($inv) => [
            'id' => $inv->id,
            'product_id' => $inv->product_id,
            'product_name' => $inv->product?->name ?? 'Unknown',
            'product_slug' => $inv->product?->slug ?? '',
            'category' => $inv->product?->category?->name ?? '',
            'warehouse_id' => $inv->warehouse_id,
            'warehouse' => $inv->warehouse?->name ?? 'Unknown',
            'quantity' => $inv->quantity,
            'low_stock_threshold' => $inv->low_stock_threshold,
            'is_low' => $inv->quantity <= $inv->low_stock_threshold,
            'is_out' => $inv->quantity <= 0,
        ]);

        return Inertia::render('GrowMart/Admin/Inventory/Index', [
            'inventory' => $formatted,
            'warehouses' => GrowMartWarehouse::where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['warehouse_id', 'q', 'low_stock']),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1',
        ]);

        $inventory = GrowMartInventory::findOrFail($id);
        $inventory->update($validated);

        return back()->with('success', 'Stock level updated.');
    }
}
