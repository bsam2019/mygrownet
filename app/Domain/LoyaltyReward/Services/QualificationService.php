<?php

namespace App\Domain\LoyaltyReward\Services;

use App\Models\User;

class QualificationService
{
    private const REQUIRED_FIRST_LEVEL_MEMBERS = 3;
    private const REQUIRED_ACTIVITY_COUNT = 2;
    private const STARTER_PACKAGE_AMOUNT = 1000;

    public function isQualified(User $user): bool
    {
        return $this->hasStarterPackage($user)
            && $this->hasCompletedTraining($user)
            && $this->hasRequiredNetworkSize($user)
            && $this->hasRequiredActivities($user);
    }

    public function getQualificationStatus(User $user): array
    {
        return [
            'starter_package' => $this->hasStarterPackage($user),
            'training_completed' => $this->hasCompletedTraining($user),
            'network_size' => $this->hasRequiredNetworkSize($user),
            'activities_completed' => $this->hasRequiredActivities($user),
            'is_qualified' => $this->isQualified($user),
        ];
    }

    private function hasStarterPackage(User $user): bool
    {
        // Check if user has purchased starter package
        return $user->starterKitPurchases()
            ->where('amount', '>=', self::STARTER_PACKAGE_AMOUNT)
            ->where('status', 'completed')
            ->exists();
    }

    private function hasCompletedTraining(User $user): bool
    {
        // Check if user has completed Business Fundamentals training
        return $user->completedCourses()
            ->where('course_slug', 'business-fundamentals')
            ->exists();
    }

    private function hasRequiredNetworkSize(User $user): bool
    {
        // Check if user has at least 3 active first-level members
        $firstLevelCount = $user->directReferrals()
            ->whereHas('starterKitPurchases', function ($query) {
                $query->where('status', 'completed');
            })
            ->whereHas('profile', function ($query) {
                $query->where('kyc_verified', true);
            })
            ->count();

        return $firstLevelCount >= self::REQUIRED_FIRST_LEVEL_MEMBERS;
    }

    private function hasRequiredActivities(User $user): bool
    {
        // Count qualifying activities
        $activityCount = 0;

        // Learning activities
        if ($user->completedCourses()->count() >= 2) {
            $activityCount++;
        }

        if ($user->quizResults()->where('passed', true)->exists()) {
            $activityCount++;
        }

        if ($user->webinarAttendances()->exists()) {
            $activityCount++;
        }

        // Business activities
        if ($user->marketplaceListings()->exists()) {
            $activityCount++;
        }

        if ($user->marketplacePurchases()->exists()) {
            $activityCount++;
        }

        if ($user->communityPosts()->exists()) {
            $activityCount++;
        }

        if ($user->businessPlans()->where('completed', true)->exists()) {
            $activityCount++;
        }

        return $activityCount >= self::REQUIRED_ACTIVITY_COUNT;
    }

    public function getMissingRequirements(User $user): array
    {
        $missing = [];

        if (!$this->hasStarterPackage($user)) {
            $missing[] = 'Purchase K1,000 Starter Package';
        }

        if (!$this->hasCompletedTraining($user)) {
            $missing[] = 'Complete Business Fundamentals training';
        }

        if (!$this->hasRequiredNetworkSize($user)) {
            $currentCount = $user->directReferrals()
                ->whereHas('starterKitPurchases', function ($query) {
                    $query->where('status', 'completed');
                })
                ->count();
            $needed = self::REQUIRED_FIRST_LEVEL_MEMBERS - $currentCount;
            $missing[] = "Recruit {$needed} more active member(s) (currently {$currentCount}/3)";
        }

        if (!$this->hasRequiredActivities($user)) {
            $missing[] = 'Complete at least 2 platform activities';
        }

        return $missing;
    }
}
