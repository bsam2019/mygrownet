<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Services;

use App\Domain\GrowBiz\Repositories\TaskRepositoryInterface;
use App\Domain\GrowBiz\Repositories\EmployeeRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SummaryService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    /**
     * Generate daily summary for a manager
     */
    public function getDailySummary(int $managerId, ?string $date = null): array
    {
        try {
            $targetDate = $date ? new DateTimeImmutable($date) : new DateTimeImmutable('today');
            $tasks = $this->taskRepository->findByManagerId($managerId);
            
            $summary = [
                'date' => $targetDate->format('Y-m-d'),
                'date_formatted' => $targetDate->format('l, F j, Y'),
                'tasks_created' => 0,
                'tasks_completed' => 0,
                'tasks_started' => 0,
                'hours_logged' => 0.0,
                'overdue_tasks' => 0,
                'due_today' => 0,
                'high_priority_pending' => 0,
                'completed_tasks' => [],
                'started_tasks' => [],
                'overdue_list' => [],
                'due_today_list' => [],
            ];

            foreach ($tasks as $task) {
                $createdDate = $task->createdAt()->format('Y-m-d');
                $completedDate = $task->completedAt()?->format('Y-m-d');
                $startedDate = $task->startedAt()?->format('Y-m-d');
                $dueDate = $task->dueDate()?->format('Y-m-d');
                $targetDateStr = $targetDate->format('Y-m-d');

                // Tasks created on target date
                if ($createdDate === $targetDateStr) {
                    $summary['tasks_created']++;
                }

                // Tasks completed on target date
                if ($completedDate === $targetDateStr) {
                    $summary['tasks_completed']++;
                    $summary['completed_tasks'][] = [
                        'id' => $task->id(),
                        'title' => $task->title(),
                        'priority' => $task->priority()->value(),
                    ];
                }

                // Tasks started on target date
                if ($startedDate === $targetDateStr) {
                    $summary['tasks_started']++;
                    $summary['started_tasks'][] = [
                        'id' => $task->id(),
                        'title' => $task->title(),
                        'priority' => $task->priority()->value(),
                    ];
                }

                // Overdue tasks (as of target date)
                if ($dueDate && $dueDate < $targetDateStr && 
                    !in_array($task->status()->value(), ['completed', 'cancelled'])) {
                    $summary['overdue_tasks']++;
                    $summary['overdue_list'][] = [
                        'id' => $task->id(),
                        'title' => $task->title(),
                        'due_date' => $dueDate,
                        'priority' => $task->priority()->value(),
                    ];
                }

                // Due today
                if ($dueDate === $targetDateStr && 
                    !in_array($task->status()->value(), ['completed', 'cancelled'])) {
                    $summary['due_today']++;
                    $summary['due_today_list'][] = [
                        'id' => $task->id(),
                        'title' => $task->title(),
                        'status' => $task->status()->value(),
                        'priority' => $task->priority()->value(),
                    ];
                }

                // High priority pending
                if (in_array($task->priority()->value(), ['high', 'urgent']) &&
                    in_array($task->status()->value(), ['pending', 'in_progress'])) {
                    $summary['high_priority_pending']++;
                }
            }

            // Get hours logged on target date from task updates
            $summary['hours_logged'] = $this->getHoursLoggedOnDate($managerId, $targetDate);

            return $summary;
        } catch (Throwable $e) {
            Log::error('Failed to generate daily summary', [
                'manager_id' => $managerId,
                'date' => $date,
                'error' => $e->getMessage(),
            ]);
            return $this->getEmptyDailySummary($date);
        }
    }


    /**
     * Generate weekly summary for a manager
     */
    public function getWeeklySummary(int $managerId, ?string $weekStart = null): array
    {
        try {
            $startDate = $weekStart 
                ? new DateTimeImmutable($weekStart) 
                : new DateTimeImmutable('monday this week');
            $endDate = $startDate->modify('+6 days');
            
            $tasks = $this->taskRepository->findByManagerId($managerId);
            $employees = $this->employeeRepository->findActiveByManagerId($managerId);
            
            $summary = [
                'week_start' => $startDate->format('Y-m-d'),
                'week_end' => $endDate->format('Y-m-d'),
                'week_formatted' => $startDate->format('M j') . ' - ' . $endDate->format('M j, Y'),
                'tasks_created' => 0,
                'tasks_completed' => 0,
                'tasks_in_progress' => 0,
                'total_hours_logged' => 0.0,
                'completion_rate' => 0,
                'on_time_rate' => 0,
                'overdue_tasks' => 0,
                'daily_breakdown' => [],
                'top_performers' => [],
                'priority_breakdown' => [
                    'urgent' => ['created' => 0, 'completed' => 0],
                    'high' => ['created' => 0, 'completed' => 0],
                    'medium' => ['created' => 0, 'completed' => 0],
                    'low' => ['created' => 0, 'completed' => 0],
                ],
                'completed_on_time' => 0,
                'completed_late' => 0,
            ];

            // Initialize daily breakdown
            for ($i = 0; $i < 7; $i++) {
                $day = $startDate->modify("+{$i} days");
                $summary['daily_breakdown'][$day->format('Y-m-d')] = [
                    'date' => $day->format('Y-m-d'),
                    'day_name' => $day->format('D'),
                    'created' => 0,
                    'completed' => 0,
                    'hours' => 0.0,
                ];
            }

            foreach ($tasks as $task) {
                $createdDate = $task->createdAt()->format('Y-m-d');
                $completedDate = $task->completedAt()?->format('Y-m-d');
                $dueDate = $task->dueDate()?->format('Y-m-d');
                $priority = $task->priority()->value();

                // Tasks created this week
                if ($createdDate >= $startDate->format('Y-m-d') && 
                    $createdDate <= $endDate->format('Y-m-d')) {
                    $summary['tasks_created']++;
                    $summary['priority_breakdown'][$priority]['created']++;
                    
                    if (isset($summary['daily_breakdown'][$createdDate])) {
                        $summary['daily_breakdown'][$createdDate]['created']++;
                    }
                }

                // Tasks completed this week
                if ($completedDate && 
                    $completedDate >= $startDate->format('Y-m-d') && 
                    $completedDate <= $endDate->format('Y-m-d')) {
                    $summary['tasks_completed']++;
                    $summary['priority_breakdown'][$priority]['completed']++;
                    
                    if (isset($summary['daily_breakdown'][$completedDate])) {
                        $summary['daily_breakdown'][$completedDate]['completed']++;
                    }

                    // Check if completed on time
                    if ($dueDate) {
                        if ($completedDate <= $dueDate) {
                            $summary['completed_on_time']++;
                        } else {
                            $summary['completed_late']++;
                        }
                    }
                }

                // Currently in progress
                if ($task->status()->value() === 'in_progress') {
                    $summary['tasks_in_progress']++;
                }

                // Overdue as of end of week
                if ($dueDate && $dueDate <= $endDate->format('Y-m-d') &&
                    !in_array($task->status()->value(), ['completed', 'cancelled'])) {
                    $summary['overdue_tasks']++;
                }
            }

            // Calculate rates
            $totalCompleted = $summary['completed_on_time'] + $summary['completed_late'];
            if ($totalCompleted > 0) {
                $summary['on_time_rate'] = round(($summary['completed_on_time'] / $totalCompleted) * 100, 1);
            }

            if ($summary['tasks_created'] > 0) {
                $summary['completion_rate'] = round(($summary['tasks_completed'] / $summary['tasks_created']) * 100, 1);
            }

            // Get hours logged this week
            $summary['total_hours_logged'] = $this->getHoursLoggedInRange($managerId, $startDate, $endDate);

            // Calculate top performers
            $summary['top_performers'] = $this->getTopPerformersForWeek($managerId, $startDate, $endDate);

            // Convert daily breakdown to array
            $summary['daily_breakdown'] = array_values($summary['daily_breakdown']);

            return $summary;
        } catch (Throwable $e) {
            Log::error('Failed to generate weekly summary', [
                'manager_id' => $managerId,
                'week_start' => $weekStart,
                'error' => $e->getMessage(),
            ]);
            return $this->getEmptyWeeklySummary($weekStart);
        }
    }

    /**
     * Get hours logged on a specific date
     */
    private function getHoursLoggedOnDate(int $managerId, DateTimeImmutable $date): float
    {
        $tasks = $this->taskRepository->findByManagerId($managerId);
        $totalHours = 0.0;

        foreach ($tasks as $task) {
            $updates = $this->taskRepository->getTaskUpdates($task->id(), 'time_log');
            foreach ($updates as $update) {
                $updateDate = (new DateTimeImmutable($update['created_at']))->format('Y-m-d');
                if ($updateDate === $date->format('Y-m-d')) {
                    $totalHours += (float) ($update['hours_logged'] ?? 0);
                }
            }
        }

        return round($totalHours, 1);
    }

    /**
     * Get hours logged in a date range
     */
    private function getHoursLoggedInRange(int $managerId, DateTimeImmutable $start, DateTimeImmutable $end): float
    {
        $tasks = $this->taskRepository->findByManagerId($managerId);
        $totalHours = 0.0;

        foreach ($tasks as $task) {
            $updates = $this->taskRepository->getTaskUpdates($task->id(), 'time_log');
            foreach ($updates as $update) {
                $updateDate = (new DateTimeImmutable($update['created_at']))->format('Y-m-d');
                if ($updateDate >= $start->format('Y-m-d') && $updateDate <= $end->format('Y-m-d')) {
                    $totalHours += (float) ($update['hours_logged'] ?? 0);
                }
            }
        }

        return round($totalHours, 1);
    }

    /**
     * Get top performers for a week
     */
    private function getTopPerformersForWeek(int $managerId, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $employees = $this->employeeRepository->findActiveByManagerId($managerId);
        $performers = [];

        foreach ($employees as $employee) {
            $tasks = $this->taskRepository->findByEmployeeId($employee->id());
            $completedThisWeek = 0;

            foreach ($tasks as $task) {
                $completedDate = $task->completedAt()?->format('Y-m-d');
                if ($completedDate && 
                    $completedDate >= $start->format('Y-m-d') && 
                    $completedDate <= $end->format('Y-m-d')) {
                    $completedThisWeek++;
                }
            }

            if ($completedThisWeek > 0) {
                $performers[] = [
                    'id' => $employee->id(),
                    'name' => $employee->name(),
                    'position' => $employee->position(),
                    'tasks_completed' => $completedThisWeek,
                ];
            }
        }

        // Sort by tasks completed descending
        usort($performers, fn($a, $b) => $b['tasks_completed'] <=> $a['tasks_completed']);

        return array_slice($performers, 0, 5);
    }

    /**
     * Get empty daily summary structure
     */
    private function getEmptyDailySummary(?string $date = null): array
    {
        $targetDate = $date ? new DateTimeImmutable($date) : new DateTimeImmutable('today');
        return [
            'date' => $targetDate->format('Y-m-d'),
            'date_formatted' => $targetDate->format('l, F j, Y'),
            'tasks_created' => 0,
            'tasks_completed' => 0,
            'tasks_started' => 0,
            'hours_logged' => 0.0,
            'overdue_tasks' => 0,
            'due_today' => 0,
            'high_priority_pending' => 0,
            'completed_tasks' => [],
            'started_tasks' => [],
            'overdue_list' => [],
            'due_today_list' => [],
        ];
    }

    /**
     * Get empty weekly summary structure
     */
    private function getEmptyWeeklySummary(?string $weekStart = null): array
    {
        $startDate = $weekStart 
            ? new DateTimeImmutable($weekStart) 
            : new DateTimeImmutable('monday this week');
        $endDate = $startDate->modify('+6 days');

        return [
            'week_start' => $startDate->format('Y-m-d'),
            'week_end' => $endDate->format('Y-m-d'),
            'week_formatted' => $startDate->format('M j') . ' - ' . $endDate->format('M j, Y'),
            'tasks_created' => 0,
            'tasks_completed' => 0,
            'tasks_in_progress' => 0,
            'total_hours_logged' => 0.0,
            'completion_rate' => 0,
            'on_time_rate' => 0,
            'overdue_tasks' => 0,
            'daily_breakdown' => [],
            'top_performers' => [],
            'priority_breakdown' => [
                'urgent' => ['created' => 0, 'completed' => 0],
                'high' => ['created' => 0, 'completed' => 0],
                'medium' => ['created' => 0, 'completed' => 0],
                'low' => ['created' => 0, 'completed' => 0],
            ],
            'completed_on_time' => 0,
            'completed_late' => 0,
        ];
    }
}
