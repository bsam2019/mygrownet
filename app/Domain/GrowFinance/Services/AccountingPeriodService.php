<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\AccountingPeriod;
use App\Domain\GrowFinance\Entities\FiscalYear;
use App\Domain\GrowFinance\Events\PeriodClosed;
use App\Domain\GrowFinance\Repositories\AccountingPeriodRepositoryInterface;
use App\Domain\GrowFinance\Repositories\FiscalYearRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use App\Domain\Core\Services\OutboxService;
use DateTimeImmutable;

class AccountingPeriodService
{
    public function __construct(
        private FiscalYearRepositoryInterface $fiscalYearRepo,
        private AccountingPeriodRepositoryInterface $periodRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private readonly OutboxService $outbox,
    ) {}

    public function createFiscalYear(int $businessId, string $label, string $startDate, string $endDate, bool $generatePeriods = true): FiscalYear
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

    public function getCurrentPeriod(int $businessId): ?AccountingPeriod
    {
        return $this->periodRepo->findCurrent($businessId);
    }

    public function getPeriodsForFiscalYear(int $fiscalYearId): array
    {
        return $this->periodRepo->findByFiscalYear($fiscalYearId);
    }

    public function validatePeriodIsOpen(int $businessId, DateTimeImmutable $date): AccountingPeriod
    {
        $period = $this->periodRepo->findByDate($businessId, $date);
        if (!$period) {
            throw new \RuntimeException('No accounting period found for date: ' . $date->format('Y-m-d'));
        }

        if (!$period->status->isPostable()) {
            throw new \RuntimeException('Accounting period is not open: ' . $period->label);
        }

        return $period;
    }

    public function closePeriod(int $periodId, int $userId): AccountingPeriod
    {
        $period = $this->periodRepo->findById($periodId);
        if (!$period) {
            throw new \RuntimeException('Accounting period not found');
        }

        $entries = $this->journalEntryRepo->findByDateRange(
            $period->businessId,
            $period->startDate,
            $period->endDate,
        );

        $unposted = array_filter($entries, fn($e) => $e->status === JournalStatus::DRAFT);
        if (!empty($unposted)) {
            throw new \RuntimeException(
                'Cannot close period: ' . count($unposted) . ' journal entries are still unposted'
            );
        }

        $closed = $period->close($userId, new DateTimeImmutable());
        $saved = $this->periodRepo->save($closed);

        $this->outbox->insert(
            eventName: PeriodClosed::NAME,
            payload: (new PeriodClosed(
                companyId: $period->businessId,
                periodType: 'monthly',
                periodStart: $period->startDate,
                periodEnd: $period->endDate,
                closedAt: new DateTimeImmutable(),
            ))->toPayload(),
            context: [
                'business_id' => $period->businessId,
                'period_id' => $period->id,
            ],
            publisher: 'growfinance',
        );

        return $saved;
    }

    public function reopenPeriod(int $periodId): AccountingPeriod
    {
        $period = $this->periodRepo->findById($periodId);
        if (!$period) {
            throw new \RuntimeException('Accounting period not found');
        }

        $reopened = $period->reopen();
        return $this->periodRepo->save($reopened);
    }
}
