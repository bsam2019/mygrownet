<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\MeasurementService;
use App\Domain\CMS\Core\Services\PricingRulesService;
use App\Infrastructure\Persistence\Eloquent\CMS\MeasurementRecordModel;
use App\Infrastructure\Persistence\Eloquent\CMS\MeasurementItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MeasurementController extends Controller
{
    public function __construct(
        private MeasurementService $measurementService,
        private PricingRulesService $pricingRulesService
    ) {}

    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $query = MeasurementRecordModel::with(['customer', 'createdBy.user', 'items'])
            ->where('company_id', $companyId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('measurement_number', 'like', "%{$request->search}%")
                  ->orWhere('project_name', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
            });
        }

        $measurements = $query->orderBy('measurement_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total'     => MeasurementRecordModel::where('company_id', $companyId)->count(),
            'draft'     => MeasurementRecordModel::where('company_id', $companyId)->where('status', 'draft')->count(),
            'completed' => MeasurementRecordModel::where('company_id', $companyId)->where('status', 'completed')->count(),
            'quoted'    => MeasurementRecordModel::where('company_id', $companyId)->where('status', 'quoted')->count(),
        ];

        $customers = CustomerModel::forCompany($companyId)->active()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('CMS/Measurements/Index', [
            'measurements' => $measurements,
            'summary'      => $summary,
            'customers'    => $customers,
            'filters'      => $request->only(['status', 'customer_id', 'search']),
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $customers = CustomerModel::forCompany($companyId)->active()->orderBy('name')->get(['id', 'customer_number', 'name', 'phone']);
        $workers   = CmsUserModel::forCompany($companyId)->active()->with('user')->get();

        return Inertia::render('CMS/Measurements/Create', [
            'customers' => $customers,
            'workers'   => $workers,
            'itemTypes' => $this->getItemTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $validated = $request->validate([
            'customer_id'      => 'required|integer|exists:cms_customers,id',
            'project_name'     => 'required|string|max:255',
            'location'         => 'nullable|string|max:255',
            'measured_by'      => 'nullable|integer|exists:cms_users,id',
            'measurement_date' => 'required|date',
            'notes'            => 'nullable|string',
            'items'            => 'nullable|array',
            'items.*.location_name' => 'required|string|max:255',
            'items.*.type'          => 'required|in:sliding_window,casement_window,sliding_door,hinged_door,other',
            'items.*.width_top'     => 'required|numeric|min:1',
            'items.*.width_middle'  => 'required|numeric|min:1',
            'items.*.width_bottom'  => 'required|numeric|min:1',
            'items.*.height_left'   => 'required|numeric|min:1',
            'items.*.height_right'  => 'required|numeric|min:1',
            'items.*.quantity'      => 'required|integer|min:1',
            'items.*.notes'         => 'nullable|string',
        ]);

        // Ensure customer belongs to this company
        $customer = CustomerModel::where('id', $validated['customer_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        $measurement = $this->measurementService->createMeasurement(array_merge($validated, [
            'company_id' => $companyId,
            'created_by' => $request->user()->cmsUser->id,
        ]));

        return redirect()->route('cms.measurements.show', $measurement->id)
            ->with('success', "Measurement {$measurement->measurement_number} created successfully.");
    }

    public function show(Request $request, int $id): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $measurement = MeasurementRecordModel::with(['customer', 'items', 'measuredBy.user', 'createdBy.user', 'quotations'])
            ->where('company_id', $companyId)
            ->findOrFail($id);

        $profitSummary = $this->measurementService->getProfitSummary($measurement);

        return Inertia::render('CMS/Measurements/Show', [
            'measurement'   => $measurement,
            'profitSummary' => $profitSummary,
            'itemTypes'     => $this->getItemTypes(),
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $measurement = MeasurementRecordModel::with(['customer', 'items'])
            ->where('company_id', $companyId)
            ->findOrFail($id);

        $customers = CustomerModel::forCompany($companyId)->active()->orderBy('name')->get(['id', 'customer_number', 'name', 'phone']);
        $workers   = CmsUserModel::forCompany($companyId)->active()->with('user')->get();

        return Inertia::render('CMS/Measurements/Edit', [
            'measurement' => $measurement,
            'customers'   => $customers,
            'workers'     => $workers,
            'itemTypes'   => $this->getItemTypes(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $measurement = MeasurementRecordModel::where('company_id', $companyId)->findOrFail($id);

        if ($measurement->isQuoted()) {
            return back()->withErrors(['error' => 'Cannot edit a measurement that has already been quoted.']);
        }

        $validated = $request->validate([
            'customer_id'      => 'required|integer|exists:cms_customers,id',
            'project_name'     => 'required|string|max:255',
            'location'         => 'nullable|string|max:255',
            'measured_by'      => 'nullable|integer|exists:cms_users,id',
            'measurement_date' => 'required|date',
            'notes'            => 'nullable|string',
        ]);

        $this->measurementService->updateMeasurement($measurement, array_merge($validated, [
            'updated_by' => $request->user()->cmsUser->id,
        ]));

        return redirect()->route('cms.measurements.show', $measurement->id)
            ->with('success', 'Measurement updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $measurement = MeasurementRecordModel::where('company_id', $companyId)->findOrFail($id);

        if ($measurement->isQuoted()) {
            return back()->withErrors(['error' => 'Cannot delete a measurement that has been quoted.']);
        }

        $number = $measurement->measurement_number;
        $measurement->delete();

        return redirect()->route('cms.measurements.index')
            ->with('success', "Measurement {$number} deleted.");
    }

    // ── Item management ──────────────────────────────────────────────────────

    public function storeItem(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $measurement = MeasurementRecordModel::where('company_id', $companyId)->findOrFail($id);

        $validated = $request->validate([
            'location_name' => 'required|string|max:255',
            'type'          => 'required|in:sliding_window,casement_window,sliding_door,hinged_door,other',
            'width_top'     => 'required|numeric|min:1',
            'width_middle'  => 'required|numeric|min:1',
            'width_bottom'  => 'required|numeric|min:1',
            'height_left'   => 'required|numeric|min:1',
            'height_right'  => 'required|numeric|min:1',
            'quantity'      => 'required|integer|min:1',
            'notes'         => 'nullable|string',
        ]);

        $item = $this->measurementService->addItem($measurement, $validated);

        return back()->with('success', 'Item added successfully.');
    }

    public function updateItem(Request $request, int $id, int $itemId)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $item = MeasurementItemModel::where('company_id', $companyId)
            ->where('measurement_id', $id)
            ->findOrFail($itemId);

        $validated = $request->validate([
            'location_name' => 'required|string|max:255',
            'type'          => 'required|in:sliding_window,casement_window,sliding_door,hinged_door,other',
            'width_top'     => 'required|numeric|min:1',
            'width_middle'  => 'required|numeric|min:1',
            'width_bottom'  => 'required|numeric|min:1',
            'height_left'   => 'required|numeric|min:1',
            'height_right'  => 'required|numeric|min:1',
            'quantity'      => 'required|integer|min:1',
            'notes'         => 'nullable|string',
        ]);

        $this->measurementService->updateItem($item, $validated);

        return back()->with('success', 'Item updated successfully.');
    }

    public function destroyItem(Request $request, int $id, int $itemId)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $item = MeasurementItemModel::where('company_id', $companyId)
            ->where('measurement_id', $id)
            ->findOrFail($itemId);

        $item->delete();

        return back()->with('success', 'Item removed.');
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function complete(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $measurement = MeasurementRecordModel::where('company_id', $companyId)->findOrFail($id);

        if ($measurement->items()->count() === 0) {
            return back()->withErrors(['error' => 'Cannot complete a measurement with no items.']);
        }

        $this->measurementService->completeMeasurement($measurement, $request->user()->cmsUser->id);

        return back()->with('success', 'Measurement marked as completed.');
    }

    public function generateQuotation(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $measurement = MeasurementRecordModel::with('items')
            ->where('company_id', $companyId)
            ->findOrFail($id);

        if ($measurement->items()->count() === 0) {
            return back()->withErrors(['error' => 'Cannot generate a quotation with no measurement items.']);
        }

        if ($measurement->isDraft()) {
            return back()->withErrors(['error' => 'Please complete the measurement before generating a quotation.']);
        }

        $quotation = $this->measurementService->generateQuotation($measurement, $request->user()->cmsUser->id);

        return redirect()->route('cms.quotations.show', $quotation->id)
            ->with('success', "Quotation {$quotation->quotation_number} generated successfully.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getItemTypes(): array
    {
        return [
            ['value' => 'sliding_window',  'label' => 'Sliding Window'],
            ['value' => 'casement_window', 'label' => 'Casement Window'],
            ['value' => 'sliding_door',    'label' => 'Sliding Door'],
            ['value' => 'hinged_door',     'label' => 'Hinged Door'],
            ['value' => 'other',           'label' => 'Other'],
        ];
    }
}
