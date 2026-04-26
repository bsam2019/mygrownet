<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Projects\Services\ProjectService;
use App\Infrastructure\Persistence\Eloquent\CMS\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ProjectMilestoneModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $projects = ProjectModel::where('company_id', $companyId)
            ->with(['customer', 'projectManager', 'jobs'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where(function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('project_number', 'like', "%{$request->search}%");
            }))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        
        return Inertia::render('CMS/Projects/Create', [
            'projectNumber' => $this->projectService->generateProjectNumber($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'nullable|exists:cms_customers,id',
            'site_location' => 'nullable|string',
            'site_address' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project_manager_id' => 'nullable|exists:cms_employees,id',
            'milestones' => 'nullable|array',
            'milestones.*.name' => 'required|string',
            'milestones.*.target_date' => 'required|date',
            'milestones.*.payment_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $validated['company_id'] = $companyId;
        $validated['project_number'] = $this->projectService->generateProjectNumber($companyId);

        $project = $this->projectService->createProject($validated);

        return redirect()->route('cms.projects.show', $project->id)
            ->with('success', 'Project created successfully');
    }

    public function show(Request $request, ProjectModel $project)
    {
        $project->load([
            'customer',
            'projectManager',
            'milestones' => fn($q) => $q->orderBy('order'),
            'jobs',
            'diaryEntries' => fn($q) => $q->latest()->limit(10),
            'documents' => fn($q) => $q->latest(),
        ]);

        $stats = $this->projectService->getProjectStats($project);

        return Inertia::render('CMS/Projects/Show', [
            'project' => $project,
            'stats' => $stats,
        ]);
    }

    public function edit(ProjectModel $project)
    {
        $project->load(['customer', 'projectManager', 'milestones']);

        return Inertia::render('CMS/Projects/Edit', [
            'project' => $project,
        ]);
    }

    public function update(Request $request, ProjectModel $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'nullable|exists:cms_customers,id',
            'site_location' => 'nullable|string',
            'site_address' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project_manager_id' => 'nullable|exists:cms_employees,id',
        ]);

        $this->projectService->updateProject($project, $validated);

        return redirect()->route('cms.projects.show', $project->id)
            ->with('success', 'Project updated successfully');
    }

    public function updateStatus(Request $request, ProjectModel $project)
    {
        $validated = $request->validate([
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
        ]);

        $this->projectService->updateProjectStatus($project, $validated['status']);

        return back()->with('success', 'Project status updated');
    }

    public function destroy(ProjectModel $project)
    {
        $project->delete();

        return redirect()->route('cms.projects.index')
            ->with('success', 'Project deleted successfully');
    }

    // Milestones
    public function storeMilestone(Request $request, ProjectModel $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'required|date',
            'payment_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $milestone = $this->projectService->addMilestone($project, $validated);

        return back()->with('success', 'Milestone added successfully');
    }

    public function completeMilestone(ProjectModel $project, ProjectMilestoneModel $milestone)
    {
        $this->projectService->completeMilestone($milestone);

        return back()->with('success', 'Milestone completed');
    }

    public function timeline(ProjectModel $project)
    {
        $timeline = $this->projectService->getProjectTimeline($project);

        return response()->json(['timeline' => $timeline]);
    }
}
