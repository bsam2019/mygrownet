<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'investment'])
            ->when($request->type, function($query, $type) {
                return $query->where('transaction_type', $type);
            })
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->date_from, function($query, $date) {
                return $query->whereDate('created_at', '>=', Carbon::parse($date));
            })
            ->when($request->date_to, function($query, $date) {
                return $query->whereDate('created_at', '<=', Carbon::parse($date));
            });

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $query->latest()->paginate(15),
            'summary' => [
                'total' => [
                    'deposits' => Transaction::where('transaction_type', 'deposit')->sum('amount'),
                    'withdrawals' => Transaction::where('transaction_type', 'withdrawal')->sum('amount'),
                    'returns' => Transaction::where('transaction_type', 'return')->sum('amount'),
                    'referrals' => Transaction::where('transaction_type', 'referral')->sum('amount')
                ],
                'pending' => [
                    'deposits' => Transaction::where('transaction_type', 'deposit')
                        ->where('status', 'pending')
                        ->sum('amount'),
                    'withdrawals' => Transaction::where('transaction_type', 'withdrawal')
                        ->where('status', 'pending')
                        ->sum('amount')
                ]
            ],
            'filters' => $request->only(['type', 'status', 'date_from', 'date_to'])
        ]);
    }

    public function show(Transaction $transaction)
    {
        return Inertia::render('Admin/Transactions/Show', [
            'transaction' => $transaction->load(['user', 'investment'])
        ]);
    }

    public function updateStatus(Transaction $transaction, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,failed,cancelled',
            'notes' => 'nullable|string'
        ]);

        $transaction->update([
            'status' => $validated['status'],
            'notes' => $validated['notes']
        ]);

        if ($validated['status'] === 'completed' && $transaction->transaction_type === 'withdrawal') {
            // Update investment amount for completed withdrawals
            if ($transaction->investment) {
                $transaction->investment->update([
                    'amount' => $transaction->investment->amount - $transaction->amount
                ]);
            }
        }

        return back()->with('success', 'Transaction status updated successfully');
    }

    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
            'type' => 'nullable|in:deposit,withdrawal,return,referral'
        ]);

        $transactions = Transaction::with(['user', 'investment'])
            ->when($request->type, function($query, $type) {
                return $query->where('transaction_type', $type);
            })
            ->whereBetween('created_at', [
                Carbon::parse($request->date_from)->startOfDay(),
                Carbon::parse($request->date_to)->endOfDay()
            ])
            ->get()
            ->map(function($transaction) {
                return [
                    'Date' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'Reference' => $transaction->reference_number,
                    'Type' => $transaction->transaction_type,
                    'Amount (ZMW)' => number_format($transaction->amount, 2),
                    'Status' => $transaction->status,
                    'User' => $transaction->user->name,
                    'Email' => $transaction->user->email
                ];
            });

        // Return CSV response
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions.csv"'
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, array_keys($transactions->first()));

            foreach ($transactions as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
