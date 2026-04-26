<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Production\Services\ProductionService;
use App\Infrastructure\Persistence\Eloquent\CMS\ProductionOrderModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CuttingListModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WasteTrackingModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductionController extends Controller
{
    public function __construct(
        private ProductionService $productionService
    ) {}

    public function index(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;
        
        $orders = ProductionOrderModel::with(['job', 'assignedUser', 'cuttingLists'])
            ->where('company_id', $companyId)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('job', function ($jobQuery) use ($search) {
                          $jobQuery->where('job_number', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20);
        
        $statistics = $this->productionService->getProductionStatistics($companyId);
        
        return Inertia::render('CMS/Production/Index', [
            'orders' => $orders,
            'statistics' => $statistics,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;
        
        $jobs = JobModel::where('company_id', $companyId)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->get(['id', 'job_number', 'title']);
        
        return Inertia::render('CMS/Production/Create', [
            'jobs' => $jobs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|exists:cms_jobs,id',
            'order_date' => 'required|date',
            'required_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        
        $companyId = $request->user()->currentCompany->id;
        $order = $this->productionService->createProductionOrder($companyId, $validated);
        
        return redirect()->route('cms.production.show', $order->id)
            ->with('success', 'Production order created successfully');
    }

    public function show(int $id): Response
    {
        $order = ProductionOrderModel::with([
            'job',
            'assignedUser',
            'cuttingLists.items.material',
            'tracking.user',
            'qualityCheckpoints.inspector',
            'materialsUsage.material',
            'wasteRecords.material',
        ])->findOrFail($id);
        
        return Inertia::render('CMS/Production/Show', [
            'order' => $order,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'required_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        
        $order = $this->productionService->updateProductionOrder($id, $validated);
        
        return back()->with('success', 'Production order updated successfully');
    }

    public function destroy(int $id)
    {
        $this->productionService->deleteProductionOrder($id);
        
        return redirect()->route('cms.production.index')
            ->with('success', 'Production order deleted successfully');
    }

    // Cutting Lists
    public function cuttingLists(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;
        
        $lists = CuttingListModel::with(['productionOrder.job', 'items'])
            ->where('company_id', $companyId)
            ->when($request->search, function ($query, $search) {
                $query->where('list_number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20);
        
        return Inertia::render('CMS/Production/CuttingLists/Index', [
            'lists' => $lists,
            'filters' => $request->only(['search']),
        ]);
    }

    public function createCuttingList(int $productionOrderId): Response
    {
        $order = ProductionOrderModel::with('job.materialPlan.materials.material')
            ->findOrFail($productionOrderId);
        
        return Inertia::render('CMS/Production/CuttingLists/Create', [
            'order' => $order,
        ]);
    }

    public function storeCuttingList(Request $request, int $productionOrderId)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:cms_materials,id',
            'items.*.item_code' => 'nullable|string',
            'items.*.description' => 'required|string',
            'items.*.required_length' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        
        $cuttingList = $this->productionService->generateCuttingList(
            $productionOrderId,
            $validated['items']
        );
        
        return redirect()->route('cms.production.cutting-lists.show', $cuttingList->id)
            ->with('success', 'Cutting list created successfully');
    }

    public function showCuttingList(int $id): Response
    {
        $list = CuttingListModel::with([
            'productionOrder.job',
            'items.material',
            'items.cutByUser',
        ])->findOrFail($id);
        
        return Inertia::render('CMS/Production/CuttingLists/Show', [
            'list' => $list,
        ]);
    }

    public function optimizeCuttingList(Request $request, int $id)
    {
        $validated = $request->validate([
            'stock_length' => 'required|numeric|min:0',
        ]);
        
        $result = $this->productionService->optimizeCuttingList(
            $id,
            $validated['stock_length']
        );
        
        return back()->with('success', 'Cutting list optimized successfully')
            ->with('optimization', $result);
    }

    // Waste Tracking
    public function wasteTracking(Request $request): Response
    {
        $companyId = $request->user()->currentCompany->id;
        
        $waste = WasteTrackingModel::with(['material', 'productionOrder', 'recordedBy'])
            ->where('company_id', $companyId)
            ->when($request->start_date, fn($q, $date) => $q->where('waste_date', '>=', $date))
            ->when($request->end_date, fn($q, $date) => $q->where('waste_date', '<=', $date))
            ->latest('waste_date')
            ->paginate(20);
        
        $statistics = $this->productionService->getWasteStatistics($companyId, [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        
        return Inertia::render('CMS/Production/Waste/Index', [
            'waste' => $waste,
            'statistics' => $statistics,
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    public function storeWaste(Request $request)
    {
        $validated = $request->validate([
            'production_order_id' => 'nullable|exists:cms_production_orders,id',
            'material_id' => 'required|exists:cms_materials,id',
            'waste_date' => 'required|date',
            'waste_type' => 'required|in:offcut,damaged,defective,expired,other',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'value' => 'nullable|numeric|min:0',
            'disposal_method' => 'nullable|in:reuse,recycle,scrap,discard',
            'reason' => 'nullable|string',
        ]);
        
        $companyId = $request->user()->currentCompany->id;
        $validated['recorded_by'] = $request->user()->id;
        
        $waste = $this->productionService->recordWaste($companyId, $validated);
        
        return back()->with('success', 'Waste recorded successfully');
    }

    // Production Tracking
    public function updateTracking(Request $request, int $productionOrderId)
    {
        $validated = $request->validate([
            'stage' => 'required|in:cutting,assembly,finishing,quality_check,packaging',
            'status' => 'required|in:not_started,in_progress,completed,on_hold',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'hours_spent' => 'nullable|numeric|min:0',
            'quantity_completed' => 'nullable|integer|min:0',
            'quantity_rejected' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);
        
        $validated['user_id'] = $request->user()->id;
        
        $tracking = $this->productionService->updateProductionTracking($productionOrderId, $validated);
        
        return back()->with('success', 'Production tracking updated successfully');
    }

    // Quality Checkpoints
    public function updateCheckpoint(Request $request, int $checkpointId)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,passed,failed,waived',
            'findings' => 'nullable|string',
            'corrective_action' => 'nullable|string',
            'requires_rework' => 'nullable|boolean',
        ]);
        
        $validated['inspector_id'] = $request->user()->id;
        $validated['inspected_at'] = now();
        
        $checkpoint = $this->productionService->updateQualityCheckpoint($checkpointId, $validated);
        
        return back()->with('success', 'Quality checkpoint updated successfully');
    }

    // Material Usage
    public function recordMaterialUsage(Request $request, int $productionOrderId)
    {
        $validated = $request->validate([
            'materials' => 'required|array|min:1',
            'materials.*.material_id' => 'required|exists:cms_materials,id',
            'materials.*.planned_quantity' => 'required|numeric|min:0',
            'materials.*.actual_quantity' => 'required|numeric|min:0',
            'materials.*.unit' => 'required|string',
            'materials.*.unit_cost' => 'nullable|numeric|min:0',
            'materials.*.notes' => 'nullable|string',
        ]);
        
        $this->productionService->recordMaterialUsage($productionOrderId, $validated['materials']);
        
        return back()->with('success', 'Material usage recorded successfully');
    }
}
