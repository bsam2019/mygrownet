<?php

namespace App\Domain\Notification\Services;

use App\Domain\Notification\Entities\Notification;
use App\Domain\Notification\Entities\NotificationPreferences;
use App\Domain\Notification\ValueObjects\NotificationType;
use App\Domain\Notification\ValueObjects\NotificationPriority;
use Illuminate\Support\Str;

class NotificationDomainService
{
    /**
     * Create a new notification
     */
    public function createNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $actionText = null,
        array $data = [],
        string $priority = 'normal'
    ): Notification {
        return Notification::create(
            id: Str::uuid()->toString(),
            userId: $userId,
            type: NotificationType::fromString($type),
            title: $title,
            message: $message,
            actionUrl: $actionUrl,
            actionText: $actionText,
            data: $data,
            priority: NotificationPriority::from($priority)
        );
    }

    /**
     * Determine which channels should be used for a notification
     */
    public function determineChannels(
        NotificationPreferences $preferences,
        string $category,
        ?array $forceChannels = null
    ): array {
        // If channels are forced, use those
        if ($forceChannels !== null) {
            return $forceChannels;
        }

        // Check if category is enabled
        if (!$preferences->isCategoryEnabled($category)) {
            return ['in_app']; // Always send in-app even if category disabled
        }

        // Return all enabled channels
        return $preferences->getEnabledChannels();
    }

    /**
     * Check if notification should be sent based on preferences
     */
    public function shouldSendNotification(
        NotificationPreferences $preferences,
        string $category
    ): bool {
        return $preferences->isCategoryEnabled($category);
    }
}
