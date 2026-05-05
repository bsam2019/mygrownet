<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Operations\Services\OperationsService;
use App\Infrastructure\Persistence\Eloquent\CMS\TaskModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkflowModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OperationsController extends Controller
{
    public function __construct(
        private OperationsService $operationsService
    ) {}

    // Dashboard
    public function dashboard(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;

        $statistics = $this->operationsService->getTaskStatistics($companyId);
        $workload = $this->operationsService->getWorkloadByUser($companyId);
        $bottlenecks = $this->operationsService->detectBottlenecks($companyId);

        $recentTasks = TaskModel::where('company_id', $companyId)
            ->with(['assignedUser', 'workflowStage', 'creator'])
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('CMS/Operations/Dashboard', [
            'statistics' => $statistics,
            'workload' => $workload,
            'bottlenecks' => $bottlenecks,
            'recentTasks' => $recentTasks,
        ]);
    }

    // Tasks List
    public function index(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;

        $tasks = TaskModel::where('company_id', $companyId)
            ->with(['assignedUser', 'workflowStage', 'workflow', 'creator'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->priority, fn($q, $priority) => $q->where('priority', $priority))
            ->when($request->assigned_to, fn($q, $userId) => $q->where('assigned_to', $userId))
            ->when($request->workflow_id, fn($q, $workflowId) => $q->where('workflow_id', $workflowId))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                          ->orWhere('task_number', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);

        $workflows = WorkflowModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        $users = \App\Models\User::whereHas('cmsCompanies', function ($q) use ($companyId) {
                $q->where('cms_companies.id', $companyId);
            })
            ->get(['id', 'name']);

        return Inertia::render('CMS/Operations/Tasks/Index', [
            'tasks' => $tasks,
            'workflows' => $workflows,
            'users' => $users,
            'filters' => $request->only(['status', 'priority', 'assigned_to', 'workflow_id', 'search']),
        ]);
    }

    // Create Task
    public function create(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;

        $workflows = WorkflowModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->with('stages')
            ->get();

        $users = \App\Models\User::whereHas('cmsCompanies', function ($q) use ($companyId) {
                $q->where('cms_companies.id', $companyId);
            })
            ->get(['id', 'name']);

        return Inertia::render('CMS/Operations/Tasks/Create', [
            'workflows' => $workflows,
            'users' => $users,
        ]);
    }

    // Store Task
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:task,order,job,project_task,maintenance,inspection',
            'priority' => 'required|in:low,medium,high,urgent',
            'workflow_id' => 'nullable|exists:cms_workflows,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'project_id' => 'nullable|exists:cms_projects,id',
        ]);

        $companyId = $request->user()->currentCompany->id;
        $task = $this->operationsService->createTask($companyId, $validated);

        return redirect()->route('cms.operations.tasks.show', $task->id)
            ->with('success', 'Task created successfully');
    }

    // Show Task
    public function show(int $id): Response
    {
        $task = TaskModel::with([
            'workflow.stages',
            'workflowStage',
            'assignedUser',
            'creator',
            'logs.user',
            'issues.reporter',
            'checklistResponses',
            'job',
            'project',
        ])->findOrFail($id);

        return Inertia::render('CMS/Operations/Tasks/Show', [
            'task' => $task,
        ]);
    }

    // Update Task
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
        ]);

        $task = $this->operationsService->updateTask($id, $validated);

        return back()->with('success', 'Task updated successfully');
    }

    // Start Task
    public function start(int $id)
    {
        $task = $this->operationsService->startTask($id);

        return back()->with('success', 'Task started');
    }

    // Complete Task
    public function complete(Request $request, int $id)
    {
        $validated = $request->validate([
            'actual_hours' => 'nullable|numeric|min:0',
        ]);

        $task = $this->operationsService->completeTask($id, $validated['actual_hours'] ?? null);

        return back()->with('success', 'Task completed');
    }

    // Block Task
    public function block(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $task = $this->operationsService->blockTask($id, $validated['reason']);

        return back()->with('success', 'Task blocked');
    }

    // Unblock Task
    public function unblock(int $id)
    {
        $task = $this->operationsService->unblockTask($id);

        return back()->with('success', 'Task unblocked');
    }

    // Reassign Task
    public function reassign(Request $request, int $id)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $task = $this->operationsService->reassignTask($id, $validated['assigned_to']);

        return back()->with('success', 'Task reassigned');
    }

    // My Tasks (Worker View)
    public function myTasks(Request $request): Response
    {
        $userId = $request->user()->id;
        $companyId = $request->user()->currentCompany->id;

        $tasks = TaskModel::where('company_id', $companyId)
            ->where('assigned_to', $userId)
            ->with(['workflowStage', 'workflow'])
            ->when($request->filter === 'today', fn($q) => $q->dueToday())
            ->when($request->filter === 'overdue', fn($q) => $q->overdue())
            ->when($request->filter === 'active', fn($q) => $q->active())
            ->latest()
            ->paginate(20);

        $statistics = [
            'total' => TaskModel::where('assigned_to', $userId)->active()->count(),
            'due_today' => TaskModel::where('assigned_to', $userId)->dueToday()->count(),
            'overdue' => TaskModel::where('assigned_to', $userId)->overdue()->count(),
        ];

        return Inertia::render('CMS/Operations/MyTasks', [
            'tasks' => $tasks,
            'statistics' => $statistics,
            'filter' => $request->filter,
        ]);
    }

    // Workflows Management
    public function workflows(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;

        $workflows = WorkflowModel::where('company_id', $companyId)
            ->withCount('tasks')
            ->with('stages')
            ->get();

        return Inertia::render('CMS/Operations/Workflows/Index', [
            'workflows' => $workflows,
        ]);
    }

    // Planning Dashboard
    public function planning(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;

        $workload = $this->operationsService->getWorkloadByUser($companyId);
        $bottlenecks = $this->operationsService->detectBottlenecks($companyId);

        // Get tasks for calendar view
        $tasks = TaskModel::where('company_id', $companyId)
            ->active()
            ->with(['assignedUser', 'workflowStage'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()->addWeeks(2)])
            ->get();

        return Inertia::render('CMS/Operations/Planning', [
            'workload' => $workload,
            'bottlenecks' => $bottlenecks,
            'tasks' => $tasks,
        ]);
    }
}
