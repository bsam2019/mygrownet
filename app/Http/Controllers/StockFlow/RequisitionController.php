<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\RequisitionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RequisitionController extends Controller
{
    public function __construct(private RequisitionService $requisitionService) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $status = $request->get('status');
        return Inertia::render('StockFlow/Requisitions/Index', [
            'requisitions' => array_map(fn($r) => $r->toArray(), $this->requisitionService->getRequisitions($companyId, $status)),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $userId = $request->user()->id;
        $validated = $request->validate([
            'date_required' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $this->requisitionService->createRequisition($companyId, $userId, $validated);
        return redirect()->back()->with('success', 'Requisition created.');
    }

    public function show(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $requisitions = $this->requisitionService->getRequisitions($companyId);
        $requisition = collect($requisitions)->first(fn($r) => $r->id() === $id);
        if (!$requisition) abort(404);
        return Inertia::render('StockFlow/Requisitions/Show', ['requisition' => $requisition->toArray()]);
    }

    public function approve(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $userId = $request->user()->id;
        $this->requisitionService->approveRequisition($id, $companyId, $userId);
        return redirect()->back()->with('success', 'Requisition approved.');
    }

    public function reject(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $this->requisitionService->rejectRequisition($id, $companyId);
        return redirect()->back()->with('success', 'Requisition rejected.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $this->requisitionService->deleteRequisition($id, $companyId);
        return redirect()->back()->with('success', 'Requisition deleted.');
    }
}
