<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Repositories\WarehouseRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $warehouseRepository,
    ) {}

    public function index()
    {
        $warehouses = $this->warehouseRepository->findAll(['per_page' => 20]);

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

        $this->warehouseRepository->save($validated);

        return redirect()->route('admin.growmart.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function edit(int $id)
    {
        $warehouse = $this->warehouseRepository->findById($id);

        return Inertia::render('GrowMart/Admin/Warehouses/Edit', [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $this->warehouseRepository->update($id, $validated);

        return redirect()->route('admin.growmart.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(int $id)
    {
        if ($this->warehouseRepository->inventoryCount($id) > 0) {
            return back()->with('error', 'Cannot delete warehouse with existing inventory.');
        }

        $this->warehouseRepository->delete($id);

        return redirect()->route('admin.growmart.warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}
