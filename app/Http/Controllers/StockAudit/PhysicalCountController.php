<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\PhysicalCountService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhysicalCountController extends Controller
{
    public function __construct(
        private PhysicalCountService $physicalCountService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $counts = $this->physicalCountService->getCountsForCompany($companyId);

        return Inertia::render('StockAudit/PhysicalCounts/Index', [
            'counts' => $counts,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'count_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $count = $this->physicalCountService->createCount($companyId, $validated, $request->user()->id);

        return redirect()->sfRoute('stock-audit.physical-counts.show', $count->id());
    }

    public function show(int $physicalCountId)
    {
        $count = $this->physicalCountService->getCountById($physicalCountId, session('stock_audit_company_id'));

        if (!$count) {
            abort(404);
        }

        return Inertia::render('StockAudit/PhysicalCounts/Show', [
            'count' => $count->toArray(),
        ]);
    }

    public function updateItems(Request $request, int $physicalCountId)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|numeric',
            'items.*.physical_quantity' => 'required|numeric|min:0',
        ]);

        $this->physicalCountService->updateCountItems($physicalCountId, $validated['items']);

        return redirect()->sfRoute('stock-audit.physical-counts.show', $physicalCountId);
    }

    public function complete(Request $request, int $physicalCountId)
    {
        $this->physicalCountService->completeCount($physicalCountId, $request->user()->id);

        return redirect()->sfRoute('stock-audit.physical-counts.show', $physicalCountId);
    }

    public function generateAudit(Request $request, int $physicalCountId)
    {
        $audit = $this->physicalCountService->generateAudit($physicalCountId);

        return redirect()->sfRoute('stock-audit.audits.show', $audit->id());
    }
}
