<?php

namespace App\Http\Controllers\BMS;

use App\Http\Controllers\Controller;
use App\Domain\BMS\Core\Services\JobService;
use App\Domain\BMS\Repositories\JobRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\BMS\BranchModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CmsUserModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    public function __construct(
        private JobService $jobService,
        private JobRepositoryInterface $jobRepo
    ) {}

    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id ?? null;

        if (!$companyId) {
            abort(403, 'No CMS company access');
        }

        $jobs = JobModel::with(['customer', 'assignedTo.user', 'createdBy.user', 'branch'])
            ->forCompany($companyId)
            ->forBranch($request->branch_id)
            ->when($request->status, fn($q) => $q->byStatus($request->status))
            ->when($request->assigned_to, fn($q) => $q->assignedTo($request->assigned_to))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $branches = BranchModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'branch_name']);

        return Inertia::render('BMS/Jobs/Index', [
            'jobs' => $jobs,
            'filters' => $request->only(['status', 'assigned_to', 'branch_id']),
            'branches' => $branches,
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id ?? null;

        $customers = CustomerModel::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'customer_number', 'name', 'phone']);

        return Inertia::render('BMS/Jobs/Create', [
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
            ->route('bms.jobs.show', $job->id)
            ->with('success', 'Job created successfully');
    }

    public function show(JobModel $job): Response
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($job->company_id !== $companyId) abort(403);

        $job->load([
            'customer',
            'quotation.measurement.items',
            'assignedTo.user',
            'createdBy.user',
            'lockedBy.user',
            'attachments',
            'statusHistory.changedBy.user',
        ]);

        $workers = CmsUserModel::where('company_id', $companyId)
            ->with('user')
            ->get()
            ->map(fn($cmsUser) => [
                'id' => $cmsUser->id,
                'name' => $cmsUser->user->name,
            ]);

        return Inertia::render('BMS/Jobs/Show', [
            'job' => $job,
            'fabricationStatuses' => $this->getFabricationStatuses(),
            'workers' => $workers,
        ]);
    }

    public function assign(Request $request, JobModel $job)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:cms_users,id',
        ]);

        $assignedBy = $request->user()->cmsUser->id;

        $entity = $this->jobRepo->findById($job->id);
        if ($entity) {
            $this->jobService->assignJob($entity, $validated['assigned_to'], $assignedBy);
        }

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

        $entity = $this->jobRepo->findById($job->id);
        if ($entity) {
            $this->jobService->completeJob($entity, $validated, $completedBy);
        }

        return back()->with('success', 'Job completed successfully');
    }

    public function updateStatus(Request $request, JobModel $job)
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($job->company_id !== $companyId) abort(403);

        $validated = $request->validate([
            'status' => 'required|in:pending,materials_ordered,fabrication,ready_for_install,installing,completed,cancelled',
            'notes'  => 'nullable|string|max:500',
        ]);

        $entity = $this->jobRepo->findById($job->id);
        if ($entity) {
            $this->jobService->updateJobStatus($entity, $validated['status'], $request->user()->cmsUser->id, $validated['notes'] ?? null);
        }

        return back()->with('success', 'Job status updated.');
    }

    private function getFabricationStatuses(): array
    {
        return [
            ['value' => 'pending',            'label' => 'Pending',              'color' => 'gray'],
            ['value' => 'materials_ordered',  'label' => 'Materials Ordered',    'color' => 'amber'],
            ['value' => 'fabrication',        'label' => 'In Fabrication',       'color' => 'blue'],
            ['value' => 'ready_for_install',  'label' => 'Ready for Install',    'color' => 'indigo'],
            ['value' => 'installing',         'label' => 'Installing',           'color' => 'purple'],
            ['value' => 'completed',          'label' => 'Completed',            'color' => 'green'],
            ['value' => 'cancelled',          'label' => 'Cancelled',            'color' => 'red'],
        ];
    }

    public function uploadAttachment(Request $request, JobModel $job)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $companyId = $request->user()->cmsUser->company_id;

        $filename = $file->getClientOriginalName();
        $sanitizedFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($filename));
        $uuid = \Illuminate\Support\Str::uuid();
        $s3Key = "cms/companies/{$companyId}/jobs/{$job->id}/attachments/{$uuid}_{$sanitizedFilename}";

        \Illuminate\Support\Facades\Storage::disk('s3')->put(
            $s3Key,
            file_get_contents($file->getRealPath()),
            [
                'ContentType' => $file->getClientMimeType(),
                'visibility' => 'private',
            ]
        );

        $job->attachments()->create([
            'company_id' => $companyId,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $s3Key,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
            'uploaded_by' => $request->user()->cmsUser->id,
        ]);

        return back()->with('success', 'Attachment uploaded successfully');
    }
}
