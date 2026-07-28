<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use DateTimeImmutable;

class GeneralLedgerEngine
{
    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
    ) {}

    public function getAccountActivity(int $orgId, int $accountId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $entries = $this->journalEntryRepo->findByDateRange($orgId, $from, $to);
        $lines = [];

        foreach ($entries as $entry) {
            if ($entry->status->value !== 'posted') {
                continue;
            }

            $entryLines = $this->journalLineRepo->findByJournalEntry($entry->id);
            foreach ($entryLines as $line) {
                if ($line->accountId === $accountId) {
                    $lines[] = [
                        'date' => $entry->date?->format('Y-m-d'),
                        'journal_number' => $entry->journalNumber,
                        'description' => $line->description ?? $entry->description,
                        'reference' => $entry->reference,
                        'debit' => $line->debitAmount,
                        'credit' => $line->creditAmount,
                    ];
                }
            }
        }

        return $lines;
    }

    public function getAccountBalance(int $orgId, int $accountId, DateTimeImmutable $asOf): float
    {
        $account = $this->accountRepo->findById($accountId);
        if (!$account) {
            throw new \RuntimeException('Account not found: ' . $accountId);
        }

        $entries = $this->journalEntryRepo->findByDateRange($orgId, new DateTimeImmutable('0001-01-01'), $asOf);

        $balance = $account->openingBalance;

        foreach ($entries as $entry) {
            if ($entry->status->value !== 'posted') {
                continue;
            }

            $lines = $this->journalLineRepo->findByJournalEntry($entry->id);
            foreach ($lines as $line) {
                if ($line->accountId !== $accountId) {
                    continue;
                }

                $netAmount = $line->debitAmount - $line->creditAmount;

                $balance += $account->normalBalance === 'debit' ? $netAmount : -$netAmount;
            }
        }

        return $balance;
    }

    public function getPeriodBalances(int $orgId, int $periodId): array
    {
        $entries = $this->journalEntryRepo->findByPeriod($orgId, $periodId);
        $accounts = $this->accountRepo->findActive($orgId);

        $balances = [];
        foreach ($accounts as $account) {
            $balances[$account->id] = [
                'account' => $account->toArray(),
                'debit' => 0.0,
                'credit' => 0.0,
            ];
        }

        foreach ($entries as $entry) {
            if ($entry->status->value !== 'posted') {
                continue;
            }

            $lines = $this->journalLineRepo->findByJournalEntry($entry->id);
            foreach ($lines as $line) {
                if (!isset($balances[$line->accountId])) {
                    continue;
                }
                $balances[$line->accountId]['debit'] += $line->debitAmount;
                $balances[$line->accountId]['credit'] += $line->creditAmount;
            }
        }

        return array_values($balances);
    }

    public function getTrialBalance(int $orgId, ?DateTimeImmutable $asOf = null): array
    {
        $accounts = $this->accountRepo->findActive($orgId);

        usort($accounts, fn(Account $a, Account $b) => strcmp($a->code, $b->code));

        $totalDebits = 0.0;
        $totalCredits = 0.0;
        $balances = [];

        foreach ($accounts as $account) {
            $balance = $account->currentBalance;

            if ($account->normalBalance === 'debit') {
                if ($balance >= 0) {
                    $totalDebits += $balance;
                    $balances[] = ['account' => $account->toArray(), 'debit' => $balance, 'credit' => 0.0];
                } else {
                    $totalCredits += abs($balance);
                    $balances[] = ['account' => $account->toArray(), 'debit' => 0.0, 'credit' => abs($balance)];
                }
            } else {
                if ($balance >= 0) {
                    $totalCredits += $balance;
                    $balances[] = ['account' => $account->toArray(), 'debit' => 0.0, 'credit' => $balance];
                } else {
                    $totalDebits += abs($balance);
                    $balances[] = ['account' => $account->toArray(), 'debit' => abs($balance), 'credit' => 0.0];
                }
            }
        }

        return [
            'balances' => $balances,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
        ];
    }
}
