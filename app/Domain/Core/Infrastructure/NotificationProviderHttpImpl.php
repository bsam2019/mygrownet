<?php

namespace App\Domain\Core\Infrastructure;

use App\Domain\Core\Contracts\NotificationProvider;
use Illuminate\Support\Facades\Http;

class NotificationProviderHttpImpl implements NotificationProvider
{
    public function __construct(
        private string $baseUrl = '',
        private ?string $apiKey = null,
    ) {}

    public function capability(): string
    {
        return 'notifications';
    }

    public function send(int $userId, string $type, string $title, string $message, ?string $actionUrl = null, ?string $actionText = null, string $category = 'general', string $priority = 'normal', array $data = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ($this->apiKey ?? config('services.notification.api_key')),
            'Content-Type' => 'application/json',
        ])->post($this->getUrl('/api/notifications/send'), [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'category' => $category,
            'priority' => $priority,
            'data' => $data,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Notification service returned: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    public function getUnreadCount(int $userId): int
    {
        $response = Http::withToken($this->apiKey ?? config('services.notification.api_key'))
            ->get($this->getUrl("/api/notifications/{$userId}/unread/count"));

        return $response->json('count', 0);
    }

    public function markAsRead(string $notificationId): void
    {
        Http::withToken($this->apiKey ?? config('services.notification.api_key'))
            ->post($this->getUrl("/api/notifications/{$notificationId}/read"));
    }

    private function getUrl(string $path): string
    {
        $base = $this->baseUrl ?: config('services.notification.base_url', 'http://localhost');
        return rtrim($base, '/') . $path;
    }
}
