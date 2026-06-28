<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusExpenseModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusTaskModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusHabitModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusHabitLogModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusGigModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusNoteModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get comprehensive user analytics
     */
    public function getUserAnalytics(int $userId, ?string $period = 'month'): array
    {
        $startDate = $this->getStartDate($period);

        return [
            'expenses' => $this->getExpenseAnalytics($userId, $startDate),
            'tasks' => $this->getTaskAnalytics($userId, $startDate),
            'habits' => $this->getHabitAnalytics($userId, $startDate),
            'gigs' => $this->getGigAnalytics($userId, $startDate),
            'activity' => $this->getActivityTimeline($userId, $startDate),
            'summary' => $this->getSummary($userId, $startDate),
        ];
    }

    /**
     * Get expense analytics with charts data
     */
    public function getExpenseAnalytics(int $userId, Carbon $startDate): array
    {
        $expenses = LifePlusExpenseModel::where('user_id', $userId)
            ->where('expense_date', '>=', $startDate)
            ->get();

        // By category
        $byCategory = $expenses->groupBy('category_id')
            ->map(fn($items) => [
                'total' => $items->sum('amount'),
                'count' => $items->count(),
            ])
            ->toArray();

        // Daily spending trend
        $dailyTrend = $expenses->groupBy(fn($e) => $e->expense_date->format('Y-m-d'))
            ->map(fn($items) => $items->sum('amount'))
            ->toArray();

        // Fill missing days
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), Carbon::now());
        $filledTrend = [];
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $filledTrend[$key] = $dailyTrend[$key] ?? 0;
        }

        return [
            'total' => $expenses->sum('amount'),
            'count' => $expenses->count(),
            'average_per_day' => $expenses->count() > 0
                ? round($expenses->sum('amount') / max(1, $startDate->diffInDays(Carbon::now())), 2)
                : 0,
            'by_category' => $byCategory,
            'daily_trend' => $filledTrend,
            'highest_expense' => $expenses->max('amount') ?? 0,
            'lowest_expense' => $expenses->min('amount') ?? 0,
        ];
    }

    /**
     * Get task analytics
     */
    public function getTaskAnalytics(int $userId, Carbon $startDate): array
    {
        $tasks = LifePlusTaskModel::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->get();

        $completed = $tasks->where('is_completed', true);
        $pending = $tasks->where('is_completed', false);
        $overdue = $pending->filter(fn($t) => $t->due_date && $t->due_date < Carbon::now());

        // Completion by day
        $completionTrend = $completed->groupBy(fn($t) => $t->completed_at?->format('Y-m-d'))
            ->map(fn($items) => $items->count())
            ->filter()
            ->toArray();

        // By priority
        $byPriority = $tasks->groupBy('priority')
            ->map(fn($items) => [
                'total' => $items->count(),
                'completed' => $items->where('is_completed', true)->count(),
            ])
            ->toArray();

        return [
            'total' => $tasks->count(),
            'completed' => $completed->count(),
            'pending' => $pending->count(),
            'overdue' => $overdue->count(),
            'completion_rate' => $tasks->count() > 0
                ? round(($completed->count() / $tasks->count()) * 100, 1)
                : 0,
            'by_priority' => $byPriority,
            'completion_trend' => $completionTrend,
        ];
    }

    /**
     * Get habit analytics
     */
    public function getHabitAnalytics(int $userId, Carbon $startDate): array
    {
        $habits = LifePlusHabitModel::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $habitIds = $habits->pluck('id');
        $logs = LifePlusHabitLogModel::whereIn('habit_id', $habitIds)
            ->where('completed_date', '>=', $startDate)
            ->get();

        // Calculate streaks
        $habitStats = $habits->map(function ($habit) use ($logs) {
            $habitLogs = $logs->where('habit_id', $habit->id)
                ->pluck('completed_date')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->unique()
                ->sort()
                ->values();

            $currentStreak = $this->calculateCurrentStreak($habitLogs->toArray());
            $longestStreak = $this->calculateLongestStreak($habitLogs->toArray());

            return [
                'id' => $habit->id,
                'name' => $habit->name,
                'total_completions' => $habitLogs->count(),
                'current_streak' => $currentStreak,
                'longest_streak' => $longestStreak,
            ];
        });

        // Daily completion rate
        $totalDays = max(1, $startDate->diffInDays(Carbon::now()));
        $totalPossible = $habits->count() * $totalDays;
        $totalCompleted = $logs->count();

        return [
            'total_habits' => $habits->count(),
            'total_completions' => $totalCompleted,
            'overall_completion_rate' => $totalPossible > 0
                ? round(($totalCompleted / $totalPossible) * 100, 1)
                : 0,
            'habits' => $habitStats->toArray(),
            'best_streak' => $habitStats->max('current_streak') ?? 0,
        ];
    }

    /**
     * Get gig analytics
     */
    public function getGigAnalytics(int $userId, Carbon $startDate): array
    {
        $posted = LifePlusGigModel::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->get();

        $applied = DB::table('lifeplus_gig_applications')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'posted' => [
                'total' => $posted->count(),
                'open' => $posted->where('status', 'open')->count(),
                'completed' => $posted->where('status', 'completed')->count(),
                'total_value' => $posted->sum('payment_amount'),
            ],
            'applied' => [
                'total' => $applied->count(),
                'accepted' => $applied->where('status', 'accepted')->count(),
                'pending' => $applied->where('status', 'pending')->count(),
            ],
        ];
    }

    /**
     * Get activity timeline
     */
    public function getActivityTimeline(int $userId, Carbon $startDate): array
    {
        $activities = [];

        // Recent expenses
        $expenses = LifePlusExpenseModel::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($e) => [
                'type' => 'expense',
                'title' => 'Added expense',
                'description' => $e->description ?? 'K' . number_format($e->amount),
                'date' => $e->created_at->toISOString(),
            ]);

        // Recent tasks completed
        $tasks = LifePlusTaskModel::where('user_id', $userId)
            ->where('is_completed', true)
            ->where('completed_at', '>=', $startDate)
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'type' => 'task',
                'title' => 'Completed task',
                'description' => $t->title,
                'date' => $t->completed_at->toISOString(),
            ]);

        // Merge and sort
        return collect($expenses)
            ->merge($tasks)
            ->sortByDesc('date')
            ->take(20)
            ->values()
            ->toArray();
    }

    /**
     * Get summary stats
     */
    public function getSummary(int $userId, Carbon $startDate): array
    {
        $notes = LifePlusNoteModel::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->count();

        return [
            'period_start' => $startDate->toDateString(),
            'period_end' => Carbon::now()->toDateString(),
            'notes_created' => $notes,
            'days_active' => $this->calculateActiveDays($userId, $startDate),
        ];
    }

    private function getStartDate(string $period): Carbon
    {
        return match ($period) {
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'quarter' => Carbon::now()->subQuarter(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth(),
        };
    }

    private function calculateCurrentStreak(array $dates): int
    {
        if (empty($dates)) return 0;

        $streak = 0;
        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        // Check if completed today or yesterday
        if (!in_array($today, $dates) && !in_array($yesterday, $dates)) {
            return 0;
        }

        $checkDate = in_array($today, $dates) ? Carbon::today() : Carbon::yesterday();

        while (in_array($checkDate->format('Y-m-d'), $dates)) {
            $streak++;
            $checkDate->subDay();
        }

        return $streak;
    }

    private function calculateLongestStreak(array $dates): int
    {
        if (empty($dates)) return 0;

        $longest = 0;
        $current = 0;
        $prevDate = null;

        foreach ($dates as $date) {
            $currentDate = Carbon::parse($date);

            if ($prevDate && $currentDate->diffInDays($prevDate) === 1) {
                $current++;
            } else {
                $current = 1;
            }

            $longest = max($longest, $current);
            $prevDate = $currentDate;
        }

        return $longest;
    }

    private function calculateActiveDays(int $userId, Carbon $startDate): int
    {
        $expenseDays = LifePlusExpenseModel::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->distinct()
            ->pluck('date');

        $taskDays = LifePlusTaskModel::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->distinct()
            ->pluck('date');

        return $expenseDays->merge($taskDays)->unique()->count();
    }
}
