<?php

namespace App\Domain\GrowBiz\Services;

use App\Domain\Module\Contracts\ModuleUsageProviderInterface;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * GrowBiz Usage Provider
 * 
 * Provides usage metrics for GrowBiz module.
 * Implements the centralized ModuleUsageProviderInterface.
 */
class GrowBizUsageProvider implements ModuleUsageProviderInterface
{
    public function getModuleId(): string
    {
        return 'growbiz';
    }

    public function getUsageMetrics(User $user): array
    {
        return [
            'tasks_per_month' => $this->getMonthlyTaskCount($user),
            'employees' => $this->getEmployeeCount($user),
            'task_templates' => $this->getTaskTemplateCount($user),
            'projects' => $this->getProjectCount($user),
            'file_storage_mb' => (int) round($this->getStorageUsed($user) / 1024 / 1024),
        ];
    }

    public function getMetric(User $user, string $metricKey): int
    {
        return match ($metricKey) {
            'tasks_per_month' => $this->getMonthlyTaskCount($user),
            'employees' => $this->getEmployeeCount($user),
            'task_templates' => $this->getTaskTemplateCount($user),
            'projects' => $this->getProjectCount($user),
            'file_storage_mb' => (int) round($this->getStorageUsed($user) / 1024 / 1024),
            'task_comments_per_task' => 0, // This is per-resource, handled differently
            default => 0,
        };
    }

    /**
     * Get comment count for a specific task
     */
    public function getTaskCommentCount(int $taskId): int
    {
        return DB::table('growbiz_task_comments')
            ->where('task_id', $taskId)
            ->count();
    }

    public function clearCache(User $user): void
    {
        $monthKey = now()->format('Y-m');
        Cache::forget("growbiz_task_count_{$user->id}_{$monthKey}");
        Cache::forget("growbiz_employee_count_{$user->id}");
        Cache::forget("growbiz_template_count_{$user->id}");
        Cache::forget("growbiz_project_count_{$user->id}");
        Cache::forget("growbiz_storage_{$user->id}");
    }

    public function getStorageUsed(User $user): int
    {
        $cacheKey = "growbiz_storage_{$user->id}";

        return Cache::remember($cacheKey, 600, function () use ($user) {
            return DB::table('growbiz_task_attachments')
                ->join('growbiz_tasks', 'growbiz_task_attachments.task_id', '=', 'growbiz_tasks.id')
                ->where('growbiz_tasks.manager_id', $user->id)
                ->sum('growbiz_task_attachments.file_size') ?? 0;
        });
    }

    private function getMonthlyTaskCount(User $user): int
    {
        $cacheKey = "growbiz_task_count_{$user->id}_" . now()->format('Y-m');

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return DB::table('growbiz_tasks')
                ->where('manager_id', $user->id)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();
        });
    }

    private function getEmployeeCount(User $user): int
    {
        $cacheKey = "growbiz_employee_count_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return DB::table('growbiz_employees')
                ->where('manager_id', $user->id)
                ->whereIn('status', ['active', 'on_leave'])
                ->count();
        });
    }

    private function getTaskTemplateCount(User $user): int
    {
        $cacheKey = "growbiz_template_count_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return DB::table('growbiz_task_templates')
                ->where('manager_id', $user->id)
                ->count();
        });
    }

    private function getProjectCount(User $user): int
    {
        $cacheKey = "growbiz_project_count_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return DB::table('growbiz_projects')
                ->where('manager_id', $user->id)
                ->count();
        });
    }
}
