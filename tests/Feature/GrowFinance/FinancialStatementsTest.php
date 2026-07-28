<?php

namespace Tests\Feature\GrowFinance;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Services\AccountingService;
use App\Domain\GrowFinance\Services\PostingEngine;
use App\Domain\GrowFinance\Services\ReportingEngine;

class FinancialStatementsTest extends GrowFinanceTestCase
{
    private AccountingService $accountingService;
    private AccountRepositoryInterface $accountRepo;
    private ReportingEngine $reportingEngine;
    private PostingEngine $postingEngine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountingService = app(AccountingService::class);
        $this->accountRepo = app(AccountRepositoryInterface::class);
        $this->reportingEngine = app(ReportingEngine::class);
        $this->postingEngine = app(PostingEngine::class);
    }

    public function test_profit_and_loss_returns_expected_sections(): void
    {
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $entry = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'P&L test revenue',
            lines: [
                ['account_id' => $arAccount->id, 'debit_amount' => 5000.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 5000.00],
            ],
        );
        $this->postingEngine->post($entry['id']);

        $pnl = $this->reportingEngine->getProfitAndLoss($this->businessId, new \DateTimeImmutable('-1 month'), new \DateTimeImmutable('+1 month'));

        $this->assertArrayHasKey('income', $pnl);
        $this->assertArrayHasKey('expenses', $pnl);
        $this->assertArrayHasKey('net_income', $pnl);
        $this->assertGreaterThan(0, $pnl['total_income']);
        $this->assertGreaterThan(0, $pnl['net_income']);
    }

    public function test_balance_sheet_returns_expected_sections(): void
    {
        $cashAccount = $this->accountRepo->findByCode($this->businessId, '1110');
        $capitalAccount = $this->accountRepo->findByCode($this->businessId, '3000');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $entry = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'BS test entry',
            lines: [
                ['account_id' => $cashAccount->id, 'debit_amount' => 10000.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 10000.00],
            ],
        );
        $this->postingEngine->post($entry['id']);

        $bs = $this->reportingEngine->getBalanceSheet($this->businessId, new \DateTimeImmutable('now'));

        $this->assertArrayHasKey('assets', $bs);
        $this->assertArrayHasKey('liabilities', $bs);
        $this->assertArrayHasKey('equity', $bs);
        $this->assertGreaterThan(0, $bs['total_assets']);
    }

    public function test_balance_sheet_balances(): void
    {
        $cashAccount = $this->accountRepo->findByCode($this->businessId, '1110');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');
        $retainedEarnings = $this->accountRepo->findByCode($this->businessId, '3100');

        $entry = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Revenue entry',
            lines: [
                ['account_id' => $cashAccount->id, 'debit_amount' => 10000.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 10000.00],
            ],
        );
        $this->postingEngine->post($entry['id']);

        $closeEntry = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Close revenue to retained earnings',
            lines: [
                ['account_id' => $revenueAccount->id, 'debit_amount' => 10000.00, 'credit_amount' => 0],
                ['account_id' => $retainedEarnings->id, 'debit_amount' => 0, 'credit_amount' => 10000.00],
            ],
        );
        $this->postingEngine->post($closeEntry['id']);

        $bs = $this->reportingEngine->getBalanceSheet($this->businessId, new \DateTimeImmutable('now'));

        $this->assertArrayHasKey('assets', $bs);
        $this->assertArrayHasKey('liabilities', $bs);
        $this->assertArrayHasKey('equity', $bs);
        $this->assertEqualsWithDelta($bs['total_assets'], $bs['total_liabilities'] + $bs['total_equity'], 0.01);
    }

    public function test_balance_sheet_with_liability(): void
    {
        $cashAccount = $this->accountRepo->findByCode($this->businessId, '1110');
        $apAccount = $this->accountRepo->findByCode($this->businessId, '2100');

        $entry = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Liability test',
            lines: [
                ['account_id' => $cashAccount->id, 'debit_amount' => 3000.00, 'credit_amount' => 0],
                ['account_id' => $apAccount->id, 'debit_amount' => 0, 'credit_amount' => 3000.00],
            ],
        );
        $this->postingEngine->post($entry['id']);

        $bs = $this->reportingEngine->getBalanceSheet($this->businessId, new \DateTimeImmutable('now'));

        $this->assertGreaterThan(0, $bs['total_liabilities']);
        $this->assertEqualsWithDelta($bs['total_assets'], $bs['total_liabilities'] + $bs['total_equity'], 0.01);
    }

    public function test_cash_flow_report_returns_data(): void
    {
        $cashAccount = $this->accountRepo->findByCode($this->businessId, '1110');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $entry = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Cash flow test',
            lines: [
                ['account_id' => $cashAccount->id, 'debit_amount' => 6000.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 6000.00],
            ],
        );
        $this->postingEngine->post($entry['id']);

        $cashFlow = $this->reportingEngine->getCashFlow($this->businessId, new \DateTimeImmutable('-1 month'), new \DateTimeImmutable('+1 month'));

        $this->assertArrayHasKey('inflows', $cashFlow);
        $this->assertArrayHasKey('outflows', $cashFlow);
        $this->assertArrayHasKey('net_cash_flow', $cashFlow);
        $this->assertArrayHasKey('opening_balance', $cashFlow);
        $this->assertArrayHasKey('closing_balance', $cashFlow);
    }
}
