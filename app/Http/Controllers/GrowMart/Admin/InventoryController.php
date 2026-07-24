<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Repositories\ProductRepositoryInterface;
use App\Domain\GrowMart\Repositories\WarehouseRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly WarehouseRepositoryInterface $warehouseRepository,
    ) {}

    public function index(Request $request)
    {
        $inventory = $this->productRepository->findInventoryWithFilters([
            'warehouse_id' => $request->warehouse_id,
            'q' => $request->q,
            'low_stock' => $request->low_stock,
            'per_page' => 30,
        ]);

        $formatted = array_map(fn($inv) => [
            'id' => $inv['id'],
            'product_id' => $inv['product_id'],
            'product_name' => $inv['product']['name'] ?? 'Unknown',
            'product_slug' => $inv['product']['slug'] ?? '',
            'category' => $inv['product']['category']['name'] ?? '',
            'warehouse_id' => $inv['warehouse_id'],
            'warehouse' => $inv['warehouse']['name'] ?? 'Unknown',
            'quantity' => $inv['quantity'],
            'low_stock_threshold' => $inv['low_stock_threshold'],
            'is_low' => $inv['quantity'] <= $inv['low_stock_threshold'],
            'is_out' => $inv['quantity'] <= 0,
        ], $inventory['data'] ?? []);

        return Inertia::render('GrowMart/Admin/Inventory/Index', [
            'inventory' => $formatted,
            'warehouses' => $this->warehouseRepository->findActive(),
            'filters' => $request->only(['warehouse_id', 'q', 'low_stock']),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1',
        ]);

        $this->productRepository->updateInventory($id, $validated);

        return back()->with('success', 'Stock level updated.');
    }
}
