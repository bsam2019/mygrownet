<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Domain\GrowBiz\Services\TaskManagementService;
use App\Domain\GrowBiz\ValueObjects\TaskStatus;
use App\Domain\GrowBiz\ValueObjects\TaskPriority;
use App\Domain\GrowBiz\Exceptions\InvalidAssignmentException;
use App\Domain\GrowBiz\Exceptions\OperationFailedException;
use App\Domain\GrowBiz\Exceptions\TaskNotFoundException;
use App\Domain\GrowBiz\Exceptions\UnauthorizedAccessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function __construct(
        private TaskManagementService $taskService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = [
            'status' => $request->get('status'),
            'priority' => $request->get('priority'),
            'assigned_to' => $request->get('assigned_to'),
            'search' => $request->get('search'),
        ];

        // Check if user is an employee (not a business owner)
        $employeeRecord = \App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        
        $isOwner = \App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel::where('manager_id', $user->id)->exists();

        if ($employeeRecord && !$isOwner) {
            // Employee: show only tasks assigned to them
            $tasks = $this->taskService->getTasksAssignedToEmployee($employeeRecord->id);
            $stats = $this->calculateEmployeeTaskStats($tasks);
            $userRole = 'employee';
        } else {
            // Owner: show all tasks
            $tasks = $this->taskService->getTasksForUser($user->id, $filters);
            $stats = $this->taskService->getTaskStatistics($user->id);
            $userRole = 'owner';
        }

        // Convert Task entities to arrays for the frontend
        $tasksArray = array_map(fn($task) => $task->toArray(), $tasks);

        return Inertia::render('GrowBiz/Tasks/Index', [
            'tasks' => $tasksArray,
            'stats' => $stats,
            'filters' => $filters,
            'statuses' => TaskStatus::all(),
            'priorities' => TaskPriority::all(),
            'userRole' => $userRole,
        ]);
    }

    private function calculateEmployeeTaskStats(array $tasks): array
    {
        $total = count($tasks);
        $pending = 0;
        $inProgress = 0;
        $completed = 0;
        $overdue = 0;

        foreach ($tasks as $task) {
            $status = $task->status()->value();
            
            if ($status === 'pending') $pending++;
            elseif ($status === 'in_progress') $inProgress++;
            elseif ($status === 'completed') $completed++;
            
            if ($task->isOverdue() && $status !== 'completed') {
                $overdue++;
            }
        }

        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'overdue' => $overdue,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    public function create()
    {
        $user = Auth::user();
        
        // Check if user can create tasks (must be owner or supervisor)
        $isOwner = \App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel::where('manager_id', $user->id)->exists();
        $employeeRecord = \App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        
        if (!$isOwner && $employeeRecord && !$employeeRecord->hasSupervisorRole()) {
            // Regular employee cannot create tasks
            return redirect()->route('growbiz.tasks.index')
                ->with('error', 'You do not have permission to create tasks.');
        }
        
        $employees = $this->taskService->getAvailableAssignees($user->id);

        // Convert Employee entities to simple arrays for the frontend
        $employeesArray = array_map(fn($employee) => [
            'id' => $employee->id(),
            'name' => $employee->getName(),
        ], $employees);

        return Inertia::render('GrowBiz/Tasks/Create', [
            'employees' => $employeesArray,
            'priorities' => TaskPriority::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'due_date' => 'nullable|date|after:today',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:growbiz_employees,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
        ]);

        $user = Auth::user();
        
        try {
            $task = $this->taskService->createTask(
                ownerId: $user->id,
                title: $validated['title'],
                description: $validated['description'] ?? null,
                priority: $validated['priority'],
                dueDate: $validated['due_date'] ?? null,
                assignedTo: $validated['assigned_to'] ?? [],
                estimatedHours: $validated['estimated_hours'] ?? null,
                tags: $validated['tags'] ?? []
            );

            return redirect()->route('growbiz.tasks.show', $task->id())
                ->with('success', 'Task created successfully.');
        } catch (InvalidAssignmentException $e) {
            return back()
                ->withInput()
                ->withErrors(['assigned_to' => $e->getMessage()]);
        } catch (OperationFailedException $e) {
            Log::error('Task creation failed in controller', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', 'Failed to create task. Please try again.');
        }
    }

    public function show(int $id)
    {
        try {
            $task = $this->taskService->getTaskById($id);
            $this->authorizeTask($task);

            $updates = $this->taskService->getTaskUpdates($id);
            $comments = $this->taskService->getTaskComments($id);

            return Inertia::render('GrowBiz/Tasks/Show', [
                'task' => $task->toArray(),
                'updates' => $updates,
                'comments' => $comments,
                'currentUserId' => Auth::id(),
                'statuses' => TaskStatus::all(),
                'priorities' => TaskPriority::all(),
            ]);
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        }
    }

    public function edit(int $id)
    {
        try {
            $task = $this->taskService->getTaskById($id);
            $this->authorizeTask($task);
            
            $user = Auth::user();
            $employees = $this->taskService->getAvailableAssignees($user->id);

            // Convert Employee entities to simple arrays for the frontend
            $employeesArray = array_map(fn($employee) => [
                'id' => $employee->id(),
                'name' => $employee->getName(),
            ], $employees);

            return Inertia::render('GrowBiz/Tasks/Edit', [
                'task' => $task->toArray(),
                'employees' => $employeesArray,
                'statuses' => TaskStatus::all(),
                'priorities' => TaskPriority::all(),
            ]);
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'status' => 'required|string|in:pending,in_progress,on_hold,completed,cancelled',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:growbiz_employees,id',
        ]);

        try {
            $task = $this->taskService->getTaskById($id);
            $this->authorizeTask($task);

            $this->taskService->updateTask($id, $validated);

            return redirect()->route('growbiz.tasks.show', $id)
                ->with('success', 'Task updated successfully.');
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (InvalidAssignmentException $e) {
            return back()
                ->withInput()
                ->withErrors(['assigned_to' => $e->getMessage()]);
        } catch (OperationFailedException $e) {
            Log::error('Task update failed in controller', [
                'task_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', 'Failed to update task. Please try again.');
        }
    }

    public function destroy(int $id)
    {
        try {
            $task = $this->taskService->getTaskById($id);
            $this->authorizeTask($task);

            $this->taskService->deleteTask($id);

            return redirect()->route('growbiz.tasks.index')
                ->with('success', 'Task deleted successfully.');
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (OperationFailedException $e) {
            Log::error('Task deletion failed in controller', [
                'task_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to delete task. Please try again.');
        }
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,in_progress,on_hold,completed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $task = $this->taskService->getTaskById($id);
            $this->authorizeTask($task);

            $user = Auth::user();
            $this->taskService->updateTaskStatus(
                $id, 
                $validated['status'], 
                $user->id,
                $validated['notes'] ?? null
            );

            return back()->with('success', 'Task status updated.');
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (OperationFailedException $e) {
            Log::error('Task status update failed in controller', [
                'task_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update status. Please try again.');
        }
    }

    public function updateProgress(Request $request, int $id)
    {
        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $task = $this->taskService->getTaskById($id);
            $this->authorizeTask($task);

            $user = Auth::user();
            $this->taskService->updateTaskProgress(
                $id,
                $validated['progress'],
                $user->id,
                $validated['notes'] ?? null
            );

            return back()->with('success', 'Task progress updated.');
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (OperationFailedException $e) {
            Log::error('Task progress update failed in controller', [
                'task_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update progress. Please try again.');
        }
    }

    public function logTime(Request $request, int $id)
    {
        $validated = $request->validate([
            'hours' => 'required|numeric|min:0.1|max:24',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $task = $this->taskService->getTaskById($id);
            $this->authorizeTask($task);

            $user = Auth::user();
            $this->taskService->logTime(
                $id,
                (float) $validated['hours'],
                $user->id,
                $validated['notes'] ?? null
            );

            return back()->with('success', 'Time logged successfully.');
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (OperationFailedException $e) {
            Log::error('Time logging failed in controller', [
                'task_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to log time. Please try again.');
        }
    }

    public function addNote(Request $request, int $id)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        try {
            $task = $this->taskService->getTaskById($id);
            $this->authorizeTask($task);

            $user = Auth::user();
            $this->taskService->addNote($id, $user->id, $validated['notes']);

            return back()->with('success', 'Note added.');
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (OperationFailedException $e) {
            Log::error('Note addition failed in controller', [
                'task_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to add note. Please try again.');
        }
    }

    public function getUpdates(int $id)
    {
        try {
            $task = $this->taskService->getTaskById($id);
            $this->authorizeTask($task);

            $updates = $this->taskService->getTaskUpdates($id);

            return response()->json(['updates' => $updates]);
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        }
    }

    public function addComment(Request $request, int $id)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        try {
            $task = $this->taskService->getTaskById($id);
            $this->authorizeTask($task);

            $user = Auth::user();
            $this->taskService->addComment($id, $user->id, $validated['content']);

            return back()->with('success', 'Comment added.');
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (OperationFailedException $e) {
            Log::error('Comment addition failed in controller', [
                'task_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to add comment. Please try again.');
        }
    }

    public function deleteComment(int $id, int $commentId)
    {
        try {
            $task = $this->taskService->getTaskById($id);
            $this->authorizeTask($task);

            $user = Auth::user();
            $this->taskService->deleteComment($id, $commentId, $user->id);

            return back()->with('success', 'Comment deleted.');
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (OperationFailedException $e) {
            Log::error('Comment deletion failed in controller', [
                'task_id' => $id,
                'comment_id' => $commentId,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to delete comment. Please try again.');
        }
    }

    /**
     * @throws UnauthorizedAccessException
     */
    private function authorizeTask($task): void
    {
        $user = Auth::user();
        
        if ($task->ownerId() === $user->id) {
            return;
        }

        // Check if user is an assigned employee
        $employeeRecord = \App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        
        if ($employeeRecord) {
            // Check if this employee is assigned to the task
            $isAssigned = \App\Infrastructure\Persistence\Eloquent\GrowBizTaskAssignmentModel::where('task_id', $task->id())
                ->where('employee_id', $employeeRecord->id)
                ->exists();
            
            if ($isAssigned) {
                return;
            }
        }

        if ($user->is_admin) {
            return;
        }

        throw new UnauthorizedAccessException('task', $task->id(), $user->id);
    }
}
