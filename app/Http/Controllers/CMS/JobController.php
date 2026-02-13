<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\JobService;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    public function __construct(
        private JobService $jobService
    ) {}

    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id ?? null;

        if (!$companyId) {
            abort(403, 'No CMS company access');
        }

        $jobs = JobModel::with(['customer', 'assignedTo.user', 'createdBy.user'])
            ->forCompany($companyId)
            ->when($request->status, fn($q) => $q->byStatus($request->status))
            ->when($request->assigned_to, fn($q) => $q->assignedTo($request->assigned_to))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/Jobs/Index', [
            'jobs' => $jobs,
            'filters' => $request->only(['status', 'assigned_to']),
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id ?? null;

        $customers = CustomerModel::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'customer_number', 'name', 'phone']);

        return Inertia::render('CMS/Jobs/Create', [
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:cms_customers,id',
            'job_type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'quoted_value' => 'nullable|numeric|min:0',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'deadline' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $userId = $request->user()->cmsUser->id;

        $job = $this->jobService->createJob([
            ...$validated,
            'company_id' => $companyId,
            'created_by' => $userId,
        ]);

        return redirect()
            ->route('cms.jobs.show', $job->id)
            ->with('success', 'Job created successfully');
    }

    public function show(JobModel $job): Response
    {
        $job->load([
            'customer',
            'assignedTo.user',
            'createdBy.user',
            'lockedBy.user',
            'attachments',
            'statusHistory.changedBy.user'
        ]);

        return Inertia::render('CMS/Jobs/Show', [
            'job' => $job,
        ]);
    }

    public function assign(Request $request, JobModel $job)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:cms_users,id',
        ]);

        $assignedBy = $request->user()->cmsUser->id;

        $this->jobService->assignJob($job, $validated['assigned_to'], $assignedBy);

        return back()->with('success', 'Job assigned successfully');
    }

    public function complete(Request $request, JobModel $job)
    {
        $validated = $request->validate([
            'actual_value' => 'required|numeric|min:0',
            'material_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'overhead_cost' => 'nullable|numeric|min:0',
        ]);

        $completedBy = $request->user()->cmsUser->id;

        $this->jobService->completeJob($job, $validated, $completedBy);

        return back()->with('success', 'Job completed successfully');
    }

    public function uploadAttachment(Request $request, JobModel $job)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // 5MB max
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $companyId = $request->user()->cmsUser->company_id;
        
        // Store file
        $path = $file->store("cms/companies/{$companyId}/jobs/{$job->id}/attachments", 'public');
        
        // Create attachment record
        $job->attachments()->create([
            'company_id' => $companyId,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => '/storage/' . $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
            'uploaded_by' => $request->user()->cmsUser->id,
        ]);

        return back()->with('success', 'Attachment uploaded successfully');
    }
}
