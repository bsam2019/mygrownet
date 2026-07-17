<?php

namespace App\Extensions\Manufacturing\Controllers;

use App\Http\Controllers\Controller;
use App\Extensions\Manufacturing\Services\ManufacturingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillOfMaterialsController extends Controller
{
    public function __construct(private ManufacturingService $service) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/Manufacturing/BillOfMaterials/Index', [
            'boms' => $this->service->getBoms($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'sa_item_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'uom' => 'nullable|string|max:20',
            'materials' => 'nullable|array',
            'materials.*.sa_item_id' => 'required|integer',
            'materials.*.quantity' => 'required|numeric|min:0',
            'materials.*.uom' => 'nullable|string|max:20',
            'materials.*.waste_factor' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);
        $this->service->createBom($companyId, $validated['sa_item_id'], $validated['name'], $validated['quantity'], $validated['materials'] ?? []);
        return redirect()->back()->with('success', 'Bill of Materials created.');
    }
}
