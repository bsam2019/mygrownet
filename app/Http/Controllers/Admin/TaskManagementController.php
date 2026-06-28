<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeTask;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TaskManagementController extends Controller
{
    /**
     * Display task management dashboard
     */
    public function index(Request $request)
    {
        $query = EmployeeTask::with(['assignee', 'assigner', 'department']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $tasks = $query->orderBy('due_date', 'asc')
            ->orderBy('priority', 'desc')
            ->paginate(20)
            ->through(fn($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'is_overdue' => $task->isOverdue(),
                'assignee' => $task->assignee ? [
                    'id' => $task->assignee->id,
                    'name' => $task->assignee->full_name,
                ] : null,
                'assigner' => $task->assigner ? [
                    'id' => $task->assigner->id,
                    'name' => $task->assigner->full_name,
                ] : null,
                'department' => $task->department?->name,
                'created_at' => $task->created_at->format('Y-m-d H:i'),
            ]);

        // Get stats
        $stats = [
            'total' => EmployeeTask::count(),
            'pending' => EmployeeTask::where('status', 'pending')->count(),
            'in_progress' => EmployeeTask::where('status', 'in_progress')->count(),
            'completed' => EmployeeTask::where('status', 'completed')->count(),
            'overdue' => EmployeeTask::overdue()->count(),
        ];

        // Get employees and departments for filters
        $employees = Employee::select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get()
            ->map(fn($e) => ['id' => $e->id, 'name' => $e->full_name]);

        $departments = Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Tasks/Index', [
            'tasks' => $tasks,
            'stats' => $stats,
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $request->only(['status', 'priority', 'department_id', 'assigned_to', 'search']),
        ]);
    }

    /**
     * Show create task form
     */
    public function create()
    {
        $employees = Employee::select('id', 'first_name', 'last_name', 'department_id')
            ->with('department:id,name')
            ->orderBy('first_name')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->full_name,
                'department' => $e->department?->name,
            ]);

        $departments = Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Tasks/Create', [
            'employees' => $employees,
            'departments' => $departments,
        ]);
    }

    /**
     * Store a new task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Get the admin's employee record if they have one, otherwise use null
        $adminEmployee = Employee::where('user_id', Auth::id())->first();
        $validated['assigned_by'] = $adminEmployee?->id;
        $validated['status'] = 'pending';

        $task = EmployeeTask::create($validated);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task created and assigned successfully.');
    }


    /**
     * Show task details
     */
    public function show(EmployeeTask $task)
    {
        $task->load(['assignee', 'assigner', 'department', 'comments.author', 'attachments']);

        return Inertia::render('Admin/Tasks/Show', [
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'started_at' => $task->started_at?->format('Y-m-d H:i'),
                'completed_at' => $task->completed_at?->format('Y-m-d H:i'),
                'estimated_hours' => $task->estimated_hours,
                'actual_hours' => $task->actual_hours,
                'is_overdue' => $task->isOverdue(),
                'tags' => $task->tags ?? [],
                'notes' => $task->notes,
                'assignee' => $task->assignee ? [
                    'id' => $task->assignee->id,
                    'name' => $task->assignee->full_name,
                    'email' => $task->assignee->email,
                ] : null,
                'assigner' => $task->assigner ? [
                    'id' => $task->assigner->id,
                    'name' => $task->assigner->full_name,
                ] : null,
                'department' => $task->department?->name,
                'comments' => $task->comments->map(fn($c) => [
                    'id' => $c->id,
                    'content' => $c->content,
                    'author' => $c->author?->full_name ?? 'System',
                    'created_at' => $c->created_at->format('Y-m-d H:i'),
                ]),
                'created_at' => $task->created_at->format('Y-m-d H:i'),
                'updated_at' => $task->updated_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * Show edit task form
     */
    public function edit(EmployeeTask $task)
    {
        $employees = Employee::select('id', 'first_name', 'last_name', 'department_id')
            ->with('department:id,name')
            ->orderBy('first_name')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->full_name,
                'department' => $e->department?->name,
            ]);

        $departments = Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Tasks/Edit', [
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'assigned_to' => $task->assigned_to,
                'department_id' => $task->department_id,
                'priority' => $task->priority,
                'status' => $task->status,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'estimated_hours' => $task->estimated_hours,
                'tags' => $task->tags ?? [],
                'notes' => $task->notes,
            ],
            'employees' => $employees,
            'departments' => $departments,
        ]);
    }

    /**
     * Update a task
     */
    public function update(Request $request, EmployeeTask $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled,on_hold',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Handle status changes
        if ($validated['status'] === 'in_progress' && $task->status !== 'in_progress') {
            $validated['started_at'] = now();
        }
        if ($validated['status'] === 'completed' && $task->status !== 'completed') {
            $validated['completed_at'] = now();
        }

        $task->update($validated);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Delete a task
     */
    public function destroy(EmployeeTask $task)
    {
        $task->delete();

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * Quick assign task to employee (AJAX)
     */
    public function quickAssign(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:employees,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        $adminEmployee = Employee::where('user_id', Auth::id())->first();
        $validated['assigned_by'] = $adminEmployee?->id;
        $validated['status'] = 'pending';

        $task = EmployeeTask::create($validated);

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
            ],
        ]);
    }

    /**
     * Bulk assign tasks
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.title' => 'required|string|max:255',
            'tasks.*.assigned_to' => 'required|exists:employees,id',
            'tasks.*.priority' => 'required|in:low,medium,high,urgent',
            'tasks.*.due_date' => 'nullable|date',
            'tasks.*.description' => 'nullable|string',
        ]);

        $adminEmployee = Employee::where('user_id', Auth::id())->first();
        $created = [];

        foreach ($validated['tasks'] as $taskData) {
            $taskData['assigned_by'] = $adminEmployee?->id;
            $taskData['status'] = 'pending';
            $created[] = EmployeeTask::create($taskData);
        }

        return response()->json([
            'success' => true,
            'count' => count($created),
        ]);
    }

    /**
     * Update task status (AJAX)
     */
    public function updateStatus(Request $request, EmployeeTask $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled,on_hold',
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'in_progress' && !$task->started_at) {
            $updates['started_at'] = now();
        }
        if ($validated['status'] === 'completed' && !$task->completed_at) {
            $updates['completed_at'] = now();
        }

        $task->update($updates);

        return response()->json(['success' => true]);
    }
}
