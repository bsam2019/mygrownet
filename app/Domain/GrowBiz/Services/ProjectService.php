<?php

namespace App\Domain\GrowBiz\Services;

use App\Infrastructure\Persistence\Eloquent\GrowBizProjectModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizKanbanColumnModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizMilestoneModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizTaskModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizTaskDependencyModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectService
{
    // Default Kanban columns for new projects
    private array $defaultColumns = [
        ['name' => 'To Do', 'color' => '#6b7280', 'sort_order' => 0],
        ['name' => 'In Progress', 'color' => '#3b82f6', 'sort_order' => 1],
        ['name' => 'Review', 'color' => '#f59e0b', 'sort_order' => 2],
        ['name' => 'Done', 'color' => '#10b981', 'sort_order' => 3, 'is_done_column' => true],
    ];

    // Projects
    public function getProjects(int $userId, array $filters = []): array
    {
        $query = GrowBizProjectModel::where('manager_id', $userId)
            ->withCount(['tasks', 'members'])
            ->with(['columns']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $query->orderBy('sort_order')->orderBy('created_at', 'desc');

        return $query->get()->map(fn($p) => $this->mapProject($p))->toArray();
    }

    public function getProjectById(int $id, int $userId): ?array
    {
        $project = GrowBizProjectModel::where('id', $id)
            ->where('manager_id', $userId)
            ->with(['columns.tasks.assignees', 'milestones', 'members'])
            ->first();

        return $project ? $this->mapProject($project, true) : null;
    }

    public function createProject(int $userId, array $data): array
    {
        $project = GrowBizProjectModel::create([
            'manager_id' => $userId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#3b82f6',
            'status' => $data['status'] ?? 'planning',
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'budget' => $data['budget'] ?? null,
        ]);

        // Create default Kanban columns
        foreach ($this->defaultColumns as $column) {
            GrowBizKanbanColumnModel::create([
                'project_id' => $project->id,
                ...$column,
            ]);
        }

        return $this->mapProject($project->fresh(['columns']));
    }

    public function updateProject(int $id, int $userId, array $data): ?array
    {
        $project = GrowBizProjectModel::where('id', $id)->where('manager_id', $userId)->first();
        if (!$project) return null;

        $project->update($data);
        return $this->mapProject($project->fresh(['columns', 'milestones']));
    }

    public function deleteProject(int $id, int $userId): bool
    {
        return GrowBizProjectModel::where('id', $id)->where('manager_id', $userId)->delete() > 0;
    }

    // Kanban Board
    public function getKanbanBoard(int $projectId, int $userId): ?array
    {
        $project = GrowBizProjectModel::where('id', $projectId)
            ->where('manager_id', $userId)
            ->with(['columns' => fn($q) => $q->orderBy('sort_order')])
            ->first();

        if (!$project) return null;

        $columns = $project->columns->map(function ($column) {
            $tasks = GrowBizTaskModel::where('kanban_column_id', $column->id)
                ->with(['assignees', 'subtasks'])
                ->orderBy('kanban_order')
                ->get();

            return [
                'id' => $column->id,
                'name' => $column->name,
                'color' => $column->color,
                'wip_limit' => $column->wip_limit,
                'is_done_column' => $column->is_done_column,
                'task_count' => $tasks->count(),
                'tasks' => $tasks->map(fn($t) => $this->mapTaskForKanban($t))->toArray(),
            ];
        });

        return [
            'project' => $this->mapProject($project),
            'columns' => $columns->toArray(),
        ];
    }

    public function moveTask(int $taskId, int $userId, int $columnId, int $position): bool
    {
        $task = GrowBizTaskModel::where('id', $taskId)->where('manager_id', $userId)->first();
        if (!$task) return false;

        $column = GrowBizKanbanColumnModel::find($columnId);
        if (!$column) return false;

        // Update task column and position
        $task->update([
            'kanban_column_id' => $columnId,
            'kanban_order' => $position,
        ]);

        // If moved to done column, mark as completed
        if ($column->is_done_column && $task->status !== 'completed') {
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        // Reorder other tasks in the column
        $this->reorderTasksInColumn($columnId, $taskId, $position);

        // Update project progress
        if ($task->project_id) {
            $task->project->updateProgress();
        }

        return true;
    }

    private function reorderTasksInColumn(int $columnId, int $movedTaskId, int $newPosition): void
    {
        $tasks = GrowBizTaskModel::where('kanban_column_id', $columnId)
            ->where('id', '!=', $movedTaskId)
            ->orderBy('kanban_order')
            ->get();

        $order = 0;
        foreach ($tasks as $task) {
            if ($order === $newPosition) $order++;
            $task->update(['kanban_order' => $order]);
            $order++;
        }
    }

    // Kanban Columns
    public function createColumn(int $projectId, int $userId, array $data): ?array
    {
        $project = GrowBizProjectModel::where('id', $projectId)->where('manager_id', $userId)->first();
        if (!$project) return null;

        $maxOrder = GrowBizKanbanColumnModel::where('project_id', $projectId)->max('sort_order') ?? -1;

        $column = GrowBizKanbanColumnModel::create([
            'project_id' => $projectId,
            'name' => $data['name'],
            'color' => $data['color'] ?? '#6b7280',
            'sort_order' => $maxOrder + 1,
            'wip_limit' => $data['wip_limit'] ?? null,
            'is_done_column' => $data['is_done_column'] ?? false,
        ]);

        return [
            'id' => $column->id,
            'name' => $column->name,
            'color' => $column->color,
            'sort_order' => $column->sort_order,
            'wip_limit' => $column->wip_limit,
            'is_done_column' => $column->is_done_column,
            'task_count' => 0,
            'tasks' => [],
        ];
    }

    public function updateColumn(int $columnId, int $userId, array $data): ?array
    {
        $column = GrowBizKanbanColumnModel::whereHas('project', fn($q) => $q->where('manager_id', $userId))
            ->where('id', $columnId)
            ->first();

        if (!$column) return null;

        $column->update($data);
        return [
            'id' => $column->id,
            'name' => $column->name,
            'color' => $column->color,
            'sort_order' => $column->sort_order,
            'wip_limit' => $column->wip_limit,
            'is_done_column' => $column->is_done_column,
        ];
    }

    public function deleteColumn(int $columnId, int $userId): bool
    {
        $column = GrowBizKanbanColumnModel::whereHas('project', fn($q) => $q->where('manager_id', $userId))
            ->where('id', $columnId)
            ->first();

        if (!$column) return false;

        // Move tasks to first column or unassign
        $firstColumn = GrowBizKanbanColumnModel::where('project_id', $column->project_id)
            ->where('id', '!=', $columnId)
            ->orderBy('sort_order')
            ->first();

        GrowBizTaskModel::where('kanban_column_id', $columnId)
            ->update(['kanban_column_id' => $firstColumn?->id]);

        return $column->delete();
    }

    public function reorderColumns(int $projectId, int $userId, array $columnIds): bool
    {
        $project = GrowBizProjectModel::where('id', $projectId)->where('manager_id', $userId)->first();
        if (!$project) return false;

        foreach ($columnIds as $order => $columnId) {
            GrowBizKanbanColumnModel::where('id', $columnId)
                ->where('project_id', $projectId)
                ->update(['sort_order' => $order]);
        }

        return true;
    }

    // Gantt Chart Data
    public function getGanttData(int $projectId, int $userId): ?array
    {
        $project = GrowBizProjectModel::where('id', $projectId)
            ->where('manager_id', $userId)
            ->with(['milestones', 'tasks.dependencies.dependsOnTask', 'tasks.assignees'])
            ->first();

        if (!$project) return null;

        $tasks = $project->tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'name' => $task->title,
                'start' => $task->started_at?->format('Y-m-d') ?? $task->created_at->format('Y-m-d'),
                'end' => $task->due_date?->format('Y-m-d') ?? $task->created_at->addDays(7)->format('Y-m-d'),
                'progress' => $task->progress_percentage,
                'status' => $task->status,
                'priority' => $task->priority,
                'assignees' => $task->assignees->pluck('first_name')->toArray(),
                'dependencies' => $task->dependencies->map(fn($d) => [
                    'task_id' => $d->depends_on_task_id,
                    'type' => $d->type,
                    'lag' => $d->lag_days,
                ])->toArray(),
                'milestone_id' => $task->milestone_id,
                'parent_id' => $task->parent_task_id,
            ];
        });

        $milestones = $project->milestones->map(fn($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'date' => $m->due_date?->format('Y-m-d'),
            'status' => $m->status,
        ]);

        return [
            'project' => $this->mapProject($project),
            'tasks' => $tasks->toArray(),
            'milestones' => $milestones->toArray(),
            'start_date' => $project->start_date?->format('Y-m-d'),
            'end_date' => $project->end_date?->format('Y-m-d'),
        ];
    }

    // Milestones
    public function createMilestone(int $projectId, int $userId, array $data): ?array
    {
        $project = GrowBizProjectModel::where('id', $projectId)->where('manager_id', $userId)->first();
        if (!$project) return null;

        $maxOrder = GrowBizMilestoneModel::where('project_id', $projectId)->max('sort_order') ?? -1;

        $milestone = GrowBizMilestoneModel::create([
            'project_id' => $projectId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        return $this->mapMilestone($milestone);
    }

    public function updateMilestone(int $milestoneId, int $userId, array $data): ?array
    {
        $milestone = GrowBizMilestoneModel::whereHas('project', fn($q) => $q->where('manager_id', $userId))
            ->where('id', $milestoneId)
            ->first();

        if (!$milestone) return null;

        $milestone->update($data);
        return $this->mapMilestone($milestone->fresh());
    }

    public function deleteMilestone(int $milestoneId, int $userId): bool
    {
        return GrowBizMilestoneModel::whereHas('project', fn($q) => $q->where('manager_id', $userId))
            ->where('id', $milestoneId)
            ->delete() > 0;
    }

    // Task Dependencies
    public function addDependency(int $taskId, int $dependsOnTaskId, int $userId, string $type = 'finish_to_start', int $lagDays = 0): ?array
    {
        $task = GrowBizTaskModel::where('id', $taskId)->where('manager_id', $userId)->first();
        $dependsOn = GrowBizTaskModel::where('id', $dependsOnTaskId)->where('manager_id', $userId)->first();

        if (!$task || !$dependsOn) return null;

        // Prevent circular dependencies
        if ($this->wouldCreateCircularDependency($taskId, $dependsOnTaskId)) {
            return null;
        }

        $dependency = GrowBizTaskDependencyModel::updateOrCreate(
            ['task_id' => $taskId, 'depends_on_task_id' => $dependsOnTaskId],
            ['type' => $type, 'lag_days' => $lagDays]
        );

        return [
            'id' => $dependency->id,
            'task_id' => $dependency->task_id,
            'depends_on_task_id' => $dependency->depends_on_task_id,
            'type' => $dependency->type,
            'lag_days' => $dependency->lag_days,
        ];
    }

    public function removeDependency(int $taskId, int $dependsOnTaskId, int $userId): bool
    {
        $task = GrowBizTaskModel::where('id', $taskId)->where('manager_id', $userId)->first();
        if (!$task) return false;

        return GrowBizTaskDependencyModel::where('task_id', $taskId)
            ->where('depends_on_task_id', $dependsOnTaskId)
            ->delete() > 0;
    }

    private function wouldCreateCircularDependency(int $taskId, int $dependsOnTaskId): bool
    {
        $visited = [$taskId];
        $queue = [$dependsOnTaskId];

        while (!empty($queue)) {
            $current = array_shift($queue);
            if ($current === $taskId) return true;
            if (in_array($current, $visited)) continue;

            $visited[] = $current;
            $dependencies = GrowBizTaskDependencyModel::where('task_id', $current)->pluck('depends_on_task_id')->toArray();
            $queue = array_merge($queue, $dependencies);
        }

        return false;
    }

    // Statistics
    public function getProjectStatistics(int $projectId, int $userId): ?array
    {
        $project = GrowBizProjectModel::where('id', $projectId)
            ->where('manager_id', $userId)
            ->first();

        if (!$project) return null;

        $tasks = GrowBizTaskModel::where('project_id', $projectId);
        $total = (clone $tasks)->count();
        $completed = (clone $tasks)->where('status', 'completed')->count();
        $inProgress = (clone $tasks)->where('status', 'in_progress')->count();
        $overdue = (clone $tasks)->where('due_date', '<', now())->whereNotIn('status', ['completed', 'cancelled'])->count();

        $totalHours = (clone $tasks)->sum('estimated_hours');
        $actualHours = (clone $tasks)->sum('actual_hours');

        return [
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'in_progress_tasks' => $inProgress,
            'overdue_tasks' => $overdue,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'estimated_hours' => (float) $totalHours,
            'actual_hours' => (float) $actualHours,
            'hours_variance' => (float) ($actualHours - $totalHours),
            'days_remaining' => $project->days_remaining,
            'is_overdue' => $project->is_overdue,
        ];
    }

    // Mapping helpers
    private function mapProject($project, bool $detailed = false): array
    {
        $data = [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'color' => $project->color,
            'status' => $project->status,
            'status_label' => $project->status_label,
            'status_color' => $project->status_color,
            'start_date' => $project->start_date?->format('Y-m-d'),
            'end_date' => $project->end_date?->format('Y-m-d'),
            'budget' => (float) $project->budget,
            'currency' => $project->currency,
            'progress_percentage' => $project->progress_percentage,
            'task_count' => $project->tasks_count ?? $project->tasks()->count(),
            'completed_task_count' => $project->completed_task_count,
            'days_remaining' => $project->days_remaining,
            'is_overdue' => $project->is_overdue,
            'created_at' => $project->created_at->format('Y-m-d H:i:s'),
        ];

        if ($detailed) {
            $data['columns'] = $project->columns->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'color' => $c->color,
                'sort_order' => $c->sort_order,
                'wip_limit' => $c->wip_limit,
                'is_done_column' => $c->is_done_column,
            ])->toArray();

            $data['milestones'] = $project->milestones->map(fn($m) => $this->mapMilestone($m))->toArray();
            $data['members'] = $project->members->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->first_name . ' ' . $m->last_name,
                'role' => $m->pivot->role,
            ])->toArray();
        }

        return $data;
    }

    private function mapTaskForKanban($task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'status' => $task->status,
            'due_date' => $task->due_date?->format('Y-m-d'),
            'is_overdue' => $task->due_date && $task->due_date->isPast() && $task->status !== 'completed',
            'progress_percentage' => $task->progress_percentage,
            'assignees' => $task->assignees->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->first_name . ' ' . $a->last_name,
                'initials' => strtoupper(substr($a->first_name, 0, 1) . substr($a->last_name, 0, 1)),
            ])->toArray(),
            'subtask_count' => $task->subtasks->count(),
            'completed_subtask_count' => $task->subtasks->where('status', 'completed')->count(),
            'tags' => $task->tags ?? [],
            'kanban_order' => $task->kanban_order,
        ];
    }

    private function mapMilestone($milestone): array
    {
        return [
            'id' => $milestone->id,
            'name' => $milestone->name,
            'description' => $milestone->description,
            'due_date' => $milestone->due_date?->format('Y-m-d'),
            'status' => $milestone->status,
            'status_color' => $milestone->status_color,
            'is_overdue' => $milestone->is_overdue,
            'progress' => $milestone->progress,
            'completed_at' => $milestone->completed_at?->format('Y-m-d H:i:s'),
        ];
    }
}
