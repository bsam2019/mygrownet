<?php

namespace App\Http\Controllers\Investment;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get real MyGrowNet transactions (commissions, profit shares, withdrawals)
        $commissions = $user->referralCommissions()
            ->select('id', 'amount', 'level', 'status', 'created_at')
            ->get()
            ->map(function ($commission) {
                return [
                    'id' => 'C-' . $commission->id,
                    'type' => 'commission',
                    'amount' => (float) $commission->amount,
                    'status' => $commission->status,
                    'created_at' => $commission->created_at->toISOString(),
                    'description' => 'Level ' . ($commission->level ?? '1') . ' Commission',
                ];
            });
        
        $profitShares = $user->profitShares()
            ->select('id', 'amount', 'created_at')
            ->get()
            ->map(function ($share) {
                return [
                    'id' => 'P-' . $share->id,
                    'type' => 'profit_share',
                    'amount' => (float) $share->amount,
                    'status' => 'completed',
                    'created_at' => $share->created_at->toISOString(),
                    'description' => 'Monthly Profit Share',
                ];
            });
        
        $withdrawals = $user->withdrawals()
            ->select('id', 'amount', 'status', 'created_at')
            ->get()
            ->map(function ($withdrawal) {
                return [
                    'id' => 'W-' . $withdrawal->id,
                    'type' => 'withdrawal',
                    'amount' => -(float) $withdrawal->amount, // Negative for withdrawals
                    'status' => $withdrawal->status,
                    'created_at' => $withdrawal->created_at->toISOString(),
                    'description' => 'Withdrawal Request',
                ];
            });
        
        // Combine and sort by date
        $allTransactions = $commissions
            ->concat($profitShares)
            ->concat($withdrawals)
            ->sortByDesc('created_at')
            ->values();
        
        // Paginate manually
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $total = $allTransactions->count();
        $transactions = $allTransactions->forPage($currentPage, $perPage);
        
        return Inertia::render('Transactions/Index', [
            'transactions' => [
                'data' => $transactions->values()->all(),
                'links' => $this->generatePaginationLinks($currentPage, $total, $perPage),
            ],
        ]);
    }
    
    private function generatePaginationLinks($currentPage, $total, $perPage)
    {
        $lastPage = ceil($total / $perPage);
        $links = [];
        
        // Previous link
        $links[] = [
            'url' => $currentPage > 1 ? route('transactions', ['page' => $currentPage - 1]) : null,
            'label' => '&laquo; Previous',
            'active' => false,
        ];
        
        // Page links
        for ($i = 1; $i <= $lastPage; $i++) {
            $links[] = [
                'url' => route('transactions', ['page' => $i]),
                'label' => (string) $i,
                'active' => $i === $currentPage,
            ];
        }
        
        // Next link
        $links[] = [
            'url' => $currentPage < $lastPage ? route('transactions', ['page' => $currentPage + 1]) : null,
            'label' => 'Next &raquo;',
            'active' => false,
        ];
        
        return $links;
    }

    public function show(Transaction $transaction)
    {
        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction->load('user')
        ]);
    }
}
