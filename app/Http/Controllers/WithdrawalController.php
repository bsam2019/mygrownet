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
        $withdrawals = $user->withdrawals()->latest()->paginate(10);

        // Build investments array matching the front-end expectations
        $rawInvestments = $user->investments()
            ->where('status', 'active')
            ->with(['tier', 'category'])
            ->get();

        $investments = $rawInvestments->map(function ($inv) {
            // Safe current value accessor fallback
            $currentValue = 0.0;
            if (method_exists($inv, 'getCurrentValue')) {
                $currentValue = (float) $inv->getCurrentValue();
            } elseif (isset($inv->current_value)) {
                $currentValue = (float) $inv->current_value;
            } else {
                // fallback to amount if no calculated value present
                $currentValue = (float) $inv->amount;
            }

            // Determine tier info (fallback to category if tier relation isn't present)
            $tierName = $inv->tier?->name ?? $inv->category?->name ?? 'Unknown';
            $tierMin = $inv->tier->minimum_amount ?? $inv->category->minimum_amount ?? 0;

            // Determine investment date
            $investmentDate = $inv->investment_date ?? $inv->created_at;

            return [
                'id' => $inv->id,
                'amount' => (float) $inv->amount,
                'current_value' => $currentValue,
                'investment_date' => optional($investmentDate)->toISOString(),
                'tier' => [
                    'name' => $tierName,
                    'minimum_amount' => (float) $tierMin,
                ],
            ];
        });

        return Inertia::render('Withdrawals/Index', [
            'withdrawals' => $withdrawals,
            'investments' => $investments,
        ]);
    }

    public function create()
    {
        return Inertia::render('Withdrawals/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'wallet_address' => 'required|string',
            'withdrawal_method' => 'required|in:bitcoin,ethereum,bank'
        ]);

        auth()->user()->withdrawals()->create([
            'amount' => $validated['amount'],
            'wallet_address' => $validated['wallet_address'],
            'withdrawal_method' => $validated['withdrawal_method'],
            'status' => 'pending'
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
