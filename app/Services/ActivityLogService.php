<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Log;

class ActivityLogService
{
    public function getRecentActivities(array $filters = [], int $limit = 10): Collection
    {
        try {
            return ActivityLog::query()
                ->with(['user', 'loggable'])
                ->when(isset($filters['type']), function (Builder $query) use ($filters) {
                    match ($filters['type']) {
                        'investments' => $query->where('loggable_type', 'App\\Models\\Investment'),
                        'withdrawals' => $query->where('loggable_type', 'App\\Models\\Withdrawal'),
                        'users' => $query->whereIn('action', ['register', 'login', 'profile_update']),
                        default => null
                    };
                })
                ->latest()
                ->take($limit)
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'user' => $activity->user?->name ?? 'System',
                        'action' => $activity->action,
                        'description' => $activity->description,
                        'loggable_type' => $activity->loggable_type,
                        'loggable' => $activity->loggable,
                        'created_at' => $activity->created_at,
                        'type' => strtolower(class_basename($activity->loggable_type ?? ''))
                    ];
                });
        } catch (Exception $e) {
            Log::error('Error fetching activity logs: ' . $e->getMessage());
            return collect([]);
        }
    }

    public function getPaginatedActivities(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            return ActivityLog::query()
                ->with(['user', 'loggable'])
                ->when(isset($filters['type']), function (Builder $query) use ($filters) {
                    match ($filters['type']) {
                        'investments' => $query->where('loggable_type', 'App\\Models\\Investment'),
                        'withdrawals' => $query->where('loggable_type', 'App\\Models\\Withdrawal'),
                        'users' => $query->whereIn('action', ['register', 'login', 'profile_update']),
                        default => null
                    };
                })
                ->when(isset($filters['status']), function (Builder $query) use ($filters) {
                    $query->whereHasMorph(
                        'loggable',
                        ['App\\Models\\Investment', 'App\\Models\\Withdrawal'],
                        function (Builder $query) use ($filters) {
                            $query->where('status', $filters['status']);
                        }
                    );
                })
                ->when(isset($filters['date_range']), function (Builder $query) use ($filters) {
                    $query->whereBetween('created_at', $filters['date_range']);
                })
                ->latest()
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error fetching paginated activity logs: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getActivityById(int $id)
    {
        try {
            return ActivityLog::with(['user', 'loggable'])
                ->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Activity log not found');
        }
    }

    public function logActivity(string $type, string $description, $model = null): void
    {
        try {
            ActivityLog::create([
                'type' => $type,
                'description' => $description,
                'loggable_type' => $model ? get_class($model) : null,
                'loggable_id' => $model ? $model->id : null,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }

    public function log(Model $model, string $action, array $data = []): ActivityLog
    {
        try {
            return $model->activityLogs()->create([
                'action' => $action,
                'data' => $data,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } catch (Exception $e) {
            report($e);
            throw new Exception('Failed to log activity: ' . $e->getMessage());
        }
    }

    public function getActivityLogs(int $perPage = 15): LengthAwarePaginator
    {
        try {
            return ActivityLog::with(['user', 'loggable'])
                ->latest()
                ->paginate($perPage);
        } catch (Exception $e) {
            report($e);
            throw new Exception('Failed to retrieve activity logs: ' . $e->getMessage());
        }
    }

    public function getModelActivityLogs(Model $model, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return $model->activityLogs()
                ->with('user')
                ->latest()
                ->paginate($perPage);
        } catch (Exception $e) {
            report($e);
            throw new Exception('Failed to retrieve model activity logs: ' . $e->getMessage());
        }
    }
}
