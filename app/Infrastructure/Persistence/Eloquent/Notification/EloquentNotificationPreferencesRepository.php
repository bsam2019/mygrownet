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
        // Load actual preferences from database
        $categoryPreferences = [
            'wallet' => (bool) $model->notify_wallet,
            'commissions' => (bool) $model->notify_commissions,
            'withdrawals' => (bool) $model->notify_withdrawals,
            'subscriptions' => (bool) $model->notify_subscriptions,
            'referrals' => (bool) $model->notify_referrals,
            'workshops' => (bool) $model->notify_workshops,
            'ventures' => (bool) $model->notify_ventures,
            'bgf' => (bool) $model->notify_bgf,
            'points' => (bool) $model->notify_points,
            'security' => (bool) $model->notify_security,
            'marketing' => (bool) $model->notify_marketing,
            'messages' => (bool) ($model->notify_messages ?? true), // Default to true for messages
        ];

        // Use reflection to create entity with actual database values
        $reflection = new \ReflectionClass(NotificationPreferences::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        
        $this->setProperty($instance, 'userId', $model->user_id);
        $this->setProperty($instance, 'emailEnabled', (bool) $model->email_enabled);
        $this->setProperty($instance, 'smsEnabled', (bool) $model->sms_enabled);
        $this->setProperty($instance, 'pushEnabled', (bool) $model->push_enabled);
        $this->setProperty($instance, 'inAppEnabled', (bool) $model->in_app_enabled);
        $this->setProperty($instance, 'categoryPreferences', $categoryPreferences);
        $this->setProperty($instance, 'digestFrequency', $model->digest_frequency ?? 'instant');
        
        return $instance;
    }
    
    private function setProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }
}
