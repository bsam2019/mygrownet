<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\Entities\BankAccount;
use App\Domain\GrowFinance\Entities\BankStatement;
use App\Domain\GrowFinance\Entities\BankStatementLine;
use App\Domain\GrowFinance\Entities\JournalLine;
use App\Domain\GrowFinance\Entities\ReconciliationMatch;
use App\Domain\GrowFinance\Entities\ReconciliationPeriod;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BankAccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BankStatementLineRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BankStatementRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ReconciliationMatchRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ReconciliationPeriodRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ReconciliationService
{
    public function __construct(
        private BankAccountRepositoryInterface $bankAccountRepo,
        private BankStatementRepositoryInterface $bankStatementRepo,
        private BankStatementLineRepositoryInterface $bankStatementLineRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
        private ReconciliationPeriodRepositoryInterface $reconciliationPeriodRepo,
        private ReconciliationMatchRepositoryInterface $reconciliationMatchRepo,
        private AccountRepositoryInterface $accountRepo,
    ) {}

    public function importStatement(
        int $businessId,
        int $bankAccountId,
        array $data,
        array $lines
    ): array {
        return DB::transaction(function () use ($businessId, $bankAccountId, $data, $lines) {
            $statement = $this->bankStatementRepo->save(new BankStatement(
                id: null,
                businessId: $businessId,
                bankAccountId: $bankAccountId,
                statementPeriod: $data['statement_period'] ?? null,
                startDate: isset($data['start_date']) ? new \DateTimeImmutable($data['start_date']) : null,
                endDate: isset($data['end_date']) ? new \DateTimeImmutable($data['end_date']) : null,
                openingBalance: (float) ($data['opening_balance'] ?? 0),
                closingBalance: (float) ($data['closing_balance'] ?? 0),
                fileName: $data['file_name'] ?? null,
                filePath: $data['file_path'] ?? null,
                lineCount: count($lines),
                status: 'imported',
            ));

            foreach ($lines as $line) {
                $this->bankStatementLineRepo->save(new BankStatementLine(
                    id: null,
                    statementId: $statement->id,
                    transactionDate: isset($line['transaction_date']) ? new \DateTimeImmutable($line['transaction_date']) : null,
                    description: $line['description'] ?? null,
                    reference: $line['reference'] ?? null,
                    debitAmount: (float) ($line['debit_amount'] ?? 0),
                    creditAmount: (float) ($line['credit_amount'] ?? 0),
                    runningBalance: isset($line['running_balance']) ? (float) $line['running_balance'] : null,
                    status: 'unmatched',
                ));
            }

            $this->autoMatchStatementLines($businessId, $statement);

            $savedLines = $this->bankStatementLineRepo->findByStatement($statement->id);
            $result = $statement->toArray();
            $result['lines'] = array_map(fn(BankStatementLine $l) => $l->toArray(), $savedLines);

            return $result;
        });
    }

    public function autoMatchStatementLines(int $businessId, BankStatement $statement): int
    {
        $matched = 0;
        $lines = $this->bankStatementLineRepo->findByStatement($statement->id);
        $postedEntries = $this->journalEntryRepo->findPosted($businessId);

        foreach ($lines as $line) {
            if ($line->status !== 'unmatched') continue;
            if (!$line->transactionDate) continue;

            $startRange = $line->transactionDate->modify('-3 days');
            $endRange = $line->transactionDate->modify('+3 days');

            $candidates = [];
            foreach ($postedEntries as $entry) {
                if (!$entry->entryDate || $entry->entryDate < $startRange || $entry->entryDate > $endRange) continue;

                $entryLines = $this->journalLineRepo->findByJournalEntry($entry->id);
                foreach ($entryLines as $jl) {
                    if ($jl->debitAmount == $line->debitAmount && $jl->creditAmount == $line->creditAmount) {
                        $candidates[] = $jl;
                    }
                }
            }

            if (count($candidates) === 1) {
                $candidate = $candidates[0];

                $updatedLine = new BankStatementLine(
                    id: $line->id,
                    statementId: $line->statementId,
                    transactionDate: $line->transactionDate,
                    description: $line->description,
                    reference: $line->reference,
                    debitAmount: $line->debitAmount,
                    creditAmount: $line->creditAmount,
                    runningBalance: $line->runningBalance,
                    status: 'matched',
                    notes: $line->notes,
                    createdAt: $line->createdAt,
                    updatedAt: new \DateTimeImmutable('now'),
                );
                $this->bankStatementLineRepo->save($updatedLine);

                $updatedJl = new JournalLine(
                    id: $candidate->id,
                    journalEntryId: $candidate->journalEntryId,
                    accountId: $candidate->accountId,
                    debitAmount: $candidate->debitAmount,
                    creditAmount: $candidate->creditAmount,
                    description: $candidate->description,
                    createdAt: $candidate->createdAt,
                    updatedAt: new \DateTimeImmutable('now'),
                );
                $this->journalLineRepo->save($updatedJl);

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
    ): array {
        $statement = $this->bankStatementRepo->findById($statementId);
        $account = $this->bankAccountRepo->findById($bankAccountId);

        if (!$statement || !$account) {
            throw new \RuntimeException('Statement or Bank Account not found');
        }

        return DB::transaction(function () use ($businessId, $bankAccountId, $statement, $account, $createdBy) {
            $period = $this->reconciliationPeriodRepo->save(new ReconciliationPeriod(
                id: null,
                businessId: $businessId,
                bankAccountId: $bankAccountId,
                startDate: $statement->startDate,
                endDate: $statement->endDate,
                openingBalance: $statement->openingBalance,
                closingBalance: $statement->closingBalance,
                bookBalance: $account->currentBalance,
                difference: ($statement->closingBalance ?? 0) - ($account->currentBalance ?? 0),
                status: 'in_progress',
                createdBy: $createdBy,
                completedBy: null,
                completedAt: null,
                notes: null,
                createdAt: null,
                updatedAt: null,
            ));

            return $period->toArray();
        });
    }

    public function matchTransaction(
        int $reconciliationPeriodId,
        int $statementLineId,
        int $journalLineId,
        string $matchType = 'manual'
    ): array {
        return DB::transaction(function () use ($reconciliationPeriodId, $statementLineId, $journalLineId, $matchType) {
            $statementLine = $this->bankStatementLineRepo->findById($statementLineId);
            $journalLine = $this->journalLineRepo->findById($journalLineId);

            if (!$statementLine || !$journalLine) {
                throw new \RuntimeException('Statement line or Journal line not found');
            }

            $statementAmount = ($statementLine->debitAmount ?? 0) > 0
                ? -($statementLine->debitAmount ?? 0) : ($statementLine->creditAmount ?? 0);
            $journalAmount = ($journalLine->debitAmount ?? 0) > 0
                ? ($journalLine->debitAmount ?? 0) : -($journalLine->creditAmount ?? 0);

            $match = $this->reconciliationMatchRepo->save(new ReconciliationMatch(
                id: null,
                reconciliationPeriodId: $reconciliationPeriodId,
                statementLineId: $statementLineId,
                journalLineId: $journalLineId,
                statementAmount: $statementAmount,
                journalAmount: $journalAmount,
                matchType: $matchType,
                createdAt: null,
                updatedAt: null,
            ));

            $updatedLine = new BankStatementLine(
                id: $statementLine->id,
                statementId: $statementLine->statementId,
                transactionDate: $statementLine->transactionDate,
                description: $statementLine->description,
                reference: $statementLine->reference,
                debitAmount: $statementLine->debitAmount,
                creditAmount: $statementLine->creditAmount,
                runningBalance: $statementLine->runningBalance,
                status: 'matched',
                notes: $statementLine->notes,
                createdAt: $statementLine->createdAt,
                updatedAt: new \DateTimeImmutable('now'),
            );
            $this->bankStatementLineRepo->save($updatedLine);

            $updatedJl = new JournalLine(
                id: $journalLine->id,
                journalEntryId: $journalLine->journalEntryId,
                accountId: $journalLine->accountId,
                debitAmount: $journalLine->debitAmount,
                creditAmount: $journalLine->creditAmount,
                description: $journalLine->description,
                createdAt: $journalLine->createdAt,
                updatedAt: new \DateTimeImmutable('now'),
            );
            $this->journalLineRepo->save($updatedJl);

            return $match->toArray();
        });
    }

    public function unmatchTransaction(int $matchId): void
    {
        DB::transaction(function () use ($matchId) {
            $match = $this->reconciliationMatchRepo->findById($matchId);
            if (!$match) return;

            if ($match->statementLineId) {
                $statementLine = $this->bankStatementLineRepo->findById($match->statementLineId);
                if ($statementLine) {
                    $updatedLine = new BankStatementLine(
                        id: $statementLine->id,
                        statementId: $statementLine->statementId,
                        transactionDate: $statementLine->transactionDate,
                        description: $statementLine->description,
                        reference: $statementLine->reference,
                        debitAmount: $statementLine->debitAmount,
                        creditAmount: $statementLine->creditAmount,
                        runningBalance: $statementLine->runningBalance,
                        status: 'unmatched',
                        notes: $statementLine->notes,
                        createdAt: $statementLine->createdAt,
                        updatedAt: new \DateTimeImmutable('now'),
                    );
                    $this->bankStatementLineRepo->save($updatedLine);
                }
            }

            if ($match->journalLineId) {
                $journalLine = $this->journalLineRepo->findById($match->journalLineId);
                if ($journalLine) {
                    $updatedJl = new JournalLine(
                        id: $journalLine->id,
                        journalEntryId: $journalLine->journalEntryId,
                        accountId: $journalLine->accountId,
                        debitAmount: $journalLine->debitAmount,
                        creditAmount: $journalLine->creditAmount,
                        description: $journalLine->description,
                        createdAt: $journalLine->createdAt,
                        updatedAt: new \DateTimeImmutable('now'),
                    );
                    $this->journalLineRepo->save($updatedJl);
                }
            }

            DB::table('growfinance_reconciliation_matches')->where('id', $matchId)->delete();
        });
    }

    public function completeReconciliation(int $periodId, int $completedBy): array
    {
        return DB::transaction(function () use ($periodId, $completedBy) {
            $period = $this->reconciliationPeriodRepo->findById($periodId);
            if (!$period) {
                throw new \RuntimeException('Reconciliation period not found');
            }

            $statements = $this->bankStatementRepo->findByBankAccount($period->bankAccountId);

            $relevantStatementIds = [];
            foreach ($statements as $stmt) {
                if ($stmt->startDate && $period->startDate && $period->endDate) {
                    if ($stmt->startDate >= $period->startDate && $stmt->startDate <= $period->endDate) {
                        $relevantStatementIds[] = $stmt->id;
                    }
                }
            }

            $unmatchedCount = 0;
            $ignoredCount = 0;
            foreach ($relevantStatementIds as $stmtId) {
                $statementLines = $this->bankStatementLineRepo->findByStatement($stmtId);
                foreach ($statementLines as $sl) {
                    if ($sl->status === 'unmatched') $unmatchedCount++;
                    if ($sl->status === 'ignored') $ignoredCount++;
                }
            }

            $difference = ($period->closingBalance ?? 0) - ($period->bookBalance ?? 0);
            $notes = $period->notes ?? "Completed with {$unmatchedCount} unmatched, {$ignoredCount} ignored";

            $updated = new ReconciliationPeriod(
                id: $period->id,
                businessId: $period->businessId,
                bankAccountId: $period->bankAccountId,
                startDate: $period->startDate,
                endDate: $period->endDate,
                openingBalance: $period->openingBalance,
                closingBalance: $period->closingBalance,
                bookBalance: $period->bookBalance,
                difference: $difference,
                status: 'completed',
                createdBy: $period->createdBy,
                completedBy: $completedBy,
                completedAt: new \DateTimeImmutable('now'),
                notes: $notes,
                createdAt: $period->createdAt,
                updatedAt: new \DateTimeImmutable('now'),
            );
            $this->reconciliationPeriodRepo->save($updated);

            $account = $this->bankAccountRepo->findById($period->bankAccountId);
            if ($account) {
                $updatedAccount = new BankAccount(
                    id: $account->id,
                    businessId: $account->businessId,
                    accountName: $account->accountName,
                    accountNumber: $account->accountNumber,
                    bankName: $account->bankName,
                    bankBranch: $account->bankBranch,
                    accountType: $account->accountType,
                    currency: $account->currency,
                    openingBalance: $account->openingBalance,
                    currentBalance: $period->closingBalance ?? $account->currentBalance,
                    isDefault: $account->isDefault,
                    isActive: $account->isActive,
                    notes: $account->notes,
                    deletedAt: $account->deletedAt,
                    createdAt: $account->createdAt,
                    updatedAt: new \DateTimeImmutable('now'),
                );
                $this->bankAccountRepo->save($updatedAccount);
            }

            return $updated->toArray();
        });
    }

    public function getUnreconciledJournalLines(int $businessId, int $accountId, array $dateRange = []): array
    {
        $accounts = $this->accountRepo->findByBusiness($businessId);
        $bankCategories = ['Cash', 'Bank', 'Mobile Money', 'cash', 'bank'];
        $bankAccountIds = array_map(
            fn(Account $a) => $a->id,
            array_filter(
                $accounts,
                fn(Account $a) => $a->category !== null && in_array($a->category, $bankCategories)
            )
        );

        $postedEntries = $this->journalEntryRepo->findPosted($businessId);

        $lines = [];
        foreach ($postedEntries as $entry) {
            if (!empty($dateRange) && $entry->entryDate) {
                if ($entry->entryDate < new \DateTimeImmutable($dateRange[0]) || $entry->entryDate > new \DateTimeImmutable($dateRange[1])) {
                    continue;
                }
            }

            $entryLines = $this->journalLineRepo->findByJournalEntry($entry->id);
            foreach ($entryLines as $line) {
                if (in_array($line->accountId, $bankAccountIds)) {
                    $lines[] = $line->toArray();
                }
            }
        }

        usort($lines, fn($a, $b) => ($a['journal_entry_id'] ?? 0) <=> ($b['journal_entry_id'] ?? 0));

        return $lines;
    }
}
