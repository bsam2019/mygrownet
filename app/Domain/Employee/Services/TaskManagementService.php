<?php

declare(strict_types=1);

namespace App\Domain\Employee\Services;

use App\Domain\Employee\Entities\Task;
use App\Domain\Employee\ValueObjects\TaskId;
use App\Domain\Employee\ValueObjects\TaskPriority;
use App\Domain\Employee\ValueObjects\TaskStatus;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Repositories\TaskRepositoryInterface;
use App\Domain\Employee\Exceptions\TaskException;
use App\Models\Employee;
use App\Models\EmployeeNotification;
use DateTimeImmutable;
use Illuminate\Support\Collection;

class TaskManagementService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function createTask(
        string $title,
        EmployeeId $assignedTo,
        string $priority,
        ?string $description = null,
        ?EmployeeId $assignedBy = null,
        ?int $departmentId = null,
        ?DateTimeImmutable $dueDate = null,
        ?float $estimatedHours = null,
        array $tags = []
    ): Task {
        $task = Task::create(
            $title,
            $assignedTo,
            TaskPriority::fromString($priority),
            $description,
            $assignedBy,
            $departmentId,
            $dueDate,
            $estimatedHours,
            $tags
        );

        $this->taskRepository->save($task);

        // Send notification to the assigned employee
        $this->notifyTaskAssignment($task, $assignedTo, $assignedBy);

        return $task;
    }

    /**
     * Send notification when a task is assigned to an employee
     */
    private function notifyTaskAssignment(Task $task, EmployeeId $assignedTo, ?EmployeeId $assignedBy): void
    {
        $employee = Employee::find($assignedTo->toInt());
        if (!$employee) {
            return;
        }

        $assignerName = 'System';
        if ($assignedBy) {
            $assigner = Employee::find($assignedBy->toInt());
            $assignerName = $assigner ? $assigner->full_name : 'A manager';
        }

        $dueDateText = $task->getDueDate() 
            ? ' Due: ' . $task->getDueDate()->format('M j, Y')
            : '';

        EmployeeNotification::notify(
            $employee,
            'task_assigned',
            'New Task Assigned',
            "{$assignerName} assigned you a new task: \"{$task->getTitle()}\".{$dueDateText}",
            [
                'task_title' => $task->getTitle(),
                'priority' => $task->getPriority()->getValue(),
                'assigned_by' => $assignedBy?->toInt(),
            ],
            route('employee.portal.tasks.index')
        );
    }

    public function getTasksForEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        return $this->taskRepository->findByEmployee($employeeId, $filters);
    }

    public function getTaskById(TaskId $taskId, EmployeeId $employeeId): Task
    {
        $task = $this->taskRepository->findById($taskId);

        if (!$task) {
            throw TaskException::taskNotFound($taskId->toString());
        }

        if (!$task->getAssignedTo()->equals($employeeId)) {
            throw TaskException::unauthorizedAccess($taskId->toString(), $employeeId->toString());
        }

        return $task;
    }

    public function updateTaskStatus(TaskId $taskId, EmployeeId $employeeId, string $newStatus): Task
    {
        $task = $this->getTaskById($taskId, $employeeId);

        match ($newStatus) {
            'in_progress' => $task->start(),
            'review' => $task->submitForReview(),
            'completed' => $task->complete(),
            'cancelled' => $task->cancel(),
            'todo' => $task->reopen(),
            default => throw new \InvalidArgumentException("Invalid status: {$newStatus}")
        };

        $this->taskRepository->save($task);

        return $task;
    }

    public function getTasksGroupedByStatus(EmployeeId $employeeId): array
    {
        return $this->taskRepository->getTasksByStatusGrouped($employeeId);
    }

    public function getStatusCounts(EmployeeId $employeeId): array
    {
        return $this->taskRepository->getStatusCounts($employeeId);
    }

    public function getOverdueTasks(EmployeeId $employeeId): Collection
    {
        return $this->taskRepository->findOverdue($employeeId);
    }

    public function getUpcomingTasks(EmployeeId $employeeId, int $days = 7): Collection
    {
        return $this->taskRepository->findUpcoming($employeeId, $days);
    }

    public function updateTaskDetails(
        TaskId $taskId,
        EmployeeId $employeeId,
        string $title,
        ?string $description = null,
        ?DateTimeImmutable $dueDate = null,
        ?float $estimatedHours = null,
        array $tags = []
    ): Task {
        $task = $this->getTaskById($taskId, $employeeId);
        
        $task->updateDetails($title, $description, $dueDate, $estimatedHours, $tags);
        $this->taskRepository->save($task);

        return $task;
    }

    public function changePriority(TaskId $taskId, EmployeeId $employeeId, string $priority): Task
    {
        $task = $this->getTaskById($taskId, $employeeId);
        
        $task->changePriority(TaskPriority::fromString($priority));
        $this->taskRepository->save($task);

        return $task;
    }

    public function addComment(TaskId $taskId, EmployeeId $employeeId, string $comment): Task
    {
        $task = $this->getTaskById($taskId, $employeeId);
        
        $task->addNotes($comment);
        $this->taskRepository->save($task);

        return $task;
    }

    public function getCompletionStats(EmployeeId $employeeId, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        return $this->taskRepository->getCompletionStats($employeeId, $from, $to);
    }

    public function getProductivityMetrics(EmployeeId $employeeId): array
    {
        $stats = $this->getCompletionStats($employeeId);
        $statusCounts = $this->getStatusCounts($employeeId);
        $overdue = $this->getOverdueTasks($employeeId);

        return [
            'total_tasks' => $statusCounts['total'] ?? 0,
            'completed_tasks' => $statusCounts['completed'] ?? 0,
            'in_progress_tasks' => $statusCounts['in_progress'] ?? 0,
            'overdue_tasks' => $overdue->count(),
            'completion_rate' => $stats['completion_rate'] ?? 0,
            'average_completion_time' => $stats['average_completion_time'] ?? 0,
            'on_time_rate' => $stats['on_time_rate'] ?? 0,
        ];
    }

    /**
     * Reassign a task to a different employee with notification
     */
    public function reassignTask(TaskId $taskId, EmployeeId $newAssignee, ?EmployeeId $reassignedBy = null): void
    {
        $task = $this->taskRepository->findById($taskId);
        
        if (!$task) {
            throw TaskException::taskNotFound($taskId->toString());
        }

        $oldAssignee = $task->getAssignedTo();
        
        // Update the task assignment in the database directly
        \App\Models\EmployeeTask::where('id', $taskId->toInt())
            ->update(['assigned_to' => $newAssignee->toInt()]);

        // Notify the new assignee
        $this->notifyTaskAssignment($task, $newAssignee, $reassignedBy);

        // Notify the old assignee that the task was reassigned
        if (!$oldAssignee->equals($newAssignee)) {
            $oldEmployee = Employee::find($oldAssignee->toInt());
            if ($oldEmployee) {
                $reassignerName = 'System';
                if ($reassignedBy) {
                    $reassigner = Employee::find($reassignedBy->toInt());
                    $reassignerName = $reassigner ? $reassigner->full_name : 'A manager';
                }

                EmployeeNotification::notify(
                    $oldEmployee,
                    'task_reassigned',
                    'Task Reassigned',
                    "Your task \"{$task->getTitle()}\" has been reassigned by {$reassignerName}.",
                    [
                        'task_title' => $task->getTitle(),
                        'reassigned_by' => $reassignedBy?->toInt(),
                    ],
                    null
                );
            }
        }
    }
}
