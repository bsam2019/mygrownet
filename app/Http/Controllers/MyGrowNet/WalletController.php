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
        
        // Reset daily withdrawal limit if needed
        $this->resetDailyWithdrawalIfNeeded($user);
        
        // Use WalletService for consistent balance calculation
        $walletService = app(\App\Services\WalletService::class);
        $breakdown = $walletService->getWalletBreakdown($user);
        
        $balance = $breakdown['balance'];
        $totalEarnings = $breakdown['credits']['earnings']['total'];
        $commissionEarnings = $breakdown['credits']['earnings']['commissions'];
        $profitEarnings = $breakdown['credits']['earnings']['profit_shares'];
        $walletTopups = $breakdown['credits']['deposits'];
        $totalWithdrawals = $breakdown['debits']['withdrawals'];
        $workshopExpenses = $breakdown['debits']['expenses'];
        // Note: starter_kits removed - already included in withdrawals
        
        // Get verification limits
        $limits = $this->getVerificationLimits($user->verification_level ?? 'basic');
        $remainingDailyLimit = $limits['daily_withdrawal'] - ($user->daily_withdrawal_used ?? 0);
        
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
        
        // Calculate LGR withdrawable amount
        // Use custom percentage if set, otherwise use global setting
        $lgrWithdrawablePercentage = $user->lgr_custom_withdrawable_percentage 
            ?? \App\Models\LgrSetting::get('lgr_max_cash_conversion', 40);
        
        $lgrAwardedTotal = (float) ($user->loyalty_points_awarded_total ?? 0);
        $lgrWithdrawnTotal = (float) ($user->loyalty_points_withdrawn_total ?? 0);
        $lgrMaxWithdrawable = ($lgrAwardedTotal * $lgrWithdrawablePercentage / 100) - $lgrWithdrawnTotal;
        $lgrWithdrawable = min($user->loyalty_points, max(0, $lgrMaxWithdrawable));
        
        // Check if user is blocked from LGR withdrawals
        $lgrWithdrawalBlocked = (bool) ($user->lgr_withdrawal_blocked ?? false);
        if ($lgrWithdrawalBlocked) {
            $lgrWithdrawable = 0;
        }
        
        // Get loan summary
        $loanService = app(\App\Domain\Financial\Services\LoanService::class);
        $loanSummary = $loanService->getLoanSummary($user);
        
        return Inertia::render('MyGrowNet/Wallet', [
            'balance' => $balance,
            'bonusBalance' => (float) ($user->bonus_balance ?? 0),
            'loyaltyPoints' => (float) ($user->loyalty_points ?? 0),
            'lgrWithdrawable' => $lgrWithdrawable,
            'lgrWithdrawablePercentage' => $lgrWithdrawablePercentage,
            'lgrAwardedTotal' => $lgrAwardedTotal,
            'lgrWithdrawnTotal' => $lgrWithdrawnTotal,
            'lgrWithdrawalBlocked' => $lgrWithdrawalBlocked,
            'lgrRestrictionReason' => $user->lgr_restriction_reason ?? null,
            'totalEarnings' => $totalEarnings,
            'totalWithdrawals' => $totalWithdrawals,
            'recentTransactions' => $recentTransactions,
            'pendingWithdrawals' => (float) ($user->withdrawals()->where('status', 'pending')->sum('amount') ?? 0),
            'commissionEarnings' => $commissionEarnings,
            'profitEarnings' => $profitEarnings,
            'walletTopups' => $walletTopups,
            'workshopExpenses' => $workshopExpenses,
            'starterKitExpenses' => 0, // Deprecated - now included in withdrawals
            'verificationLevel' => $user->verification_level ?? 'basic',
            'verificationLimits' => $limits,
            'remainingDailyLimit' => max(0, $remainingDailyLimit),
            'policyAccepted' => (bool) ($user->wallet_policy_accepted ?? false),
            'loanSummary' => $loanSummary,
        ]);
    }
    
    /**
     * Accept wallet policy
     */
    public function acceptPolicy(Request $request)
    {
        $user = $request->user();
        
        $user->update([
            'wallet_policy_accepted' => true,
            'wallet_policy_accepted_at' => now(),
        ]);
        
        return back()->with('success', 'Wallet policy accepted successfully.');
    }
    
    /**
     * Get verification limits based on level
     */
    private function getVerificationLimits(string $level): array
    {
        return match($level) {
            'basic' => [
                'daily_withdrawal' => 1000,
                'monthly_withdrawal' => 10000,
                'single_transaction' => 500,
            ],
            'enhanced' => [
                'daily_withdrawal' => 5000,
                'monthly_withdrawal' => 50000,
                'single_transaction' => 2000,
            ],
            'premium' => [
                'daily_withdrawal' => 20000,
                'monthly_withdrawal' => 200000,
                'single_transaction' => 10000,
            ],
            default => [
                'daily_withdrawal' => 1000,
                'monthly_withdrawal' => 10000,
                'single_transaction' => 500,
            ],
        };
    }
    
    /**
     * Reset daily withdrawal limit if needed
     */
    private function resetDailyWithdrawalIfNeeded($user): void
    {
        $today = now()->toDateString();
        $resetDate = $user->daily_withdrawal_reset_date;
        
        if (!$resetDate || $resetDate !== $today) {
            $user->update([
                'daily_withdrawal_used' => 0,
                'daily_withdrawal_reset_date' => $today,
            ]);
        }
    }
    
    /**
     * Check if withdrawal is within limits
     */
    public function checkWithdrawalLimit(Request $request)
    {
        $user = $request->user();
        $amount = (float) $request->input('amount', 0);
        
        $this->resetDailyWithdrawalIfNeeded($user);
        
        $limits = $this->getVerificationLimits($user->verification_level ?? 'basic');
        $remainingDaily = $limits['daily_withdrawal'] - ($user->daily_withdrawal_used ?? 0);
        
        $canWithdraw = $amount <= $remainingDaily && $amount <= $limits['single_transaction'];
        
        return response()->json([
            'canWithdraw' => $canWithdraw,
            'limits' => $limits,
            'remainingDaily' => max(0, $remainingDaily),
            'message' => $canWithdraw 
                ? 'Withdrawal amount is within limits.' 
                : 'Withdrawal amount exceeds your current limits.',
        ]);
    }
    
    /**
     * Track withdrawal for daily limit
     */
    public function trackWithdrawal($userId, float $amount): void
    {
        $user = \App\Models\User::find($userId);
        if ($user) {
            $this->resetDailyWithdrawalIfNeeded($user);
            $user->increment('daily_withdrawal_used', $amount);
        }
    }
}
