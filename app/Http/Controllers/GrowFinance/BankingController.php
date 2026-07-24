<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\BankingService;
use App\Domain\GrowFinance\Services\ReconciliationService;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceBankAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceBankStatementModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceJournalEntryModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceReconciliationPeriodModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankingController extends Controller
{
    public function __construct(
        private BankingService $bankingService,
        private ReconciliationService $reconciliationService
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
        $periodId = $request->get('period_id');

        // Get bank accounts (from growfinance_bank_accounts)
        $bankAccounts = GrowFinanceBankAccountModel::forBusiness($businessId)
            ->active()
            ->orderBy('account_name')
            ->get();

        // Get cash accounts from chart of accounts
        $cashAccounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('type', AccountType::ASSET->value)
            ->whereIn('category', ['Cash', 'Bank', 'Mobile Money', 'cash', 'bank'])
            ->active()
            ->orderBy('code')
            ->get();

        $selectedBankAccount = $accountId 
            ? $bankAccounts->firstWhere('id', $accountId) 
            : $bankAccounts->first();

        // Get reconciliation periods for the selected account
        $periods = collect();
        $selectedPeriod = null;
        $unreconciledTransactions = [];
        $statements = collect();

        if ($selectedBankAccount) {
            $periods = GrowFinanceReconciliationPeriodModel::where('business_id', $businessId)
                ->where('bank_account_id', $selectedBankAccount->id)
                ->latest()
                ->get();

            $selectedPeriod = $periodId 
                ? $periods->firstWhere('id', $periodId)
                : $periods->first();

            // Get unreconciled journal lines for cash/bank accounts
            $bankAccountIds = $cashAccounts->pluck('id')->toArray();
            $dateRange = $selectedPeriod 
                ? [$selectedPeriod->start_date->format('Y-m-d'), $selectedPeriod->end_date->format('Y-m-d')]
                : [];

            $unreconciledTransactions = $this->reconciliationService->getUnreconciledJournalLines(
                $businessId, $selectedBankAccount->id, $dateRange
            );

            // Get statements for this account
            $statements = GrowFinanceBankStatementModel::where('business_id', $businessId)
                ->where('bank_account_id', $selectedBankAccount->id)
                ->with('lines')
                ->latest()
                ->get();
        }

        return Inertia::render('GrowFinance/Banking/Reconcile', [
            'bankAccounts' => $bankAccounts,
            'cashAccounts' => $cashAccounts,
            'selectedBankAccount' => $selectedBankAccount,
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'transactions' => $unreconciledTransactions,
            'statements' => $statements,
        ]);
    }

    public function storeReconciliation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:growfinance_bank_accounts,id',
            'statement_balance' => 'required|numeric',
            'period_id' => 'required|exists:growfinance_reconciliation_periods,id',
        ]);

        $this->reconciliationService->completeReconciliation(
            $validated['period_id'],
            $request->user()->id
        );

        return redirect()->route('growfinance.banking.reconcile', ['account_id' => $validated['account_id']])
            ->with('success', 'Reconciliation completed successfully.');
    }

    public function importStatement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:growfinance_bank_accounts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'opening_balance' => 'required|numeric',
            'closing_balance' => 'required|numeric',
            'statement_period' => 'nullable|string|max:100',
            'lines' => 'required|array|min:1',
            'lines.*.transaction_date' => 'required|date',
            'lines.*.description' => 'required|string|max:500',
            'lines.*.reference' => 'nullable|string|max:100',
            'lines.*.debit_amount' => 'required|numeric|min:0',
            'lines.*.credit_amount' => 'required|numeric|min:0',
        ]);

        $businessId = $request->user()->id;

        $statement = $this->reconciliationService->importStatement(
            $businessId,
            $validated['bank_account_id'],
            $validated,
            $validated['lines']
        );

        // Create reconciliation period
        $this->reconciliationService->createReconciliationPeriod(
            $businessId,
            $validated['bank_account_id'],
            $statement->id,
            $request->user()->id
        );

        return redirect()->route('growfinance.banking.reconcile', [
            'account_id' => $validated['bank_account_id'],
        ])->with('success', 'Statement imported with ' . $statement->line_count . ' transactions.');
    }

    public function matchTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_id' => 'required|exists:growfinance_reconciliation_periods,id',
            'statement_line_id' => 'required|exists:growfinance_bank_statement_lines,id',
            'journal_line_id' => 'required|exists:growfinance_journal_lines,id',
        ]);

        $this->reconciliationService->matchTransaction(
            $validated['period_id'],
            $validated['statement_line_id'],
            $validated['journal_line_id'],
            'manual'
        );

        return back()->with('success', 'Transaction matched successfully.');
    }

    public function unmatchTransaction(Request $request, int $matchId): RedirectResponse
    {
        $this->reconciliationService->unmatchTransaction($matchId);
        return back()->with('success', 'Match removed.');
    }
}
