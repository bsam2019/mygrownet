<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusUserProfileModel;
use App\Models\User;

class ProfileService
{
    public function getProfile(int $userId): ?array
    {
        $user = User::find($userId);
        if (!$user) return null;

        $profile = LifePlusUserProfileModel::firstOrCreate(
            ['user_id' => $userId],
            [
                'location' => null,
                'bio' => null,
                'skills' => [],
                'avatar_url' => null,
            ]
        );

        return [
            'id' => $profile->id,
            'user_id' => $userId,
            'name' => $user->name,
            'email' => $user->email,
            'location' => $profile->location,
            'bio' => $profile->bio,
            'skills' => $profile->skills ?? [],
            'avatar_url' => $profile->avatar_url,
        ];
    }

    public function updateProfile(int $userId, array $data): ?array
    {
        $profile = LifePlusUserProfileModel::where('user_id', $userId)->first();

        if (!$profile) {
            $profile = LifePlusUserProfileModel::create([
                'user_id' => $userId,
                ...$data,
            ]);
        } else {
            $profile->update($data);
        }

        return $this->getProfile($userId);
    }

    public function updateSkills(int $userId, array $skills): ?array
    {
        return $this->updateProfile($userId, ['skills' => $skills]);
    }

    public function getStats(int $userId): array
    {
        // Aggregate stats from various services
        $taskService = app(TaskService::class);
        $expenseService = app(ExpenseService::class);
        $habitService = app(HabitService::class);
        $gigService = app(GigService::class);

        $taskStats = $taskService->getStats($userId);
        $monthSummary = $expenseService->getMonthSummary($userId);
        $habits = $habitService->getHabits($userId);
        $myGigs = $gigService->getMyGigs($userId);

        $completedGigs = collect($myGigs)->where('status', 'completed')->count();
        $totalStreaks = collect($habits)->sum('streak');

        return [
            'tasks_completed' => $taskStats['completed'],
            'tasks_pending' => $taskStats['pending'],
            'task_completion_rate' => $taskStats['completion_rate'],
            'month_spent' => $monthSummary['total_spent'],
            'month_budget' => $monthSummary['budget'],
            'active_habits' => count($habits),
            'total_streaks' => $totalStreaks,
            'gigs_posted' => count($myGigs),
            'gigs_completed' => $completedGigs,
        ];
    }
}
