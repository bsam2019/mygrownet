<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\QualityInspectionModel;
use App\Infrastructure\Persistence\Eloquent\CMS\NonConformanceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CorrectiveActionModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerComplaintModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ReworkRecordModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QualityController extends Controller
{
    public function inspections(Request $request)
    {
        $inspections = QualityInspectionModel::where('company_id', $request->user()->current_company_id)
            ->with(['checklist', 'inspectedBy'])
            ->latest('inspection_date')
            ->paginate(20);

        return Inertia::render('CMS/Quality/Inspections/Index', ['inspections' => $inspections]);
    }

    public function storeInspection(Request $request)
    {
        $validated = $request->validate([
            'checklist_id' => 'required|exists:cms_quality_checklists,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'production_order_id' => 'nullable|exists:cms_production_orders,id',
            'inspection_date' => 'required|date',
            'inspected_by' => 'required|exists:users,id',
            'inspection_stage' => 'required|in:incoming,in_process,final,pre_delivery',
            'overall_result' => 'required|in:pass,fail,conditional',
            'notes' => 'nullable|string',
        ]);

        $inspection = QualityInspectionModel::create([
            ...$validated,
            'company_id' => $request->user()->current_company_id,
        ]);

        if ($request->has('results')) {
            foreach ($request->results as $result) {
                $inspection->results()->create($result);
            }
        }

        return back()->with('success', 'Inspection recorded');
    }

    public function showInspection(int $id)
    {
        $inspection = QualityInspectionModel::with(['results.checklistItem', 'inspectedBy'])
            ->findOrFail($id);

        return Inertia::render('CMS/Quality/Inspections/Show', ['inspection' => $inspection]);
    }

    public function ncr(Request $request)
    {
        $ncrs = NonConformanceModel::where('company_id', $request->user()->current_company_id)
            ->with(['reportedBy', 'assignedTo'])
            ->latest('reported_date')
            ->paginate(20);

        return Inertia::render('CMS/Quality/NCR/Index', ['ncrs' => $ncrs]);
    }

    public function storeNCR(Request $request)
    {
        $validated = $request->validate([
            'ncr_number' => 'required|string|max:50',
            'reported_date' => 'required|date',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'production_order_id' => 'nullable|exists:cms_production_orders,id',
            'inspection_id' => 'nullable|exists:cms_quality_inspections,id',
            'non_conformance_type' => 'required|in:material,process,product,documentation,other',
            'severity' => 'required|in:minor,major,critical',
            'description' => 'required|string',
            'root_cause' => 'nullable|string',
            'reported_by' => 'required|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        NonConformanceModel::create([
            ...$validated,
            'company_id' => $request->user()->current_company_id,
            'status' => 'open',
        ]);

        return back()->with('success', 'NCR created');
    }

    public function storeCorrectiveAction(Request $request, int $ncrId)
    {
        $validated = $request->validate([
            'action_description' => 'required|string',
            'responsible_person' => 'required|exists:users,id',
            'target_date' => 'required|date',
            'action_type' => 'required|in:immediate,corrective,preventive',
        ]);

        $ncr = NonConformanceModel::findOrFail($ncrId);
        
        $ncr->correctiveActions()->create([
            ...$validated,
            'status' => 'planned',
        ]);

        return back()->with('success', 'Corrective action added');
    }

    public function complaints(Request $request)
    {
        $complaints = CustomerComplaintModel::where('company_id', $request->user()->current_company_id)
            ->with(['customer', 'job', 'assignedTo'])
            ->latest('complaint_date')
            ->paginate(20);

        return Inertia::render('CMS/Quality/Complaints/Index', ['complaints' => $complaints]);
    }

    public function storeComplaint(Request $request)
    {
        $validated = $request->validate([
            'complaint_number' => 'required|string|max:50',
            'complaint_date' => 'required|date',
            'customer_id' => 'required|exists:cms_customers,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'complaint_type' => 'required|in:product_quality,service,delivery,pricing,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'required|string',
            'customer_expectation' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        CustomerComplaintModel::create([
            ...$validated,
            'company_id' => $request->user()->current_company_id,
            'status' => 'open',
        ]);

        return back()->with('success', 'Complaint recorded');
    }

    public function rework(Request $request)
    {
        $rework = ReworkRecordModel::where('company_id', $request->user()->current_company_id)
            ->with(['job', 'productionOrder', 'authorizedBy'])
            ->latest('rework_date')
            ->paginate(20);

        return Inertia::render('CMS/Quality/Rework/Index', ['rework' => $rework]);
    }

    public function storeRework(Request $request)
    {
        $validated = $request->validate([
            'rework_number' => 'required|string|max:50',
            'rework_date' => 'required|date',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'production_order_id' => 'nullable|exists:cms_production_orders,id',
            'ncr_id' => 'nullable|exists:cms_non_conformances,id',
            'reason' => 'required|string',
            'rework_description' => 'required|string',
            'estimated_hours' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'authorized_by' => 'required|exists:users,id',
        ]);

        ReworkRecordModel::create([
            ...$validated,
            'company_id' => $request->user()->current_company_id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Rework record created');
    }
}
