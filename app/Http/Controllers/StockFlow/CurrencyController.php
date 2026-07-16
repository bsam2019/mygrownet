<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\CurrencyService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CurrencyController extends Controller
{
    public function __construct(private CurrencyService $currencyService) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/ExchangeRates/Index', [
            'rates' => array_map(fn($r) => $r->toArray(), $this->currencyService->getRates($companyId)),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'from_currency' => 'required|string|size:3',
            'to_currency' => 'required|string|size:3',
            'rate' => 'required|numeric|min:0',
            'effective_date' => 'nullable|date',
        ]);
        $this->currencyService->createRate($companyId, $validated);
        return redirect()->back()->with('success', 'Exchange rate created.');
    }

    public function update(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'rate' => 'required|numeric|min:0',
            'effective_date' => 'nullable|date',
        ]);
        $this->currencyService->updateRate($id, $companyId, $validated);
        return redirect()->back()->with('success', 'Exchange rate updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $this->currencyService->deleteRate($id, $companyId);
        return redirect()->back()->with('success', 'Exchange rate deleted.');
    }
}
