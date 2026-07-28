<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\Core\Services\OutboxService;
use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\Entities\JournalEntry;
use App\Domain\GrowFinance\Entities\JournalLine;
use App\Domain\GrowFinance\Events\AccountBalanceChanged;
use App\Domain\GrowFinance\Events\PeriodClosed;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
        private readonly OutboxService $outbox,
        private readonly PostingEngine $postingEngine,
        private readonly GeneralLedgerEngine $generalLedgerEngine,
    ) {}

    public function initializeChartOfAccounts(int $businessId, string $template = 'default'): void
    {
        $defaultAccounts = match ($template) {
            'retail' => $this->getRetailAccounts(),
            'service' => $this->getServiceAccounts(),
            'manufacturing' => $this->getManufacturingAccounts(),
            'ngo' => $this->getNgoAccounts(),
            default => $this->getDefaultAccounts(),
        };

        $created = [];

        foreach ($defaultAccounts as $account) {
            $existing = $this->accountRepo->findByCode($businessId, $account['code']);
            if ($existing !== null) {
                $created[$account['code']] = $existing;
                continue;
            }

            $isDebitNormal = $account['type']->isDebitNormal();
            $normalBalance = $account['normal_balance'] ?? ($isDebitNormal ? 'debit' : 'credit');

            $parentId = null;
            $path = $account['code'];

            if (isset($account['parent_code']) && isset($created[$account['parent_code']])) {
                $parentId = $created[$account['parent_code']]->id;
                $parentPath = $created[$account['parent_code']]->path ?? $created[$account['parent_code']]->code;
                $path = $parentPath . '/' . $account['code'];
            }

            $saved = $this->accountRepo->save(new Account(
                id: null,
                businessId: $businessId,
                code: $account['code'],
                name: $account['name'],
                type: $account['type'],
                normalBalance: $normalBalance,
                parentId: $parentId,
                level: $account['level'] ?? 1,
                path: $path,
                statementCategory: $account['statement_category'] ?? null,
                category: $account['category'] ?? null,
                isSystem: true,
            ));

            $created[$account['code']] = $saved;
        }
    }

    public function createJournalEntry(
        int $businessId,
        string $description,
        array $lines,
        ?string $reference = null,
        ?int $createdBy = null,
        ?DateTimeImmutable $date = null,
        ?array $dimensions = null,
        string $currencyCode = 'ZMW',
        float $exchangeRate = 1.0,
    ): array {
        return DB::transaction(function () use ($businessId, $description, $lines, $reference, $createdBy, $date, $dimensions, $currencyCode, $exchangeRate) {
            $journalNumber = $this->generateJournalNumber($businessId);

            $entry = $this->journalEntryRepo->save(new JournalEntry(
                id: null,
                businessId: $businessId,
                journalNumber: $journalNumber,
                date: $date ?? new DateTimeImmutable(),
                description: $description,
                reference: $reference,
                status: JournalStatus::DRAFT,
                currencyCode: $currencyCode,
                exchangeRate: $exchangeRate,
                createdBy: $createdBy,
                dimensions: $dimensions,
            ));

            // Compute functional amounts for multi-currency entries
            $isMultiCurrency = strtoupper($currencyCode) !== 'ZMW' && abs($exchangeRate - 1.0) > 0.0001;

            foreach ($lines as $line) {
                $debitAmount = (float) ($line['debit_amount'] ?? 0);
                $creditAmount = (float) ($line['credit_amount'] ?? 0);

                $functionalDebit = null;
                $functionalCredit = null;
                if ($isMultiCurrency) {
                    $functionalDebit = round($debitAmount * $exchangeRate, 2);
                    $functionalCredit = round($creditAmount * $exchangeRate, 2);
                }

                $this->journalLineRepo->save(new JournalLine(
                    id: null,
                    journalEntryId: $entry->id,
                    accountId: $line['account_id'],
                    debitAmount: $debitAmount,
                    creditAmount: $creditAmount,
                    functionalDebitAmount: $functionalDebit,
                    functionalCreditAmount: $functionalCredit,
                    description: $line['description'] ?? null,
                    dimensions: $line['dimensions'] ?? null,
                ));
            }

            $this->outbox->insert(
                eventName: 'growfinance.journal.created.v1',
                payload: [
                    'business_id' => $businessId,
                    'journal_id' => $entry->id,
                    'journal_number' => $journalNumber,
                    'description' => $description,
                ],
                context: ['business_id' => $businessId],
                publisher: 'growfinance',
            );

            return $entry->toArray();
        });
    }

    public function postJournalEntry(int $entryId): bool
    {
        try {
            $this->postingEngine->post($entryId);
            return true;
        } catch (\DomainException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    public function reverseJournalEntry(int $entryId, string $reason): array
    {
        return $this->postingEngine->reverse($entryId, $reason);
    }

    public function closePeriod(int $businessId, string $periodStart, string $periodEnd): bool
    {
        $start = new DateTimeImmutable($periodStart);
        $end = new DateTimeImmutable($periodEnd);

        $entries = $this->journalEntryRepo->findByDateRange($businessId, $start, $end);
        $unposted = array_filter($entries, fn($e) => $e->status === JournalStatus::DRAFT);
        if (!empty($unposted)) {
            throw new \RuntimeException('Cannot close period: ' . count($unposted) . ' journal entries are still unposted');
        }

        $periodEvent = new PeriodClosed(
            companyId: $businessId,
            periodType: 'monthly',
            periodStart: $start,
            periodEnd: $end,
            closedAt: new DateTimeImmutable(),
        );
        $this->outbox->insert(
            eventName: PeriodClosed::NAME,
            payload: $periodEvent->toPayload(),
            context: ['business_id' => $businessId, 'period_start' => $periodStart, 'period_end' => $periodEnd],
            publisher: 'growfinance',
        );

        return true;
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
        return $this->generalLedgerEngine->getTrialBalance($businessId);
    }

    private function generateJournalNumber(int $businessId): string
    {
        $lastEntry = DB::table('growfinance_journal_entries')
            ->where('business_id', $businessId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastEntry ? ((int) substr($lastEntry->journal_number, 3)) + 1 : 1;

        return 'JE-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    private function getDefaultAccounts(): array
    {
        return [
            ['code' => '1000', 'name' => 'Current Assets', 'type' => AccountType::ASSET, 'level' => 1, 'statement_category' => 'current_asset'],
            ['code' => '1100', 'name' => 'Cash and Cash Equivalents', 'type' => AccountType::ASSET, 'level' => 2, 'parent_code' => '1000', 'statement_category' => 'cash'],
            ['code' => '1110', 'name' => 'Cash on Hand', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1100', 'category' => 'Cash', 'statement_category' => 'cash'],
            ['code' => '1120', 'name' => 'Bank Account', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1100', 'category' => 'Cash', 'statement_category' => 'cash'],
            ['code' => '1130', 'name' => 'Mobile Money', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1100', 'category' => 'Cash', 'statement_category' => 'cash'],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => AccountType::ASSET, 'level' => 2, 'parent_code' => '1000', 'category' => 'Receivables', 'statement_category' => 'receivables'],
            ['code' => '1300', 'name' => 'Inventory', 'type' => AccountType::ASSET, 'level' => 2, 'parent_code' => '1000', 'category' => 'Inventory', 'statement_category' => 'inventory'],
            ['code' => '1400', 'name' => 'Prepaid Expenses', 'type' => AccountType::ASSET, 'level' => 2, 'parent_code' => '1000', 'category' => 'Prepaid', 'statement_category' => 'prepayments'],
            ['code' => '1500', 'name' => 'Non-Current Assets', 'type' => AccountType::ASSET, 'level' => 1, 'statement_category' => 'fixed_asset'],
            ['code' => '1510', 'name' => 'Fixed Assets', 'type' => AccountType::ASSET, 'level' => 2, 'parent_code' => '1500', 'statement_category' => 'fixed_asset'],
            ['code' => '1520', 'name' => 'Accumulated Depreciation', 'type' => AccountType::ASSET, 'normal_balance' => 'credit', 'level' => 2, 'parent_code' => '1500', 'statement_category' => 'fixed_asset'],
            ['code' => '2000', 'name' => 'Current Liabilities', 'type' => AccountType::LIABILITY, 'level' => 1, 'statement_category' => 'current_liability'],
            ['code' => '2100', 'name' => 'Accounts Payable', 'type' => AccountType::LIABILITY, 'level' => 2, 'parent_code' => '2000', 'category' => 'Payables', 'statement_category' => 'payables'],
            ['code' => '2200', 'name' => 'Accrued Expenses', 'type' => AccountType::LIABILITY, 'level' => 2, 'parent_code' => '2000', 'category' => 'Accrued', 'statement_category' => 'accruals'],
            ['code' => '2300', 'name' => 'Short-term Loans', 'type' => AccountType::LIABILITY, 'level' => 2, 'parent_code' => '2000', 'category' => 'Loans', 'statement_category' => 'borrowings'],
            ['code' => '2400', 'name' => 'VAT Payable', 'type' => AccountType::LIABILITY, 'level' => 2, 'parent_code' => '2000', 'category' => 'Tax', 'statement_category' => 'tax'],
            ['code' => '2500', 'name' => 'Withholding Tax Payable', 'type' => AccountType::LIABILITY, 'level' => 2, 'parent_code' => '2000', 'statement_category' => 'tax'],
            ['code' => '2600', 'name' => 'Non-Current Liabilities', 'type' => AccountType::LIABILITY, 'level' => 1, 'statement_category' => 'long_term_liability'],
            ['code' => '2610', 'name' => 'Long-term Loans', 'type' => AccountType::LIABILITY, 'level' => 2, 'parent_code' => '2600', 'statement_category' => 'borrowings'],
            ['code' => '3000', 'name' => "Owner's Capital", 'type' => AccountType::EQUITY, 'level' => 2, 'category' => 'Capital', 'statement_category' => 'equity'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => AccountType::EQUITY, 'level' => 2, 'category' => 'Earnings', 'statement_category' => 'retained_earnings'],
            ['code' => '3200', 'name' => "Owner's Drawings", 'type' => AccountType::EQUITY, 'level' => 2, 'category' => 'Drawings', 'statement_category' => 'drawings'],
            ['code' => '4000', 'name' => 'Operating Revenue', 'type' => AccountType::INCOME, 'level' => 1, 'statement_category' => 'operating_revenue'],
            ['code' => '4100', 'name' => 'Sales Revenue', 'type' => AccountType::INCOME, 'level' => 2, 'parent_code' => '4000', 'category' => 'Sales', 'statement_category' => 'operating_revenue'],
            ['code' => '4200', 'name' => 'Service Revenue', 'type' => AccountType::INCOME, 'level' => 2, 'parent_code' => '4000', 'category' => 'Services', 'statement_category' => 'operating_revenue'],
            ['code' => '4300', 'name' => 'Other Income', 'type' => AccountType::INCOME, 'level' => 2, 'parent_code' => '4000', 'category' => 'Other', 'statement_category' => 'other_income'],
            ['code' => '4400', 'name' => 'Interest Income', 'type' => AccountType::INCOME, 'level' => 2, 'parent_code' => '4000', 'category' => 'Interest', 'statement_category' => 'other_income'],
            ['code' => '5000', 'name' => 'Cost of Sales', 'type' => AccountType::EXPENSE, 'level' => 1, 'statement_category' => 'cost_of_sales'],
            ['code' => '5100', 'name' => 'Cost of Goods Sold', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5000', 'category' => 'COGS', 'statement_category' => 'cost_of_sales'],
            ['code' => '5200', 'name' => 'Operating Expenses', 'type' => AccountType::EXPENSE, 'level' => 1, 'statement_category' => 'operating_expense'],
            ['code' => '5210', 'name' => 'Salaries & Wages', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5200', 'category' => 'Payroll', 'statement_category' => 'operating_expense'],
            ['code' => '5220', 'name' => 'Rent Expense', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5200', 'category' => 'Rent', 'statement_category' => 'operating_expense'],
            ['code' => '5230', 'name' => 'Utilities', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5200', 'category' => 'Utilities', 'statement_category' => 'operating_expense'],
            ['code' => '5240', 'name' => 'Transport & Fuel', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5200', 'category' => 'Transport', 'statement_category' => 'operating_expense'],
            ['code' => '5250', 'name' => 'Office Supplies', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5200', 'category' => 'Supplies', 'statement_category' => 'operating_expense'],
            ['code' => '5260', 'name' => 'Marketing & Advertising', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5200', 'category' => 'Marketing', 'statement_category' => 'operating_expense'],
            ['code' => '5270', 'name' => 'Bank Charges', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5200', 'category' => 'Bank', 'statement_category' => 'operating_expense'],
            ['code' => '5280', 'name' => 'Depreciation', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5200', 'category' => 'Depreciation', 'statement_category' => 'operating_expense'],
            ['code' => '5290', 'name' => 'Professional Fees', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5200', 'statement_category' => 'operating_expense'],
            ['code' => '5300', 'name' => 'Miscellaneous Expenses', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5200', 'category' => 'Other', 'statement_category' => 'operating_expense'],
            ['code' => '4305', 'name' => 'Realized FX Gain', 'type' => AccountType::INCOME, 'level' => 2, 'parent_code' => '4000', 'category' => 'Currency', 'statement_category' => 'other_income'],
            ['code' => '5305', 'name' => 'Realized FX Loss', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5200', 'category' => 'Currency', 'statement_category' => 'operating_expense'],
            ['code' => '3300', 'name' => 'Unrealized FX Revaluation', 'type' => AccountType::EQUITY, 'level' => 2, 'category' => 'Currency', 'statement_category' => 'equity'],
        ];
    }

    private function getRetailAccounts(): array
    {
        $base = $this->getDefaultAccounts();
        $retail = [
            ['code' => '1160', 'name' => 'Petty Cash', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1100', 'category' => 'Cash', 'statement_category' => 'cash'],
            ['code' => '1310', 'name' => 'Merchandise Inventory', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1300', 'category' => 'Inventory', 'statement_category' => 'inventory'],
            ['code' => '1600', 'name' => 'POS Systems', 'type' => AccountType::ASSET, 'level' => 2, 'parent_code' => '1500', 'statement_category' => 'fixed_asset'],
            ['code' => '4110', 'name' => 'Retail Sales', 'type' => AccountType::INCOME, 'level' => 3, 'parent_code' => '4100', 'category' => 'Sales', 'statement_category' => 'operating_revenue'],
            ['code' => '4120', 'name' => 'Wholesale Sales', 'type' => AccountType::INCOME, 'level' => 3, 'parent_code' => '4100', 'category' => 'Sales', 'statement_category' => 'operating_revenue'],
            ['code' => '5110', 'name' => 'Cost of Goods Sold - Retail', 'type' => AccountType::EXPENSE, 'level' => 3, 'parent_code' => '5100', 'category' => 'COGS', 'statement_category' => 'cost_of_sales'],
            ['code' => '5120', 'name' => 'Cost of Goods Sold - Wholesale', 'type' => AccountType::EXPENSE, 'level' => 3, 'parent_code' => '5100', 'category' => 'COGS', 'statement_category' => 'cost_of_sales'],
            ['code' => '5310', 'name' => 'Point of Sale Expenses', 'type' => AccountType::EXPENSE, 'level' => 3, 'parent_code' => '5200', 'category' => 'Other', 'statement_category' => 'operating_expense'],
            ['code' => '5320', 'name' => 'Shelf Rental Income', 'type' => AccountType::INCOME, 'level' => 3, 'parent_code' => '4000', 'category' => 'Other', 'statement_category' => 'other_income'],
        ];
        return array_merge($base, $retail);
    }

    private function getServiceAccounts(): array
    {
        $base = $this->getDefaultAccounts();
        $service = [
            ['code' => '1210', 'name' => 'Unbilled Receivables', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1200', 'category' => 'Receivables', 'statement_category' => 'receivables'],
            ['code' => '1410', 'name' => 'Contract Assets', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1400', 'category' => 'Prepaid', 'statement_category' => 'prepayments'],
            ['code' => '2210', 'name' => 'Deferred Revenue', 'type' => AccountType::LIABILITY, 'level' => 3, 'parent_code' => '2200', 'category' => 'Accrued', 'statement_category' => 'accruals'],
            ['code' => '4210', 'name' => 'Consulting Revenue', 'type' => AccountType::INCOME, 'level' => 3, 'parent_code' => '4200', 'category' => 'Services', 'statement_category' => 'operating_revenue'],
            ['code' => '4220', 'name' => 'Managed Services Revenue', 'type' => AccountType::INCOME, 'level' => 3, 'parent_code' => '4200', 'category' => 'Services', 'statement_category' => 'operating_revenue'],
            ['code' => '4230', 'name' => 'Retainer Revenue', 'type' => AccountType::INCOME, 'level' => 3, 'parent_code' => '4200', 'category' => 'Services', 'statement_category' => 'operating_revenue'],
            ['code' => '5330', 'name' => 'Contract Labor', 'type' => AccountType::EXPENSE, 'level' => 3, 'parent_code' => '5200', 'category' => 'Payroll', 'statement_category' => 'operating_expense'],
            ['code' => '5340', 'name' => 'Software & Subscriptions', 'type' => AccountType::EXPENSE, 'level' => 3, 'parent_code' => '5200', 'category' => 'Other', 'statement_category' => 'operating_expense'],
        ];
        return array_merge($base, $service);
    }

    private function getManufacturingAccounts(): array
    {
        $base = $this->getDefaultAccounts();
        $manufacturing = [
            ['code' => '1310', 'name' => 'Raw Materials', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1300', 'category' => 'Inventory', 'statement_category' => 'inventory'],
            ['code' => '1320', 'name' => 'Work in Progress', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1300', 'category' => 'Inventory', 'statement_category' => 'inventory'],
            ['code' => '1330', 'name' => 'Finished Goods', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1300', 'category' => 'Inventory', 'statement_category' => 'inventory'],
            ['code' => '1340', 'name' => 'Production Supplies', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1300', 'category' => 'Inventory', 'statement_category' => 'inventory'],
            ['code' => '1700', 'name' => 'Plant & Machinery', 'type' => AccountType::ASSET, 'level' => 2, 'parent_code' => '1500', 'statement_category' => 'fixed_asset'],
            ['code' => '1710', 'name' => 'Accum. Depreciation - Plant', 'type' => AccountType::ASSET, 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '1700', 'statement_category' => 'fixed_asset'],
            ['code' => '5130', 'name' => 'Raw Material Usage', 'type' => AccountType::EXPENSE, 'level' => 3, 'parent_code' => '5100', 'category' => 'COGS', 'statement_category' => 'cost_of_sales'],
            ['code' => '5140', 'name' => 'Direct Labor', 'type' => AccountType::EXPENSE, 'level' => 3, 'parent_code' => '5100', 'category' => 'COGS', 'statement_category' => 'cost_of_sales'],
            ['code' => '5150', 'name' => 'Manufacturing Overhead', 'type' => AccountType::EXPENSE, 'level' => 3, 'parent_code' => '5100', 'category' => 'COGS', 'statement_category' => 'cost_of_sales'],
            ['code' => '5160', 'name' => 'Freight & Shipping', 'type' => AccountType::EXPENSE, 'level' => 3, 'parent_code' => '5100', 'category' => 'COGS', 'statement_category' => 'cost_of_sales'],
            ['code' => '5350', 'name' => 'Equipment Maintenance', 'type' => AccountType::EXPENSE, 'level' => 3, 'parent_code' => '5200', 'category' => 'Other', 'statement_category' => 'operating_expense'],
            ['code' => '5360', 'name' => 'Quality Control', 'type' => AccountType::EXPENSE, 'level' => 3, 'parent_code' => '5200', 'category' => 'Other', 'statement_category' => 'operating_expense'],
        ];
        return array_merge($base, $manufacturing);
    }

    private function getNgoAccounts(): array
    {
        $base = $this->getDefaultAccounts();
        $ngo = [
            ['code' => '1220', 'name' => 'Grant Receivables', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1200', 'category' => 'Receivables', 'statement_category' => 'receivables'],
            ['code' => '1420', 'name' => 'Grant Prepayments', 'type' => AccountType::ASSET, 'level' => 3, 'parent_code' => '1400', 'category' => 'Prepaid', 'statement_category' => 'prepayments'],
            ['code' => '2220', 'name' => 'Restricted Funds', 'type' => AccountType::LIABILITY, 'level' => 3, 'parent_code' => '2200', 'category' => 'Accrued', 'statement_category' => 'accruals'],
            ['code' => '2310', 'name' => 'Donor Restricted Grants', 'type' => AccountType::LIABILITY, 'level' => 3, 'parent_code' => '2300', 'category' => 'Loans', 'statement_category' => 'borrowings'],
            ['code' => '3400', 'name' => 'Restricted Net Assets', 'type' => AccountType::EQUITY, 'level' => 2, 'category' => 'Capital', 'statement_category' => 'equity'],
            ['code' => '3410', 'name' => 'Unrestricted Net Assets', 'type' => AccountType::EQUITY, 'level' => 2, 'category' => 'Earnings', 'statement_category' => 'equity'],
            ['code' => '3420', 'name' => 'Temporarily Restricted Net Assets', 'type' => AccountType::EQUITY, 'level' => 3, 'parent_code' => '3400', 'statement_category' => 'equity'],
            ['code' => '4240', 'name' => 'Grant Revenue', 'type' => AccountType::INCOME, 'level' => 3, 'parent_code' => '4200', 'category' => 'Services', 'statement_category' => 'operating_revenue'],
            ['code' => '4250', 'name' => 'Donation Revenue', 'type' => AccountType::INCOME, 'level' => 3, 'parent_code' => '4200', 'category' => 'Services', 'statement_category' => 'operating_revenue'],
            ['code' => '4260', 'name' => 'Membership Revenue', 'type' => AccountType::INCOME, 'level' => 3, 'parent_code' => '4200', 'category' => 'Services', 'statement_category' => 'operating_revenue'],
            ['code' => '5400', 'name' => 'Program Expenses', 'type' => AccountType::EXPENSE, 'level' => 1, 'statement_category' => 'program_expense'],
            ['code' => '5410', 'name' => 'Program - Education', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5400', 'category' => 'Program', 'statement_category' => 'program_expense'],
            ['code' => '5420', 'name' => 'Program - Health', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5400', 'category' => 'Program', 'statement_category' => 'program_expense'],
            ['code' => '5430', 'name' => 'Program - Community', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5400', 'category' => 'Program', 'statement_category' => 'program_expense'],
            ['code' => '5500', 'name' => 'Administrative Expenses', 'type' => AccountType::EXPENSE, 'level' => 1, 'statement_category' => 'admin_expense'],
            ['code' => '5510', 'name' => 'Admin - Salaries', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5500', 'category' => 'Payroll', 'statement_category' => 'admin_expense'],
            ['code' => '5520', 'name' => 'Admin - Office Rent', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5500', 'category' => 'Rent', 'statement_category' => 'admin_expense'],
            ['code' => '5530', 'name' => 'Admin - Utilities', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5500', 'category' => 'Utilities', 'statement_category' => 'admin_expense'],
            ['code' => '5600', 'name' => 'Fundraising Expenses', 'type' => AccountType::EXPENSE, 'level' => 1, 'statement_category' => 'fundraising_expense'],
            ['code' => '5610', 'name' => 'Fundraising Events', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5600', 'category' => 'Marketing', 'statement_category' => 'fundraising_expense'],
            ['code' => '5620', 'name' => 'Donor Acquisition', 'type' => AccountType::EXPENSE, 'level' => 2, 'parent_code' => '5600', 'category' => 'Marketing', 'statement_category' => 'fundraising_expense'],
        ];
        return array_merge($base, $ngo);
    }
}
