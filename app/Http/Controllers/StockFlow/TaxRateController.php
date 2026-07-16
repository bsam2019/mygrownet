<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\TaxService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxRateController extends Controller
{
    public function __construct(private TaxService $taxService) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/TaxRates/Index', [
            'taxRates' => array_map(fn($t) => $t->toArray(), $this->taxService->getTaxRates($companyId)),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:inclusive,exclusive',
            'is_default' => 'boolean',
        ]);
        $this->taxService->createTaxRate($companyId, $validated);
        return redirect()->back()->with('success', 'Tax rate created.');
    }

    public function update(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:inclusive,exclusive',
            'is_default' => 'boolean',
        ]);
        $this->taxService->updateTaxRate($id, $companyId, $validated);
        return redirect()->back()->with('success', 'Tax rate updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $this->taxService->deleteTaxRate($id, $companyId);
        return redirect()->back()->with('success', 'Tax rate deleted.');
    }
}
