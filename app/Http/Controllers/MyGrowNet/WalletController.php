<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get wallet balance (sum of all earnings minus withdrawals)
        $commissionEarnings = (float) ($user->referralCommissions()->where('status', 'paid')->sum('amount') ?? 0);
        $profitEarnings = (float) ($user->profitShares()->sum('amount') ?? 0);
        $totalEarnings = $commissionEarnings + $profitEarnings;
        $totalWithdrawals = (float) ($user->withdrawals()->where('status', 'approved')->sum('amount') ?? 0);
        $balance = $totalEarnings - $totalWithdrawals;
        
        // Get recent transactions (combine commissions and profit shares)
        $recentCommissions = $user->referralCommissions()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($commission) {
                return [
                    'id' => $commission->id,
                    'type' => 'commission',
                    'amount' => (float) $commission->amount,
                    'status' => $commission->status,
                    'date' => $commission->created_at->format('M d, Y'),
                    'description' => 'Level ' . ($commission->level ?? '1') . ' Commission',
                ];
            });
            
        $recentProfits = $user->profitShares()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($profit) {
                return [
                    'id' => $profit->id,
                    'type' => 'profit_share',
                    'amount' => (float) $profit->amount,
                    'status' => 'paid',
                    'date' => $profit->created_at->format('M d, Y'),
                    'description' => 'Monthly Profit Share',
                ];
            });
        
        $recentTransactions = $recentCommissions->concat($recentProfits)
            ->sortByDesc('date')
            ->take(10)
            ->values();
        
        return Inertia::render('MyGrowNet/Wallet', [
            'balance' => $balance,
            'totalEarnings' => $totalEarnings,
            'totalWithdrawals' => $totalWithdrawals,
            'recentTransactions' => $recentTransactions,
            'pendingWithdrawals' => (float) ($user->withdrawals()->where('status', 'pending')->sum('amount') ?? 0),
        ]);
    }
}
