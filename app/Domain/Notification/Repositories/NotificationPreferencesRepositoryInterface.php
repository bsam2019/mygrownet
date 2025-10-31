<?php

namespace App\Domain\Notification\Repositories;

use App\Domain\Notification\Entities\NotificationPreferences;

interface NotificationPreferencesRepositoryInterface
{
    public function save(NotificationPreferences $preferences): void;
    
    public function findByUserId(int $userId): ?NotificationPreferences;
    
    public function getOrCreateDefault(int $userId): NotificationPreferences;
}
