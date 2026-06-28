<?php

namespace App\Application\Notification\UseCases;

use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use App\Domain\Notification\Repositories\NotificationPreferencesRepositoryInterface;
use App\Domain\Notification\Services\NotificationDomainService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendNotificationUseCase
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepository,
        private readonly NotificationPreferencesRepositoryInterface $preferencesRepository,
        private readonly NotificationDomainService $domainService
    ) {}

    public function execute(
        int $userId,
        string $type,
        array $data = [],
        ?array $forceChannels = null
    ): void {
        try {
            // Get user
            $user = User::find($userId);
            if (!$user) {
                Log::warning("User not found for notification", ['user_id' => $userId]);
                return;
            }

            // Get user preferences
            $preferences = $this->preferencesRepository->getOrCreateDefault($userId);

            // Extract category from type
            $category = explode('.', $type)[0];

            // Check if notification should be sent
            if (!$this->domainService->shouldSendNotification($preferences, $category)) {
                Log::info("Notification skipped due to user preferences", [
                    'user_id' => $userId,
                    'type' => $type
                ]);
                return;
            }

            // Create notification entity
            $notification = $this->domainService->createNotification(
                userId: $userId,
                type: $type,
                title: $data['title'] ?? 'Notification',
                message: $data['message'] ?? '',
                actionUrl: $data['action_url'] ?? null,
                actionText: $data['action_text'] ?? null,
                data: $data,
                priority: $data['priority'] ?? 'normal'
            );

            // Save in-app notification
            $this->notificationRepository->save($notification);

            // Determine channels
            $channels = $this->domainService->determineChannels(
                $preferences,
                $category,
                $forceChannels
            );

            // Queue other channels (email, SMS, push)
            // This will be handled by Laravel's notification system
            if (count($channels) > 1) {
                // TODO: Dispatch to notification channels
                Log::info("Notification queued for channels", [
                    'user_id' => $userId,
                    'type' => $type,
                    'channels' => $channels
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to send notification", [
                'user_id' => $userId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }
}
