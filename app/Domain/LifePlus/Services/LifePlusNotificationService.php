<?php

namespace App\Domain\LifePlus\Services;

use App\Domain\Notification\Core\Services\NotificationDataService;
use App\Models\User;
use Illuminate\Support\Str;

class LifePlusNotificationService
{
    private const MODULE = 'lifeplus';

    public function __construct(
        private NotificationDataService $notifications,
    ) {}

    public function create(
        int $userId,
        string $title,
        string $message,
        string $category = 'info',
        ?string $actionUrl = null,
        ?string $actionText = null,
        array $data = []
    ) {
        return $this->notifications->create(
            userId: $userId,
            type: 'App\Notifications\LifePlusNotification',
            title: $title,
            message: $message,
            module: self::MODULE,
            category: $category,
            actionUrl: $actionUrl,
            actionText: $actionText,
            data: $data,
        );
    }

    public function createWelcomeNotifications(int $userId): void
    {
        $hasWelcome = $this->notifications->existsForUser($userId, self::MODULE, 'welcome');

        if ($hasWelcome) {
            return;
        }

        $this->create(
            userId: $userId,
            title: 'Welcome to LifePlus!',
            message: 'Your LifePlus membership is now active. Explore health and wellness benefits.',
            category: 'welcome',
            actionUrl: '/lifeplus/dashboard',
            actionText: 'Go to LifePlus',
        );
    }

    public function createPointsAwardedNotification(int $userId, string $category, int $points, string $description): void
    {
        $this->create(
            userId: $userId,
            title: 'Points Awarded!',
            message: "You've earned {$points} LifePlus points: {$description}",
            category: 'points',
            actionUrl: '/lifeplus/points',
            actionText: 'View Points',
        );
    }

    public function createBenefitNotification(int $userId, string $benefitName, string $description): void
    {
        $this->create(
            userId: $userId,
            title: "New Benefit: {$benefitName}",
            message: $description,
            category: 'benefits',
            actionUrl: '/lifeplus/benefits',
            actionText: 'View Benefits',
        );
    }

    public function createTierUpgradeNotification(int $userId, string $tierName): void
    {
        $this->create(
            userId: $userId,
            title: 'Tier Upgrade!',
            message: "Congratulations! You've been upgraded to {$tierName} tier.",
            category: 'tier',
            actionUrl: '/lifeplus/tiers',
            actionText: 'View Tier Benefits',
        );
    }

    public function createExpirationNotification(int $userId, string $itemName, string $expirationDate): void
    {
        $this->create(
            userId: $userId,
            title: 'Expiring Soon',
            message: "Your {$itemName} is expiring on {$expirationDate}. Renew to keep enjoying LifePlus benefits.",
            category: 'expiration',
            actionUrl: '/lifeplus/renew',
            actionText: 'Renew Now',
        );
    }

    public function createMilestoneNotification(int $userId, string $milestoneName, string $description): void
    {
        $this->create(
            userId: $userId,
            title: "Milestone: {$milestoneName}",
            message: $description,
            category: 'milestones',
            actionUrl: '/lifeplus/milestones',
            actionText: 'View Achievements',
        );
    }

    public function createReminderNotification(int $userId, string $title, string $message, ?string $actionUrl = null): void
    {
        $this->create(
            userId: $userId,
            title: $title,
            message: $message,
            category: 'reminders',
            actionUrl: $actionUrl,
        );
    }

    public function markAsRead(string $notificationId): void
    {
        $this->notifications->forUser(0)
            ->where('id', $notificationId)
            ->update(['read_at' => now()]);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->notifications->forUser($userId)
            ->where('module', self::MODULE)
            ->whereNull('read_at')
            ->count();
    }

    public function getRecentNotifications(int $userId, int $limit = 10)
    {
        return $this->notifications->forUser($userId)
            ->where('module', self::MODULE)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
