<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Resources\TransactionResource;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->transactions()
            ->with(['investment'])
            ->when($request->type, function($query, $type) {
                return $query->where('transaction_type', $type);
            })
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            });

        return TransactionResource::collection(
            $query->latest()->paginate(10)
        );
    }

    public function summary()
    {
        return response()->json([
            'deposits' => auth()->user()->transactions()
                ->where('transaction_type', 'deposit')
                ->where('status', 'completed')
                ->sum('amount'),
            'withdrawals' => auth()->user()->transactions()
                ->where('transaction_type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('amount'),
            'returns' => auth()->user()->transactions()
                ->where('transaction_type', 'return')
                ->where('status', 'completed')
                ->sum('amount'),
            'referrals' => auth()->user()->transactions()
                ->where('transaction_type', 'referral')
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_withdrawals' => auth()->user()->transactions()
                ->where('transaction_type', 'withdrawal')
                ->where('status', 'pending')
                ->sum('amount'),
            'currency' => 'ZMW'
        ]);
    }

    public function show($reference)
    {
        $transaction = auth()->user()->transactions()
            ->with(['investment', 'investment.category'])
            ->where('reference_number', $reference)
            ->firstOrFail();

        return new TransactionResource($transaction);
    }
}
