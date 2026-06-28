<?php

namespace App\Domain\GrowFinance\Services;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceBankAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceBankStatementModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceBankStatementLineModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceJournalEntryModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceJournalLineModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceReconciliationPeriodModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceReconciliationMatchModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconciliationService
{
    public function importStatement(
        int $businessId,
        int $bankAccountId,
        array $data,
        array $lines
    ): GrowFinanceBankStatementModel {
        return DB::transaction(function () use ($businessId, $bankAccountId, $data, $lines) {
            $statement = GrowFinanceBankStatementModel::create([
                'business_id' => $businessId,
                'bank_account_id' => $bankAccountId,
                'statement_period' => $data['statement_period'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'opening_balance' => $data['opening_balance'] ?? 0,
                'closing_balance' => $data['closing_balance'] ?? 0,
                'file_name' => $data['file_name'] ?? null,
                'file_path' => $data['file_path'] ?? null,
                'line_count' => count($lines),
                'status' => 'imported',
            ]);

            foreach ($lines as $line) {
                $statement->lines()->create([
                    'transaction_date' => $line['transaction_date'],
                    'description' => $line['description'],
                    'reference' => $line['reference'] ?? null,
                    'debit_amount' => $line['debit_amount'] ?? 0,
                    'credit_amount' => $line['credit_amount'] ?? 0,
                    'running_balance' => $line['running_balance'] ?? null,
                    'status' => 'unmatched',
                ]);
            }

            // Auto-match against journal entries
            $this->autoMatchStatementLines($businessId, $statement);

            return $statement->load('lines');
        });
    }

    public function autoMatchStatementLines(int $businessId, GrowFinanceBankStatementModel $statement): int
    {
        $matched = 0;

        foreach ($statement->lines as $line) {
            if ($line->status !== 'unmatched') continue;

            // Find journal entries with matching amount and close date
            $candidates = GrowFinanceJournalLineModel::whereHas('journalEntry', function ($q) use ($businessId, $line) {
                $q->where('business_id', $businessId)
                  ->where('is_posted', true)
                  ->whereBetween('entry_date', [
                      $line->transaction_date->copy()->subDays(3),
                      $line->transaction_date->copy()->addDays(3),
                  ]);
            })
            ->where('reconciled', false)
            ->where(function ($q) use ($line) {
                $q->where('debit_amount', $line->debit_amount)
                  ->where('credit_amount', $line->credit_amount);
            })
            ->get();

            if ($candidates->count() === 1) {
                $candidate = $candidates->first();
                $line->update(['status' => 'matched']);
                $candidate->update(['reconciled' => true, 'reconciled_at' => now()]);
                $matched++;
            }
        }

        return $matched;
    }

    public function createReconciliationPeriod(
        int $businessId,
        int $bankAccountId,
        int $statementId,
        int $createdBy
    ): GrowFinanceReconciliationPeriodModel {
        $statement = GrowFinanceBankStatementModel::findOrFail($statementId);
        $account = GrowFinanceBankAccountModel::findOrFail($bankAccountId);

        return DB::transaction(function () use ($businessId, $bankAccountId, $statement, $account, $createdBy) {
            return GrowFinanceReconciliationPeriodModel::create([
                'business_id' => $businessId,
                'bank_account_id' => $bankAccountId,
                'start_date' => $statement->start_date,
                'end_date' => $statement->end_date,
                'opening_balance' => $statement->opening_balance,
                'closing_balance' => $statement->closing_balance,
                'book_balance' => $account->current_balance,
                'difference' => $statement->closing_balance - $account->current_balance,
                'status' => 'in_progress',
                'created_by' => $createdBy,
            ]);
        });
    }

    public function matchTransaction(
        int $reconciliationPeriodId,
        int $statementLineId,
        int $journalLineId,
        string $matchType = 'manual'
    ): GrowFinanceReconciliationMatchModel {
        return DB::transaction(function () use ($reconciliationPeriodId, $statementLineId, $journalLineId, $matchType) {
            $statementLine = GrowFinanceBankStatementLineModel::findOrFail($statementLineId);
            $journalLine = GrowFinanceJournalLineModel::findOrFail($journalLineId);

            $match = GrowFinanceReconciliationMatchModel::create([
                'reconciliation_period_id' => $reconciliationPeriodId,
                'statement_line_id' => $statementLineId,
                'journal_line_id' => $journalLineId,
                'statement_amount' => $statementLine->debit_amount > 0
                    ? -$statementLine->debit_amount : $statementLine->credit_amount,
                'journal_amount' => $journalLine->debit_amount > 0
                    ? $journalLine->debit_amount : -$journalLine->credit_amount,
                'match_type' => $matchType,
            ]);

            $statementLine->update(['status' => 'matched']);
            $journalLine->update(['reconciled' => true, 'reconciled_at' => now()]);

            return $match;
        });
    }

    public function unmatchTransaction(int $matchId): void
    {
        DB::transaction(function () use ($matchId) {
            $match = GrowFinanceReconciliationMatchModel::findOrFail($matchId);

            $match->statementLine()->update(['status' => 'unmatched']);
            $match->journalLine()->update(['reconciled' => false, 'reconciled_at' => null]);

            $match->delete();
        });
    }

    public function completeReconciliation(int $periodId, int $completedBy): GrowFinanceReconciliationPeriodModel
    {
        return DB::transaction(function () use ($periodId, $completedBy) {
            $period = GrowFinanceReconciliationPeriodModel::findOrFail($periodId);

            $statementLines = GrowFinanceBankStatementLineModel::whereIn(
                'statement_id',
                GrowFinanceBankStatementModel::where('bank_account_id', $period->bank_account_id)
                    ->whereBetween('start_date', [$period->start_date, $period->end_date])
                    ->pluck('id')
            );

            $unmatchedCount = (clone $statementLines)->where('status', 'unmatched')->count();
            $ignoredCount = (clone $statementLines)->where('status', 'ignored')->count();

            $period->update([
                'status' => 'completed',
                'completed_by' => $completedBy,
                'completed_at' => now(),
                'difference' => $period->closing_balance - $period->book_balance,
                'notes' => $period->notes ?: "Completed with {$unmatchedCount} unmatched, {$ignoredCount} ignored",
            ]);

            // Update bank account balance
            $account = GrowFinanceBankAccountModel::findOrFail($period->bank_account_id);
            $account->update(['current_balance' => $period->closing_balance]);

            return $period;
        });
    }

    public function getUnreconciledJournalLines(int $businessId, int $accountId, array $dateRange = []): array
    {
        $query = GrowFinanceJournalLineModel::whereHas('journalEntry', function ($q) use ($businessId, $dateRange) {
            $q->where('business_id', $businessId)->where('is_posted', true);
            if (!empty($dateRange)) {
                $q->whereBetween('entry_date', [$dateRange[0], $dateRange[1]]);
            }
        })
        ->where('reconciled', false);

        // Filter lines where the journal entry's other line(s) involve the bank account
        $bankAccountIds = GrowFinanceAccountModel::forBusiness($businessId)
            ->whereIn('category', ['Cash', 'Bank', 'Mobile Money', 'cash', 'bank'])
            ->pluck('id');

        return $query->whereIn('account_id', $bankAccountIds)
            ->with(['journalEntry', 'account'])
            ->orderBy('journal_entry_id')
            ->get()
            ->toArray();
    }
}
