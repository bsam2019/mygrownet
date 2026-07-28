<?php

namespace Tests\Feature\GrowFinance\AutoJournaling;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\Services\AutoJournalingService;
use Tests\Feature\GrowFinance\GrowFinanceTestCase;

class StockFlowSaleIntegrationTest extends GrowFinanceTestCase
{
    private AutoJournalingService $autoJournaling;
    private JournalEntryRepositoryInterface $journalEntryRepo;
    private JournalLineRepositoryInterface $journalLineRepo;
    private AccountRepositoryInterface $accountRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->autoJournaling = app(AutoJournalingService::class);
        $this->journalEntryRepo = app(JournalEntryRepositoryInterface::class);
        $this->journalLineRepo = app(JournalLineRepositoryInterface::class);
        $this->accountRepo = app(AccountRepositoryInterface::class);
    }

    public function test_sale_completed_creates_revenue_and_cogs_entries(): void
    {
        $payload = [
            'business_id' => $this->businessId,
            'sale_id' => 1,
            'receipt_number' => 'REC-001',
            'total' => 1000.00,
            'payment_method' => 'cash',
            'sold_by' => $this->user->id,
            'items' => [
                ['item_id' => 1, 'quantity' => 2, 'unit_price' => 500, 'line_total' => 1000],
            ],
            'occurred_at' => now()->format('Y-m-d H:i:s'),
        ];

        $result = $this->autoJournaling->onSaleCompleted($payload);

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($this->businessId, $result['business_id']);

        $entry = $this->journalEntryRepo->findById($result['id']);
        $this->assertNotNull($entry);
        $this->assertStringContainsString('sale #REC-001', $entry->description ?? '');

        $lines = $this->journalLineRepo->findByJournalEntry($entry->id);
        $this->assertCount(2, $lines);

        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $this->assertNotNull($arAccount);
        $this->assertNotNull($revenueAccount);

        $arLine = current(array_filter($lines, fn($l) => $l->accountId === $arAccount->id));
        $revenueLine = current(array_filter($lines, fn($l) => $l->accountId === $revenueAccount->id));

        $this->assertNotFalse($arLine, 'AR line not found');
        $this->assertNotFalse($revenueLine, 'Revenue line not found');
        $this->assertEquals(1000.00, $arLine->debitAmount);
        $this->assertEquals(1000.00, $revenueLine->creditAmount);
    }

    public function test_sale_completed_with_cogs_items(): void
    {
        $payload = [
            'business_id' => $this->businessId,
            'sale_id' => 2,
            'receipt_number' => 'REC-002',
            'total' => 500.00,
            'payment_method' => 'cash',
            'sold_by' => $this->user->id,
            'items' => [
                ['item_id' => 1, 'quantity' => 2, 'unit_price' => 250, 'line_total' => 500],
            ],
            'occurred_at' => now()->format('Y-m-d H:i:s'),
        ];

        $result = $this->autoJournaling->onSaleCompleted($payload);

        $this->assertArrayHasKey('id', $result);

        $entry = $this->journalEntryRepo->findById($result['id']);
        $lines = $this->journalLineRepo->findByJournalEntry($entry->id);

        $totalDebit = array_sum(array_map(fn($l) => $l->debitAmount, $lines));
        $totalCredit = array_sum(array_map(fn($l) => $l->creditAmount, $lines));

        $this->assertEqualsWithDelta($totalDebit, $totalCredit, 0.01, 'Journal entry must be balanced');
    }
}
