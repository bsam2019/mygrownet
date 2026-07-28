<?php

namespace Tests\Feature\GrowFinance;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\Services\AccountingService;
use App\Domain\GrowFinance\Services\PostingEngine;
use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use DateTimeImmutable;

class JournalsTest extends GrowFinanceTestCase
{
    private AccountingService $accountingService;
    private JournalEntryRepositoryInterface $journalEntryRepo;
    private JournalLineRepositoryInterface $journalLineRepo;
    private AccountRepositoryInterface $accountRepo;
    private PostingEngine $postingEngine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountingService = app(AccountingService::class);
        $this->journalEntryRepo = app(JournalEntryRepositoryInterface::class);
        $this->journalLineRepo = app(JournalLineRepositoryInterface::class);
        $this->accountRepo = app(AccountRepositoryInterface::class);
        $this->postingEngine = app(PostingEngine::class);
    }

    public function test_can_create_journal_entry(): void
    {
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $this->assertNotNull($arAccount);
        $this->assertNotNull($revenueAccount);

        $result = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Test journal entry',
            lines: [
                ['account_id' => $arAccount->id, 'debit_amount' => 1000.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 1000.00],
            ],
            reference: 'TEST-001',
        );

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('journal_number', $result);
        $this->assertStringStartsWith('JE-', $result['journal_number']);
    }

    public function test_created_journal_is_draft(): void
    {
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $result = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Draft test',
            lines: [
                ['account_id' => $arAccount->id, 'debit_amount' => 500.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 500.00],
            ],
        );

        $entry = $this->journalEntryRepo->findById($result['id']);
        $this->assertNotNull($entry);
        $this->assertEquals(JournalStatus::DRAFT, $entry->status);
    }

    public function test_can_post_journal_entry(): void
    {
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $result = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Post test',
            lines: [
                ['account_id' => $arAccount->id, 'debit_amount' => 2000.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 2000.00],
            ],
        );

        $posted = $this->postingEngine->post($result['id']);
        $this->assertNotNull($posted);
        $this->assertEquals(JournalStatus::POSTED, $posted->status);
        $this->assertNotNull($posted->postedAt);
    }

    public function test_cannot_post_unbalanced_entry(): void
    {
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $result = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Unbalanced entry',
            lines: [
                ['account_id' => $arAccount->id, 'debit_amount' => 1000.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 999.00],
            ],
        );

        $this->expectException(\DomainException::class);
        $this->postingEngine->post($result['id']);
    }

    public function test_can_reverse_posted_journal(): void
    {
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $result = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Reverse test',
            lines: [
                ['account_id' => $arAccount->id, 'debit_amount' => 3000.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 3000.00],
            ],
        );

        $this->postingEngine->post($result['id']);

        $reversal = $this->accountingService->reverseJournalEntry($result['id'], 'Test reversal');
        $this->assertArrayHasKey('id', $reversal);

        $originalEntry = $this->journalEntryRepo->findById($result['id']);
        $this->assertNotNull($originalEntry);
        $this->assertEquals(JournalStatus::POSTED, $originalEntry->status);
        $this->assertNotNull($originalEntry->reversalReason);
        $this->assertEquals('Test reversal', $originalEntry->reversalReason);

        $reversalEntry = $this->journalEntryRepo->findById($reversal['id']);
        $this->assertNotNull($reversalEntry);
        $this->assertEquals(JournalStatus::POSTED, $reversalEntry->status);
        $this->assertEquals($result['id'], $reversalEntry->reversalOfId);
    }

    public function test_reversed_journal_swaps_debits_and_credits(): void
    {
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $result = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Swap test',
            lines: [
                ['account_id' => $arAccount->id, 'debit_amount' => 1500.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 1500.00],
            ],
        );

        $this->postingEngine->post($result['id']);
        $reversal = $this->accountingService->reverseJournalEntry($result['id'], 'Swap reversal');

        $reversalLines = $this->journalLineRepo->findByJournalEntry($reversal['id']);
        $this->assertCount(2, $reversalLines);

        $arLine = current(array_filter($reversalLines, fn($l) => $l->accountId === $arAccount->id));
        $revenueLine = current(array_filter($reversalLines, fn($l) => $l->accountId === $revenueAccount->id));

        $this->assertEquals(0, $arLine->debitAmount);
        $this->assertEquals(1500.00, $arLine->creditAmount);

        $this->assertEquals(1500.00, $revenueLine->debitAmount);
        $this->assertEquals(0, $revenueLine->creditAmount);
    }

    public function test_journal_balances_after_creation(): void
    {
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $entry = $this->journalEntryRepo->save(new \App\Domain\GrowFinance\Entities\JournalEntry(
            id: null,
            businessId: $this->businessId,
            journalNumber: 'JE-TEST-001',
            date: new DateTimeImmutable('now'),
            description: 'Balance test',
            reference: 'BAL-TEST',
            status: JournalStatus::DRAFT,
        ));

        $this->journalLineRepo->save(new \App\Domain\GrowFinance\Entities\JournalLine(
            id: null, journalEntryId: $entry->id,
            accountId: $arAccount->id, debitAmount: 1000.00, creditAmount: 0,
        ));
        $this->journalLineRepo->save(new \App\Domain\GrowFinance\Entities\JournalLine(
            id: null, journalEntryId: $entry->id,
            accountId: $revenueAccount->id, debitAmount: 0, creditAmount: 1000.00,
        ));

        $lines = $this->journalLineRepo->findByJournalEntry($entry->id);
        $totalDebit = array_sum(array_map(fn($l) => $l->debitAmount, $lines));
        $totalCredit = array_sum(array_map(fn($l) => $l->creditAmount, $lines));
        $this->assertEqualsWithDelta($totalDebit, $totalCredit, 0.01);
        $this->assertEquals(1000.00, $totalDebit);
        $this->assertEquals(1000.00, $totalCredit);
    }

    public function test_create_and_post_in_one_flow(): void
    {
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $result = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Full flow test',
            lines: [
                ['account_id' => $arAccount->id, 'debit_amount' => 5000.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 5000.00],
            ],
            reference: 'FLOW-001',
            date: new DateTimeImmutable('now'),
        );

        $this->accountingService->postJournalEntry($result['id']);

        $entry = $this->journalEntryRepo->findById($result['id']);
        $this->assertEquals(JournalStatus::POSTED, $entry->status);
        $this->assertNotNull($entry->postedAt);
    }
}
