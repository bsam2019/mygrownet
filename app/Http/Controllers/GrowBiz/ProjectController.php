<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Domain\GrowBiz\Services\ProjectService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService
    ) {}

    // Projects List
    public function index(Request $request)
    {
        $userId = auth()->id();
        $filters = $request->only(['status', 'search']);

        return Inertia::render('GrowBiz/Projects/Index', [
            'projects' => $this->projectService->getProjects($userId, $filters),
            'filters' => $filters,
        ]);
    }

    // Create Project
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'color' => 'nullable|string|max:7',
            'status' => 'nullable|in:planning,active,on_hold,completed,archived',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
        ]);

        $project = $this->projectService->createProject(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($project, 201);
        }

        return redirect()->route('growbiz.projects.kanban', $project['id'])
            ->with('success', 'Project created successfully');
    }

    // Project Detail
    public function show(int $id)
    {
        $project = $this->projectService->getProjectById($id, auth()->id());

        if (!$project) {
            return redirect()->route('growbiz.projects.index')
                ->with('error', 'Project not found');
        }

        return Inertia::render('GrowBiz/Projects/Show', [
            'project' => $project,
            'statistics' => $this->projectService->getProjectStatistics($id, auth()->id()),
        ]);
    }

    // Update Project
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'color' => 'nullable|string|max:7',
            'status' => 'nullable|in:planning,active,on_hold,completed,archived',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
        ]);

        $project = $this->projectService->updateProject($id, auth()->id(), $validated);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        if ($request->wantsJson()) {
            return response()->json($project);
        }

        return back()->with('success', 'Project updated');
    }

    // Delete Project
    public function destroy(int $id)
    {
        $deleted = $this->projectService->deleteProject($id, auth()->id());

        if (request()->wantsJson()) {
            return response()->json(['success' => $deleted]);
        }

        return redirect()->route('growbiz.projects.index')
            ->with($deleted ? 'success' : 'error', $deleted ? 'Project deleted' : 'Failed to delete');
    }

    // Kanban Board View
    public function kanban(int $id)
    {
        $board = $this->projectService->getKanbanBoard($id, auth()->id());

        if (!$board) {
            return redirect()->route('growbiz.projects.index')
                ->with('error', 'Project not found');
        }

        return Inertia::render('GrowBiz/Projects/Kanban', [
            'project' => $board['project'],
            'columns' => $board['columns'],
        ]);
    }

    // Move Task on Kanban
    public function moveTask(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'task_id' => 'required|integer',
            'column_id' => 'required|integer',
            'position' => 'required|integer|min:0',
        ]);

        $success = $this->projectService->moveTask(
            $validated['task_id'],
            auth()->id(),
            $validated['column_id'],
            $validated['position']
        );

        return response()->json(['success' => $success]);
    }

    // Gantt Chart View
    public function gantt(int $id)
    {
        $data = $this->projectService->getGanttData($id, auth()->id());

        if (!$data) {
            return redirect()->route('growbiz.projects.index')
                ->with('error', 'Project not found');
        }

        return Inertia::render('GrowBiz/Projects/Gantt', [
            'project' => $data['project'],
            'tasks' => $data['tasks'],
            'milestones' => $data['milestones'],
            'startDate' => $data['start_date'],
            'endDate' => $data['end_date'],
        ]);
    }

    // === COLUMNS ===
    public function storeColumn(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
            'wip_limit' => 'nullable|integer|min:1',
            'is_done_column' => 'boolean',
        ]);

        $column = $this->projectService->createColumn($projectId, auth()->id(), $validated);

        if (!$column) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        return response()->json($column, 201);
    }

    public function updateColumn(Request $request, int $projectId, int $columnId)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'color' => 'nullable|string|max:7',
            'wip_limit' => 'nullable|integer|min:1',
            'is_done_column' => 'boolean',
        ]);

        $column = $this->projectService->updateColumn($columnId, auth()->id(), $validated);

        if (!$column) {
            return response()->json(['error' => 'Column not found'], 404);
        }

        return response()->json($column);
    }

    public function destroyColumn(int $projectId, int $columnId)
    {
        $deleted = $this->projectService->deleteColumn($columnId, auth()->id());
        return response()->json(['success' => $deleted]);
    }

    public function reorderColumns(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'column_ids' => 'required|array',
            'column_ids.*' => 'integer',
        ]);

        $success = $this->projectService->reorderColumns($projectId, auth()->id(), $validated['column_ids']);
        return response()->json(['success' => $success]);
    }

    // === MILESTONES ===
    public function storeMilestone(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date' => 'nullable|date',
        ]);

        $milestone = $this->projectService->createMilestone($projectId, auth()->id(), $validated);

        if (!$milestone) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        return response()->json($milestone, 201);
    }

    public function updateMilestone(Request $request, int $projectId, int $milestoneId)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,completed',
        ]);

        $milestone = $this->projectService->updateMilestone($milestoneId, auth()->id(), $validated);

        if (!$milestone) {
            return response()->json(['error' => 'Milestone not found'], 404);
        }

        return response()->json($milestone);
    }

    public function destroyMilestone(int $projectId, int $milestoneId)
    {
        $deleted = $this->projectService->deleteMilestone($milestoneId, auth()->id());
        return response()->json(['success' => $deleted]);
    }

    // === DEPENDENCIES ===
    public function addDependency(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'task_id' => 'required|integer',
            'depends_on_task_id' => 'required|integer|different:task_id',
            'type' => 'nullable|in:finish_to_start,start_to_start,finish_to_finish,start_to_finish',
            'lag_days' => 'nullable|integer|min:0',
        ]);

        $dependency = $this->projectService->addDependency(
            $validated['task_id'],
            $validated['depends_on_task_id'],
            auth()->id(),
            $validated['type'] ?? 'finish_to_start',
            $validated['lag_days'] ?? 0
        );

        if (!$dependency) {
            return response()->json(['error' => 'Invalid dependency or circular reference'], 422);
        }

        return response()->json($dependency, 201);
    }

    public function removeDependency(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'task_id' => 'required|integer',
            'depends_on_task_id' => 'required|integer',
        ]);

        $success = $this->projectService->removeDependency(
            $validated['task_id'],
            $validated['depends_on_task_id'],
            auth()->id()
        );

        return response()->json(['success' => $success]);
    }

    // Statistics
    public function statistics(int $id)
    {
        $stats = $this->projectService->getProjectStatistics($id, auth()->id());

        if (!$stats) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        return response()->json($stats);
    }
}
