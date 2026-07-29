<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\Entities\JournalEntry;
use App\Domain\GrowFinance\Entities\JournalLine;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\Services\GeneralLedgerEngine;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GeneralLedgerEngineTest extends TestCase
{
    #[Test]
    public function get_account_balance_returns_correct_value()
    {
        $accountRepo = $this->createMock(AccountRepositoryInterface::class);
        $journalEntryRepo = $this->createMock(JournalEntryRepositoryInterface::class);
        $journalLineRepo = $this->createMock(JournalLineRepositoryInterface::class);

        $account = new Account(id: 100, businessId: 5, code: '1000', name: 'Cash', type: AccountType::ASSET, normalBalance: 'debit', openingBalance: 1000.0);

        $entry = new JournalEntry(id: 1, businessId: 5, journalNumber: 'JE-001', date: new DateTimeImmutable('2026-01-15'), description: 'Deposit', reference: null, status: JournalStatus::POSTED);
        $line = new JournalLine(id: 1, journalEntryId: 1, accountId: 100, debitAmount: 500.0, creditAmount: 0.0);

        $accountRepo->expects($this->any())->method('findById')->willReturn($account);
        $journalEntryRepo->expects($this->any())->method('findByDateRange')->willReturn([$entry]);
        $journalLineRepo->expects($this->any())->method('findByJournalEntry')->willReturn([$line]);

        $engine = new GeneralLedgerEngine($accountRepo, $journalEntryRepo, $journalLineRepo);
        $balance = $engine->getAccountBalance(5, 100, new DateTimeImmutable('2026-01-31'));

        $this->assertSame(1500.0, $balance);
    }

    #[Test]
    public function get_account_balance_throws_when_account_not_found()
    {
        $accountRepo = $this->createMock(AccountRepositoryInterface::class);
        $journalEntryRepo = $this->createMock(JournalEntryRepositoryInterface::class);
        $journalLineRepo = $this->createMock(JournalLineRepositoryInterface::class);

        $accountRepo->expects($this->any())->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $engine = new GeneralLedgerEngine($accountRepo, $journalEntryRepo, $journalLineRepo);
        $engine->getAccountBalance(5, 999, new DateTimeImmutable('2026-01-31'));
    }

    #[Test]
    public function get_period_balances_aggregates_correctly()
    {
        $accountRepo = $this->createMock(AccountRepositoryInterface::class);
        $journalEntryRepo = $this->createMock(JournalEntryRepositoryInterface::class);
        $journalLineRepo = $this->createMock(JournalLineRepositoryInterface::class);

        $account = new Account(id: 100, businessId: 5, code: '1000', name: 'Cash', type: AccountType::ASSET);
        $accountRepo->expects($this->any())->method('findActive')->willReturn([$account]);

        $entry = new JournalEntry(id: 1, businessId: 5, journalNumber: 'JE-001', date: new DateTimeImmutable('2026-01-15'), description: 'Entry', reference: null, status: JournalStatus::POSTED);
        $journalEntryRepo->expects($this->any())->method('findByPeriod')->willReturn([$entry]);

        $line = new JournalLine(id: 1, journalEntryId: 1, accountId: 100, debitAmount: 1000.0, creditAmount: 0.0);
        $journalLineRepo->expects($this->any())->method('findByJournalEntry')->willReturn([$line]);

        $engine = new GeneralLedgerEngine($accountRepo, $journalEntryRepo, $journalLineRepo);
        $balances = $engine->getPeriodBalances(5, 1);

        $this->assertCount(1, $balances);
        $this->assertSame(1000.0, $balances[0]['debit']);
    }

    #[Test]
    public function get_trial_balance_returns_balanced_result()
    {
        $accountRepo = $this->createMock(AccountRepositoryInterface::class);
        $journalEntryRepo = $this->createMock(JournalEntryRepositoryInterface::class);
        $journalLineRepo = $this->createMock(JournalLineRepositoryInterface::class);

        $asset = new Account(id: 100, businessId: 5, code: '1000', name: 'Cash', type: AccountType::ASSET, normalBalance: 'debit', currentBalance: 5000.0);
        $income = new Account(id: 200, businessId: 5, code: '4000', name: 'Revenue', type: AccountType::INCOME, normalBalance: 'credit', currentBalance: 5000.0);

        $accountRepo->expects($this->any())->method('findActive')->willReturn([$asset, $income]);

        $engine = new GeneralLedgerEngine($accountRepo, $journalEntryRepo, $journalLineRepo);
        $trial = $engine->getTrialBalance(5);

        $this->assertTrue($trial['is_balanced']);
        $this->assertSame(5000.0, $trial['total_debits']);
        $this->assertSame(5000.0, $trial['total_credits']);
    }

    #[Test]
    public function get_account_activity_returns_only_posted_entries()
    {
        $accountRepo = $this->createMock(AccountRepositoryInterface::class);
        $journalEntryRepo = $this->createMock(JournalEntryRepositoryInterface::class);
        $journalLineRepo = $this->createMock(JournalLineRepositoryInterface::class);

        $posted = new JournalEntry(id: 1, businessId: 5, journalNumber: 'JE-001', date: new DateTimeImmutable('2026-01-15'), description: 'Posted', reference: null, status: JournalStatus::POSTED);
        $draft = new JournalEntry(id: 2, businessId: 5, journalNumber: 'JE-002', date: new DateTimeImmutable('2026-01-16'), description: 'Draft', reference: null, status: JournalStatus::DRAFT);

        $journalEntryRepo->expects($this->any())->method('findByDateRange')->willReturn([$posted, $draft]);
        $journalLineRepo->expects($this->any())->method('findByJournalEntry')->willReturnCallback(fn($id) => match ($id) {
            1 => [new JournalLine(id: 1, journalEntryId: 1, accountId: 100, debitAmount: 500.0, creditAmount: 0.0)],
            2 => [new JournalLine(id: 2, journalEntryId: 2, accountId: 100, debitAmount: 200.0, creditAmount: 0.0)],
        });

        $engine = new GeneralLedgerEngine($accountRepo, $journalEntryRepo, $journalLineRepo);
        $activity = $engine->getAccountActivity(5, 100, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-01-31'));

        $this->assertCount(1, $activity);
        $this->assertSame(500.0, $activity[0]['debit']);
    }
}
