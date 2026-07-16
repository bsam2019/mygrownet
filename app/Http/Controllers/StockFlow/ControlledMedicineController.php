<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\ControlledMedicineService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ControlledMedicineController extends Controller
{
    public function __construct(private ControlledMedicineService $service) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $itemId = $request->get('item_id');
        return Inertia::render('StockFlow/ControlledMedicines/Index', [
            'transactions' => $this->service->getTransactions($companyId, $itemId ? (int) $itemId : null),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'sa_item_id' => 'required|integer',
            'transaction_type' => 'required|in:received,issued,returned,disposed',
            'quantity' => 'required|numeric|min:0',
            'patient_name' => 'nullable|string|max:255',
            'patient_id_number' => 'nullable|string|max:255',
            'prescription_number' => 'nullable|string|max:255',
            'sa_lot_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);
        $validated['user_id'] = $request->user()->id;
        $this->service->recordTransaction($companyId, $validated);
        return redirect()->back()->with('success', 'Transaction recorded.');
    }
}
