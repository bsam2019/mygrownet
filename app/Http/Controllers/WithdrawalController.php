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

    public function create()
    {
        return Inertia::render('Withdrawals/Create');
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
            'payment_method' => 'required|in:mobile_money,bank_transfer',
            'phone_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        $user->withdrawals()->create([
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'phone_number' => $validated['phone_number'],
            'account_name' => $validated['account_name'],
            'status' => 'pending',
        ]);

        return redirect()->route('withdrawals.index')
            ->with('success', 'Withdrawal request submitted successfully.');
    }

    public function show(Withdrawal $withdrawal)
    {
        $this->authorize('view', $withdrawal);

        return Inertia::render('Withdrawals/Show', [
            'withdrawal' => $withdrawal
        ]);
    }
}
