<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\PhysicalCountService;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaPhysicalCountModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhysicalCountController extends Controller
{
    public function __construct(
        private PhysicalCountService $physicalCountService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = SaPhysicalCountModel::where('sa_company_id', $companyId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $counts = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

        return Inertia::render('StockFlow/PhysicalCounts/Index', [
            'counts' => $counts,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'count_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $count = $this->physicalCountService->createCount($companyId, $validated, $request->user()->id);

        return redirect()->sfRoute('stockflow.physical-counts.show', $count->id())->with('success', 'Physical count created successfully.');
    }

    public function show(int $physicalCountId)
    {
        $count = $this->physicalCountService->getCountById($physicalCountId, session('stockflow_company_id'));

        if (!$count) {
            abort(404);
        }

        return Inertia::render('StockFlow/PhysicalCounts/Show', [
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

        return redirect()->sfRoute('stockflow.physical-counts.show', $physicalCountId)->with('success', 'Count items updated successfully.');
    }

    public function complete(Request $request, int $physicalCountId)
    {
        $this->physicalCountService->completeCount($physicalCountId, $request->user()->id);

        return redirect()->sfRoute('stockflow.physical-counts.show', $physicalCountId)->with('success', 'Physical count completed successfully.');
    }

    public function generateAudit(Request $request, int $physicalCountId)
    {
        $audit = $this->physicalCountService->generateAudit($physicalCountId);

        return redirect()->sfRoute('stockflow.audits.show', $audit->id())->with('success', 'Audit generated successfully.');
    }
}
