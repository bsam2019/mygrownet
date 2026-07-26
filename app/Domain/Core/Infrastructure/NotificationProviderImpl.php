<?php

namespace App\Domain\Core\Infrastructure;

use App\Domain\Core\Contracts\NotificationProvider;
use App\Domain\Notification\Core\Services\NotificationDataService;

class NotificationProviderImpl implements NotificationProvider
{
    public function __construct(
        private NotificationDataService $notifications,
    ) {}

    public function capability(): string
    {
        return 'notifications';
    }

    public function send(int $userId, string $type, string $title, string $message, ?string $actionUrl = null, ?string $actionText = null, string $category = 'general', string $priority = 'normal', array $data = []): array
    {
        $notification = $this->notifications->create(
            userId: $userId,
            type: $type,
            title: $title,
            message: $message,
            module: 'platform',
            actionUrl: $actionUrl,
            actionText: $actionText,
            category: $category,
            priority: $priority,
            data: $data,
        );

        return $notification->toArray();
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->notifications->forUser($userId)->whereNull('read_at')->count();
    }

    public function markAsRead(string $notificationId): void
    {
        $this->notifications->forUser(0)->where('id', $notificationId)->update(['read_at' => now()]);
    }
}
