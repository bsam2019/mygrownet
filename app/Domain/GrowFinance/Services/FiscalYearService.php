<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\AccountingPeriod;
use App\Domain\GrowFinance\Entities\FiscalYear;
use App\Domain\GrowFinance\Repositories\AccountingPeriodRepositoryInterface;
use App\Domain\GrowFinance\Repositories\FiscalYearRepositoryInterface;
use DateTimeImmutable;

class FiscalYearService
{
    public function __construct(
        private FiscalYearRepositoryInterface $fiscalYearRepo,
        private AccountingPeriodRepositoryInterface $periodRepo,
    ) {}

    public function create(int $businessId, string $label, string $startDate, string $endDate, bool $generatePeriods = true): FiscalYear
    {
        $start = new DateTimeImmutable($startDate);
        $end = new DateTimeImmutable($endDate);

        $existing = $this->fiscalYearRepo->findByDate($businessId, $start);
        if ($existing) {
            throw new \RuntimeException('A fiscal year already covers the date ' . $startDate);
        }

        $year = $this->fiscalYearRepo->save(new FiscalYear(
            id: null,
            businessId: $businessId,
            label: $label,
            startDate: $start,
            endDate: $end,
        ));

        if ($generatePeriods) {
            $this->generateMonthlyPeriods($year);
        }

        return $year;
    }

    public function generateMonthlyPeriods(FiscalYear $year): array
    {
        $periods = [];
        $current = $year->startDate;
        $monthIndex = 1;

        while ($current < $year->endDate) {
            $monthEnd = $current->modify('last day of this month')->setTime(23, 59, 59);
            if ($monthEnd > $year->endDate) {
                $monthEnd = $year->endDate;
            }

            $periods[] = $this->periodRepo->save(new AccountingPeriod(
                id: null,
                businessId: $year->businessId,
                fiscalYearId: $year->id,
                label: $current->format('F Y'),
                startDate: $current,
                endDate: $monthEnd,
            ));

            $current = $current->modify('first day of next month')->setTime(0, 0, 0);
            $monthIndex++;
        }

        return $periods;
    }

    public function findById(int $id): ?FiscalYear
    {
        return $this->fiscalYearRepo->findById($id);
    }

    public function findByBusiness(int $businessId): array
    {
        return $this->fiscalYearRepo->findByBusiness($businessId);
    }

    public function findCurrentForBusiness(int $businessId): ?FiscalYear
    {
        return $this->fiscalYearRepo->findCurrentForBusiness($businessId);
    }

    public function findByDate(int $businessId, DateTimeImmutable $date): ?FiscalYear
    {
        return $this->fiscalYearRepo->findByDate($businessId, $date);
    }
}
