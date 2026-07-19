<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Subcontractors\Services\SubcontractorService;
use App\Infrastructure\Persistence\Eloquent\CMS\SubcontractorModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubcontractorController extends Controller
{
    public function __construct(
        private SubcontractorService $subcontractorService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $subcontractors = SubcontractorModel::where('company_id', $companyId)
            ->when($request->search, fn($q) => $q->where(function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('company_name', 'like', "%{$request->search}%");
            }))
            ->when($request->trade, fn($q) => $q->where('trade', $request->trade))
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('CMS/Subcontractors/Index', [
            'subcontractors' => $subcontractors,
            'filters' => $request->only(['search', 'trade']),
        ]);
    }

    public function create()
    {
        return Inertia::render('CMS/Subcontractors/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'trade' => 'required|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:50',
            'insurance_expiry' => 'nullable|date',
            'certifications' => 'nullable|array',
            'hourly_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $validated['company_id'] = $companyId;

        $subcontractor = $this->subcontractorService->createSubcontractor($validated);

        return redirect()->route('cms.subcontractors.show', $subcontractor->id)
            ->with('success', 'Subcontractor created successfully');
    }

    public function show(SubcontractorModel $subcontractor)
    {
        $subcontractor->load(['assignments.project', 'assignments.job', 'payments', 'ratings']);
        $stats = $this->subcontractorService->getSubcontractorStats($subcontractor);

        return Inertia::render('CMS/Subcontractors/Show', [
            'subcontractor' => $subcontractor,
            'stats' => $stats,
        ]);
    }

    public function edit(SubcontractorModel $subcontractor)
    {
        return Inertia::render('CMS/Subcontractors/Edit', [
            'subcontractor' => $subcontractor,
        ]);
    }

    public function update(Request $request, SubcontractorModel $subcontractor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'trade' => 'required|string|max:100',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:50',
            'insurance_expiry' => 'nullable|date',
            'certifications' => 'nullable|array',
            'hourly_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $this->subcontractorService->updateSubcontractor($subcontractor, $validated);

        return redirect()->route('cms.subcontractors.show', $subcontractor->id)
            ->with('success', 'Subcontractor updated successfully');
    }

    public function destroy(SubcontractorModel $subcontractor)
    {
        $subcontractor->delete();

        return redirect()->route('cms.subcontractors.index')
            ->with('success', 'Subcontractor deleted successfully');
    }

    public function assign(Request $request, SubcontractorModel $subcontractor)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:cms_projects,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'scope_of_work' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'contract_amount' => 'required|numeric|min:0',
        ]);

        $assignment = $this->subcontractorService->assignToProject($subcontractor, $validated);

        return back()->with('success', 'Subcontractor assigned successfully');
    }

    public function recordPayment(Request $request, SubcontractorModel $subcontractor)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:cms_subcontractor_assignments,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $payment = $this->subcontractorService->recordPayment($subcontractor, $validated);

        return back()->with('success', 'Payment recorded successfully');
    }

    public function rate(Request $request, SubcontractorModel $subcontractor)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:cms_subcontractor_assignments,id',
            'quality_rating' => 'required|integer|min:1|max:5',
            'timeliness_rating' => 'required|integer|min:1|max:5',
            'communication_rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
        ]);

        $rating = $this->subcontractorService->rateSubcontractor($subcontractor, $validated);

        return back()->with('success', 'Rating submitted successfully');
    }
}
