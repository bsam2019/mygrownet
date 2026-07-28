<?php

namespace Tests\Feature\GrowFinance\AutoJournaling;

use App\Domain\GrowFinance\Events\AccountBalanceChanged;
use App\Domain\GrowFinance\Events\JournalPosted;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\Services\AccountingService;
use DateTimeImmutable;
use Tests\Feature\GrowFinance\GrowFinanceTestCase;

class BmsInvoiceIntegrationTest extends GrowFinanceTestCase
{
    private AccountingService $accountingService;
    private JournalEntryRepositoryInterface $journalEntryRepo;
    private JournalLineRepositoryInterface $journalLineRepo;
    private AccountRepositoryInterface $accountRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountingService = app(AccountingService::class);
        $this->journalEntryRepo = app(JournalEntryRepositoryInterface::class);
        $this->journalLineRepo = app(JournalLineRepositoryInterface::class);
        $this->accountRepo = app(AccountRepositoryInterface::class);
    }

    public function test_invoice_created_creates_ar_revenue_journal(): void
    {
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $this->assertNotNull($arAccount);
        $this->assertNotNull($revenueAccount);

        $result = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'BMS Invoice #INV-001 sync',
            lines: [
                ['account_id' => $arAccount->id, 'debit_amount' => 2500.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 2500.00],
            ],
            reference: 'INV-001',
            date: new DateTimeImmutable('now'),
        );

        $this->assertArrayHasKey('id', $result);

        $entry = $this->journalEntryRepo->findById($result['id']);
        $this->assertNotNull($entry);
        $this->assertEquals($this->businessId, $entry->businessId);

        $lines = $this->journalLineRepo->findByJournalEntry($entry->id);
        $this->assertCount(2, $lines);

        $totalDebit = array_sum(array_map(fn($l) => $l->debitAmount, $lines));
        $totalCredit = array_sum(array_map(fn($l) => $l->creditAmount, $lines));
        $this->assertEqualsWithDelta($totalDebit, $totalCredit, 0.01, 'Journal entry must be balanced');
        $this->assertEquals(2500.00, $totalDebit);
        $this->assertEquals(2500.00, $totalCredit);
    }

    public function test_payment_recorded_creates_cash_ar_journal(): void
    {
        $cashAccount = $this->accountRepo->findByCode($this->businessId, '1110');
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');

        $this->assertNotNull($cashAccount);
        $this->assertNotNull($arAccount);

        $result = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'BMS Payment #PAY-001 sync',
            lines: [
                ['account_id' => $cashAccount->id, 'debit_amount' => 2500.00, 'credit_amount' => 0],
                ['account_id' => $arAccount->id, 'debit_amount' => 0, 'credit_amount' => 2500.00],
            ],
            reference: 'PAY-001',
            date: new DateTimeImmutable('now'),
        );

        $this->assertArrayHasKey('id', $result);

        $entry = $this->journalEntryRepo->findById($result['id']);
        $this->assertNotNull($entry);
        $this->assertEquals($this->businessId, $entry->businessId);
    }
}
