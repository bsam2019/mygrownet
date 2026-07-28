<?php

namespace Tests\Feature\GrowFinance\AutoJournaling;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\Services\AutoJournalingService;
use Tests\Feature\GrowFinance\GrowFinanceTestCase;

class PlatformPaymentsIntegrationTest extends GrowFinanceTestCase
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

    public function test_payment_settled_creates_cash_and_bank_charges_entry(): void
    {
        $result = $this->autoJournaling->onPaymentSettled(
            organizationId: $this->businessId,
            settledAmount: 950.00,
            fee: 50.00,
            currency: 'ZMW',
        );

        $this->assertArrayHasKey('id', $result);

        $entry = $this->journalEntryRepo->findById($result['id']);
        $this->assertNotNull($entry);
        $this->assertStringContainsString('Payment settlement', $entry->description ?? '');

        $lines = $this->journalLineRepo->findByJournalEntry($entry->id);

        $bankAccount = $this->accountRepo->findByCode($this->businessId, '1120');
        $bankChargesAccount = $this->accountRepo->findByCode($this->businessId, '5270');
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');

        $this->assertNotNull($bankAccount);
        $this->assertNotNull($bankChargesAccount);
        $this->assertNotNull($arAccount);

        $bankLine = current(array_filter($lines, fn($l) => $l->accountId === $bankAccount->id));
        $chargesLine = current(array_filter($lines, fn($l) => $l->accountId === $bankChargesAccount->id));
        $arLine = current(array_filter($lines, fn($l) => $l->accountId === $arAccount->id));

        $this->assertNotFalse($bankLine, 'Bank line not found');
        $this->assertNotFalse($chargesLine, 'Bank charges line not found');
        $this->assertNotFalse($arLine, 'AR line not found');

        $this->assertEquals(950.00, $bankLine->debitAmount);
        $this->assertEquals(50.00, $chargesLine->debitAmount);
        $this->assertEquals(1000.00, $arLine->creditAmount);

        $totalDebit = array_sum(array_map(fn($l) => $l->debitAmount, $lines));
        $totalCredit = array_sum(array_map(fn($l) => $l->creditAmount, $lines));
        $this->assertEqualsWithDelta($totalDebit, $totalCredit, 0.01, 'Journal must be balanced');
    }
}
