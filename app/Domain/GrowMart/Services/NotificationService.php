<?php

declare(strict_types=1);

namespace App\Domain\GrowMart\Services;

use App\Domain\Notification\Core\Services\NotificationDataService;
use App\Models\User;

class NotificationService
{
    private const MODULE = 'growmart';

    public function __construct(
        private NotificationDataService $notifications,
    ) {}

    public function notify(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $actionText = null,
        string $category = 'orders',
        string $priority = 'normal',
        array $data = [],
    ) {
        return $this->notifications->create(
            userId: $user->id,
            type: $type,
            title: $title,
            message: $message,
            module: self::MODULE,
            actionUrl: $actionUrl,
            actionText: $actionText,
            category: $category,
            priority: $priority,
            data: $data,
        );
    }
}
