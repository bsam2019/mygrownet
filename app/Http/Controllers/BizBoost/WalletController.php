<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\WalletService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    private WalletService $wallet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
    }

    public function index()
    {
        $user = auth()->user();
        $wallet = $this->wallet->getOrCreateWallet($user->id);
        $transactions = $this->wallet->getTransactions($user->id);

        return Inertia::render('BizBoost/Wallet/Index', [
            'wallet' => [
                'balance' => (float) $wallet->balance,
                'locked_balance' => (float) $wallet->locked_balance,
                'available_balance' => (float) ($wallet->balance - $wallet->locked_balance),
                'currency' => $wallet->currency,
            ],
            'transactions' => $transactions,
        ]);
    }

    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $reference = 'deposit_' . $user->id . '_' . now()->timestamp;
        $wallet = $this->wallet->deposit(
            userId: $user->id,
            amount: $validated['amount'],
            reference: $reference,
            description: $validated['description'] ?? "Wallet deposit",
            meta: ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
        );

        return redirect()->back()->with('success', "{$validated['amount']} {$wallet->currency} deposited successfully");
    }

    public function balance()
    {
        $user = auth()->user();
        return response()->json([
            'balance' => $this->wallet->getBalance($user->id),
            'available' => $this->wallet->getAvailableBalance($user->id),
        ]);
    }
}
