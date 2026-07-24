<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\Entities\JournalEntry;
use App\Domain\GrowFinance\Entities\JournalLine;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
    ) {}

    public function initializeChartOfAccounts(int $businessId): void
    {
        $defaultAccounts = $this->getDefaultAccounts();

        foreach ($defaultAccounts as $account) {
            $existing = $this->accountRepo->findByCode($businessId, $account['code']);
            if ($existing === null) {
                $this->accountRepo->save(new Account(
                    id: null,
                    businessId: $businessId,
                    code: $account['code'],
                    name: $account['name'],
                    type: $account['type'],
                    category: $account['category'] ?? null,
                    isSystem: true,
                ));
            }
        }
    }

    public function createJournalEntry(
        int $businessId,
        string $description,
        array $lines,
        ?string $reference = null,
        ?int $createdBy = null
    ): array {
        return DB::transaction(function () use ($businessId, $description, $lines, $reference, $createdBy) {
            $entryNumber = $this->generateEntryNumber($businessId);

            $entry = $this->journalEntryRepo->save(new JournalEntry(
                id: null,
                businessId: $businessId,
                entryNumber: $entryNumber,
                entryDate: new \DateTimeImmutable(),
                description: $description,
                reference: $reference,
                isPosted: false,
                createdBy: $createdBy,
                createdAt: null,
                updatedAt: null,
            ));

            foreach ($lines as $line) {
                $this->journalLineRepo->save(new JournalLine(
                    id: null,
                    journalEntryId: $entry->id,
                    accountId: $line['account_id'],
                    debitAmount: (float) ($line['debit_amount'] ?? 0),
                    creditAmount: (float) ($line['credit_amount'] ?? 0),
                    description: $line['description'] ?? null,
                    createdAt: null,
                    updatedAt: null,
                ));
            }

            return $entry->toArray();
        });
    }

    public function postJournalEntry(int $entryId): bool
    {
        $entry = $this->journalEntryRepo->findById($entryId);
        if (!$entry) {
            return false;
        }

        if ($entry->isPosted) {
            return false;
        }

        if (!$entry->isBalanced()) {
            throw new \InvalidArgumentException('Journal entry is not balanced');
        }

        return DB::transaction(function () use ($entry) {
            $lines = $this->journalLineRepo->findByJournalEntry($entry->id);

            foreach ($lines as $line) {
                $accountEntity = $this->accountRepo->findById($line->accountId);
                if (!$accountEntity) {
                    continue;
                }

                $netAmount = $line->debitAmount - $line->creditAmount;

                if ($accountEntity->type->isDebitNormal()) {
                    $newBalance = $accountEntity->currentBalance + $netAmount;
                } else {
                    $newBalance = $accountEntity->currentBalance - $netAmount;
                }

                $this->accountRepo->save(new Account(
                    id: $accountEntity->id,
                    businessId: $accountEntity->businessId,
                    code: $accountEntity->code,
                    name: $accountEntity->name,
                    type: $accountEntity->type,
                    category: $accountEntity->category,
                    description: $accountEntity->description,
                    isSystem: $accountEntity->isSystem,
                    isActive: $accountEntity->isActive,
                    openingBalance: $accountEntity->openingBalance,
                    currentBalance: $newBalance,
                    createdAt: $accountEntity->createdAt,
                    updatedAt: null,
                ));
            }

            $this->journalEntryRepo->save(new JournalEntry(
                id: $entry->id,
                businessId: $entry->businessId,
                entryNumber: $entry->entryNumber,
                entryDate: $entry->entryDate,
                description: $entry->description,
                reference: $entry->reference,
                isPosted: true,
                createdBy: $entry->createdBy,
                createdAt: $entry->createdAt,
                updatedAt: null,
            ));

            return true;
        });
    }

    public function getAccountBalance(int $accountId): float
    {
        $account = $this->accountRepo->findById($accountId);
        if (!$account) {
            throw new \RuntimeException('Account not found');
        }
        return $account->currentBalance;
    }

    public function getTrialBalance(int $businessId): array
    {
        $accounts = $this->accountRepo->findActive($businessId);

        usort($accounts, fn(Account $a, Account $b) => strcmp($a->code, $b->code));

        $totalDebits = 0;
        $totalCredits = 0;
        $balances = [];

        foreach ($accounts as $account) {
            $balance = $account->currentBalance;

            if ($account->type->isDebitNormal()) {
                if ($balance >= 0) {
                    $totalDebits += $balance;
                    $balances[] = ['account' => $account->toArray(), 'debit' => $balance, 'credit' => 0];
                } else {
                    $totalCredits += abs($balance);
                    $balances[] = ['account' => $account->toArray(), 'debit' => 0, 'credit' => abs($balance)];
                }
            } else {
                if ($balance >= 0) {
                    $totalCredits += $balance;
                    $balances[] = ['account' => $account->toArray(), 'debit' => 0, 'credit' => $balance];
                } else {
                    $totalDebits += abs($balance);
                    $balances[] = ['account' => $account->toArray(), 'debit' => abs($balance), 'credit' => 0];
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

    private function generateEntryNumber(int $businessId): string
    {
        $lastEntry = DB::table('growfinance_journal_entries')
            ->where('business_id', $businessId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastEntry ? ((int) substr($lastEntry->entry_number, 3)) + 1 : 1;

        return 'JE-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    private function getDefaultAccounts(): array
    {
        return [
            ['code' => '1000', 'name' => 'Cash on Hand', 'type' => AccountType::ASSET, 'category' => 'Cash'],
            ['code' => '1010', 'name' => 'Bank Account', 'type' => AccountType::ASSET, 'category' => 'Cash'],
            ['code' => '1020', 'name' => 'Mobile Money', 'type' => AccountType::ASSET, 'category' => 'Cash'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => AccountType::ASSET, 'category' => 'Receivables'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => AccountType::ASSET, 'category' => 'Inventory'],
            ['code' => '1300', 'name' => 'Prepaid Expenses', 'type' => AccountType::ASSET, 'category' => 'Prepaid'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => AccountType::LIABILITY, 'category' => 'Payables'],
            ['code' => '2100', 'name' => 'Accrued Expenses', 'type' => AccountType::LIABILITY, 'category' => 'Accrued'],
            ['code' => '2200', 'name' => 'Short-term Loans', 'type' => AccountType::LIABILITY, 'category' => 'Loans'],
            ['code' => '2300', 'name' => 'VAT Payable', 'type' => AccountType::LIABILITY, 'category' => 'Tax'],
            ['code' => '3000', 'name' => "Owner's Capital", 'type' => AccountType::EQUITY, 'category' => 'Capital'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => AccountType::EQUITY, 'category' => 'Earnings'],
            ['code' => '3200', 'name' => "Owner's Drawings", 'type' => AccountType::EQUITY, 'category' => 'Drawings'],
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => AccountType::INCOME, 'category' => 'Sales'],
            ['code' => '4100', 'name' => 'Service Revenue', 'type' => AccountType::INCOME, 'category' => 'Services'],
            ['code' => '4200', 'name' => 'Other Income', 'type' => AccountType::INCOME, 'category' => 'Other'],
            ['code' => '4300', 'name' => 'Interest Income', 'type' => AccountType::INCOME, 'category' => 'Interest'],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => AccountType::EXPENSE, 'category' => 'COGS'],
            ['code' => '5100', 'name' => 'Salaries & Wages', 'type' => AccountType::EXPENSE, 'category' => 'Payroll'],
            ['code' => '5200', 'name' => 'Rent Expense', 'type' => AccountType::EXPENSE, 'category' => 'Rent'],
            ['code' => '5300', 'name' => 'Utilities', 'type' => AccountType::EXPENSE, 'category' => 'Utilities'],
            ['code' => '5400', 'name' => 'Transport & Fuel', 'type' => AccountType::EXPENSE, 'category' => 'Transport'],
            ['code' => '5500', 'name' => 'Office Supplies', 'type' => AccountType::EXPENSE, 'category' => 'Supplies'],
            ['code' => '5600', 'name' => 'Marketing & Advertising', 'type' => AccountType::EXPENSE, 'category' => 'Marketing'],
            ['code' => '5700', 'name' => 'Bank Charges', 'type' => AccountType::EXPENSE, 'category' => 'Bank'],
            ['code' => '5800', 'name' => 'Depreciation', 'type' => AccountType::EXPENSE, 'category' => 'Depreciation'],
            ['code' => '5900', 'name' => 'Miscellaneous Expenses', 'type' => AccountType::EXPENSE, 'category' => 'Other'],
        ];
    }
}
