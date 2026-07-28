<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\Events\ReportGenerated;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\Core\Services\OutboxService;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class ReportingEngine
{
    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
        private GeneralLedgerEngine $generalLedgerEngine,
        private readonly OutboxService $outbox,
    ) {}

    public function getTrialBalance(int $orgId, ?DateTimeImmutable $asOf = null): array
    {
        return $this->generalLedgerEngine->getTrialBalance($orgId, $asOf);
    }

    public function getProfitAndLoss(int $orgId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $entries = $this->journalEntryRepo->findByDateRange($orgId, $from, $to);
        $income = [];
        $expenses = [];
        $totalIncome = 0.0;
        $totalExpenses = 0.0;

        $accounts = $this->accountRepo->findActive($orgId);
        $accountMap = [];
        foreach ($accounts as $a) {
            $accountMap[$a->id] = $a;
        }

        foreach ($entries as $entry) {
            if ($entry->status->value !== 'posted') {
                continue;
            }

            $lines = $this->journalLineRepo->findByJournalEntry($entry->id);
            foreach ($lines as $line) {
                $account = $accountMap[$line->accountId] ?? null;
                if (!$account) {
                    continue;
                }

                $netAmount = $line->creditAmount - $line->debitAmount;
                $sign = $account->normalBalance === 'credit' ? 1 : -1;
                $amount = $netAmount * $sign;

                if ($account->type === \App\Domain\GrowFinance\ValueObjects\AccountType::INCOME) {
                    $totalIncome += $amount;
                    $income[] = [
                        'account_id' => $account->id,
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'amount' => $amount,
                    ];
                } elseif ($account->type === \App\Domain\GrowFinance\ValueObjects\AccountType::EXPENSE) {
                    $totalExpenses += abs($amount);
                    $expenses[] = [
                        'account_id' => $account->id,
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'amount' => abs($amount),
                    ];
                }
            }
        }

        $netIncome = $totalIncome - $totalExpenses;

        $this->outbox->insert(
            eventName: ReportGenerated::NAME,
            payload: (new ReportGenerated(
                companyId: $orgId,
                reportType: 'profit_loss',
                generatedAt: new DateTimeImmutable(),
                periodStart: $from,
                periodEnd: $to,
            ))->toPayload(),
            context: ['business_id' => $orgId],
            publisher: 'growfinance',
        );

        return [
            'income' => $income,
            'expenses' => $expenses,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'from_date' => $from->format('Y-m-d'),
            'to_date' => $to->format('Y-m-d'),
        ];
    }

    public function getBalanceSheet(int $orgId, DateTimeImmutable $asOf): array
    {
        $accounts = $this->accountRepo->findActive($orgId);
        $assets = [];
        $liabilities = [];
        $equity = [];
        $totalAssets = 0.0;
        $totalLiabilities = 0.0;
        $totalEquity = 0.0;

        foreach ($accounts as $account) {
            $balance = $account->currentBalance;

            if ($account->type === \App\Domain\GrowFinance\ValueObjects\AccountType::ASSET) {
                $displayBalance = $account->isContraAccount() ? -$balance : $balance;
                if ($displayBalance != 0) {
                    $assets[] = $this->formatAccountBalance($account, $displayBalance);
                    $totalAssets += $displayBalance;
                }
            } elseif ($account->type === \App\Domain\GrowFinance\ValueObjects\AccountType::LIABILITY) {
                if ($balance != 0) {
                    $liabilities[] = $this->formatAccountBalance($account, $balance);
                    $totalLiabilities += $balance;
                }
            } elseif ($account->type === \App\Domain\GrowFinance\ValueObjects\AccountType::EQUITY) {
                if ($balance != 0) {
                    $equity[] = $this->formatAccountBalance($account, $balance);
                    $totalEquity += $balance;
                }
            }
        }

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'as_of_date' => $asOf->format('Y-m-d'),
        ];
    }

    public function getCashFlow(int $orgId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $cashAccounts = $this->accountRepo->getAccountsByStatementCategory($orgId, 'cash');

        $cashAccountIds = array_map(fn(Account $a) => $a->id, $cashAccounts);

        $openingBalance = (float) DB::table('growfinance_journal_lines as jl')
            ->join('growfinance_journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->whereIn('jl.account_id', $cashAccountIds)
            ->where('je.status', 'posted')
            ->where('je.date', '<', $from->format('Y-m-d'))
            ->selectRaw('COALESCE(SUM(jl.debit_amount) - SUM(jl.credit_amount), 0) as balance')
            ->value('balance') ?? 0;

        $inflows = (float) DB::table('growfinance_journal_lines as jl')
            ->join('growfinance_journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->whereIn('jl.account_id', $cashAccountIds)
            ->where('je.status', 'posted')
            ->whereBetween('je.date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->where('jl.debit_amount', '>', 0)
            ->sum('jl.debit_amount');

        $outflows = (float) DB::table('growfinance_journal_lines as jl')
            ->join('growfinance_journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->whereIn('jl.account_id', $cashAccountIds)
            ->where('je.status', 'posted')
            ->whereBetween('je.date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->where('jl.credit_amount', '>', 0)
            ->sum('jl.credit_amount');

        $netCashFlow = $inflows - $outflows;
        $closingBalance = $openingBalance + $netCashFlow;

        return [
            'opening_balance' => $openingBalance,
            'inflows' => $inflows,
            'outflows' => $outflows,
            'net_cash_flow' => $netCashFlow,
            'closing_balance' => $closingBalance,
            'from_date' => $from->format('Y-m-d'),
            'to_date' => $to->format('Y-m-d'),
        ];
    }

    private function formatAccountBalance(Account $account, float $balance): array
    {
        return [
            'account_id' => $account->id,
            'account_code' => $account->code,
            'account_name' => $account->name,
            'balance' => $balance,
            'statement_category' => $account->statementCategory,
        ];
    }
}
