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
            'comments.user',
            'attachments.uploader',
            'timeEntries.user',
            'dependencies.dependsOnTask',
            'watchers.user',
            'template',
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

    // Kanban Board
    public function kanban(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;

        $workflows = WorkflowModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        $workflowId = $request->workflow_id;

        // Get stages
        $stages = WorkflowStageModel::when($workflowId, function ($q) use ($workflowId) {
                $q->where('workflow_id', $workflowId);
            })
            ->when(!$workflowId, function ($q) use ($companyId) {
                $q->whereHas('workflow', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)->where('is_active', true);
                });
            })
            ->orderBy('sequence_order')
            ->get();

        // Get tasks
        $tasks = TaskModel::where('company_id', $companyId)
            ->active()
            ->with(['assignedUser', 'workflowStage'])
            ->withCount(['comments', 'attachments'])
            ->when($workflowId, fn($q) => $q->where('workflow_id', $workflowId))
            ->get();

        return Inertia::render('CMS/Operations/Kanban', [
            'workflows' => $workflows,
            'stages' => $stages,
            'tasks' => $tasks,
        ]);
    }

    // Task Comments
    public function storeComment(Request $request, int $taskId)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:cms_task_comments,id',
            'is_internal' => 'boolean',
        ]);

        $task = TaskModel::findOrFail($taskId);
        
        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        // Update last activity
        $task->update(['last_activity_at' => now()]);

        return back()->with('success', 'Comment added');
    }

    // Task Attachments
    public function storeAttachment(Request $request, int $taskId)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string',
        ]);

        $task = TaskModel::findOrFail($taskId);
        
        $file = $request->file('file');
        $path = $file->store('task-attachments', 'public');

        $attachment = $task->attachments()->create([
            'uploaded_by' => auth()->id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => $validated['description'] ?? null,
        ]);

        // Update last activity
        $task->update(['last_activity_at' => now()]);

        return back()->with('success', 'File attached');
    }

    public function deleteAttachment(int $taskId, int $attachmentId)
    {
        $attachment = \App\Infrastructure\Persistence\Eloquent\CMS\TaskAttachmentModel::findOrFail($attachmentId);
        
        // Delete file from storage
        \Storage::disk('public')->delete($attachment->file_path);
        
        $attachment->delete();

        return back()->with('success', 'Attachment deleted');
    }

    // Task Time Entries
    public function storeTimeEntry(Request $request, int $taskId)
    {
        $validated = $request->validate([
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after:started_at',
            'hours' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_billable' => 'boolean',
        ]);

        $task = TaskModel::findOrFail($taskId);

        // Calculate hours if not provided
        if (!isset($validated['hours']) && isset($validated['ended_at'])) {
            $start = \Carbon\Carbon::parse($validated['started_at']);
            $end = \Carbon\Carbon::parse($validated['ended_at']);
            $validated['hours'] = $end->diffInHours($start, true);
        }

        $timeEntry = $task->timeEntries()->create([
            'user_id' => auth()->id(),
            'started_at' => $validated['started_at'],
            'ended_at' => $validated['ended_at'] ?? null,
            'hours' => $validated['hours'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_billable' => $validated['is_billable'] ?? true,
        ]);

        return back()->with('success', 'Time entry recorded');
    }

    public function stopTimeEntry(int $taskId, int $entryId)
    {
        $entry = \App\Infrastructure\Persistence\Eloquent\CMS\TaskTimeEntryModel::findOrFail($entryId);
        
        $entry->update([
            'ended_at' => now(),
            'hours' => now()->diffInHours($entry->started_at, true),
        ]);

        return back()->with('success', 'Timer stopped');
    }

    // Task Templates
    public function templates(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;

        $templates = \App\Infrastructure\Persistence\Eloquent\CMS\TaskTemplateModel::where('company_id', $companyId)
            ->with('workflow')
            ->get();

        return Inertia::render('CMS/Operations/Templates/Index', [
            'templates' => $templates,
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:task,order,job,project_task,maintenance,inspection',
            'priority' => 'required|in:low,medium,high,urgent',
            'workflow_id' => 'nullable|exists:cms_workflows,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'checklist_items' => 'nullable|array',
            'default_assignees' => 'nullable|array',
        ]);

        $companyId = $request->user()->currentCompany->id;
        $validated['company_id'] = $companyId;

        $template = \App\Infrastructure\Persistence\Eloquent\CMS\TaskTemplateModel::create($validated);

        return redirect()->route('cms.operations.templates.index')
            ->with('success', 'Template created');
    }

    public function createFromTemplate(Request $request, int $templateId)
    {
        $template = \App\Infrastructure\Persistence\Eloquent\CMS\TaskTemplateModel::findOrFail($templateId);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $taskData = [
            'title' => $validated['title'],
            'description' => $template->description,
            'type' => $template->type,
            'priority' => $template->priority,
            'workflow_id' => $template->workflow_id,
            'estimated_hours' => $template->estimated_hours,
            'due_date' => $validated['due_date'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'template_id' => $template->id,
        ];

        $companyId = $request->user()->currentCompany->id;
        $task = $this->operationsService->createTask($companyId, $taskData);

        return redirect()->route('cms.operations.tasks.show', $task->id)
            ->with('success', 'Task created from template');
    }

    // Recurring Tasks
    public function recurringTasks(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;

        $recurringTasks = \App\Infrastructure\Persistence\Eloquent\CMS\RecurringTaskModel::where('company_id', $companyId)
            ->with(['template', 'assignedUser'])
            ->get();

        return Inertia::render('CMS/Operations/RecurringTasks/Index', [
            'recurringTasks' => $recurringTasks,
        ]);
    }

    public function storeRecurringTask(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:task,order,job,project_task,maintenance,inspection',
            'priority' => 'required|in:low,medium,high,urgent',
            'workflow_id' => 'nullable|exists:cms_workflows,id',
            'assigned_to' => 'nullable|exists:users,id',
            'template_id' => 'nullable|exists:cms_task_templates,id',
            'recurrence_pattern' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'recurrence_interval' => 'required|integer|min:1',
            'recurrence_days' => 'nullable|array',
            'recurrence_day_of_month' => 'nullable|integer|min:1|max:31',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $companyId = $request->user()->currentCompany->id;
        $validated['company_id'] = $companyId;

        // Calculate next generation date
        $validated['next_generation_at'] = \Carbon\Carbon::parse($validated['start_date']);

        $recurringTask = \App\Infrastructure\Persistence\Eloquent\CMS\RecurringTaskModel::create($validated);

        return redirect()->route('cms.operations.recurring-tasks.index')
            ->with('success', 'Recurring task created');
    }

    public function toggleRecurringTask(int $id)
    {
        $recurringTask = \App\Infrastructure\Persistence\Eloquent\CMS\RecurringTaskModel::findOrFail($id);
        
        $recurringTask->update([
            'is_active' => !$recurringTask->is_active,
        ]);

        return back()->with('success', 'Recurring task ' . ($recurringTask->is_active ? 'activated' : 'deactivated'));
    }

    // Task Dependencies
    public function storeDependency(Request $request, int $taskId)
    {
        $validated = $request->validate([
            'depends_on_task_id' => 'required|exists:cms_tasks,id',
            'dependency_type' => 'required|in:finish_to_start,start_to_start,finish_to_finish,start_to_finish',
            'lag_days' => 'nullable|integer',
        ]);

        $task = TaskModel::findOrFail($taskId);
        
        $task->dependencies()->create($validated);

        return back()->with('success', 'Dependency added');
    }

    public function deleteDependency(int $taskId, int $dependencyId)
    {
        $dependency = \App\Infrastructure\Persistence\Eloquent\CMS\TaskDependencyModel::findOrFail($dependencyId);
        $dependency->delete();

        return back()->with('success', 'Dependency removed');
    }

    // Task Watchers
    public function addWatcher(Request $request, int $taskId)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $task = TaskModel::findOrFail($taskId);
        
        $task->watchers()->firstOrCreate([
            'user_id' => $validated['user_id'],
        ]);

        return back()->with('success', 'Watcher added');
    }

    public function removeWatcher(int $taskId, int $watcherId)
    {
        $watcher = \App\Infrastructure\Persistence\Eloquent\CMS\TaskWatcherModel::findOrFail($watcherId);
        $watcher->delete();

        return back()->with('success', 'Watcher removed');
    }

    // Move task to different stage (Kanban)
    public function moveTask(Request $request, int $taskId)
    {
        $validated = $request->validate([
            'workflow_stage_id' => 'required|exists:cms_workflow_stages,id',
        ]);

        $task = TaskModel::findOrFail($taskId);
        $oldStage = $task->workflowStage;
        
        $task->update([
            'workflow_stage_id' => $validated['workflow_stage_id'],
            'last_activity_at' => now(),
        ]);

        $newStage = $task->fresh()->workflowStage;

        $task->logs()->create([
            'user_id' => auth()->id(),
            'action' => 'stage_changed',
            'note' => "Moved from {$oldStage->name} to {$newStage->name}",
            'changes' => [
                'old_stage' => $oldStage->name,
                'new_stage' => $newStage->name,
            ],
        ]);

        return back()->with('success', 'Task moved');
    }
}
