<?php

namespace App\Console\Commands;

use App\Domain\CMS\Core\Services\ScheduledReportService;
use App\Http\Controllers\CMS\ReportController;
use App\Infrastructure\Persistence\Eloquent\CMS\ScheduledReportModel;
use App\Services\CMS\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendScheduledReports extends Command
{
    protected $signature = 'cms:send-scheduled-reports';
    protected $description = 'Send scheduled reports via email';

    public function __construct(
        private ScheduledReportService $scheduledReportService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Checking for scheduled reports to send...');

        $dueReports = ScheduledReportModel::dueForExecution()->get();

        if ($dueReports->isEmpty()) {
            $this->info('No reports due for execution.');
            return 0;
        }

        $this->info("Found {$dueReports->count()} report(s) to send.");

        foreach ($dueReports as $report) {
            try {
                $this->info("Processing: {$report->name} (ID: {$report->id})");
                
                $this->sendReport($report);
                
                // Update last sent and next run time
                $report->last_sent_at = now();
                $report->next_run_at = $this->scheduledReportService->calculateNextRunTime($report);
                $report->save();

                $this->scheduledReportService->logReportExecution(
                    $report->id,
                    'success',
                    null,
                    count($report->recipients)
                );

                $this->info("✓ Successfully sent: {$report->name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to send: {$report->name}");
                $this->error("Error: {$e->getMessage()}");

                $this->scheduledReportService->logReportExecution(
                    $report->id,
                    'failed',
                    $e->getMessage(),
                    0
                );
            }
        }

        $this->info('Scheduled reports processing complete.');
        return 0;
    }

    private function sendReport(ScheduledReportModel $report): void
    {
        // Generate report data
        $reportController = app(ReportController::class);
        
        // Calculate date range based on frequency
        [$startDate, $endDate] = $this->getDateRange($report->frequency);

        // Create a mock request
        $request = new \Illuminate\Http\Request([
            'report_type' => $report->report_type,
            'format' => $report->format,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Set company context (mock user)
        $cmsUser = new \stdClass();
        $cmsUser->company_id = $report->company_id;
        $request->setUserResolver(function () use ($cmsUser) {
            $user = new \stdClass();
            $user->cmsUser = $cmsUser;
            return $user;
        });

        // Generate export
        $response = $reportController->export($request);
        
        // Get the content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Save to temporary file
        $filename = "{$report->report_type}_report_{$startDate}_to_{$endDate}.{$report->format}";
        $tempPath = "temp/{$filename}";
        Storage::put($tempPath, $content);

        // Send email to all recipients
        foreach ($report->recipients as $recipient) {
            Mail::raw(
                "Please find attached the {$report->name} report for the period {$startDate} to {$endDate}.",
                function ($message) use ($recipient, $report, $tempPath, $filename) {
                    $message->to($recipient)
                        ->subject($report->name . ' - ' . now()->format('Y-m-d'))
                        ->attach(Storage::path($tempPath), ['as' => $filename]);
                }
            );
        }

        // Clean up temporary file
        Storage::delete($tempPath);
    }

    private function getDateRange(string $frequency): array
    {
        $now = now();

        switch ($frequency) {
            case 'daily':
                return [
                    $now->copy()->subDay()->toDateString(),
                    $now->copy()->subDay()->toDateString(),
                ];

            case 'weekly':
                return [
                    $now->copy()->subWeek()->startOfWeek()->toDateString(),
                    $now->copy()->subWeek()->endOfWeek()->toDateString(),
                ];

            case 'monthly':
                return [
                    $now->copy()->subMonth()->startOfMonth()->toDateString(),
                    $now->copy()->subMonth()->endOfMonth()->toDateString(),
                ];

            default:
                return [
                    $now->startOfMonth()->toDateString(),
                    $now->endOfMonth()->toDateString(),
                ];
        }
    }
}
