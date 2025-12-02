<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Services;

use App\Domain\GrowBiz\Repositories\TaskRepositoryInterface;
use App\Domain\GrowBiz\Repositories\EmployeeRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExportService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private EmployeeRepositoryInterface $employeeRepository,
        private AnalyticsService $analyticsService,
        private SummaryService $summaryService
    ) {}

    /**
     * Export tasks to CSV format
     */
    public function exportTasksToCsv(int $managerId, array $filters = []): array
    {
        try {
            $tasks = $this->taskRepository->findByOwnerWithFilters($managerId, $filters);
            
            $headers = [
                'ID',
                'Title',
                'Description',
                'Status',
                'Priority',
                'Category',
                'Due Date',
                'Progress %',
                'Estimated Hours',
                'Actual Hours',
                'Started At',
                'Completed At',
                'Created At',
            ];

            $rows = [];
            foreach ($tasks as $task) {
                $rows[] = [
                    $task->id(),
                    $task->title(),
                    $task->description() ?? '',
                    ucfirst(str_replace('_', ' ', $task->status()->value())),
                    ucfirst($task->priority()->value()),
                    $task->category() ?? '',
                    $task->dueDate()?->format('Y-m-d') ?? '',
                    $task->progressPercentage(),
                    $task->estimatedHours() ?? '',
                    $task->actualHours(),
                    $task->startedAt()?->format('Y-m-d H:i') ?? '',
                    $task->completedAt()?->format('Y-m-d H:i') ?? '',
                    $task->createdAt()->format('Y-m-d H:i'),
                ];
            }

            return [
                'filename' => 'tasks_export_' . date('Y-m-d_His') . '.csv',
                'headers' => $headers,
                'rows' => $rows,
                'count' => count($rows),
            ];
        } catch (Throwable $e) {
            Log::error('Failed to export tasks', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Export employees to CSV format
     */
    public function exportEmployeesToCsv(int $managerId, array $filters = []): array
    {
        try {
            $employees = $this->employeeRepository->findByOwnerWithFilters($managerId, $filters);
            
            $headers = [
                'ID',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Position',
                'Department',
                'Status',
                'Hire Date',
                'Hourly Rate',
                'Created At',
            ];

            $rows = [];
            foreach ($employees as $employee) {
                $rows[] = [
                    $employee->id(),
                    $employee->firstName(),
                    $employee->lastName(),
                    $employee->email() ?? '',
                    $employee->phone() ?? '',
                    $employee->position() ?? '',
                    $employee->department() ?? '',
                    ucfirst(str_replace('_', ' ', $employee->status()->value())),
                    $employee->hireDate()?->format('Y-m-d') ?? '',
                    $employee->hourlyRate() ?? '',
                    $employee->getCreatedAt()->format('Y-m-d H:i'),
                ];
            }

            return [
                'filename' => 'employees_export_' . date('Y-m-d_His') . '.csv',
                'headers' => $headers,
                'rows' => $rows,
                'count' => count($rows),
            ];
        } catch (Throwable $e) {
            Log::error('Failed to export employees', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Export weekly summary report
     */
    public function exportWeeklySummary(int $managerId, ?string $weekStart = null): array
    {
        try {
            $summary = $this->summaryService->getWeeklySummary($managerId, $weekStart);
            
            $headers = ['Metric', 'Value'];
            $rows = [
                ['Report Period', $summary['week_formatted']],
                ['Tasks Created', $summary['tasks_created']],
                ['Tasks Completed', $summary['tasks_completed']],
                ['Tasks In Progress', $summary['tasks_in_progress']],
                ['Completion Rate', $summary['completion_rate'] . '%'],
                ['On-Time Rate', $summary['on_time_rate'] . '%'],
                ['Total Hours Logged', $summary['total_hours_logged']],
                ['Overdue Tasks', $summary['overdue_tasks']],
                ['Completed On Time', $summary['completed_on_time']],
                ['Completed Late', $summary['completed_late']],
                ['', ''],
                ['Priority Breakdown', ''],
                ['Urgent - Created', $summary['priority_breakdown']['urgent']['created']],
                ['Urgent - Completed', $summary['priority_breakdown']['urgent']['completed']],
                ['High - Created', $summary['priority_breakdown']['high']['created']],
                ['High - Completed', $summary['priority_breakdown']['high']['completed']],
                ['Medium - Created', $summary['priority_breakdown']['medium']['created']],
                ['Medium - Completed', $summary['priority_breakdown']['medium']['completed']],
                ['Low - Created', $summary['priority_breakdown']['low']['created']],
                ['Low - Completed', $summary['priority_breakdown']['low']['completed']],
            ];

            // Add daily breakdown
            $rows[] = ['', ''];
            $rows[] = ['Daily Breakdown', ''];
            foreach ($summary['daily_breakdown'] as $day) {
                $rows[] = [
                    $day['day_name'] . ' (' . $day['date'] . ')',
                    'Created: ' . $day['created'] . ', Completed: ' . $day['completed'],
                ];
            }

            // Add top performers
            if (!empty($summary['top_performers'])) {
                $rows[] = ['', ''];
                $rows[] = ['Top Performers', ''];
                foreach ($summary['top_performers'] as $performer) {
                    $rows[] = [
                        $performer['name'],
                        $performer['tasks_completed'] . ' tasks completed',
                    ];
                }
            }

            return [
                'filename' => 'weekly_summary_' . $summary['week_start'] . '.csv',
                'headers' => $headers,
                'rows' => $rows,
                'count' => count($rows),
            ];
        } catch (Throwable $e) {
            Log::error('Failed to export weekly summary', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Export performance report
     */
    public function exportPerformanceReport(int $managerId): array
    {
        try {
            $performance = $this->analyticsService->getEmployeePerformance($managerId);
            
            $headers = [
                'Employee',
                'Position',
                'Department',
                'Total Tasks',
                'Completed',
                'In Progress',
                'Pending',
                'Completion Rate',
                'Hours Logged',
                'On-Time Rate',
            ];

            $rows = [];
            foreach ($performance as $emp) {
                $rows[] = [
                    $emp['name'],
                    $emp['position'] ?? '',
                    $emp['department'] ?? '',
                    $emp['tasks']['total'],
                    $emp['tasks']['completed'],
                    $emp['tasks']['in_progress'],
                    $emp['tasks']['pending'],
                    $emp['tasks']['completion_rate'] . '%',
                    $emp['hours_logged'],
                    $emp['on_time_rate'] . '%',
                ];
            }

            return [
                'filename' => 'performance_report_' . date('Y-m-d_His') . '.csv',
                'headers' => $headers,
                'rows' => $rows,
                'count' => count($rows),
            ];
        } catch (Throwable $e) {
            Log::error('Failed to export performance report', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate CSV content from headers and rows
     */
    public function generateCsvContent(array $headers, array $rows): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for Excel UTF-8 compatibility
        fwrite($output, "\xEF\xBB\xBF");
        
        // Write headers
        fputcsv($output, $headers);
        
        // Write rows
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);
        
        return $content;
    }
}
