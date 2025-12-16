<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\TaskService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function __construct(protected TaskService $taskService) {}

    public function index(Request $request)
    {
        $userId = auth()->id();
        $filters = $request->only(['is_completed', 'priority', 'due_date']);

        return Inertia::render('LifePlus/Tasks/Index', [
            'tasks' => $this->taskService->getTasks($userId, $filters),
            'stats' => $this->taskService->getStats($userId),
            'filters' => $filters,
        ]);
    }

    public function today()
    {
        $userId = auth()->id();

        return Inertia::render('LifePlus/Tasks/Today', [
            'tasks' => $this->taskService->getTodayTasks($userId),
            'stats' => $this->taskService->getStats($userId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'local_id' => 'nullable|string',
        ]);

        $task = $this->taskService->createTask(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($task, 201);
        }

        return back()->with('success', 'Task created');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
        ]);

        $task = $this->taskService->updateTask($id, auth()->id(), $validated);

        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        if ($request->wantsJson()) {
            return response()->json($task);
        }

        return back()->with('success', 'Task updated');
    }

    public function toggle(int $id)
    {
        $task = $this->taskService->toggleTask($id, auth()->id());

        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        return response()->json($task);
    }

    public function destroy(int $id)
    {
        $deleted = $this->taskService->deleteTask($id, auth()->id());

        if (!$deleted) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        return back()->with('success', 'Task deleted');
    }

    public function sync(Request $request)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.title' => 'required|string|max:255',
            'tasks.*.local_id' => 'required|string',
        ]);

        $synced = $this->taskService->syncTasks(auth()->id(), $validated['tasks']);

        return response()->json(['synced' => $synced]);
    }

    public function calendar(Request $request)
    {
        $userId = auth()->id();
        $month = $request->get('month', now()->format('Y-m'));

        return Inertia::render('LifePlus/Tasks/Calendar', [
            'tasks' => $this->taskService->getTasksForMonth($userId, $month),
            'month' => $month,
        ]);
    }
}
