<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusHabitModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusHabitLogModel;
use Carbon\Carbon;

class HabitService
{
    public function getHabits(int $userId): array
    {
        return LifePlusHabitModel::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('created_at')
            ->get()
            ->map(fn($h) => $this->mapHabit($h))
            ->toArray();
    }

    public function createHabit(int $userId, array $data): array
    {
        $habit = LifePlusHabitModel::create([
            'user_id' => $userId,
            'name' => $data['name'],
            'icon' => $data['icon'] ?? '⭐',
            'color' => $data['color'] ?? '#10b981',
            'frequency' => $data['frequency'] ?? 'daily',
            'reminder_time' => $data['reminder_time'] ?? null,
            'is_active' => true,
        ]);

        return $this->mapHabit($habit);
    }

    public function updateHabit(int $id, int $userId, array $data): ?array
    {
        $habit = LifePlusHabitModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$habit) return null;

        $habit->update($data);
        return $this->mapHabit($habit->fresh());
    }

    public function deleteHabit(int $id, int $userId): bool
    {
        return LifePlusHabitModel::where('id', $id)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function logHabit(int $habitId, int $userId, ?string $date = null): ?array
    {
        $habit = LifePlusHabitModel::where('id', $habitId)
            ->where('user_id', $userId)
            ->first();

        if (!$habit) return null;

        $logDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        // Toggle - if already logged, remove it
        $existing = LifePlusHabitLogModel::where('habit_id', $habitId)
            ->where('completed_date', $logDate)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            LifePlusHabitLogModel::create([
                'habit_id' => $habitId,
                'completed_date' => $logDate,
            ]);
        }

        return $this->mapHabit($habit->fresh());
    }

    public function getWeekProgress(int $userId): array
    {
        $habits = LifePlusHabitModel::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $days[] = $startOfWeek->copy()->addDays($i)->toDateString();
        }

        return $habits->map(function ($habit) use ($days) {
            $logs = LifePlusHabitLogModel::where('habit_id', $habit->id)
                ->whereBetween('completed_date', [$days[0], $days[6]])
                ->pluck('completed_date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->toArray();

            return [
                'id' => $habit->id,
                'name' => $habit->name,
                'icon' => $habit->icon,
                'color' => $habit->color,
                'days' => collect($days)->map(fn($d) => [
                    'date' => $d,
                    'completed' => in_array($d, $logs),
                ])->toArray(),
                'completed_count' => count($logs),
            ];
        })->toArray();
    }

    public function getStreak(int $habitId): int
    {
        $logs = LifePlusHabitLogModel::where('habit_id', $habitId)
            ->orderBy('completed_date', 'desc')
            ->pluck('completed_date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        if (empty($logs)) return 0;

        $streak = 0;
        $checkDate = now()->toDateString();

        // If not completed today, start from yesterday
        if (!in_array($checkDate, $logs)) {
            $checkDate = now()->subDay()->toDateString();
        }

        while (in_array($checkDate, $logs)) {
            $streak++;
            $checkDate = Carbon::parse($checkDate)->subDay()->toDateString();
        }

        return $streak;
    }

    private function mapHabit($habit): array
    {
        $streak = $this->getStreak($habit->id);
        $todayCompleted = LifePlusHabitLogModel::where('habit_id', $habit->id)
            ->where('completed_date', now()->toDateString())
            ->exists();

        return [
            'id' => $habit->id,
            'name' => $habit->name,
            'icon' => $habit->icon,
            'color' => $habit->color,
            'frequency' => $habit->frequency,
            'reminder_time' => $habit->reminder_time,
            'is_active' => $habit->is_active,
            'streak' => $streak,
            'today_completed' => $todayCompleted,
        ];
    }
}
