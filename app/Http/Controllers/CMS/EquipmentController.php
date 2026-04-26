<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Equipment\Services\EquipmentService;
use App\Infrastructure\Persistence\Eloquent\CMS\EquipmentModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EquipmentController extends Controller
{
    public function __construct(
        private EquipmentService $equipmentService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $equipment = EquipmentModel::where('company_id', $companyId)
            ->when($request->search, fn($q) => $q->where(function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('equipment_code', 'like', "%{$request->search}%");
            }))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('CMS/Equipment/Index', [
            'equipment' => $equipment,
            'filters' => $request->only(['search', 'type', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('CMS/Equipment/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'equipment_code' => 'required|string|max:50',
            'type' => 'required|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'maintenance_interval_days' => 'nullable|integer|min:1',
            'last_maintenance_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $validated['company_id'] = $companyId;

        $equipment = $this->equipmentService->createEquipment($validated);

        return redirect()->route('cms.equipment.show', $equipment->id)
            ->with('success', 'Equipment created successfully');
    }

    public function show(EquipmentModel $equipment)
    {
        $equipment->load(['maintenanceRecords', 'usageRecords', 'rentals']);
        $stats = $this->equipmentService->getEquipmentStats($equipment);

        return Inertia::render('CMS/Equipment/Show', [
            'equipment' => $equipment,
            'stats' => $stats,
        ]);
    }

    public function edit(EquipmentModel $equipment)
    {
        return Inertia::render('CMS/Equipment/Edit', [
            'equipment' => $equipment,
        ]);
    }

    public function update(Request $request, EquipmentModel $equipment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'maintenance_interval_days' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $this->equipmentService->updateEquipment($equipment, $validated);

        return redirect()->route('cms.equipment.show', $equipment->id)
            ->with('success', 'Equipment updated successfully');
    }

    public function destroy(EquipmentModel $equipment)
    {
        $equipment->delete();

        return redirect()->route('cms.equipment.index')
            ->with('success', 'Equipment deleted successfully');
    }

    public function scheduleMaintenance(Request $request, EquipmentModel $equipment)
    {
        $validated = $request->validate([
            'maintenance_type' => 'required|string',
            'scheduled_date' => 'required|date',
            'description' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        $maintenance = $this->equipmentService->scheduleMaintenance($equipment, $validated);

        return back()->with('success', 'Maintenance scheduled successfully');
    }

    public function completeMaintenance(Request $request, $maintenanceId)
    {
        $validated = $request->validate([
            'completed_date' => 'required|date',
            'actual_cost' => 'required|numeric|min:0',
            'performed_by' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $this->equipmentService->completeMaintenance($maintenanceId, $validated);

        return back()->with('success', 'Maintenance completed successfully');
    }

    public function recordUsage(Request $request, EquipmentModel $equipment)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:cms_projects,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'hours_used' => 'nullable|numeric|min:0',
            'operator_id' => 'nullable|exists:cms_employees,id',
            'notes' => 'nullable|string',
        ]);

        $usage = $this->equipmentService->recordUsage($equipment, $validated);

        return back()->with('success', 'Usage recorded successfully');
    }

    public function createRental(Request $request, EquipmentModel $equipment)
    {
        $validated = $request->validate([
            'rented_to' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'daily_rate' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $rental = $this->equipmentService->createRental($equipment, $validated);

        return back()->with('success', 'Rental created successfully');
    }
}
