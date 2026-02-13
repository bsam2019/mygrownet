<?php

declare(strict_types=1);

namespace App\Domain\CMS\Core\Services;

use App\Domain\CMS\Core\ValueObjects\AccountType;
use App\Infrastructure\Persistence\Eloquent\CMS\AccountModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JournalEntryModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JournalLineModel;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsService
{
    public function initializeChartOfAccounts(int $companyId): void
    {
        $defaultAccounts = $this->getDefaultAccounts();

        foreach ($defaultAccounts as $account) {
            AccountModel::firstOrCreate(
                ['company_id' => $companyId, 'code' => $account['code']],
                array_merge($account, ['company_id' => $companyId, 'is_system' => true])
            );
        }
    }

    public function createJournalEntry(
        int $companyId,
        string $description,
        array $lines,
        ?string $reference = null,
        ?int $createdBy = null
    ): JournalEntryModel {
        return DB::transaction(function () use ($companyId, $description, $lines, $reference, $createdBy) {
            $entryNumber = $this->generateEntryNumber($companyId);

            $entry = JournalEntryModel::create([
                'company_id' => $companyId,
                'entry_number' => $entryNumber,
                'entry_date' => now(),
                'description' => $description,
                'reference' => $reference,
                'is_posted' => false,
                'created_by' => $createdBy,
            ]);

            foreach ($lines as $line) {
                JournalLineModel::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit_amount' => $line['debit_amount'] ?? 0,
                    'credit_amount' => $line['credit_amount'] ?? 0,
                    'description' => $line['description'] ?? null,
                ]);
            }

            return $entry->load('lines.account');
        });
    }

    public function postJournalEntry(JournalEntryModel $entry): bool
    {
        if ($entry->is_posted) {
            return false;
        }

        if (!$entry->isBalanced()) {
            throw new \InvalidArgumentException('Journal entry is not balanced');
        }

        return DB::transaction(function () use ($entry) {
            foreach ($entry->lines as $line) {
                $account = $line->account;
                $netAmount = $line->debit_amount - $line->credit_amount;

                if ($account->type->isDebitNormal()) {
                    $account->current_balance += $netAmount;
                } else {
                    $account->current_balance -= $netAmount;
                }

                $account->save();
            }

            $entry->is_posted = true;
            $entry->posted_at = now();
            $entry->save();

            return true;
        });
    }

    public function getTrialBalance(int $companyId): array
    {
        $accounts = AccountModel::forCompany($companyId)
            ->active()
            ->orderBy('code')
            ->get();

        $totalDebits = 0;
        $totalCredits = 0;
        $balances = [];

        foreach ($accounts as $account) {
            $balance = (float) $account->current_balance;

            if ($account->type->isDebitNormal()) {
                if ($balance >= 0) {
                    $totalDebits += $balance;
                    $balances[] = ['account' => $account, 'debit' => $balance, 'credit' => 0];
                } else {
                    $totalCredits += abs($balance);
                    $balances[] = ['account' => $account, 'debit' => 0, 'credit' => abs($balance)];
                }
            } else {
                if ($balance >= 0) {
                    $totalCredits += $balance;
                    $balances[] = ['account' => $account, 'debit' => 0, 'credit' => $balance];
                } else {
                    $totalDebits += abs($balance);
                    $balances[] = ['account' => $account, 'debit' => abs($balance), 'credit' => 0];
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

    private function generateEntryNumber(int $companyId): string
    {
        $lastEntry = JournalEntryModel::forCompany($companyId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastEntry ? ((int) substr($lastEntry->entry_number, 3)) + 1 : 1;

        return 'JE-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    private function getDefaultAccounts(): array
    {
        return [
            // Assets (1000-1999)
            ['code' => '1000', 'name' => 'Cash on Hand', 'type' => AccountType::ASSET, 'category' => 'Cash'],
            ['code' => '1010', 'name' => 'Bank Account', 'type' => AccountType::ASSET, 'category' => 'Cash'],
            ['code' => '1020', 'name' => 'Mobile Money', 'type' => AccountType::ASSET, 'category' => 'Cash'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => AccountType::ASSET, 'category' => 'Receivables'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => AccountType::ASSET, 'category' => 'Inventory'],
            ['code' => '1300', 'name' => 'Prepaid Expenses', 'type' => AccountType::ASSET, 'category' => 'Prepaid'],
            ['code' => '1400', 'name' => 'Fixed Assets', 'type' => AccountType::ASSET, 'category' => 'Fixed Assets'],
            ['code' => '1410', 'name' => 'Accumulated Depreciation', 'type' => AccountType::ASSET, 'category' => 'Fixed Assets'],

            // Liabilities (2000-2999)
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => AccountType::LIABILITY, 'category' => 'Payables'],
            ['code' => '2100', 'name' => 'Accrued Expenses', 'type' => AccountType::LIABILITY, 'category' => 'Accrued'],
            ['code' => '2200', 'name' => 'Short-term Loans', 'type' => AccountType::LIABILITY, 'category' => 'Loans'],
            ['code' => '2300', 'name' => 'VAT Payable', 'type' => AccountType::LIABILITY, 'category' => 'Tax'],
            ['code' => '2310', 'name' => 'Withholding Tax Payable', 'type' => AccountType::LIABILITY, 'category' => 'Tax'],
            ['code' => '2400', 'name' => 'Payroll Liabilities', 'type' => AccountType::LIABILITY, 'category' => 'Payroll'],

            // Equity (3000-3999)
            ['code' => '3000', 'name' => "Owner's Capital", 'type' => AccountType::EQUITY, 'category' => 'Capital'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => AccountType::EQUITY, 'category' => 'Earnings'],
            ['code' => '3200', 'name' => "Owner's Drawings", 'type' => AccountType::EQUITY, 'category' => 'Drawings'],

            // Income (4000-4999)
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => AccountType::INCOME, 'category' => 'Sales'],
            ['code' => '4100', 'name' => 'Service Revenue', 'type' => AccountType::INCOME, 'category' => 'Services'],
            ['code' => '4200', 'name' => 'Other Income', 'type' => AccountType::INCOME, 'category' => 'Other'],
            ['code' => '4300', 'name' => 'Interest Income', 'type' => AccountType::INCOME, 'category' => 'Interest'],

            // Expenses (5000-5999)
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => AccountType::EXPENSE, 'category' => 'COGS'],
            ['code' => '5100', 'name' => 'Salaries & Wages', 'type' => AccountType::EXPENSE, 'category' => 'Payroll'],
            ['code' => '5200', 'name' => 'Rent Expense', 'type' => AccountType::EXPENSE, 'category' => 'Rent'],
            ['code' => '5300', 'name' => 'Utilities', 'type' => AccountType::EXPENSE, 'category' => 'Utilities'],
            ['code' => '5400', 'name' => 'Transport & Fuel', 'type' => AccountType::EXPENSE, 'category' => 'Transport'],
            ['code' => '5500', 'name' => 'Office Supplies', 'type' => AccountType::EXPENSE, 'category' => 'Supplies'],
            ['code' => '5600', 'name' => 'Marketing & Advertising', 'type' => AccountType::EXPENSE, 'category' => 'Marketing'],
            ['code' => '5700', 'name' => 'Bank Charges', 'type' => AccountType::EXPENSE, 'category' => 'Bank'],
            ['code' => '5800', 'name' => 'Depreciation', 'type' => AccountType::EXPENSE, 'category' => 'Depreciation'],
            ['code' => '5900', 'name' => 'Professional Fees', 'type' => AccountType::EXPENSE, 'category' => 'Professional'],
            ['code' => '5950', 'name' => 'Miscellaneous Expenses', 'type' => AccountType::EXPENSE, 'category' => 'Other'],
        ];
    }
}
