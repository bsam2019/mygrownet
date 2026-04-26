<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Materials\Services\PurchaseOrderService;
use App\Infrastructure\Persistence\Eloquent\CMS\MaterialPurchaseOrderModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private PurchaseOrderService $purchaseOrderService
    ) {}

    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id ?? null;

        if (!$companyId) {
            abort(403, 'No CMS company access');
        }

        $purchaseOrders = MaterialPurchaseOrderModel::with(['job', 'createdBy.user', 'approvedBy.user'])
            ->forCompany($companyId)
            ->when($request->status, fn($q) => $q->byStatus($request->status))
            ->when($request->job_id, fn($q) => $q->forJob($request->job_id))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders,
            'filters' => $request->only(['status', 'job_id']),
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $jobs = JobModel::forCompany($companyId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('CMS/PurchaseOrders/Create', [
            'jobs' => $jobs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'nullable|exists:cms_jobs,id',
            'supplier_name' => 'required|string|max:200',
            'supplier_contact' => 'nullable|string|max:100',
            'supplier_address' => 'nullable|string',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'nullable|exists:cms_materials,id',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = $request->user()->cmsUser->company_id;

        $po = $this->purchaseOrderService->createPurchaseOrder([
            ...$validated,
            'company_id' => $companyId,
            'created_by' => $request->user()->cmsUser->id,
        ]);

        return redirect()
            ->route('cms.purchase-orders.show', $po->id)
            ->with('success', 'Purchase order created successfully');
    }

    public function show(MaterialPurchaseOrderModel $purchaseOrder): Response
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($purchaseOrder->company_id !== $companyId) abort(403);

        $purchaseOrder->load([
            'job.customer',
            'items.material',
            'items.jobMaterialPlan',
            'createdBy.user',
            'approvedBy.user',
        ]);

        return Inertia::render('CMS/PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function createFromJob(Request $request, JobModel $job): Response
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($job->company_id !== $companyId) abort(403);

        $job->load(['materialPlans' => function ($q) {
            $q->where('status', 'planned')->with('material');
        }]);

        return Inertia::render('CMS/PurchaseOrders/CreateFromJob', [
            'job' => $job,
        ]);
    }

    public function storeFromJob(Request $request, JobModel $job)
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($job->company_id !== $companyId) abort(403);

        $validated = $request->validate([
            'material_plan_ids' => 'required|array|min:1',
            'material_plan_ids.*' => 'exists:cms_job_material_plans,id',
            'supplier_name' => 'required|string|max:200',
            'supplier_contact' => 'nullable|string|max:100',
            'supplier_address' => 'nullable|string',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        $po = $this->purchaseOrderService->createPurchaseOrderFromJobMaterials(
            $job->id,
            $validated['material_plan_ids'],
            [
                'company_id' => $companyId,
                'supplier_contact' => $validated['supplier_contact'] ?? null,
                'supplier_address' => $validated['supplier_address'] ?? null,
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'created_by' => $request->user()->cmsUser->id,
            ]
        );

        return redirect()
            ->route('cms.purchase-orders.show', $po->id)
            ->with('success', 'Purchase order created from job materials');
    }

    public function approve(MaterialPurchaseOrderModel $purchaseOrder)
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($purchaseOrder->company_id !== $companyId) abort(403);

        try {
            $this->purchaseOrderService->approvePurchaseOrder(
                $purchaseOrder,
                request()->user()->cmsUser->id
            );
            return back()->with('success', 'Purchase order approved and sent');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receive(Request $request, MaterialPurchaseOrderModel $purchaseOrder)
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($purchaseOrder->company_id !== $companyId) abort(403);

        $validated = $request->validate([
            'received_items' => 'required|array',
            'received_items.*.received_quantity' => 'required|numeric|min:0',
        ]);

        $this->purchaseOrderService->receivePurchaseOrder(
            $purchaseOrder,
            $validated['received_items']
        );

        return back()->with('success', 'Purchase order marked as received');
    }

    public function cancel(MaterialPurchaseOrderModel $purchaseOrder)
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($purchaseOrder->company_id !== $companyId) abort(403);

        $this->purchaseOrderService->cancelPurchaseOrder($purchaseOrder);

        return back()->with('success', 'Purchase order cancelled');
    }
}
