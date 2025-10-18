<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['type', 'status']);
        $transactions = $this->transactionService->getUserTransactions(auth()->user(), $filters);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $filters
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'investment_id' => 'required|exists:investments,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string'
        ]);

        $investment = Investment::findOrFail($validated['investment_id']);

        $transaction = $this->transactionService->createInvestmentTransaction(
            auth()->user(),
            $investment,
            $validated
        );

        return redirect()->back()->with('success', 'Transaction initiated successfully');
    }

    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0'
        ]);

        $this->transactionService->createWithdrawalTransaction(
            auth()->user(),
            $validated['amount']
        );

        return redirect()->back()->with('success', 'Withdrawal request submitted');
    }
}
