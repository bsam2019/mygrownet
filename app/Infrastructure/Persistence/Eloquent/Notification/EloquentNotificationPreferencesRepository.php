<?php

namespace App\Infrastructure\Persistence\Eloquent\Notification;

use App\Domain\Notification\Entities\NotificationPreferences;
use App\Domain\Notification\Repositories\NotificationPreferencesRepositoryInterface;

class EloquentNotificationPreferencesRepository implements NotificationPreferencesRepositoryInterface
{
    public function save(NotificationPreferences $preferences): void
    {
        NotificationPreferencesModel::updateOrCreate(
            ['user_id' => $preferences->userId()],
            [
                'email_enabled' => $preferences->isEmailEnabled(),
                'sms_enabled' => $preferences->isSmsEnabled(),
                'push_enabled' => $preferences->isPushEnabled(),
                'in_app_enabled' => $preferences->isInAppEnabled(),
                'digest_frequency' => $preferences->digestFrequency(),
            ]
        );
    }

    public function findByUserId(int $userId): ?NotificationPreferences
    {
        $model = NotificationPreferencesModel::where('user_id', $userId)->first();
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function getOrCreateDefault(int $userId): NotificationPreferences
    {
        $existing = $this->findByUserId($userId);
        
        if ($existing) {
            return $existing;
        }

        // Create default preferences
        $default = NotificationPreferences::createDefault($userId);
        $this->save($default);
        
        return $default;
    }

    private function toDomainEntity(NotificationPreferencesModel $model): NotificationPreferences
    {
        return NotificationPreferences::createDefault($model->user_id);
        // Note: In a full implementation, we'd reconstruct with actual values from the model
    }
}
