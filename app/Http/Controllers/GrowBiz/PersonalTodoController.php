<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Domain\GrowBiz\Services\PersonalTodoService;
use App\Domain\GrowBiz\ValueObjects\TodoStatus;
use App\Domain\GrowBiz\ValueObjects\TodoPriority;
use App\Domain\GrowBiz\Exceptions\OperationFailedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PersonalTodoController extends Controller
{
    public function __construct(
        private PersonalTodoService $todoService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = [
            'status' => $request->get('status'),
            'priority' => $request->get('priority'),
            'category' => $request->get('category'),
            'search' => $request->get('search'),
            'due_date' => $request->get('due_date'),
        ];

        $todos = $this->todoService->getTodosForUser($user->id, $filters);
        $stats = $this->todoService->getStatistics($user->id);
        $categories = $this->todoService->getCategories($user->id);

        $todosArray = array_map(fn($todo) => $todo->toArray(), $todos);

        return Inertia::render('GrowBiz/Todos/Index', [
            'todos' => $todosArray,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => $filters,
            'statuses' => TodoStatus::all(),
            'priorities' => TodoPriority::all(),
        ]);
    }

    public function today()
    {
        $user = Auth::user();
        
        $todayTodos = $this->todoService->getTodayTodos($user->id);
        $overdueTodos = $this->todoService->getOverdueTodos($user->id);
        $stats = $this->todoService->getStatistics($user->id);

        return Inertia::render('GrowBiz/Todos/Today', [
            'todayTodos' => array_map(fn($t) => $t->toArray(), $todayTodos),
            'overdueTodos' => array_map(fn($t) => $t->toArray(), $overdueTodos),
            'stats' => $stats,
            'priorities' => TodoPriority::all(),
        ]);
    }

    public function upcoming()
    {
        $user = Auth::user();
        
        $upcomingTodos = $this->todoService->getUpcomingTodos($user->id, 7);
        $stats = $this->todoService->getStatistics($user->id);

        return Inertia::render('GrowBiz/Todos/Upcoming', [
            'upcomingTodos' => array_map(fn($t) => $t->toArray(), $upcomingTodos),
            'stats' => $stats,
            'priorities' => TodoPriority::all(),
        ]);
    }

    public function completed()
    {
        $user = Auth::user();
        
        $completedTodos = $this->todoService->getCompletedTodos($user->id);
        $stats = $this->todoService->getStatistics($user->id);

        return Inertia::render('GrowBiz/Todos/Completed', [
            'completedTodos' => array_map(fn($t) => $t->toArray(), $completedTodos),
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'priority' => 'required|string|in:low,medium,high',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'is_recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'nullable|string|in:daily,weekly,monthly',
            'parent_id' => 'nullable|exists:personal_todos,id',
        ]);

        $user = Auth::user();

        try {
            $todo = $this->todoService->createTodo(
                userId: $user->id,
                title: $validated['title'],
                description: $validated['description'] ?? null,
                dueDate: $validated['due_date'] ?? null,
                dueTime: $validated['due_time'] ?? null,
                priority: $validated['priority'],
                category: $validated['category'] ?? null,
                tags: $validated['tags'] ?? [],
                isRecurring: $validated['is_recurring'] ?? false,
                recurrencePattern: $validated['recurrence_pattern'] ?? null,
                parentId: $validated['parent_id'] ?? null
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'todo' => $todo->toArray(),
                    'message' => 'Todo created successfully.',
                ]);
            }

            return back()->with('success', 'Todo created successfully.');
        } catch (OperationFailedException $e) {
            Log::error('Todo creation failed', ['error' => $e->getMessage()]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create todo.',
                ], 500);
            }

            return back()->with('error', 'Failed to create todo. Please try again.');
        }
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'priority' => 'sometimes|required|string|in:low,medium,high',
            'status' => 'sometimes|required|string|in:pending,in_progress,completed',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
        ]);

        $user = Auth::user();

        try {
            $todo = $this->todoService->updateTodo($id, $user->id, $validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'todo' => $todo->toArray(),
                    'message' => 'Todo updated successfully.',
                ]);
            }

            return back()->with('success', 'Todo updated successfully.');
        } catch (OperationFailedException $e) {
            Log::error('Todo update failed', ['todo_id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update todo.',
                ], 500);
            }

            return back()->with('error', 'Failed to update todo. Please try again.');
        }
    }

    public function toggle(Request $request, int $id)
    {
        $user = Auth::user();

        try {
            $todo = $this->todoService->toggleComplete($id, $user->id);
            $message = $todo->getStatus()->isCompleted() ? 'Todo completed!' : 'Todo reopened.';

            return back()->with('success', $message);
        } catch (OperationFailedException $e) {
            return back()->with('error', 'Failed to toggle todo.');
        }
    }

    public function destroy(int $id)
    {
        $user = Auth::user();

        try {
            $this->todoService->deleteTodo($id, $user->id);

            return back()->with('success', 'Todo deleted successfully.');
        } catch (OperationFailedException $e) {
            return back()->with('error', 'Failed to delete todo.');
        }
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'sorted_ids' => 'required|array',
            'sorted_ids.*' => 'integer|exists:personal_todos,id',
        ]);

        $user = Auth::user();

        try {
            $this->todoService->updateSortOrder($user->id, $validated['sorted_ids']);

            return back()->with('success', 'Order updated.');
        } catch (OperationFailedException $e) {
            return back()->with('error', 'Failed to update order.');
        }
    }

    public function stats()
    {
        $user = Auth::user();
        $stats = $this->todoService->getStatistics($user->id);

        return response()->json($stats);
    }
}
