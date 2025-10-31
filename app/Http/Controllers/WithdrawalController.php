<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get withdrawals
        $withdrawals = $user->withdrawals()->latest()->paginate(10);
        
        // Calculate available balance (total earnings - total withdrawals)
        $totalEarnings = $user->referralCommissions()->where('status', 'paid')->sum('amount') ?? 0;
        $totalEarnings += $user->profitShares()->sum('amount') ?? 0;
        $totalWithdrawals = $user->withdrawals()->where('status', 'approved')->sum('amount') ?? 0;
        $availableBalance = $totalEarnings - $totalWithdrawals;
        
        // Get verification limits
        $limits = $this->getVerificationLimits($user->verification_level ?? 'basic');
        
        // Reset daily withdrawal if needed
        $this->resetDailyWithdrawalIfNeeded($user);
        
        // Calculate remaining daily limit
        $remainingDaily = $limits['daily_withdrawal'] - ($user->daily_withdrawal_used ?? 0);
        
        return Inertia::render('Withdrawals/MyGrowNetIndex', [
            'withdrawals' => $withdrawals,
            'availableBalance' => (float) $availableBalance,
            'minimumWithdrawal' => 50.00,
            'userPhone' => $user->phone ?? '',
            'userName' => $user->name ?? '',
            'verificationLevel' => $user->verification_level ?? 'basic',
            'verificationLimits' => $limits,
            'remainingDailyLimit' => max(0, $remainingDaily),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Calculate available balance
        $totalEarnings = $user->referralCommissions()->where('status', 'paid')->sum('amount') ?? 0;
        $totalEarnings += $user->profitShares()->sum('amount') ?? 0;
        $totalWithdrawals = $user->withdrawals()->where('status', 'approved')->sum('amount') ?? 0;
        $availableBalance = $totalEarnings - $totalWithdrawals;
        
        // Get verification limits
        $walletController = app(\App\Http\Controllers\MyGrowNet\WalletController::class);
        $limits = $this->getVerificationLimits($user->verification_level ?? 'basic');
        
        // Reset daily withdrawal if needed
        $this->resetDailyWithdrawalIfNeeded($user);
        
        // Calculate remaining daily limit
        $remainingDaily = $limits['daily_withdrawal'] - ($user->daily_withdrawal_used ?? 0);
        
        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:50',
                'max:' . min($availableBalance, $limits['single_transaction'], $remainingDaily)
            ],
            'phone_number' => [
                'required',
                'string',
                'regex:/^(\+260|0)?[79][0-9]{8}$/'
            ],
            'account_name' => 'required|string|max:255',
        ], [
            'phone_number.regex' => 'Please enter a valid Zambian mobile number (MTN or Airtel)',
            'amount.max' => 'Amount exceeds your available balance or withdrawal limits.',
        ]);

        // Additional limit checks
        $amount = $validated['amount'];
        
        if ($amount > $limits['single_transaction']) {
            return back()->withErrors([
                'amount' => "Single transaction limit is " . number_format($limits['single_transaction'], 2) . " for your verification level."
            ]);
        }
        
        if ($amount > $remainingDaily) {
            return back()->withErrors([
                'amount' => "Daily withdrawal limit exceeded. Remaining today: K" . number_format($remainingDaily, 2)
            ]);
        }

        // Normalize phone number format
        $phoneNumber = $validated['phone_number'];
        if (strpos($phoneNumber, '+260') !== 0) {
            $phoneNumber = preg_replace('/^0/', '+260', $phoneNumber);
        }

        $user->withdrawals()->create([
            'amount' => $amount,
            'withdrawal_method' => 'mobile_money',
            'wallet_address' => $phoneNumber . ' - ' . $validated['account_name'],
            'status' => 'pending',
        ]);
        
        // Track withdrawal for daily limit
        $user->increment('daily_withdrawal_used', $amount);

        return redirect()->route('withdrawals.index')
            ->with('success', 'Withdrawal request submitted successfully. You will receive the funds within 24-48 hours.');
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

    public function show(Withdrawal $withdrawal)
    {
        $this->authorize('view', $withdrawal);

        return Inertia::render('Withdrawals/Show', [
            'withdrawal' => $withdrawal
        ]);
    }
}
