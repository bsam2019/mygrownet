<?php

namespace App\Domain\LifePlus\Services;

use App\Models\LifePlus\ScheduleBlock;
use Carbon\Carbon;

class ScheduleService
{
    public function getScheduleForDate(int $userId, string $date): array
    {
        $blocks = ScheduleBlock::forUser($userId)
            ->forDate($date)
            ->with('task')
            ->orderBy('start_time')
            ->get();

        return [
            'blocks' => $blocks,
            'date' => $date,
            'total_scheduled_minutes' => $blocks->sum('duration_minutes'),
            'completed_blocks' => $blocks->where('is_completed', true)->count(),
            'total_blocks' => $blocks->count(),
        ];
    }

    public function getScheduleForWeek(int $userId, string $startDate): array
    {
        $start = Carbon::parse($startDate)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $blocks = ScheduleBlock::forUser($userId)
            ->forDateRange($start->toDateString(), $end->toDateString())
            ->with('task')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn($block) => $block->date->format('Y-m-d'));

        $weekDays = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateStr = $date->toDateString();
            $dayBlocks = $blocks->get($dateStr, collect());
            
            $weekDays[] = [
                'date' => $dateStr,
                'day_name' => $date->format('l'),
                'blocks' => $dayBlocks,
                'total_minutes' => $dayBlocks->sum('duration_minutes'),
                'completed_blocks' => $dayBlocks->where('is_completed', true)->count(),
            ];
        }

        return [
            'week_days' => $weekDays,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];
    }

    public function createScheduleBlock(int $userId, array $data): ScheduleBlock
    {
        // Validate time overlap
        $this->validateNoOverlap($userId, $data['date'], $data['start_time'], $data['end_time'], null);

        $block = ScheduleBlock::create([
            'user_id' => $userId,
            'task_id' => $data['task_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'color' => $data['color'] ?? $this->getCategoryColor($data['category'] ?? 'other'),
            'category' => $data['category'] ?? 'other',
            'is_recurring' => $data['is_recurring'] ?? false,
            'recurrence_pattern' => $data['recurrence_pattern'] ?? null,
            'recurrence_end_date' => $data['recurrence_end_date'] ?? null,
            'local_id' => $data['local_id'] ?? null,
        ]);

        // Handle recurring blocks
        if ($block->is_recurring && $block->recurrence_pattern) {
            $this->createRecurringBlocks($block);
        }

        return $block->load('task');
    }

    public function updateScheduleBlock(int $id, int $userId, array $data): ?ScheduleBlock
    {
        $block = ScheduleBlock::forUser($userId)->find($id);

        if (!$block) {
            return null;
        }

        // Validate time overlap (excluding current block)
        if (isset($data['date']) || isset($data['start_time']) || isset($data['end_time'])) {
            $this->validateNoOverlap(
                $userId,
                $data['date'] ?? $block->date,
                $data['start_time'] ?? $block->start_time,
                $data['end_time'] ?? $block->end_time,
                $id
            );
        }

        $block->update(array_filter($data, fn($value) => $value !== null));

        return $block->load('task');
    }

    public function toggleScheduleBlock(int $id, int $userId): ?ScheduleBlock
    {
        $block = ScheduleBlock::forUser($userId)->find($id);

        if (!$block) {
            return null;
        }

        $block->update([
            'is_completed' => !$block->is_completed,
            'completed_at' => !$block->is_completed ? now() : null,
        ]);

        return $block;
    }

    public function deleteScheduleBlock(int $id, int $userId): bool
    {
        $block = ScheduleBlock::forUser($userId)->find($id);

        if (!$block) {
            return false;
        }

        return $block->delete();
    }

    public function getStats(int $userId): array
    {
        $today = now()->toDateString();
        $todayBlocks = ScheduleBlock::forUser($userId)->forDate($today)->get();

        return [
            'today_total' => $todayBlocks->count(),
            'today_completed' => $todayBlocks->where('is_completed', true)->count(),
            'today_minutes' => $todayBlocks->sum('duration_minutes'),
            'today_completed_minutes' => $todayBlocks->where('is_completed', true)->sum('duration_minutes'),
        ];
    }

    private function validateNoOverlap(int $userId, string $date, string $startTime, string $endTime, ?int $excludeId = null): void
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        if ($end->lte($start)) {
            throw new \InvalidArgumentException('End time must be after start time');
        }

        $query = ScheduleBlock::forUser($userId)
            ->forDate($date)
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($q2) use ($start, $end) {
                    // New block starts during existing block
                    $q2->where('start_time', '<=', $start->format('H:i'))
                        ->where('end_time', '>', $start->format('H:i'));
                })->orWhere(function ($q2) use ($start, $end) {
                    // New block ends during existing block
                    $q2->where('start_time', '<', $end->format('H:i'))
                        ->where('end_time', '>=', $end->format('H:i'));
                })->orWhere(function ($q2) use ($start, $end) {
                    // New block completely contains existing block
                    $q2->where('start_time', '>=', $start->format('H:i'))
                        ->where('end_time', '<=', $end->format('H:i'));
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \InvalidArgumentException('This time slot overlaps with an existing schedule block');
        }
    }

    private function createRecurringBlocks(ScheduleBlock $template): void
    {
        if (!$template->recurrence_pattern || !$template->recurrence_end_date) {
            return;
        }

        $currentDate = Carbon::parse($template->date)->addDay();
        $endDate = Carbon::parse($template->recurrence_end_date);

        while ($currentDate->lte($endDate)) {
            $shouldCreate = match ($template->recurrence_pattern) {
                'daily' => true,
                'weekly' => $currentDate->dayOfWeek === Carbon::parse($template->date)->dayOfWeek,
                'weekdays' => $currentDate->isWeekday(),
                'weekends' => $currentDate->isWeekend(),
                default => false,
            };

            if ($shouldCreate) {
                try {
                    ScheduleBlock::create([
                        'user_id' => $template->user_id,
                        'task_id' => $template->task_id,
                        'title' => $template->title,
                        'description' => $template->description,
                        'date' => $currentDate->toDateString(),
                        'start_time' => $template->start_time,
                        'end_time' => $template->end_time,
                        'color' => $template->color,
                        'category' => $template->category,
                        'is_recurring' => false, // Don't make copies recurring
                    ]);
                } catch (\Exception $e) {
                    // Skip if overlap occurs
                    continue;
                }
            }

            $currentDate->addDay();
        }
    }

    private function getCategoryColor(string $category): string
    {
        return match ($category) {
            'work' => '#3b82f6', // Blue
            'personal' => '#8b5cf6', // Purple
            'health' => '#10b981', // Green
            'learning' => '#f59e0b', // Amber
            'social' => '#ec4899', // Pink
            default => '#6b7280', // Gray
        };
    }
}
