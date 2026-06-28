<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class TransactionListController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->transactions()
            ->with('investment')
            ->when($request->type, function($query, $type) {
                return $query->where('transaction_type', $type);
            })
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            });

        return Inertia::render('Transactions/Index', [
            'transactions' => $query->latest()->paginate(10),
            'summary' => [
                'totalDeposits' => auth()->user()->transactions()
                    ->where('transaction_type', 'deposit')
                    ->where('status', 'completed')
                    ->sum('amount'),
                'totalWithdrawals' => auth()->user()->transactions()
                    ->where('transaction_type', 'withdrawal')
                    ->where('status', 'completed')
                    ->sum('amount'),
                'totalReturns' => auth()->user()->transactions()
                    ->where('transaction_type', 'return')
                    ->where('status', 'completed')
                    ->sum('amount'),
                'totalReferrals' => auth()->user()->transactions()
                    ->where('transaction_type', 'referral')
                    ->where('status', 'completed')
                    ->sum('amount'),
                'pendingWithdrawals' => auth()->user()->transactions()
                    ->where('transaction_type', 'withdrawal')
                    ->where('status', 'pending')
                    ->sum('amount')
            ],
            'filters' => $request->only(['type', 'status'])
        ]);
    }

    public function show($reference)
    {
        $transaction = auth()->user()->transactions()
            ->with(['investment', 'investment.category'])
            ->where('reference_number', $reference)
            ->firstOrFail();

        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction
        ]);
    }

    public function downloadReceipt($reference)
    {
        $transaction = auth()->user()->transactions()
            ->where('reference_number', $reference)
            ->where('status', 'completed')
            ->firstOrFail();

        // Generate PDF receipt
        $pdf = PDF::loadView('receipts.transaction', [
            'transaction' => $transaction,
            'user' => auth()->user()
        ]);

        return $pdf->download("receipt-{$reference}.pdf");
    }

    public function statement(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from'
        ]);

        $transactions = auth()->user()->transactions()
            ->whereBetween('created_at', [
                Carbon::parse($request->date_from)->startOfDay(),
                Carbon::parse($request->date_to)->endOfDay()
            ])
            ->get();

        $pdf = PDF::loadView('statements.transactions', [
            'transactions' => $transactions,
            'user' => auth()->user(),
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to
        ]);

        return $pdf->download('transaction-statement.pdf');
    }
}
