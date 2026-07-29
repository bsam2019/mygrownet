<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\FiscalYear;
use App\Domain\GrowFinance\Repositories\AccountingPeriodRepositoryInterface;
use App\Domain\GrowFinance\Repositories\FiscalYearRepositoryInterface;
use App\Domain\GrowFinance\Services\FiscalYearService;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FiscalYearServiceTest extends TestCase
{
    #[Test]
    public function create_saves_and_generates_periods()
    {
        $fiscalYearRepo = $this->createMock(FiscalYearRepositoryInterface::class);
        $periodRepo = $this->createMock(AccountingPeriodRepositoryInterface::class);

        $fiscalYearRepo->expects($this->once())->method('findByDate')->willReturn(null);
        $fiscalYearRepo->expects($this->once())->method('save')->willReturnCallback(fn(FiscalYear $year) => new FiscalYear(id: 1, businessId: $year->businessId, label: $year->label, startDate: $year->startDate, endDate: $year->endDate));
        $periodRepo->expects($this->exactly(12))->method('save');

        $service = new FiscalYearService($fiscalYearRepo, $periodRepo);
        $year = $service->create(5, 'FY 2026', '2026-01-01', '2026-12-31');

        $this->assertSame('FY 2026', $year->label);
    }

    #[Test]
    public function create_throws_when_overlap()
    {
        $fiscalYearRepo = $this->createMock(FiscalYearRepositoryInterface::class);
        $periodRepo = $this->createMock(AccountingPeriodRepositoryInterface::class);

        $existing = new FiscalYear(id: 1, businessId: 5, label: 'Old', startDate: new DateTimeImmutable('2026-01-01'), endDate: new DateTimeImmutable('2026-12-31'));
        $fiscalYearRepo->expects($this->once())->method('findByDate')->willReturn($existing);

        $this->expectException(\RuntimeException::class);
        $service = new FiscalYearService($fiscalYearRepo, $periodRepo);
        $service->create(5, 'FY 2027', '2026-06-01', '2027-05-31');
    }

    #[Test]
    public function create_without_generating_periods()
    {
        $fiscalYearRepo = $this->createMock(FiscalYearRepositoryInterface::class);
        $periodRepo = $this->createMock(AccountingPeriodRepositoryInterface::class);

        $fiscalYearRepo->expects($this->once())->method('findByDate')->willReturn(null);
        $fiscalYearRepo->expects($this->once())->method('save')->willReturnCallback(fn(FiscalYear $year) => $year);
        $periodRepo->expects($this->never())->method('save');

        $service = new FiscalYearService($fiscalYearRepo, $periodRepo);
        $year = $service->create(5, 'FY 2026', '2026-01-01', '2026-12-31', generatePeriods: false);

        $this->assertSame('FY 2026', $year->label);
    }

    #[Test]
    public function find_by_id_delegates_to_repository()
    {
        $fiscalYearRepo = $this->createMock(FiscalYearRepositoryInterface::class);
        $periodRepo = $this->createMock(AccountingPeriodRepositoryInterface::class);

        $expected = new FiscalYear(id: 1, businessId: 5, label: 'FY 2026', startDate: new DateTimeImmutable('2026-01-01'), endDate: new DateTimeImmutable('2026-12-31'));
        $fiscalYearRepo->expects($this->once())->method('findById')->with(1)->willReturn($expected);

        $service = new FiscalYearService($fiscalYearRepo, $periodRepo);
        $result = $service->findById(1);

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function find_current_for_business_delegates()
    {
        $fiscalYearRepo = $this->createMock(FiscalYearRepositoryInterface::class);
        $periodRepo = $this->createMock(AccountingPeriodRepositoryInterface::class);

        $fiscalYearRepo->expects($this->once())->method('findCurrentForBusiness')->with(5);

        $service = new FiscalYearService($fiscalYearRepo, $periodRepo);
        $service->findCurrentForBusiness(5);
    }
}
