<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardStatsController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        // Calculate user's total investment
        $totalInvestment = $user->investments()
            ->where('status', 'approved')
            ->sum('amount');

        // Get active referrals count and earnings
        $activeReferrals = $user->directReferrals()
            ->whereHas('investments', function($query) {
                $query->where('status', 'active');
            })->count();
            
        $referralEarnings = $user->transactions()
            ->where('transaction_type', 'referral')
            ->where('status', 'completed')
            ->sum('amount');

        // Calculate next payout based on active investments
        $nextPayout = $user->investments()
            ->where('status', 'active')
            ->sum('expected_return');

        // Get total earnings from all completed transactions
        $totalEarnings = $user->transactions()
            ->whereIn('transaction_type', ['return', 'referral'])
            ->where('status', 'completed')
            ->sum('amount');

        // Get pending withdrawals count
        $pendingWithdrawals = $user->transactions()
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'pending')
            ->count();

        // Get recent transactions
        $recentTransactions = $user->transactions()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->transaction_type,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'date' => $transaction->created_at->format('M d, Y')
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalInvestment' => $totalInvestment,
                'activeReferrals' => $activeReferrals,
                'referralEarnings' => $referralEarnings,
                'returnRate' => $this->calculateReturnRate($totalInvestment),
                'nextPayout' => $nextPayout,
                'totalEarnings' => $totalEarnings,
                'pendingWithdrawals' => $pendingWithdrawals,
                'recentTransactions' => $recentTransactions,
            ]
        ]);
    }

    private function calculateReturnRate($totalInvestment)
    {
        // Return rate based on investment tiers in ZMW
        return match(true) {
            $totalInvestment >= 50000 => 45, // Elite
            $totalInvestment >= 25000 => 35, // Leader
            $totalInvestment >= 10000 => 25, // Builder
            $totalInvestment >= 5000 => 20,  // Starter
            default => 15 // Basic
        };
    }
}
