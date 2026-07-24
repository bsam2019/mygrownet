<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\LifePlus;

use App\Domain\LifePlus\Entities\LifePlusTask;
use App\Domain\LifePlus\Repositories\TaskRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\LifePlusTaskModel;
use Carbon\Carbon;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function findById(int $id): ?LifePlusTask
    {
        $model = LifePlusTaskModel::find($id);
        return $model ? LifePlusTask::reconstitute($model->toArray()) : null;
    }

    public function save(LifePlusTask $entity): LifePlusTask
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            LifePlusTaskModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = LifePlusTaskModel::create($data);
        return LifePlusTask::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return LifePlusTaskModel::where('id', $id)->delete() > 0;
    }

    public function findByUser(int $userId, array $filters = []): array
    {
        $query = LifePlusTaskModel::where('user_id', $userId);

        if (isset($filters['is_completed'])) {
            $query->where('is_completed', $filters['is_completed']);
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (!empty($filters['due_date'])) {
            $query->whereDate('due_date', $filters['due_date']);
        }
        if (!empty($filters['today'])) {
            $query->whereDate('due_date', now()->toDateString());
        }
        if (!empty($filters['upcoming'])) {
            $query->where('due_date', '>', now())
                  ->where('due_date', '<=', now()->addDays(7));
        }
        if (!empty($filters['overdue'])) {
            $query->where('is_completed', false)
                  ->whereDate('due_date', '<', now()->toDateString());
        }

        return $query->orderBy('is_completed')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->limit($filters['limit'] ?? 100)
            ->get()
            ->map(fn($m) => LifePlusTask::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByLocalId(int $userId, string $localId): ?LifePlusTask
    {
        $model = LifePlusTaskModel::where('user_id', $userId)
            ->where('local_id', $localId)
            ->first();
        return $model ? LifePlusTask::reconstitute($model->toArray()) : null;
    }

    public function getStats(int $userId): array
    {
        $today = now()->toDateString();
        $total = LifePlusTaskModel::where('user_id', $userId)->count();
        $completed = LifePlusTaskModel::where('user_id', $userId)->where('is_completed', true)->count();
        $pending = $total - $completed;
        $todayTasks = LifePlusTaskModel::where('user_id', $userId)
            ->whereDate('due_date', $today)
            ->where('is_completed', false)
            ->count();
        $overdue = LifePlusTaskModel::where('user_id', $userId)
            ->where('is_completed', false)
            ->whereDate('due_date', '<', $today)
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'today' => $todayTasks,
            'overdue' => $overdue,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    public function getForMonth(int $userId, string $month): array
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return LifePlusTaskModel::where('user_id', $userId)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->get()
            ->map(fn($m) => LifePlusTask::reconstitute($m->toArray()))
            ->toArray();
    }
}
