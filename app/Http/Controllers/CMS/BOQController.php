<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\BOQ\Services\BOQService;
use App\Infrastructure\Persistence\Eloquent\CMS\BOQModel;
use App\Infrastructure\Persistence\Eloquent\CMS\BOQTemplateModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BOQController extends Controller
{
    public function __construct(
        private BOQService $boqService
    ) {}

    // Templates
    public function templatesIndex(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $templates = BOQTemplateModel::where('company_id', $companyId)
            ->with(['category', 'items'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('CMS/BOQ/Templates/Index', [
            'templates' => $templates,
            'filters' => $request->only(['search']),
        ]);
    }

    public function templatesStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:cms_boq_categories,id',
            'items' => 'nullable|array',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $validated['company_id'] = $companyId;

        $template = $this->boqService->createTemplate($validated);

        return back()->with('success', 'BOQ template created successfully');
    }

    // BOQs
    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $boqs = BOQModel::where('company_id', $companyId)
            ->with(['project', 'job', 'items'])
            ->when($request->search, fn($q) => $q->where('boq_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/BOQ/Index', [
            'boqs' => $boqs,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('CMS/BOQ/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:cms_projects,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'nullable|exists:cms_boq_templates,id',
            'items' => 'required|array|min:1',
            'items.*.item_number' => 'required|string',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.category' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $validated['company_id'] = $companyId;
        $validated['boq_number'] = $this->boqService->generateBOQNumber($companyId);

        $boq = $this->boqService->createBOQ($validated);

        return redirect()->route('cms.boq.show', $boq->id)
            ->with('success', 'BOQ created successfully');
    }

    public function show(BOQModel $boq)
    {
        $boq->load(['project', 'job', 'items', 'variations']);
        $summary = $this->boqService->getBOQSummary($boq);

        return Inertia::render('CMS/BOQ/Show', [
            'boq' => $boq,
            'summary' => $summary,
        ]);
    }

    public function edit(BOQModel $boq)
    {
        $boq->load(['items']);

        return Inertia::render('CMS/BOQ/Edit', [
            'boq' => $boq,
        ]);
    }

    public function update(Request $request, BOQModel $boq)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_number' => 'required|string',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        $this->boqService->updateBOQ($boq, $validated);

        return redirect()->route('cms.boq.show', $boq->id)
            ->with('success', 'BOQ updated successfully');
    }

    public function destroy(BOQModel $boq)
    {
        $boq->delete();

        return redirect()->route('cms.boq.index')
            ->with('success', 'BOQ deleted successfully');
    }

    public function addVariation(Request $request, BOQModel $boq)
    {
        $validated = $request->validate([
            'variation_number' => 'required|string',
            'description' => 'required|string',
            'reason' => 'required|string',
            'items' => 'required|array',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'required|string',
            'items.*.quantity' => 'required|numeric',
            'items.*.rate' => 'required|numeric',
        ]);

        $variation = $this->boqService->addVariation($boq, $validated);

        return back()->with('success', 'Variation added successfully');
    }

    public function approveVariation(BOQModel $boq, $variationId)
    {
        $this->boqService->approveVariation($variationId);

        return back()->with('success', 'Variation approved');
    }

    public function updateActuals(Request $request, BOQModel $boq)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:cms_boq_items,id',
            'items.*.actual_quantity' => 'required|numeric|min:0',
            'items.*.actual_rate' => 'nullable|numeric|min:0',
        ]);

        $this->boqService->updateActuals($boq, $validated['items']);

        return back()->with('success', 'Actual quantities updated');
    }

    public function export(BOQModel $boq)
    {
        $pdf = $this->boqService->exportToPDF($boq);

        return response()->streamDownload(
            fn() => print($pdf),
            "BOQ-{$boq->boq_number}.pdf"
        );
    }
}
