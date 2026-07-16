<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/Payments/Index', [
            'transactions' => array_map(fn($t) => $t->toArray(), $this->paymentService->getTransactions($companyId)),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'payable_type' => 'required|string',
            'payable_id' => 'required|integer',
            'gateway' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);
        $this->paymentService->createTransaction($companyId, $validated);
        return redirect()->back()->with('success', 'Payment recorded.');
    }

    public function show(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $transactions = $this->paymentService->getTransactions($companyId);
        $txn = collect($transactions)->first(fn($t) => $t->id() === $id);
        if (!$txn) abort(404);
        return Inertia::render('StockFlow/Payments/Show', ['transaction' => $txn->toArray()]);
    }
}
