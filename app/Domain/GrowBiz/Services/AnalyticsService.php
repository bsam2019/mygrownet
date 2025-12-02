<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Services;

use App\Domain\GrowBiz\Repositories\TaskRepositoryInterface;
use App\Domain\GrowBiz\Repositories\EmployeeRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnalyticsService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    /**
     * Get comprehensive task analytics for a manager
     */
    public function getTaskAnalytics(int $managerId): array
    {
        try {
            $tasks = $this->taskRepository->findByManagerId($managerId);
            $now = new DateTimeImmutable();
            
            // Basic counts
            $total = count($tasks);
            $byStatus = ['pending' => 0, 'in_progress' => 0, 'on_hold' => 0, 'completed' => 0, 'cancelled' => 0];
            $byPriority = ['low' => 0, 'medium' => 0, 'high' => 0, 'urgent' => 0];
            
            // Time tracking
            $totalEstimatedHours = 0;
            $totalActualHours = 0;
            $completedWithTime = 0;
            
            // Due date analysis
            $overdue = 0;
            $dueThisWeek = 0;
            $dueThisMonth = 0;
            $noDueDate = 0;
            
            // Completion metrics
            $completedOnTime = 0;
            $completedLate = 0;
            
            $weekFromNow = $now->modify('+7 days');
            $monthFromNow = $now->modify('+30 days');

            foreach ($tasks as $task) {
                $status = $task->status()->value();
                $priority = $task->priority()->value();
                
                // Status counts
                if (isset($byStatus[$status])) {
                    $byStatus[$status]++;
                }
                
                // Priority counts
                if (isset($byPriority[$priority])) {
                    $byPriority[$priority]++;
                }
                
                // Time tracking
                if ($task->estimatedHours()) {
                    $totalEstimatedHours += $task->estimatedHours();
                }
                $totalActualHours += $task->actualHours();
                
                if ($status === 'completed' && $task->actualHours() > 0) {
                    $completedWithTime++;
                }
                
                // Due date analysis
                $dueDate = $task->dueDate();
                if (!$dueDate) {
                    $noDueDate++;
                } else {
                    if ($dueDate < $now && $status !== 'completed' && $status !== 'cancelled') {
                        $overdue++;
                    }
                    if ($dueDate >= $now && $dueDate <= $weekFromNow) {
                        $dueThisWeek++;
                    }
                    if ($dueDate >= $now && $dueDate <= $monthFromNow) {
                        $dueThisMonth++;
                    }
                    
                    // Completion timing
                    if ($status === 'completed') {
                        $completedAt = $task->completedAt();
                        if ($completedAt && $completedAt <= $dueDate) {
                            $completedOnTime++;
                        } elseif ($completedAt) {
                            $completedLate++;
                        }
                    }
                }
            }

            $completionRate = $total > 0 ? round(($byStatus['completed'] / $total) * 100, 1) : 0;
            $onTimeRate = ($completedOnTime + $completedLate) > 0 
                ? round(($completedOnTime / ($completedOnTime + $completedLate)) * 100, 1) 
                : 0;
            $timeEfficiency = $totalEstimatedHours > 0 
                ? round(($totalActualHours / $totalEstimatedHours) * 100, 1) 
                : 0;

            return [
                'summary' => [
                    'total' => $total,
                    'completion_rate' => $completionRate,
                    'on_time_rate' => $onTimeRate,
                    'time_efficiency' => $timeEfficiency,
                    'overdue' => $overdue,
                ],
                'by_status' => $byStatus,
                'by_priority' => $byPriority,
                'time_tracking' => [
                    'total_estimated' => round($totalEstimatedHours, 1),
                    'total_actual' => round($totalActualHours, 1),
                    'tasks_with_time' => $completedWithTime,
                ],
                'due_dates' => [
                    'overdue' => $overdue,
                    'due_this_week' => $dueThisWeek,
                    'due_this_month' => $dueThisMonth,
                    'no_due_date' => $noDueDate,
                ],
                'completion' => [
                    'on_time' => $completedOnTime,
                    'late' => $completedLate,
                    'total_completed' => $byStatus['completed'],
                ],
            ];
        } catch (Throwable $e) {
            Log::error('Failed to get task analytics', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            return $this->getEmptyAnalytics();
        }
    }


    /**
     * Get employee performance analytics
     */
    public function getEmployeePerformance(int $managerId): array
    {
        try {
            $employees = $this->employeeRepository->findActiveByManagerId($managerId);
            $performance = [];

            foreach ($employees as $employee) {
                $stats = $this->taskRepository->getTaskStatsByEmployee($employee->id());
                $tasks = $this->taskRepository->findByEmployeeId($employee->id());
                
                // Calculate additional metrics
                $totalHours = 0;
                $completedOnTime = 0;
                $completedLate = 0;
                
                foreach ($tasks as $task) {
                    $totalHours += $task->actualHours();
                    
                    if ($task->status()->value() === 'completed' && $task->dueDate()) {
                        $completedAt = $task->completedAt();
                        if ($completedAt && $completedAt <= $task->dueDate()) {
                            $completedOnTime++;
                        } elseif ($completedAt) {
                            $completedLate++;
                        }
                    }
                }

                $performance[] = [
                    'id' => $employee->id(),
                    'name' => $employee->name(),
                    'position' => $employee->position(),
                    'department' => $employee->department(),
                    'tasks' => $stats,
                    'hours_logged' => round($totalHours, 1),
                    'on_time_completions' => $completedOnTime,
                    'late_completions' => $completedLate,
                    'on_time_rate' => ($completedOnTime + $completedLate) > 0 
                        ? round(($completedOnTime / ($completedOnTime + $completedLate)) * 100, 1) 
                        : 0,
                ];
            }

            // Sort by completion rate descending
            usort($performance, fn($a, $b) => $b['tasks']['completion_rate'] <=> $a['tasks']['completion_rate']);

            return $performance;
        } catch (Throwable $e) {
            Log::error('Failed to get employee performance', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get productivity trends over time (last 30 days)
     */
    public function getProductivityTrends(int $managerId, int $days = 30): array
    {
        try {
            $tasks = $this->taskRepository->findByManagerId($managerId);
            $now = new DateTimeImmutable();
            $startDate = $now->modify("-{$days} days");
            
            // Initialize daily data
            $dailyData = [];
            for ($i = 0; $i < $days; $i++) {
                $date = $startDate->modify("+{$i} days")->format('Y-m-d');
                $dailyData[$date] = [
                    'date' => $date,
                    'created' => 0,
                    'completed' => 0,
                    'hours_logged' => 0,
                ];
            }

            foreach ($tasks as $task) {
                $createdDate = $task->createdAt()->format('Y-m-d');
                if (isset($dailyData[$createdDate])) {
                    $dailyData[$createdDate]['created']++;
                }
                
                if ($task->status()->value() === 'completed' && $task->completedAt()) {
                    $completedDate = $task->completedAt()->format('Y-m-d');
                    if (isset($dailyData[$completedDate])) {
                        $dailyData[$completedDate]['completed']++;
                    }
                }
            }

            return array_values($dailyData);
        } catch (Throwable $e) {
            Log::error('Failed to get productivity trends', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get workload distribution across employees
     */
    public function getWorkloadDistribution(int $managerId): array
    {
        try {
            $employees = $this->employeeRepository->findActiveByManagerId($managerId);
            $distribution = [];

            foreach ($employees as $employee) {
                $stats = $this->taskRepository->getTaskStatsByEmployee($employee->id());
                $activeTasks = $stats['pending'] + $stats['in_progress'];
                
                $distribution[] = [
                    'id' => $employee->id(),
                    'name' => $employee->name(),
                    'active_tasks' => $activeTasks,
                    'pending' => $stats['pending'],
                    'in_progress' => $stats['in_progress'],
                    'completed' => $stats['completed'],
                    'total' => $stats['total'],
                ];
            }

            // Sort by active tasks descending
            usort($distribution, fn($a, $b) => $b['active_tasks'] <=> $a['active_tasks']);

            return $distribution;
        } catch (Throwable $e) {
            Log::error('Failed to get workload distribution', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get task completion summary for reports
     */
    public function getCompletionSummary(int $managerId, string $period = 'week'): array
    {
        try {
            $tasks = $this->taskRepository->findByManagerId($managerId);
            $now = new DateTimeImmutable();
            
            $periodStart = match($period) {
                'day' => $now->modify('today'),
                'week' => $now->modify('monday this week'),
                'month' => $now->modify('first day of this month'),
                'quarter' => $now->modify('first day of ' . ceil((int)$now->format('n') / 3) * 3 - 2 . ' months ago'),
                default => $now->modify('monday this week'),
            };

            $completed = 0;
            $created = 0;
            $hoursLogged = 0;

            foreach ($tasks as $task) {
                if ($task->createdAt() >= $periodStart) {
                    $created++;
                }
                
                if ($task->status()->value() === 'completed' && 
                    $task->completedAt() && 
                    $task->completedAt() >= $periodStart) {
                    $completed++;
                    $hoursLogged += $task->actualHours();
                }
            }

            return [
                'period' => $period,
                'period_start' => $periodStart->format('Y-m-d'),
                'tasks_created' => $created,
                'tasks_completed' => $completed,
                'hours_logged' => round($hoursLogged, 1),
            ];
        } catch (Throwable $e) {
            Log::error('Failed to get completion summary', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            return [
                'period' => $period,
                'period_start' => '',
                'tasks_created' => 0,
                'tasks_completed' => 0,
                'hours_logged' => 0,
            ];
        }
    }

    /**
     * Get empty analytics structure for error cases
     */
    private function getEmptyAnalytics(): array
    {
        return [
            'summary' => [
                'total' => 0,
                'completion_rate' => 0,
                'on_time_rate' => 0,
                'time_efficiency' => 0,
                'overdue' => 0,
            ],
            'by_status' => ['pending' => 0, 'in_progress' => 0, 'on_hold' => 0, 'completed' => 0, 'cancelled' => 0],
            'by_priority' => ['low' => 0, 'medium' => 0, 'high' => 0, 'urgent' => 0],
            'time_tracking' => ['total_estimated' => 0, 'total_actual' => 0, 'tasks_with_time' => 0],
            'due_dates' => ['overdue' => 0, 'due_this_week' => 0, 'due_this_month' => 0, 'no_due_date' => 0],
            'completion' => ['on_time' => 0, 'late' => 0, 'total_completed' => 0],
        ];
    }
}
