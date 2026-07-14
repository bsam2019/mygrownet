<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaNotificationModel;

class StockFlowNotificationService
{
    public function create(
        int $companyId,
        int $userId,
        string $type,
        string $title,
        ?string $message = null,
        ?string $actionUrl = null,
        ?string $actionText = null,
        ?array $data = null,
        string $priority = 'normal',
    ): SaNotificationModel {
        return SaNotificationModel::create([
            'sa_company_id' => $companyId,
            'sa_user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'data' => $data,
            'priority' => $priority,
        ]);
    }

    public function notifyAllAdmins(int $companyId, string $type, string $title, ?string $message = null, ?string $actionUrl = null, ?string $actionText = null, ?array $data = null, string $priority = 'normal'): void
    {
        $users = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel::whereHas('companyUsers', fn($q) => $q->where('sa_company_id', $companyId))
            ->where(function ($q) {
                $q->where('is_admin', true)
                  ->orWhereHas('companyUsers', fn($q) => $q->where('role', 'owner'));
            })
            ->get();

        foreach ($users as $user) {
            $this->create($companyId, $user->id, $type, $title, $message, $actionUrl, $actionText, $data, $priority);
        }
    }

    public function notifyCompany(int $companyId, string $type, string $title, ?string $message = null, ?string $actionUrl = null, ?string $actionText = null, ?array $data = null, string $priority = 'normal'): void
    {
        $users = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel::whereHas('companyUsers', fn($q) => $q->where('sa_company_id', $companyId))->get();

        foreach ($users as $user) {
            $this->create($companyId, $user->id, $type, $title, $message, $actionUrl, $actionText, $data, $priority);
        }
    }

    public function getUnreadCount(int $userId, ?int $companyId = null): int
    {
        $q = SaNotificationModel::unread()->forUser($userId);
        if ($companyId) $q->forCompany($companyId);
        return $q->count();
    }

    public function getNotifications(int $userId, ?int $companyId = null, int $limit = 50): array
    {
        $q = SaNotificationModel::forUser($userId)->orderBy('created_at', 'desc');
        if ($companyId) $q->forCompany($companyId);
        return $q->limit($limit)->get()->toArray();
    }

    public function markAsRead(int $notificationId): void
    {
        SaNotificationModel::where('id', $notificationId)->update(['read_at' => now()]);
    }

    public function markAllAsRead(int $userId, ?int $companyId = null): void
    {
        $q = SaNotificationModel::unread()->forUser($userId);
        if ($companyId) $q->forCompany($companyId);
        $q->update(['read_at' => now()]);
    }
}
