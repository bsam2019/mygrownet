<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\ShiftManagementService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\ShiftModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShiftController extends Controller
{
    public function __construct(
        private ShiftManagementService $shiftService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $shifts = $this->shiftService->getShifts($companyId, activeOnly: false);
        $statistics = $this->shiftService->getShiftStatistics($companyId);

        return Inertia::render('CMS/Shifts/Index', [
            'shifts' => $shifts,
            'statistics' => $statistics,
        ]);
    }

    public function create()
    {
        return Inertia::render('CMS/Shifts/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_name' => 'required|string|max:255',
            'shift_code' => 'required|string|max:50|unique:cms_shifts,shift_code,NULL,id,company_id,' . $request->user()->company_id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_duration_minutes' => 'nullable|integer|min:0',
            'grace_period_minutes' => 'nullable|integer|min:0',
            'minimum_hours_full_day' => 'nullable|numeric|min:0',
            'minimum_hours_half_day' => 'nullable|numeric|min:0',
            'overtime_threshold_minutes' => 'nullable|integer|min:0',
            'is_night_shift' => 'boolean',
            'night_shift_differential_percent' => 'nullable|numeric|min:0',
            'is_weekend_shift' => 'boolean',
            'weekend_differential_percent' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $shift = $this->shiftService->createShift([
            ...$validated,
            'company_id' => $request->user()->company_id,
        ]);

        return redirect()->route('cms.shifts.index')
            ->with('success', 'Shift created successfully.');
    }

    public function edit(ShiftModel $shift)
    {
        return Inertia::render('CMS/Shifts/Edit', [
            'shift' => $shift,
        ]);
    }

    public function update(Request $request, ShiftModel $shift)
    {
        $validated = $request->validate([
            'shift_name' => 'required|string|max:255',
            'shift_code' => 'required|string|max:50|unique:cms_shifts,shift_code,' . $shift->id . ',id,company_id,' . $request->user()->company_id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_duration_minutes' => 'nullable|integer|min:0',
            'grace_period_minutes' => 'nullable|integer|min:0',
            'minimum_hours_full_day' => 'nullable|numeric|min:0',
            'minimum_hours_half_day' => 'nullable|numeric|min:0',
            'overtime_threshold_minutes' => 'nullable|integer|min:0',
            'is_night_shift' => 'boolean',
            'night_shift_differential_percent' => 'nullable|numeric|min:0',
            'is_weekend_shift' => 'boolean',
            'weekend_differential_percent' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $this->shiftService->updateShift($shift, $validated);

        return redirect()->route('cms.shifts.index')
            ->with('success', 'Shift updated successfully.');
    }

    public function destroy(ShiftModel $shift)
    {
        try {
            $this->shiftService->deleteShift($shift);
            return redirect()->route('cms.shifts.index')
                ->with('success', 'Shift deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function assign(Request $request, ShiftModel $shift)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'notes' => 'nullable|string',
        ]);

        $this->shiftService->assignShiftToWorker([
            ...$validated,
            'company_id' => $request->user()->company_id,
            'shift_id' => $shift->id,
        ]);

        return back()->with('success', 'Shift assigned successfully.');
    }
}
