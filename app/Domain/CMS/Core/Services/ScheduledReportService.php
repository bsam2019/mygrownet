<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\ScheduledReportModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ScheduledReportLogModel;
use Carbon\Carbon;

class ScheduledReportService
{
    public function createScheduledReport(int $companyId, array $data, int $userId): ScheduledReportModel
    {
        $scheduledReport = ScheduledReportModel::create([
            'company_id' => $companyId,
            'name' => $data['name'],
            'report_type' => $data['report_type'],
            'frequency' => $data['frequency'],
            'day_of_week' => $data['day_of_week'] ?? null,
            'day_of_month' => $data['day_of_month'] ?? null,
            'time_of_day' => $data['time_of_day'] ?? '08:00:00',
            'recipients' => $data['recipients'],
            'format' => $data['format'] ?? 'csv',
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $userId,
        ]);

        // Calculate next run time
        $scheduledReport->next_run_at = $this->calculateNextRunTime($scheduledReport);
        $scheduledReport->save();

        return $scheduledReport;
    }

    public function updateScheduledReport(int $reportId, array $data): ScheduledReportModel
    {
        $scheduledReport = ScheduledReportModel::findOrFail($reportId);
        
        $scheduledReport->update([
            'name' => $data['name'] ?? $scheduledReport->name,
            'report_type' => $data['report_type'] ?? $scheduledReport->report_type,
            'frequency' => $data['frequency'] ?? $scheduledReport->frequency,
            'day_of_week' => $data['day_of_week'] ?? $scheduledReport->day_of_week,
            'day_of_month' => $data['day_of_month'] ?? $scheduledReport->day_of_month,
            'time_of_day' => $data['time_of_day'] ?? $scheduledReport->time_of_day,
            'recipients' => $data['recipients'] ?? $scheduledReport->recipients,
            'format' => $data['format'] ?? $scheduledReport->format,
            'is_active' => $data['is_active'] ?? $scheduledReport->is_active,
        ]);

        // Recalculate next run time
        $scheduledReport->next_run_at = $this->calculateNextRunTime($scheduledReport);
        $scheduledReport->save();

        return $scheduledReport;
    }

    public function calculateNextRunTime(ScheduledReportModel $report): Carbon
    {
        $now = now();
        $time = Carbon::parse($report->time_of_day);

        switch ($report->frequency) {
            case 'daily':
                $next = $now->copy()->setTime($time->hour, $time->minute, 0);
                if ($next->isPast()) {
                    $next->addDay();
                }
                return $next;

            case 'weekly':
                $dayOfWeek = $report->day_of_week ?? 'monday';
                $next = $now->copy()->next($dayOfWeek)->setTime($time->hour, $time->minute, 0);
                if ($next->isPast()) {
                    $next->addWeek();
                }
                return $next;

            case 'monthly':
                $dayOfMonth = $report->day_of_month ?? 1;
                $next = $now->copy()->day($dayOfMonth)->setTime($time->hour, $time->minute, 0);
                if ($next->isPast()) {
                    $next->addMonth();
                }
                return $next;

            default:
                return $now->addDay();
        }
    }

    public function logReportExecution(int $reportId, string $status, ?string $errorMessage = null, int $recipientsCount = 0): void
    {
        ScheduledReportLogModel::create([
            'scheduled_report_id' => $reportId,
            'status' => $status,
            'error_message' => $errorMessage,
            'recipients_count' => $recipientsCount,
            'sent_at' => now(),
        ]);
    }

    public function deleteScheduledReport(int $reportId): bool
    {
        $report = ScheduledReportModel::findOrFail($reportId);
        return $report->delete();
    }
}
