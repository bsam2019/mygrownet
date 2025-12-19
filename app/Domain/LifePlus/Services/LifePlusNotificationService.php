<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Service for creating LifePlus notifications
 */
class LifePlusNotificationService
{
    private const MODULE = 'lifeplus';

    /**
     * Create a notification for a user
     */
    public function create(
        int $userId,
        string $title,
        string $message,
        string $category = 'info',
        ?string $actionUrl = null,
        ?string $actionText = null,
        array $data = []
    ): NotificationModel {
        return NotificationModel::create([
            'id' => Str::uuid()->toString(),
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'type' => 'App\\Notifications\\LifePlusNotification',
            'module' => self::MODULE,
            'category' => $category,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'data' => $data,
            'priority' => 'normal',
            'created_at' => now(),
        ]);
    }

    /**
     * Create welcome notifications for new LifePlus users
     */
    public function createWelcomeNotifications(int $userId): void
    {
        // Check if user already has welcome notifications
        $hasWelcome = NotificationModel::forUser($userId)
            ->where('module', self::MODULE)
            ->where('category', 'welcome')
            ->exists();

        if ($hasWelcome) {
            return;
        }

        // Create welcome notification
        $this->create(
            $userId,
            'Welcome to Life+! 🎉',
            'Your personal life companion is ready. Start by adding a task or tracking an expense.',
            'welcome',
            '/lifeplus',
            'Get Started'
        );

        // Create profile setup reminder
        $this->create(
            $userId,
            'Complete Your Profile',
            'Add your skills and bio to get personalized recommendations.',
            'reminder',
            '/lifeplus/profile',
            'Set Up Profile'
        );

        // Create feature tip
        $this->create(
            $userId,
            'Tip: Track Your Habits',
            'Build positive habits with our habit tracker. Start with just one habit!',
            'info',
            '/lifeplus/tasks/habits',
            'View Habits'
        );
    }

    /**
     * Notify when a task is completed
     */
    public function notifyTaskCompleted(int $userId, string $taskTitle): void
    {
        $this->create(
            $userId,
            'Task Completed! ✓',
            "Great job completing \"{$taskTitle}\"!",
            'success'
        );
    }

    /**
     * Notify habit reminder
     */
    public function notifyHabitReminder(int $userId, string $habitName): void
    {
        $this->create(
            $userId,
            'Habit Reminder',
            "Time for your \"{$habitName}\" habit. Keep your streak going!",
            'reminder',
            '/lifeplus/tasks/habits'
        );
    }

    /**
     * Notify budget alert
     */
    public function notifyBudgetAlert(int $userId, int $percentUsed): void
    {
        $this->create(
            $userId,
            'Budget Alert',
            "You've used {$percentUsed}% of your monthly budget.",
            'warning',
            '/lifeplus/money'
        );
    }

    /**
     * Notify Chilimba contribution recorded
     */
    public function notifyChilimbaContribution(int $userId, string $groupName, float $amount): void
    {
        $this->create(
            $userId,
            'Contribution Recorded',
            "Your K{$amount} contribution to \"{$groupName}\" was recorded.",
            'success',
            '/lifeplus/money/chilimba'
        );
    }

    /**
     * Notify community post interaction
     */
    public function notifyCommunityInteraction(int $userId, string $type, string $postTitle): void
    {
        $message = match ($type) {
            'like' => "Someone liked your post \"{$postTitle}\"",
            'comment' => "New comment on your post \"{$postTitle}\"",
            default => "New activity on \"{$postTitle}\"",
        };

        $this->create(
            $userId,
            'Community Activity',
            $message,
            'info',
            '/lifeplus/community'
        );
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount(int $userId): int
    {
        return NotificationModel::forUser($userId)
            ->where(function ($query) {
                $query->where('module', self::MODULE)
                      ->orWhere('module', 'core');
            })
            ->whereNull('read_at')
            ->notArchived()
            ->count();
    }
}
