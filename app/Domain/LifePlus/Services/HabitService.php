<?php

namespace App\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusHabit;
use App\Domain\LifePlus\Repositories\HabitRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\LifePlusHabitLogModel;

class HabitService
{
    public function __construct(
        private readonly HabitRepositoryInterface $habitRepo,
    ) {}

    public function getHabits(int $userId): array
    {
        $habits = $this->habitRepo->findByUser($userId);
        return array_map(fn($h) => $this->mapHabit($h), $habits);
    }

    public function createHabit(int $userId, array $data): array
    {
        $habit = LifePlusHabit::reconstitute([
            'user_id' => $userId,
            'name' => $data['name'],
            'icon' => $data['icon'] ?? '⭐',
            'color' => $data['color'] ?? '#10b981',
            'frequency' => $data['frequency'] ?? 'daily',
            'reminder_time' => $data['reminder_time'] ?? null,
            'is_active' => true,
        ]);

        return $this->mapHabit($this->habitRepo->save($habit));
    }

    public function updateHabit(int $id, int $userId, array $data): ?array
    {
        $habit = $this->habitRepo->findById($id);
        if (!$habit || $habit->userId !== $userId) return null;

        $merged = array_merge($habit->toArray(), $data);
        return $this->mapHabit($this->habitRepo->save(LifePlusHabit::reconstitute($merged)));
    }

    public function deleteHabit(int $id, int $userId): bool
    {
        $habit = $this->habitRepo->findById($id);
        if (!$habit || $habit->userId !== $userId) return false;
        return $this->habitRepo->delete($id);
    }

    public function logHabit(int $habitId, int $userId, ?string $date = null): ?array
    {
        $habit = $this->habitRepo->findById($habitId);
        if (!$habit || $habit->userId !== $userId) return null;

        $logDate = $date ? (new \DateTimeImmutable($date))->format('Y-m-d') : now()->toDateString();

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

        return $this->mapHabit($habit);
    }

    private function getStreak(int $habitId): int
    {
        $logs = LifePlusHabitLogModel::where('habit_id', $habitId)
            ->orderBy('completed_date', 'desc')
            ->pluck('completed_date')
            ->map(fn($d) => $d instanceof \DateTimeImmutable ? $d->format('Y-m-d') : (new \DateTimeImmutable($d))->format('Y-m-d'))
            ->toArray();

        if (empty($logs)) return 0;

        $streak = 0;
        $checkDate = now()->toDateString();

        if (!in_array($checkDate, $logs)) {
            $checkDate = now()->subDay()->toDateString();
        }

        while (in_array($checkDate, $logs)) {
            $streak++;
            $checkDate = \Carbon\Carbon::parse($checkDate)->subDay()->toDateString();
        }

        return $streak;
    }

    private function mapHabit(LifePlusHabit $habit): array
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
            'reminder_time' => $habit->reminderTime,
            'is_active' => $habit->isActive,
            'streak' => $streak,
            'today_completed' => $todayCompleted,
        ];
    }
}
