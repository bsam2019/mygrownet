<?php

namespace App\Domain\GrowNet\Services;

use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\GrowNet\Achievement;

class RecognitionIncentiveService
{
    public function triggerAchievementRecognition(User $user, Achievement $achievement): void
    {
    }

    public function getUserIncentiveSummary(User $user): array
    {
        return [];
    }
}
