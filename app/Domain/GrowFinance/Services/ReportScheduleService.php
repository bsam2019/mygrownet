<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\ReportSchedule;
use App\Domain\GrowFinance\Repositories\ReportScheduleRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;

class ReportScheduleService
{
    public function __construct(
        private ReportScheduleRepositoryInterface $scheduleRepo,
        private ReportingEngine $reportingEngine,
    ) {}

    public function create(
        int $businessId,
        string $name,
        string $reportType,
        string $frequency,
        array $recipients,
        string $format = 'pdf',
    ): ReportSchedule {
        $now = new DateTimeImmutable('now');
        $nextRun = $this->computeNextRun($now, $frequency);

        return $this->scheduleRepo->save(new ReportSchedule(
            id: null,
            businessId: $businessId,
            name: $name,
            reportType: $reportType,
            frequency: $frequency,
            recipients: $recipients,
            format: $format,
            isActive: true,
            lastRunAt: null,
            nextRunAt: $nextRun,
            createdAt: $now,
            updatedAt: $now,
        ));
    }

    public function update(int $id, array $data): ReportSchedule
    {
        $schedule = $this->scheduleRepo->findById($id);
        if (!$schedule) {
            throw new \RuntimeException('Report schedule not found');
        }

        $now = new DateTimeImmutable('now');
        $frequency = $data['frequency'] ?? $schedule->frequency;
        $nextRun = isset($data['frequency']) ? $this->computeNextRun($now, $frequency) : $schedule->nextRunAt;

        return $this->scheduleRepo->save(new ReportSchedule(
            id: $schedule->id,
            businessId: $schedule->businessId,
            name: $data['name'] ?? $schedule->name,
            reportType: $data['report_type'] ?? $schedule->reportType,
            frequency: $frequency,
            recipients: $data['recipients'] ?? $schedule->recipients,
            format: $data['format'] ?? $schedule->format,
            isActive: (bool) ($data['is_active'] ?? $schedule->isActive),
            lastRunAt: $schedule->lastRunAt,
            nextRunAt: $nextRun,
            createdAt: $schedule->createdAt,
            updatedAt: $now,
        ));
    }

    public function delete(int $id): void
    {
        $this->scheduleRepo->delete($id);
    }

    public function getSchedules(int $businessId): array
    {
        return array_map(fn(ReportSchedule $s) => $s->toArray(), $this->scheduleRepo->findByBusiness($businessId));
    }

    public function processDueSchedules(): int
    {
        $now = new DateTimeImmutable('now');
        $due = $this->scheduleRepo->findDue($now);
        $processed = 0;

        foreach ($due as $schedule) {
            try {
                $this->generateAndSend($schedule);
                $nextRun = $this->computeNextRun($now, $schedule->frequency);
                $this->scheduleRepo->save($schedule->withLastRun($now, $nextRun));
                $processed++;
            } catch (\Throwable $e) {
                Log::error('ReportScheduleService: failed to process schedule', [
                    'schedule_id' => $schedule->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $processed;
    }

    public function computeNextRun(DateTimeImmutable $from, string $frequency): DateTimeImmutable
    {
        return match ($frequency) {
            'daily' => $from->modify('+1 day')->setTime(0, 0, 0),
            'weekly' => $from->modify('+1 week')->setTime(0, 0, 0),
            'monthly' => $from->modify('first day of next month')->setTime(0, 0, 0),
            'quarterly' => $from->modify('+3 months')->modify('first day of this month')->setTime(0, 0, 0),
            'yearly' => $from->modify('+1 year')->modify('first day of this month')->setTime(0, 0, 0),
            default => $from->modify('+1 month')->setTime(0, 0, 0),
        };
    }

    private function generateAndSend(ReportSchedule $schedule): void
    {
        $businessId = $schedule->businessId;
        $reportType = $schedule->reportType;
        $now = new DateTimeImmutable('now');

        $data = match ($reportType) {
            'profit_loss' => $this->reportingEngine->getProfitAndLoss($businessId, $now->modify('-1 month'), $now),
            'balance_sheet' => $this->reportingEngine->getBalanceSheet($businessId, $now),
            'trial_balance' => $this->reportingEngine->getTrialBalanceWithDates($businessId, $now),
            'cash_flow' => $this->reportingEngine->getCashFlow($businessId, $now->modify('-1 month'), $now),
            default => throw new \RuntimeException("Unknown report type: {$reportType}"),
        };

        foreach ($schedule->recipients as $recipient) {
            $email = $recipient['email'] ?? null;
            $name = $recipient['name'] ?? '';
            if ($email) {
                Log::info('ReportScheduleService: would send email', [
                    'to' => $email,
                    'name' => $name,
                    'report_type' => $reportType,
                    'format' => $schedule->format,
                    'business_id' => $businessId,
                ]);
            }
        }
    }
}
