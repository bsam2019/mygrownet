<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Materials\Services\JobMaterialPlanningService;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobMaterialPlanModel;
use App\Infrastructure\Persistence\Eloquent\CMS\MaterialModel;
use App\Infrastructure\Persistence\Eloquent\CMS\MaterialTemplateModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobMaterialPlanningController extends Controller
{
    public function __construct(
        private JobMaterialPlanningService $planningService
    ) {}

    public function index(JobModel $job): Response
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($job->company_id !== $companyId) abort(403);

        $job->load(['customer', 'materialPlans.material.category', 'materialPlans.createdBy.user']);

        $materials = MaterialModel::forCompany($companyId)
            ->active()
            ->with('category')
            ->orderBy('name')
            ->get();

        $templates = MaterialTemplateModel::forCompany($companyId)
            ->active()
            ->where('job_type', $job->job_type)
            ->with('items.material')
            ->get();

        $summary = $this->planningService->getJobMaterialSummary($job->id);

        return Inertia::render('CMS/Jobs/Materials/Index', [
            'job' => $job,
            'materials' => $materials,
            'templates' => $templates,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request, JobModel $job)
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($job->company_id !== $companyId) abort(403);

        $validated = $request->validate([
            'material_id' => 'required|exists:cms_materials,id',
            'planned_quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'wastage_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->planningService->addMaterialToJob([
            ...$validated,
            'job_id' => $job->id,
            'created_by' => $request->user()->cmsUser->id,
        ]);

        return back()->with('success', 'Material added to job successfully');
    }

    public function update(Request $request, JobModel $job, JobMaterialPlanModel $plan)
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($job->company_id !== $companyId || $plan->job_id !== $job->id) abort(403);

        $validated = $request->validate([
            'planned_quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'wastage_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->planningService->updateMaterialPlan($plan, $validated);

        return back()->with('success', 'Material plan updated successfully');
    }

    public function destroy(JobModel $job, JobMaterialPlanModel $plan)
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($job->company_id !== $companyId || $plan->job_id !== $job->id) abort(403);

        try {
            $this->planningService->removeMaterialFromJob($plan);
            return back()->with('success', 'Material removed from job');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function applyTemplate(Request $request, JobModel $job)
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($job->company_id !== $companyId) abort(403);

        $validated = $request->validate([
            'template_id' => 'required|exists:cms_material_templates,id',
            'job_size' => 'required|numeric|min:0.01',
        ]);

        $added = $this->planningService->applyTemplateToJob(
            $job->id,
            $validated['template_id'],
            $validated['job_size'],
            $request->user()->cmsUser->id
        );

        return back()->with('success', count($added) . ' materials added from template');
    }

    public function updateActualCosts(Request $request, JobModel $job, JobMaterialPlanModel $plan)
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($job->company_id !== $companyId || $plan->job_id !== $job->id) abort(403);

        $validated = $request->validate([
            'actual_quantity' => 'required|numeric|min:0',
            'actual_unit_price' => 'required|numeric|min:0',
        ]);

        $this->planningService->updateActualCosts(
            $plan,
            $validated['actual_quantity'],
            $validated['actual_unit_price']
        );

        return back()->with('success', 'Actual costs updated successfully');
    }

    public function bulkUpdateStatus(Request $request, JobModel $job)
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($job->company_id !== $companyId) abort(403);

        $validated = $request->validate([
            'plan_ids' => 'required|array',
            'plan_ids.*' => 'exists:cms_job_material_plans,id',
            'status' => 'required|in:planned,ordered,received,used',
        ]);

        $updated = $this->planningService->bulkUpdateStatus(
            $validated['plan_ids'],
            $validated['status']
        );

        return back()->with('success', "{$updated} materials updated");
    }
}
