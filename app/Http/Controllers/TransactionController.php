<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

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
        
        $withdrawals = $user->actualWithdrawals()
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
        
        // Get wallet top-ups
        $topups = \App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->select('id', 'amount', 'status', 'created_at')
            ->get()
            ->map(function ($topup) {
                return [
                    'id' => 'T-' . $topup->id,
                    'type' => 'wallet_topup',
                    'amount' => (float) $topup->amount,
                    'status' => $topup->status,
                    'created_at' => $topup->created_at->toISOString(),
                    'description' => 'Wallet Top-up',
                ];
            });
        
        // Get workshop expenses
        $workshops = \App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
            ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
            ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
            ->select('workshop_registrations.id', 'workshops.price as amount', 'workshop_registrations.status', 'workshop_registrations.created_at', 'workshops.title')
            ->get()
            ->map(function ($workshop) {
                return [
                    'id' => 'WS-' . $workshop->id,
                    'type' => 'workshop',
                    'amount' => -(float) $workshop->amount, // Negative for expenses
                    'status' => $workshop->status,
                    'created_at' => $workshop->created_at->toISOString(),
                    'description' => 'Workshop: ' . $workshop->title,
                ];
            });
        
        // Combine and sort by date
        $allTransactions = $commissions
            ->concat($profitShares)
            ->concat($withdrawals)
            ->concat($topups)
            ->concat($workshops)
            ->sortByDesc('created_at')
            ->values();
        
        // Paginate manually
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $total = $allTransactions->count();
        $transactions = $allTransactions->forPage($currentPage, $perPage);
        
        // Calculate wallet balance
        $commissionEarnings = (float) ($user->referralCommissions()->where('status', 'paid')->sum('amount') ?? 0);
        $profitEarnings = (float) ($user->profitShares()->sum('amount') ?? 0);
        $walletTopups = (float) (\App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->sum('amount') ?? 0);
        $totalEarnings = $commissionEarnings + $profitEarnings + $walletTopups;
        $totalWithdrawals = (float) ($user->withdrawals()->where('status', 'approved')->sum('amount') ?? 0);
        $workshopExpenses = (float) (\App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
            ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
            ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
            ->sum('workshops.price') ?? 0);
        $balance = $totalEarnings - $totalWithdrawals - $workshopExpenses;
        
        return Inertia::render('Transactions/Index', [
            'transactions' => [
                'data' => $transactions->values()->all(),
                'links' => $this->generatePaginationLinks($currentPage, $total, $perPage),
            ],
            'filters' => $request->only(['type', 'status']),
            'wallet_balance' => $balance
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'investment_id' => 'required|exists:investments,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string'
        ]);

        $investment = Investment::findOrFail($validated['investment_id']);

        $transaction = $this->transactionService->createInvestmentTransaction(
            auth()->user(),
            $investment,
            $validated
        );

        return redirect()->back()->with('success', 'Transaction initiated successfully');
    }

    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0'
        ]);

        $this->transactionService->createWithdrawalTransaction(
            auth()->user(),
            $validated['amount']
        );

        return redirect()->back()->with('success', 'Withdrawal request submitted');
    }
}
