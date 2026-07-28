<?php

namespace Tests\Feature\GrowFinance;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Services\AccountingService;
use App\Domain\GrowFinance\Services\GeneralLedgerEngine;
use App\Domain\GrowFinance\Services\PostingEngine;

class TrialBalanceTest extends GrowFinanceTestCase
{
    private AccountingService $accountingService;
    private AccountRepositoryInterface $accountRepo;
    private GeneralLedgerEngine $glEngine;
    private PostingEngine $postingEngine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountingService = app(AccountingService::class);
        $this->accountRepo = app(AccountRepositoryInterface::class);
        $this->glEngine = app(GeneralLedgerEngine::class);
        $this->postingEngine = app(PostingEngine::class);
    }

    public function test_trial_balance_is_balanced_with_no_entries(): void
    {
        $trialBalance = $this->glEngine->getTrialBalance($this->businessId);

        $this->assertArrayHasKey('is_balanced', $trialBalance);
        $this->assertTrue($trialBalance['is_balanced']);
        $this->assertEqualsWithDelta($trialBalance['total_debits'], $trialBalance['total_credits'], 0.01);
    }

    public function test_trial_balance_is_balanced_after_posting(): void
    {
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $entry = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'TB test entry',
            lines: [
                ['account_id' => $arAccount->id, 'debit_amount' => 2500.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 2500.00],
            ],
        );

        $this->postingEngine->post($entry['id']);

        $trialBalance = $this->glEngine->getTrialBalance($this->businessId);

        $this->assertTrue($trialBalance['is_balanced']);
        $this->assertEqualsWithDelta($trialBalance['total_debits'], $trialBalance['total_credits'], 0.01);
        $this->assertGreaterThan(0, $trialBalance['total_debits']);
    }

    public function test_trial_balance_shows_all_active_accounts(): void
    {
        $trialBalance = $this->glEngine->getTrialBalance($this->businessId);

        $this->assertArrayHasKey('balances', $trialBalance);
        $this->assertGreaterThan(0, count($trialBalance['balances']));
    }

    public function test_trial_balance_after_multiple_entries(): void
    {
        $cashAccount = $this->accountRepo->findByCode($this->businessId, '1110');
        $arAccount = $this->accountRepo->findByCode($this->businessId, '1200');
        $revenueAccount = $this->accountRepo->findByCode($this->businessId, '4100');

        $entry1 = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Sale 1',
            lines: [
                ['account_id' => $arAccount->id, 'debit_amount' => 1000.00, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => 1000.00],
            ],
        );
        $this->postingEngine->post($entry1['id']);

        $entry2 = $this->accountingService->createJournalEntry(
            businessId: $this->businessId,
            description: 'Payment received',
            lines: [
                ['account_id' => $cashAccount->id, 'debit_amount' => 500.00, 'credit_amount' => 0],
                ['account_id' => $arAccount->id, 'debit_amount' => 0, 'credit_amount' => 500.00],
            ],
        );
        $this->postingEngine->post($entry2['id']);

        $trialBalance = $this->glEngine->getTrialBalance($this->businessId);

        $this->assertTrue($trialBalance['is_balanced']);
        $this->assertEqualsWithDelta($trialBalance['total_debits'], $trialBalance['total_credits'], 0.01);
    }

    public function test_trial_balance_returns_expected_format(): void
    {
        $trialBalance = $this->glEngine->getTrialBalance($this->businessId);

        $this->assertArrayHasKey('balances', $trialBalance);
        $this->assertArrayHasKey('total_debits', $trialBalance);
        $this->assertArrayHasKey('total_credits', $trialBalance);
        $this->assertArrayHasKey('is_balanced', $trialBalance);

        foreach ($trialBalance['balances'] as $balance) {
            $this->assertArrayHasKey('account', $balance);
            $this->assertArrayHasKey('debit', $balance);
            $this->assertArrayHasKey('credit', $balance);
            $this->assertArrayHasKey('code', $balance['account']);
            $this->assertArrayHasKey('name', $balance['account']);
        }
    }
}
