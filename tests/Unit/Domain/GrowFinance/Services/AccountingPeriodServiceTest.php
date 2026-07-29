<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Services;

use App\Domain\Core\Services\OutboxService;
use App\Domain\GrowFinance\Entities\AccountingPeriod;
use App\Domain\GrowFinance\Entities\FiscalYear;
use App\Domain\GrowFinance\Repositories\AccountingPeriodRepositoryInterface;
use App\Domain\GrowFinance\Repositories\FiscalYearRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Services\AccountingPeriodService;
use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use App\Domain\GrowFinance\ValueObjects\PeriodStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AccountingPeriodServiceTest extends TestCase
{
    #[Test]
    public function create_fiscal_year_saves_and_generates_periods()
    {
        $fiscalYearRepo = $this->createMock(FiscalYearRepositoryInterface::class);
        $periodRepo = $this->createMock(AccountingPeriodRepositoryInterface::class);
        $journalEntryRepo = $this->createMock(JournalEntryRepositoryInterface::class);
        $outbox = $this->createMock(OutboxService::class);

        $fiscalYearRepo->expects($this->once())->method('findByDate')->willReturn(null);
        $fiscalYearRepo->expects($this->once())->method('save')->willReturnCallback(fn(FiscalYear $year) => new FiscalYear(id: 1, businessId: $year->businessId, label: $year->label, startDate: $year->startDate, endDate: $year->endDate));
        $periodRepo->expects($this->exactly(12))->method('save');

        $service = new AccountingPeriodService($fiscalYearRepo, $periodRepo, $journalEntryRepo, $outbox);
        $year = $service->createFiscalYear(5, 'FY 2026', '2026-01-01', '2026-12-31');

        $this->assertSame('FY 2026', $year->label);
    }

    #[Test]
    public function create_fiscal_year_throws_when_overlap()
    {
        $fiscalYearRepo = $this->createMock(FiscalYearRepositoryInterface::class);
        $periodRepo = $this->createMock(AccountingPeriodRepositoryInterface::class);
        $journalEntryRepo = $this->createMock(JournalEntryRepositoryInterface::class);
        $outbox = $this->createMock(OutboxService::class);

        $existing = new FiscalYear(id: 1, businessId: 5, label: 'Old', startDate: new DateTimeImmutable('2026-01-01'), endDate: new DateTimeImmutable('2026-12-31'));
        $fiscalYearRepo->expects($this->once())->method('findByDate')->willReturn($existing);

        $this->expectException(\RuntimeException::class);
        $service = new AccountingPeriodService($fiscalYearRepo, $periodRepo, $journalEntryRepo, $outbox);
        $service->createFiscalYear(5, 'FY 2026', '2026-06-01', '2027-05-31');
    }

    #[Test]
    public function validate_period_is_open_returns_period()
    {
        $periodRepo = $this->createMock(AccountingPeriodRepositoryInterface::class);
        $fiscalYearRepo = $this->createMock(FiscalYearRepositoryInterface::class);
        $journalEntryRepo = $this->createMock(JournalEntryRepositoryInterface::class);
        $outbox = $this->createMock(OutboxService::class);

        $period = new AccountingPeriod(id: 1, businessId: 5, fiscalYearId: 1, label: 'Jan', startDate: new DateTimeImmutable('2026-01-01'), endDate: new DateTimeImmutable('2026-01-31'));
        $periodRepo->expects($this->once())->method('findByDate')->willReturn($period);

        $service = new AccountingPeriodService($fiscalYearRepo, $periodRepo, $journalEntryRepo, $outbox);
        $result = $service->validatePeriodIsOpen(5, new DateTimeImmutable('2026-01-15'));

        $this->assertSame($period, $result);
    }

    #[Test]
    public function validate_period_is_open_throws_when_not_open()
    {
        $periodRepo = $this->createMock(AccountingPeriodRepositoryInterface::class);
        $fiscalYearRepo = $this->createMock(FiscalYearRepositoryInterface::class);
        $journalEntryRepo = $this->createMock(JournalEntryRepositoryInterface::class);
        $outbox = $this->createMock(OutboxService::class);

        $period = new AccountingPeriod(id: 1, businessId: 5, fiscalYearId: 1, label: 'Jan', startDate: new DateTimeImmutable('2026-01-01'), endDate: new DateTimeImmutable('2026-01-31'), status: PeriodStatus::CLOSED);
        $periodRepo->expects($this->once())->method('findByDate')->willReturn($period);

        $this->expectException(\RuntimeException::class);
        $service = new AccountingPeriodService($fiscalYearRepo, $periodRepo, $journalEntryRepo, $outbox);
        $service->validatePeriodIsOpen(5, new DateTimeImmutable('2026-01-15'));
    }

    #[Test]
    public function close_period_throws_when_unposted_entries()
    {
        $periodRepo = $this->createMock(AccountingPeriodRepositoryInterface::class);
        $fiscalYearRepo = $this->createMock(FiscalYearRepositoryInterface::class);
        $journalEntryRepo = $this->createMock(JournalEntryRepositoryInterface::class);
        $outbox = $this->createMock(OutboxService::class);

        $period = new AccountingPeriod(id: 1, businessId: 5, fiscalYearId: 1, label: 'Jan', startDate: new DateTimeImmutable('2026-01-01'), endDate: new DateTimeImmutable('2026-01-31'));
        $periodRepo->expects($this->once())->method('findById')->willReturn($period);

        $entry = new \App\Domain\GrowFinance\Entities\JournalEntry(id: 1, businessId: 5, journalNumber: null, date: new DateTimeImmutable('2026-01-15'), description: null, reference: null, status: JournalStatus::DRAFT);
        $journalEntryRepo->expects($this->once())->method('findByDateRange')->willReturn([$entry]);

        $this->expectException(\RuntimeException::class);
        $service = new AccountingPeriodService($fiscalYearRepo, $periodRepo, $journalEntryRepo, $outbox);
        $service->closePeriod(1, 42);
    }

    #[Test]
    public function reopen_period_reopens_closed_period()
    {
        $periodRepo = $this->createMock(AccountingPeriodRepositoryInterface::class);
        $fiscalYearRepo = $this->createMock(FiscalYearRepositoryInterface::class);
        $journalEntryRepo = $this->createMock(JournalEntryRepositoryInterface::class);
        $outbox = $this->createMock(OutboxService::class);

        $period = new AccountingPeriod(id: 1, businessId: 5, fiscalYearId: 1, label: 'Jan', startDate: new DateTimeImmutable('2026-01-01'), endDate: new DateTimeImmutable('2026-01-31'), status: PeriodStatus::CLOSED);
        $periodRepo->expects($this->once())->method('findById')->willReturn($period);
        $periodRepo->expects($this->once())->method('save')->willReturnCallback(fn(AccountingPeriod $p) => $p);

        $service = new AccountingPeriodService($fiscalYearRepo, $periodRepo, $journalEntryRepo, $outbox);
        $result = $service->reopenPeriod(1);

        $this->assertSame(PeriodStatus::OPEN, $result->status);
    }
}
