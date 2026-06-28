<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Services;

use App\Models\User;
use App\Notifications\GrowBiz\TaskAssignedNotification;
use App\Notifications\GrowBiz\TaskCommentNotification;
use App\Notifications\GrowBiz\TaskStatusChangedNotification;
use App\Notifications\GrowBiz\TaskDueReminderNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Centralized notification service for GrowBiz module
 * Uses Laravel's database notification channel (same as main app)
 */
class NotificationService
{
    /**
     * Notify user when assigned to a task
     */
    public function notifyTaskAssigned(
        User $assignee,
        int $taskId,
        string $taskTitle,
        string $assignerName,
        ?string $dueDate = null
    ): void {
        try {
            $assignee->notify(new TaskAssignedNotification(
                $taskId,
                $taskTitle,
                $assignerName,
                $dueDate
            ));

            Log::info('Task assigned notification sent', [
                'task_id' => $taskId,
                'assignee_id' => $assignee->id,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to send task assigned notification', [
                'task_id' => $taskId,
                'assignee_id' => $assignee->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify relevant users about a new comment
     */
    public function notifyTaskComment(
        array $users,
        int $taskId,
        string $taskTitle,
        string $commenterName,
        string $commentContent
    ): void {
        if (empty($users)) {
            return;
        }

        try {
            $preview = strlen($commentContent) > 100 
                ? substr($commentContent, 0, 100) . '...' 
                : $commentContent;

            Notification::send($users, new TaskCommentNotification(
                $taskId,
                $taskTitle,
                $commenterName,
                $preview
            ));

            Log::info('Task comment notification sent', [
                'task_id' => $taskId,
                'recipients' => count($users),
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to send task comment notification', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify assignees when task status changes
     */
    public function notifyTaskStatusChanged(
        array $users,
        int $taskId,
        string $taskTitle,
        string $oldStatus,
        string $newStatus,
        string $changedByName
    ): void {
        if (empty($users)) {
            return;
        }

        try {
            Notification::send($users, new TaskStatusChangedNotification(
                $taskId,
                $taskTitle,
                $changedByName,
                $oldStatus,
                $newStatus
            ));

            Log::info('Task status changed notification sent', [
                'task_id' => $taskId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'recipients' => count($users),
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to send task status changed notification', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send due date reminder
     */
    public function notifyTaskDueReminder(
        User $user,
        int $taskId,
        string $taskTitle,
        string $dueDate,
        bool $isOverdue = false
    ): void {
        try {
            $user->notify(new TaskDueReminderNotification(
                $taskId,
                $taskTitle,
                $dueDate,
                $isOverdue
            ));

            Log::info('Task due reminder notification sent', [
                'task_id' => $taskId,
                'user_id' => $user->id,
                'is_overdue' => $isOverdue,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to send task due reminder notification', [
                'task_id' => $taskId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Batch notify multiple users about task assignment
     */
    public function notifyMultipleTaskAssignments(
        array $assignees,
        int $taskId,
        string $taskTitle,
        string $assignerName,
        ?string $dueDate = null
    ): void {
        foreach ($assignees as $assignee) {
            if ($assignee instanceof User) {
                $this->notifyTaskAssigned($assignee, $taskId, $taskTitle, $assignerName, $dueDate);
            }
        }
    }
}
