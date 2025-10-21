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
        
        // Get wallet balance (sum of all earnings + topups minus withdrawals and expenses)
        $commissionEarnings = (float) ($user->referralCommissions()->where('status', 'paid')->sum('amount') ?? 0);
        $profitEarnings = (float) ($user->profitShares()->sum('amount') ?? 0);
        $walletTopups = (float) (\App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->sum('amount') ?? 0);
        $totalEarnings = $commissionEarnings + $profitEarnings + $walletTopups;
        $totalWithdrawals = (float) ($user->withdrawals()->where('status', 'approved')->sum('amount') ?? 0);
        
        // Deduct workshop expenses (paid from wallet)
        $workshopExpenses = (float) (\App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
            ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
            ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
            ->sum('workshops.price') ?? 0);
        
        $balance = $totalEarnings - $totalWithdrawals - $workshopExpenses;
        
        // Get recent transactions (combine commissions, profit shares, and wallet top-ups)
        $recentCommissions = $user->referralCommissions()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($commission) {
                return [
                    'id' => 'comm_' . $commission->id,
                    'type' => 'commission',
                    'amount' => (float) $commission->amount,
                    'status' => $commission->status,
                    'date' => $commission->created_at->format('M d, Y'),
                    'description' => 'Level ' . ($commission->level ?? '1') . ' Referral Commission',
                ];
            });
            
        $recentProfits = $user->profitShares()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($profit) {
                return [
                    'id' => 'profit_' . $profit->id,
                    'type' => 'profit_share',
                    'amount' => (float) $profit->amount,
                    'status' => 'paid',
                    'date' => $profit->created_at->format('M d, Y'),
                    'description' => 'Quarterly Profit Share',
                ];
            });
        
        $recentTopups = \App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($topup) {
                return [
                    'id' => 'topup_' . $topup->id,
                    'type' => 'wallet_topup',
                    'amount' => (float) $topup->amount,
                    'status' => 'verified',
                    'date' => $topup->created_at->format('M d, Y'),
                    'description' => 'Wallet Top-up',
                ];
            });
        
        $recentTransactions = $recentCommissions
            ->concat($recentProfits)
            ->concat($recentTopups)
            ->sortByDesc('date')
            ->take(10)
            ->values();
        
        return Inertia::render('MyGrowNet/Wallet', [
            'balance' => $balance,
            'totalEarnings' => $totalEarnings,
            'totalWithdrawals' => $totalWithdrawals,
            'recentTransactions' => $recentTransactions,
            'pendingWithdrawals' => (float) ($user->withdrawals()->where('status', 'pending')->sum('amount') ?? 0),
            'commissionEarnings' => $commissionEarnings,
            'profitEarnings' => $profitEarnings,
            'walletTopups' => $walletTopups,
            'workshopExpenses' => $workshopExpenses,
        ]);
    }
}
