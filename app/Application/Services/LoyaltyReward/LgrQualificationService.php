<?php

namespace App\Application\Services\LoyaltyReward;

use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrQualificationModel;
use App\Domain\LoyaltyReward\Entities\LgrQualification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LgrQualificationService
{
    public function checkQualification(int $userId): array
    {
        $qualification = $this->getOrCreateQualification($userId);
        
        // Update qualification status
        $this->updateQualificationStatus($qualification, $userId);
        
        return $qualification->toArray();
    }

    public function getOrCreateQualification(int $userId): LgrQualification
    {
        $model = LgrQualificationModel::firstOrCreate(
            ['user_id' => $userId],
            [
                'starter_package_completed' => false,
                'training_completed' => false,
                'first_level_members' => 0,
                'network_requirement_met' => false,
                'activities_completed' => 0,
                'activity_requirement_met' => false,
                'fully_qualified' => false,
            ]
        );

        return LgrQualification::fromArray($model->toArray());
    }

    public function updateQualificationStatus(LgrQualification $qualification, int $userId): void
    {
        $user = User::find($userId);
        
        // Check starter package
        if ($this->hasCompletedStarterPackage($user)) {
            $qualification->completeStarterPackage();
        }
        
        // Check training
        if ($this->hasCompletedTraining($user)) {
            $qualification->completeTraining();
        }
        
        // Check network
        $firstLevelCount = $this->getFirstLevelMembersCount($userId);
        $qualification->updateFirstLevelMembers($firstLevelCount);
        
        // Check activities
        $activitiesCount = $this->getQualifyingActivitiesCount($userId);
        while ($qualification->getActivitiesCompleted() < $activitiesCount) {
            $qualification->incrementActivity();
        }
        
        // Save to database
        $this->saveQualification($qualification);
    }

    private function hasCompletedStarterPackage(User $user): bool
    {
        // Check if user has purchased starter kit
        return DB::table('starter_kit_purchases')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();
    }

    private function hasCompletedTraining(User $user): bool
    {
        // Check if user has completed Business Fundamentals course
        return DB::table('course_completions')
            ->join('courses', 'courses.id', '=', 'course_completions.course_id')
            ->where('course_completions.user_id', $user->id)
            ->where('courses.title', 'LIKE', '%Business Fundamentals%')
            ->exists();
    }

    private function getFirstLevelMembersCount(int $userId): int
    {
        return User::where('referrer_id', $userId)
            ->where('status', 'active')
            ->whereHas('starterKitPurchases', function ($query) {
                $query->where('status', 'completed');
            })
            ->count();
    }

    private function getQualifyingActivitiesCount(int $userId): int
    {
        // Count distinct qualifying activities
        $activities = 0;
        
        // Learning modules completed
        if (DB::table('lesson_completions')->where('user_id', $userId)->exists()) {
            $activities++;
        }
        
        // Marketplace transactions
        if (DB::table('orders')->where('user_id', $userId)->exists()) {
            $activities++;
        }
        
        // Event attendance
        if (DB::table('workshop_attendees')->where('user_id', $userId)->exists()) {
            $activities++;
        }
        
        return $activities;
    }

    private function saveQualification(LgrQualification $qualification): void
    {
        $data = $qualification->toArray();
        
        LgrQualificationModel::updateOrCreate(
            ['user_id' => $qualification->getUserId()],
            [
                'starter_package_completed' => $data['starter_package_completed'],
                'starter_package_completed_at' => $data['starter_package_completed'] ? now() : null,
                'training_completed' => $data['training_completed'],
                'training_completed_at' => $data['training_completed'] ? now() : null,
                'first_level_members' => $data['first_level_members'],
                'network_requirement_met' => $data['network_requirement_met'],
                'network_requirement_met_at' => $data['network_requirement_met'] ? now() : null,
                'activities_completed' => $data['activities_completed'],
                'activity_requirement_met' => $data['activity_requirement_met'],
                'activity_requirement_met_at' => $data['activity_requirement_met'] ? now() : null,
                'fully_qualified' => $data['fully_qualified'],
                'fully_qualified_at' => $data['fully_qualified'] ? now() : null,
            ]
        );
    }

    public function isUserQualified(int $userId): bool
    {
        $qualification = $this->getOrCreateQualification($userId);
        $this->updateQualificationStatus($qualification, $userId);
        
        return $qualification->isFullyQualified();
    }
}
