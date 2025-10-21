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
        
        return Inertia::render('Withdrawals/MyGrowNetIndex', [
            'withdrawals' => $withdrawals,
            'availableBalance' => (float) $availableBalance,
            'minimumWithdrawal' => 50.00,
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
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:50|max:' . $availableBalance,
            'phone_number' => [
                'required',
                'string',
                'regex:/^(\+260|0)?[79][0-9]{8}$/'
            ],
            'account_name' => 'required|string|max:255',
        ], [
            'phone_number.regex' => 'Please enter a valid Zambian mobile number (MTN or Airtel)',
        ]);

        // Normalize phone number format
        $phoneNumber = $validated['phone_number'];
        if (strpos($phoneNumber, '+260') !== 0) {
            $phoneNumber = preg_replace('/^0/', '+260', $phoneNumber);
        }

        $user->withdrawals()->create([
            'amount' => $validated['amount'],
            'withdrawal_method' => 'mobile_money',
            'wallet_address' => $phoneNumber . ' - ' . $validated['account_name'],
            'status' => 'pending',
        ]);

        return redirect()->route('withdrawals.index')
            ->with('success', 'Withdrawal request submitted successfully. You will receive the funds within 24-48 hours.');
    }

    public function show(Withdrawal $withdrawal)
    {
        $this->authorize('view', $withdrawal);

        return Inertia::render('Withdrawals/Show', [
            'withdrawal' => $withdrawal
        ]);
    }
}
