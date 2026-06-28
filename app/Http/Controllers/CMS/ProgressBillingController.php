<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\ProgressBilling\Services\ProgressBillingService;
use App\Infrastructure\Persistence\Eloquent\CMS\ProgressCertificateModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentApplicationModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgressBillingController extends Controller
{
    public function __construct(
        private ProgressBillingService $progressBillingService
    ) {}

    // Progress Certificates
    public function certificatesIndex(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $certificates = ProgressCertificateModel::whereHas('project', fn($q) => $q->where('company_id', $companyId))
            ->with(['project', 'boq', 'approvedBy'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('certificate_number', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/ProgressBilling/Certificates/Index', [
            'certificates' => $certificates,
            'filters' => $request->only(['status']),
        ]);
    }

    public function certificatesCreate()
    {
        return Inertia::render('CMS/ProgressBilling/Certificates/Create');
    }

    public function certificatesStore(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:cms_projects,id',
            'boq_id' => 'nullable|exists:cms_boqs,id',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
            'work_completed' => 'required|array',
            'work_completed.*.boq_item_id' => 'required|exists:cms_boq_items,id',
            'work_completed.*.quantity_completed' => 'required|numeric|min:0',
            'retention_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $validated['certificate_number'] = $this->progressBillingService->generateCertificateNumber($companyId);

        $certificate = $this->progressBillingService->createProgressCertificate($validated);

        return redirect()->route('cms.progress-billing.certificates.show', $certificate->id)
            ->with('success', 'Progress certificate created successfully');
    }

    public function certificatesShow(ProgressCertificateModel $certificate)
    {
        $certificate->load(['project', 'boq', 'workCompleted.boqItem', 'approvedBy']);
        $summary = $this->progressBillingService->getCertificateSummary($certificate);

        return Inertia::render('CMS/ProgressBilling/Certificates/Show', [
            'certificate' => $certificate,
            'summary' => $summary,
        ]);
    }

    public function certificatesApprove(Request $request, ProgressCertificateModel $certificate)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $this->progressBillingService->approveCertificate(
            $certificate,
            $request->user()->cmsUser->id,
            $validated['notes'] ?? null
        );

        return back()->with('success', 'Progress certificate approved');
    }

    public function certificatesReject(Request $request, ProgressCertificateModel $certificate)
    {
        $validated = $request->validate([
            'notes' => 'required|string',
        ]);

        $this->progressBillingService->rejectCertificate(
            $certificate,
            $request->user()->cmsUser->id,
            $validated['notes']
        );

        return back()->with('success', 'Progress certificate rejected');
    }

    // Payment Applications
    public function applicationsIndex(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $applications = PaymentApplicationModel::whereHas('project', fn($q) => $q->where('company_id', $companyId))
            ->with(['project', 'certificate'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('application_number', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/ProgressBilling/Applications/Index', [
            'applications' => $applications,
            'filters' => $request->only(['status']),
        ]);
    }

    public function applicationsStore(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:cms_projects,id',
            'certificate_id' => 'required|exists:cms_progress_certificates,id',
            'application_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $validated['application_number'] = $this->progressBillingService->generateApplicationNumber($companyId);

        $application = $this->progressBillingService->createPaymentApplication($validated);

        return back()->with('success', 'Payment application created successfully');
    }

    public function applicationsApprove(PaymentApplicationModel $application)
    {
        $this->progressBillingService->approveApplication($application);

        return back()->with('success', 'Payment application approved');
    }

    // Retention Tracking
    public function retentionIndex(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $retentions = $this->progressBillingService->getRetentionSummary($companyId, [
            'project_id' => $request->project_id,
            'status' => $request->status,
        ]);

        return Inertia::render('CMS/ProgressBilling/Retention/Index', [
            'retentions' => $retentions,
            'filters' => $request->only(['project_id', 'status']),
        ]);
    }

    public function releaseRetention(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:cms_projects,id',
            'amount' => 'required|numeric|min:0',
            'release_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $this->progressBillingService->releaseRetention($validated);

        return back()->with('success', 'Retention released successfully');
    }

    // Billing Stages
    public function stagesIndex(Request $request, $projectId)
    {
        $stages = $this->progressBillingService->getProjectBillingStages($projectId);

        return response()->json(['stages' => $stages]);
    }

    public function stagesStore(Request $request, $projectId)
    {
        $validated = $request->validate([
            'stage_name' => 'required|string',
            'percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'milestone_id' => 'nullable|exists:cms_project_milestones,id',
        ]);

        $validated['project_id'] = $projectId;

        $stage = $this->progressBillingService->createBillingStage($validated);

        return back()->with('success', 'Billing stage created successfully');
    }

    public function stagesComplete($stageId)
    {
        $this->progressBillingService->completeBillingStage($stageId);

        return back()->with('success', 'Billing stage completed');
    }
}
