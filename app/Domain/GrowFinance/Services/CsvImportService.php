<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\Entities\JournalEntry;
use App\Domain\GrowFinance\Entities\JournalLine;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BudgetRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use DateTimeImmutable;

class CsvImportService
{
    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
        private BudgetRepositoryInterface $budgetRepo,
        private AccountingService $accountingService,
    ) {}

    public function parseCsv(string $content, bool $hasHeader = true): array
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $content));
        $rows = [];
        $startIndex = $hasHeader ? 1 : 0;

        for ($i = $startIndex; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $columns = str_getcsv($line);
            if (count($columns) < 2) continue;

            $rows[] = $columns;
        }

        return $rows;
    }

    public function importAccounts(int $businessId, string $csvContent): array
    {
        $rows = $this->parseCsv($csvContent);
        $results = ['imported' => 0, 'errors' => []];

        foreach ($rows as $i => $row) {
            try {
                $code = trim($row[0] ?? '');
                $name = trim($row[1] ?? '');
                $typeStr = trim($row[2] ?? 'expense');
                $normalBalance = trim($row[3] ?? '');

                if (empty($code) || empty($name)) {
                    $results['errors'][] = "Row $i: code and name required";
                    continue;
                }

                $existing = $this->accountRepo->findByCode($businessId, $code);
                if ($existing) {
                    $results['errors'][] = "Row $i: account $code already exists";
                    continue;
                }

                $type = AccountType::tryFrom($typeStr) ?? AccountType::EXPENSE;
                $normalBal = !empty($normalBalance) ? $normalBalance : ($type->isDebitNormal() ? 'debit' : 'credit');

                $this->accountRepo->save(new Account(
                    id: null,
                    businessId: $businessId,
                    code: $code,
                    name: $name,
                    type: $type,
                    normalBalance: $normalBal,
                    level: (int) (trim($row[4] ?? '2')),
                    description: trim($row[5] ?? ''),
                ));

                $results['imported']++;
            } catch (\Throwable $e) {
                $results['errors'][] = "Row $i: " . $e->getMessage();
            }
        }

        return $results;
    }

    public function importJournals(int $businessId, string $csvContent): array
    {
        $rows = $this->parseCsv($csvContent);
        $results = ['imported' => 0, 'errors' => []];
        $currentEntry = null;
        $currentLines = [];

        foreach ($rows as $i => $row) {
            try {
                $journalRef = trim($row[0] ?? '');
                $date = trim($row[1] ?? '');
                $description = trim($row[2] ?? '');
                $accountCode = trim($row[3] ?? '');
                $debitAmount = (float) ($row[4] ?? 0);
                $creditAmount = (float) ($row[5] ?? 0);

                if (empty($journalRef) || empty($accountCode)) continue;

                if ($currentEntry === null || $currentEntry !== $journalRef) {
                    if ($currentEntry !== null && !empty($currentLines)) {
                        $this->postJournalEntry($businessId, $currentEntry, $currentLines, $results);
                    }
                    $currentEntry = $journalRef;
                    $currentLines = [];
                }

                $account = $this->accountRepo->findByCode($businessId, $accountCode);
                if (!$account) {
                    $results['errors'][] = "Row $i: account $accountCode not found";
                    continue;
                }

                $currentLines[] = [
                    'account_id' => $account->id,
                    'debit_amount' => $debitAmount,
                    'credit_amount' => $creditAmount,
                    'description' => $description,
                ];
            } catch (\Throwable $e) {
                $results['errors'][] = "Row $i: " . $e->getMessage();
            }
        }

        if ($currentEntry !== null && !empty($currentLines)) {
            $this->postJournalEntry($businessId, $currentEntry, $currentLines, $results);
        }

        return $results;
    }

    public function importBudgets(int $businessId, string $csvContent): array
    {
        $rows = $this->parseCsv($csvContent);
        $results = ['imported' => 0, 'errors' => []];

        foreach ($rows as $i => $row) {
            try {
                $name = trim($row[0] ?? '');
                $fiscalYear = trim($row[1] ?? '');
                $accountCode = trim($row[2] ?? '');
                $amount = (float) ($row[3] ?? 0);
                $periodType = trim($row[4] ?? 'monthly');

                if (empty($name) || empty($fiscalYear)) {
                    $results['errors'][] = "Row $i: name and fiscal_year required";
                    continue;
                }

                $account = !empty($accountCode) ? $this->accountRepo->findByCode($businessId, $accountCode) : null;

                $this->budgetRepo->save(new \App\Domain\GrowFinance\Entities\Budget(
                    id: null,
                    businessId: $businessId,
                    name: $name,
                    fiscalYear: $fiscalYear,
                    accountId: $account?->id,
                    amount: $amount,
                    periodType: $periodType,
                    createdAt: new DateTimeImmutable(),
                    updatedAt: null,
                ));

                $results['imported']++;
            } catch (\Throwable $e) {
                $results['errors'][] = "Row $i: " . $e->getMessage();
            }
        }

        return $results;
    }

    private function postJournalEntry(int $businessId, string $ref, array $lines, array &$results): void
    {
        try {
            $entry = $this->accountingService->createJournalEntry(
                businessId: $businessId,
                description: 'CSV import: ' . $ref,
                lines: $lines,
                reference: $ref,
            );
            $results['imported']++;
        } catch (\Throwable $e) {
            $results['errors'][] = "Entry $ref: " . $e->getMessage();
        }
    }
}
