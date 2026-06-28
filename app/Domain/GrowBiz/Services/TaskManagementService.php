<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Services;

use App\Domain\GrowBiz\Entities\Task;
use App\Domain\GrowBiz\Exceptions\InvalidAssignmentException;
use App\Domain\GrowBiz\Exceptions\OperationFailedException;
use App\Domain\GrowBiz\Exceptions\TaskNotFoundException;
use App\Domain\GrowBiz\Repositories\TaskRepositoryInterface;
use App\Domain\GrowBiz\Repositories\EmployeeRepositoryInterface;
use App\Domain\GrowBiz\ValueObjects\EmployeeId;
use App\Domain\GrowBiz\ValueObjects\TaskId;
use App\Domain\GrowBiz\ValueObjects\TaskPriority;
use App\Domain\GrowBiz\ValueObjects\TaskStatus;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

class TaskManagementService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private EmployeeRepositoryInterface $employeeRepository,
        private ?NotificationService $notificationService = null
    ) {}

    /**
     * @throws InvalidAssignmentException
     * @throws OperationFailedException
     */
    public function createTask(
        int $ownerId,
        string $title,
        ?string $description = null,
        string $priority = 'medium',
        ?string $dueDate = null,
        array $assignedTo = [],
        ?float $estimatedHours = null,
        array $tags = [],
        ?string $category = null
    ): Task {
        try {
            // Validate assignees if provided
            if (!empty($assignedTo)) {
                $this->validateAssignees($ownerId, $assignedTo);
            }

            $task = Task::create(
                managerId: $ownerId,
                title: $title,
                description: $description,
                dueDate: $dueDate ? new DateTimeImmutable($dueDate) : null,
                priority: TaskPriority::fromString($priority),
                category: $category
            );

            $savedTask = $this->taskRepository->save($task);

            if (!empty($assignedTo)) {
                $this->taskRepository->assignEmployees($savedTask->getId(), $assignedTo);
                
                // Send notifications to assigned employees
                $this->notifyTaskAssignments($savedTask, $ownerId, $assignedTo);
            }

            Log::info('Task created', [
                'task_id' => $savedTask->id(),
                'manager_id' => $ownerId,
                'assigned_to' => $assignedTo,
            ]);

            return $savedTask;
        } catch (InvalidAssignmentException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to create task', [
                'manager_id' => $ownerId,
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('create task', $e->getMessage());
        }
    }

    /**
     * @throws TaskNotFoundException
     * @throws InvalidAssignmentException
     * @throws OperationFailedException
     */
    public function updateTask(int $taskId, array $data): Task
    {
        try {
            $task = $this->taskRepository->findById(TaskId::fromInt($taskId));
            
            if (!$task) {
                throw new TaskNotFoundException($taskId);
            }

            // Validate assignees if being updated
            if (isset($data['assigned_to']) && !empty($data['assigned_to'])) {
                $this->validateAssignees($task->ownerId(), $data['assigned_to']);
            }

            $task->update(
                title: $data['title'] ?? $task->title(),
                description: $data['description'] ?? $task->description(),
                dueDate: isset($data['due_date']) ? new DateTimeImmutable($data['due_date']) : $task->dueDate(),
                priority: isset($data['priority']) ? TaskPriority::fromString($data['priority']) : $task->priority(),
                category: $data['category'] ?? $task->category()
            );

            if (isset($data['status'])) {
                $task->updateStatus(TaskStatus::fromString($data['status']));
            }

            $savedTask = $this->taskRepository->save($task);

            if (isset($data['assigned_to'])) {
                $this->taskRepository->syncEmployees($savedTask->getId(), $data['assigned_to']);
            }

            Log::info('Task updated', [
                'task_id' => $taskId,
            ]);

            return $savedTask;
        } catch (TaskNotFoundException | InvalidAssignmentException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to update task', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('update task', $e->getMessage());
        }
    }

    /**
     * @throws TaskNotFoundException
     * @throws OperationFailedException
     */
    public function updateTaskStatus(int $taskId, string $status, int $userId, ?string $notes = null): Task
    {
        try {
            $task = $this->taskRepository->findById(TaskId::fromInt($taskId));
            
            if (!$task) {
                throw new TaskNotFoundException($taskId);
            }

            $oldStatus = $task->status()->value();
            $task->updateStatus(TaskStatus::fromString($status));
            
            $savedTask = $this->taskRepository->save($task);

            // Log the status change
            $this->taskRepository->logTaskUpdate(
                taskId: $taskId,
                userId: $userId,
                updateType: 'status_change',
                oldStatus: $oldStatus,
                newStatus: $status,
                notes: $notes
            );

            Log::info('Task status updated', [
                'task_id' => $taskId,
                'old_status' => $oldStatus,
                'new_status' => $status,
            ]);

            // Notify assignees about status change
            $this->notifyStatusChange($savedTask, $oldStatus, $status, $userId);

            return $savedTask;
        } catch (TaskNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to update task status', [
                'task_id' => $taskId,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('update task status', $e->getMessage());
        }
    }

    /**
     * @throws TaskNotFoundException
     * @throws OperationFailedException
     */
    public function updateTaskProgress(int $taskId, int $percentage, int $userId, ?string $notes = null): Task
    {
        try {
            $task = $this->taskRepository->findById(TaskId::fromInt($taskId));
            
            if (!$task) {
                throw new TaskNotFoundException($taskId);
            }

            $oldProgress = $task->progressPercentage();
            $task->updateProgress($percentage);
            
            $savedTask = $this->taskRepository->save($task);

            // Log the progress update
            $this->taskRepository->logTaskUpdate(
                taskId: $taskId,
                userId: $userId,
                updateType: 'progress_update',
                oldProgress: $oldProgress,
                newProgress: $percentage,
                notes: $notes
            );

            Log::info('Task progress updated', [
                'task_id' => $taskId,
                'old_progress' => $oldProgress,
                'new_progress' => $percentage,
            ]);

            return $savedTask;
        } catch (TaskNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to update task progress', [
                'task_id' => $taskId,
                'percentage' => $percentage,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('update task progress', $e->getMessage());
        }
    }

    /**
     * @throws TaskNotFoundException
     * @throws OperationFailedException
     */
    public function logTime(int $taskId, float $hours, int $userId, ?string $notes = null): Task
    {
        try {
            $task = $this->taskRepository->findById(TaskId::fromInt($taskId));
            
            if (!$task) {
                throw new TaskNotFoundException($taskId);
            }

            $task->logTime($hours);
            
            $savedTask = $this->taskRepository->save($task);

            // Log the time entry
            $this->taskRepository->logTaskUpdate(
                taskId: $taskId,
                userId: $userId,
                updateType: 'time_log',
                hoursLogged: $hours,
                notes: $notes
            );

            Log::info('Time logged for task', [
                'task_id' => $taskId,
                'hours' => $hours,
                'total_hours' => $savedTask->actualHours(),
            ]);

            return $savedTask;
        } catch (TaskNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to log time', [
                'task_id' => $taskId,
                'hours' => $hours,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('log time', $e->getMessage());
        }
    }

    /**
     * @throws TaskNotFoundException
     * @throws OperationFailedException
     */
    public function addNote(int $taskId, int $userId, string $notes): void
    {
        try {
            $task = $this->taskRepository->findById(TaskId::fromInt($taskId));
            
            if (!$task) {
                throw new TaskNotFoundException($taskId);
            }

            $this->taskRepository->logTaskUpdate(
                taskId: $taskId,
                userId: $userId,
                updateType: 'note',
                notes: $notes
            );

            Log::info('Note added to task', [
                'task_id' => $taskId,
                'user_id' => $userId,
            ]);
        } catch (TaskNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to add note', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('add note', $e->getMessage());
        }
    }

    public function getTaskUpdates(int $taskId, ?string $updateType = null): array
    {
        return $this->taskRepository->getTaskUpdates($taskId, $updateType);
    }

    public function getTotalTimeLogged(int $taskId): float
    {
        return $this->taskRepository->getTotalTimeLogged($taskId);
    }

    /**
     * @throws TaskNotFoundException
     */
    public function getTaskById(int $taskId): Task
    {
        $task = $this->taskRepository->findById(TaskId::fromInt($taskId));
        
        if (!$task) {
            throw new TaskNotFoundException($taskId);
        }

        return $task;
    }

    public function getTasksForUser(int $userId, array $filters = []): array
    {
        try {
            return $this->taskRepository->findByOwnerWithFilters($userId, $filters);
        } catch (Throwable $e) {
            Log::error('Failed to fetch tasks', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getTaskStatistics(int $userId): array
    {
        try {
            $tasks = $this->taskRepository->findByManagerId($userId);
            
            $total = count($tasks);
            $pending = 0;
            $inProgress = 0;
            $completed = 0;
            $overdue = 0;
            $now = new DateTimeImmutable();

            foreach ($tasks as $task) {
                $status = $task->status()->value();
                
                if ($status === 'pending') $pending++;
                elseif ($status === 'in_progress') $inProgress++;
                elseif ($status === 'completed') $completed++;
                
                if ($task->dueDate() && $task->dueDate() < $now && $status !== 'completed') {
                    $overdue++;
                }
            }

            return [
                'total' => $total,
                'pending' => $pending,
                'in_progress' => $inProgress,
                'completed' => $completed,
                'overdue' => $overdue,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            ];
        } catch (Throwable $e) {
            Log::error('Failed to get task statistics', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return [
                'total' => 0,
                'pending' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'overdue' => 0,
                'completion_rate' => 0,
            ];
        }
    }

    public function getRecentTasks(int $userId, int $limit = 5): array
    {
        try {
            return $this->taskRepository->findRecentByOwner($userId, $limit);
        } catch (Throwable $e) {
            Log::error('Failed to get recent tasks', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getUpcomingDueTasks(int $userId, int $limit = 5): array
    {
        try {
            return $this->taskRepository->findUpcomingDue($userId, $limit);
        } catch (Throwable $e) {
            Log::error('Failed to get upcoming due tasks', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getOverdueTasks(int $userId): array
    {
        try {
            return $this->taskRepository->findOverdueTasks($userId);
        } catch (Throwable $e) {
            Log::error('Failed to get overdue tasks', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getAvailableAssignees(int $userId): array
    {
        try {
            return $this->employeeRepository->findActiveByManagerId($userId);
        } catch (Throwable $e) {
            Log::error('Failed to get available assignees', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get all tasks assigned to a specific employee
     */
    public function getTasksAssignedToEmployee(int $employeeId): array
    {
        try {
            return $this->taskRepository->findByAssignedEmployee($employeeId);
        } catch (Throwable $e) {
            Log::error('Failed to get tasks assigned to employee', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * @throws TaskNotFoundException
     * @throws OperationFailedException
     */
    public function addComment(int $taskId, int $userId, string $content): void
    {
        try {
            $task = $this->taskRepository->findById(TaskId::fromInt($taskId));
            
            if (!$task) {
                throw new TaskNotFoundException($taskId);
            }

            $this->taskRepository->addComment($taskId, $userId, $content);

            Log::info('Comment added to task', [
                'task_id' => $taskId,
                'user_id' => $userId,
            ]);

            // Notify assignees about new comment
            $this->notifyNewComment($task, $userId, $content);
        } catch (TaskNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to add comment', [
                'task_id' => $taskId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('add comment', $e->getMessage());
        }
    }

    public function getTaskComments(int $taskId): array
    {
        return $this->taskRepository->getComments($taskId);
    }

    /**
     * @throws TaskNotFoundException
     * @throws OperationFailedException
     */
    public function deleteComment(int $taskId, int $commentId, int $userId): void
    {
        try {
            $task = $this->taskRepository->findById(TaskId::fromInt($taskId));
            
            if (!$task) {
                throw new TaskNotFoundException($taskId);
            }

            $this->taskRepository->deleteComment($commentId, $userId);

            Log::info('Comment deleted from task', [
                'task_id' => $taskId,
                'comment_id' => $commentId,
                'user_id' => $userId,
            ]);
        } catch (TaskNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to delete comment', [
                'task_id' => $taskId,
                'comment_id' => $commentId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('delete comment', $e->getMessage());
        }
    }

    /**
     * @throws TaskNotFoundException
     * @throws OperationFailedException
     */
    public function deleteTask(int $taskId): void
    {
        try {
            $task = $this->taskRepository->findById(TaskId::fromInt($taskId));
            
            if (!$task) {
                throw new TaskNotFoundException($taskId);
            }

            $this->taskRepository->delete(TaskId::fromInt($taskId));

            Log::info('Task deleted', [
                'task_id' => $taskId,
            ]);
        } catch (TaskNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to delete task', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('delete task', $e->getMessage());
        }
    }

    public function getTasksByManager(int $managerId): array
    {
        try {
            return $this->taskRepository->findByManagerId($managerId);
        } catch (Throwable $e) {
            Log::error('Failed to get tasks by manager', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getTasksByStatus(int $managerId, string $status): array
    {
        try {
            return $this->taskRepository->findByManagerIdAndStatus(
                $managerId,
                TaskStatus::fromString($status)
            );
        } catch (Throwable $e) {
            Log::error('Failed to get tasks by status', [
                'manager_id' => $managerId,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getTasksDueToday(int $managerId): array
    {
        try {
            return $this->taskRepository->findDueToday($managerId);
        } catch (Throwable $e) {
            Log::error('Failed to get tasks due today', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Validate that all assignees are active employees belonging to the manager
     *
     * @throws InvalidAssignmentException
     */
    private function validateAssignees(int $managerId, array $employeeIds): void
    {
        $activeEmployees = $this->employeeRepository->findActiveByManagerId($managerId);
        $activeIds = array_map(fn($e) => $e->id(), $activeEmployees);
        
        $invalidIds = array_diff($employeeIds, $activeIds);
        
        if (!empty($invalidIds)) {
            throw new InvalidAssignmentException(0, $invalidIds);
        }
    }

    /**
     * Notify assignees about task status change
     */
    private function notifyStatusChange(Task $task, string $oldStatus, string $newStatus, int $changedByUserId): void
    {
        if (!$this->notificationService) {
            return;
        }

        try {
            $assignees = $this->taskRepository->getTaskAssigneeUsers($task->id());
            $changedByUser = \App\Models\User::find($changedByUserId);
            
            // Filter out the user who made the change
            $usersToNotify = array_filter($assignees, fn($user) => $user->id !== $changedByUserId);
            
            if (!empty($usersToNotify)) {
                $this->notificationService->notifyTaskStatusChanged(
                    $usersToNotify,
                    $task->id(),
                    $task->title(),
                    $oldStatus,
                    $newStatus,
                    $changedByUser?->name ?? 'Unknown'
                );
            }
        } catch (Throwable $e) {
            Log::warning('Failed to send status change notification', [
                'task_id' => $task->id(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify assignees about new comment
     */
    private function notifyNewComment(Task $task, int $commenterId, string $content): void
    {
        if (!$this->notificationService) {
            return;
        }

        try {
            $assignees = $this->taskRepository->getTaskAssigneeUsers($task->id());
            $commenter = \App\Models\User::find($commenterId);
            
            // Filter out the commenter
            $usersToNotify = array_filter($assignees, fn($user) => $user->id !== $commenterId);
            
            if (!empty($usersToNotify)) {
                $this->notificationService->notifyTaskComment(
                    $usersToNotify,
                    $task->id(),
                    $task->title(),
                    $commenter?->name ?? 'Unknown',
                    $content
                );
            }
        } catch (Throwable $e) {
            Log::warning('Failed to send comment notification', [
                'task_id' => $task->id(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify employees when assigned to a new task
     */
    private function notifyTaskAssignments(Task $task, int $assignerId, array $employeeIds): void
    {
        if (!$this->notificationService) {
            return;
        }

        try {
            $assigner = \App\Models\User::find($assignerId);
            $assignees = $this->taskRepository->getTaskAssigneeUsers($task->id());
            
            $dueDate = $task->dueDate()?->format('M j, Y');
            
            $this->notificationService->notifyMultipleTaskAssignments(
                $assignees,
                $task->id(),
                $task->title(),
                $assigner?->name ?? 'Unknown',
                $dueDate
            );
        } catch (Throwable $e) {
            Log::warning('Failed to send task assignment notifications', [
                'task_id' => $task->id(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
