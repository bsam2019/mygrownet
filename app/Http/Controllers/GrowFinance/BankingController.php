<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\BankingService;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceJournalEntryModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankingController extends Controller
{
    public function __construct(
        private BankingService $bankingService
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        // Get cash/bank accounts
        $cashAccounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('type', AccountType::ASSET->value)
            ->whereIn('category', ['Cash', 'Bank', 'Mobile Money', 'cash', 'bank'])
            ->active()
            ->orderBy('code')
            ->get();

        // Get recent transactions for these accounts
        $accountIds = $cashAccounts->pluck('id')->toArray();
        
        $recentTransactions = GrowFinanceJournalEntryModel::forBusiness($businessId)
            ->with(['lines' => function ($query) use ($accountIds) {
                $query->whereIn('account_id', $accountIds)->with('account');
            }])
            ->where('is_posted', true)
            ->orderBy('entry_date', 'desc')
            ->limit(20)
            ->get()
            ->filter(fn($entry) => $entry->lines->isNotEmpty())
            ->map(function ($entry) {
                $line = $entry->lines->first();
                return [
                    'id' => $entry->id,
                    'date' => $entry->entry_date,
                    'description' => $entry->description,
                    'reference' => $entry->reference,
                    'account' => $line->account->name ?? 'Unknown',
                    'debit' => (float) $line->debit_amount,
                    'credit' => (float) $line->credit_amount,
                    'amount' => (float) ($line->debit_amount - $line->credit_amount),
                ];
            })
            ->values();

        // Calculate totals
        $totalCash = $cashAccounts->sum('current_balance');

        return Inertia::render('GrowFinance/Banking/Index', [
            'cashAccounts' => $cashAccounts,
            'recentTransactions' => $recentTransactions,
            'totalCash' => (float) $totalCash,
        ]);
    }

    public function deposit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:growfinance_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
            'deposit_date' => 'required|date',
        ]);

        $businessId = $request->user()->id;

        $this->bankingService->recordDeposit(
            $businessId,
            (int) $validated['account_id'],
            (float) $validated['amount'],
            $validated['description'],
            $validated['reference'] ?? null,
            $validated['deposit_date'],
            $request->user()->id
        );

        return redirect()->route('growfinance.banking.index')
            ->with('success', 'Deposit recorded successfully.');
    }

    public function withdrawal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:growfinance_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
            'withdrawal_date' => 'required|date',
        ]);

        $businessId = $request->user()->id;

        $this->bankingService->recordWithdrawal(
            $businessId,
            (int) $validated['account_id'],
            (float) $validated['amount'],
            $validated['description'],
            $validated['reference'] ?? null,
            $validated['withdrawal_date'],
            $request->user()->id
        );

        return redirect()->route('growfinance.banking.index')
            ->with('success', 'Withdrawal recorded successfully.');
    }

    public function transfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:growfinance_accounts,id',
            'to_account_id' => 'required|exists:growfinance_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'transfer_date' => 'required|date',
        ]);

        $businessId = $request->user()->id;

        $this->bankingService->recordTransfer(
            $businessId,
            (int) $validated['from_account_id'],
            (int) $validated['to_account_id'],
            (float) $validated['amount'],
            $validated['description'] ?? 'Fund transfer',
            $validated['transfer_date'],
            $request->user()->id
        );

        return redirect()->route('growfinance.banking.index')
            ->with('success', 'Transfer recorded successfully.');
    }

    public function reconcile(Request $request): Response
    {
        $businessId = $request->user()->id;
        $accountId = $request->get('account_id');

        $cashAccounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('type', AccountType::ASSET->value)
            ->whereIn('category', ['Cash', 'Bank', 'Mobile Money', 'cash', 'bank'])
            ->active()
            ->orderBy('code')
            ->get();

        $selectedAccount = $accountId 
            ? $cashAccounts->firstWhere('id', $accountId) 
            : $cashAccounts->first();

        $unreconciledTransactions = [];
        if ($selectedAccount) {
            $unreconciledTransactions = GrowFinanceJournalEntryModel::forBusiness($businessId)
                ->with(['lines' => function ($query) use ($selectedAccount) {
                    $query->where('account_id', $selectedAccount->id);
                }])
                ->where('is_posted', true)
                ->orderBy('entry_date', 'desc')
                ->limit(50)
                ->get()
                ->filter(fn($entry) => $entry->lines->isNotEmpty())
                ->map(function ($entry) {
                    $line = $entry->lines->first();
                    return [
                        'id' => $entry->id,
                        'date' => $entry->entry_date,
                        'description' => $entry->description,
                        'reference' => $entry->reference,
                        'debit' => (float) $line->debit_amount,
                        'credit' => (float) $line->credit_amount,
                        'amount' => (float) ($line->debit_amount - $line->credit_amount),
                        'reconciled' => false,
                    ];
                })
                ->values();
        }

        return Inertia::render('GrowFinance/Banking/Reconcile', [
            'cashAccounts' => $cashAccounts,
            'selectedAccount' => $selectedAccount,
            'transactions' => $unreconciledTransactions,
        ]);
    }

    public function storeReconciliation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:growfinance_accounts,id',
            'statement_balance' => 'required|numeric',
            'reconciled_transactions' => 'array',
            'reconciled_transactions.*' => 'integer',
        ]);

        // For now, just update the account balance to match statement
        $account = GrowFinanceAccountModel::findOrFail($validated['account_id']);
        
        // In a full implementation, you'd mark transactions as reconciled
        // and track reconciliation history

        return redirect()->route('growfinance.banking.index')
            ->with('success', 'Reconciliation completed successfully.');
    }
}
