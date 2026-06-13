<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrowMart\GrowMartWarehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = GrowMartWarehouse::withCount('inventory')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('GrowMart/Admin/Warehouses/Index', [
            'warehouses' => $warehouses,
        ]);
    }

    public function create()
    {
        return Inertia::render('GrowMart/Admin/Warehouses/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        GrowMartWarehouse::create($validated);

        return redirect()->route('admin.growmart.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function edit(GrowMartWarehouse $warehouse)
    {
        return Inertia::render('GrowMart/Admin/Warehouses/Edit', [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(Request $request, GrowMartWarehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $warehouse->update($validated);

        return redirect()->route('admin.growmart.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(GrowMartWarehouse $warehouse)
    {
        if ($warehouse->inventory()->count() > 0) {
            return back()->with('error', 'Cannot delete warehouse with existing inventory.');
        }

        $warehouse->delete();

        return redirect()->route('admin.growmart.warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}
