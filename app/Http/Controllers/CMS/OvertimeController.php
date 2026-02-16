<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\OvertimeService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\OvertimeRecordModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OvertimeController extends Controller
{
    public function __construct(
        private OvertimeService $overtimeService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $filters = [
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
            'overtime_type' => $request->input('overtime_type'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $records = $this->overtimeService->getOvertimeRecords($companyId, $filters);

        return Inertia::render('CMS/Overtime/Index', [
            'records' => $records,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'overtime_date' => 'required|date',
            'overtime_minutes' => 'required|integer|min:1',
            'overtime_type' => 'required|in:daily,weekly,holiday,weekend,manual',
            'overtime_rate_multiplier' => 'nullable|numeric|min:1',
            'base_hourly_rate' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $record = $this->overtimeService->createManualOvertime([
            ...$validated,
            'company_id' => $request->user()->company_id,
        ]);

        return redirect()->route('cms.overtime.index')
            ->with('success', 'Overtime record created successfully.');
    }

    public function approve(OvertimeRecordModel $record)
    {
        try {
            $this->overtimeService->approveOvertime($record);
            return back()->with('success', 'Overtime approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, OvertimeRecordModel $record)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        try {
            $this->overtimeService->rejectOvertime($record, $validated['rejection_reason']);
            return back()->with('success', 'Overtime rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function summary(Request $request)
    {
        $companyId = $request->user()->company_id;
        $workerId = $request->input('worker_id', $request->user()->id);
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->input('end_date', now()->endOfMonth()));

        $summary = $this->overtimeService->getOvertimeSummary(
            $companyId,
            $workerId,
            $startDate,
            $endDate
        );

        return response()->json($summary);
    }
}
