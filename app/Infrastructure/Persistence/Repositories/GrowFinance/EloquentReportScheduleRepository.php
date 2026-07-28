<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\ReportSchedule;
use App\Domain\GrowFinance\Repositories\ReportScheduleRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceReportScheduleModel;
use DateTimeImmutable;

class EloquentReportScheduleRepository implements ReportScheduleRepositoryInterface
{
    public function findById(int $id): ?ReportSchedule
    {
        $model = GrowFinanceReportScheduleModel::find($id);
        return $model ? ReportSchedule::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceReportScheduleModel::forBusiness($businessId)
            ->orderBy('name')
            ->get()
            ->map(fn($m) => ReportSchedule::reconstitute($m->toArray()))
            ->all();
    }

    public function findDue(DateTimeImmutable $now): array
    {
        return GrowFinanceReportScheduleModel::due()
            ->get()
            ->map(fn($m) => ReportSchedule::reconstitute($m->toArray()))
            ->all();
    }

    public function save(ReportSchedule $schedule): ReportSchedule
    {
        $data = array_filter([
            'business_id' => $schedule->businessId,
            'name' => $schedule->name,
            'report_type' => $schedule->reportType,
            'frequency' => $schedule->frequency,
            'recipients' => $schedule->recipients,
            'format' => $schedule->format,
            'is_active' => $schedule->isActive,
            'last_run_at' => $schedule->lastRunAt?->format('Y-m-d H:i:s'),
            'next_run_at' => $schedule->nextRunAt?->format('Y-m-d H:i:s'),
        ], fn($v) => $v !== null);

        if ($schedule->id) {
            GrowFinanceReportScheduleModel::where('id', $schedule->id)->update($data);
            return $this->findById($schedule->id);
        }

        $model = GrowFinanceReportScheduleModel::create($data);
        return ReportSchedule::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        GrowFinanceReportScheduleModel::where('id', $id)->delete();
    }
}
