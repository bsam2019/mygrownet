<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\HRReportsService;
use App\Infrastructure\Persistence\Eloquent\CMS\ReportTemplateModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SavedReportModel;
use App\Infrastructure\Persistence\Eloquent\CMS\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LeaveTypeModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HRReportsController extends Controller
{
    public function __construct(
        private HRReportsService $hrReportsService
    ) {}

    /**
     * Display HR reports dashboard
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $templates = ReportTemplateModel::where('company_id', $companyId)
            ->orWhere('is_system', true)
            ->with('creator')
            ->get();

        $savedReports = SavedReportModel::whereHas('template', function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->orWhere('is_system', true);
        })
            ->where('generated_by', $request->user()->id)
            ->with('template')
            ->latest()
            ->take(10)
            ->get();

        // Get filter options
        $departments = DepartmentModel::where('company_id', $companyId)
            ->select('id', 'name')
            ->get();

        $workers = WorkerModel::where('company_id', $companyId)
            ->select('id', 'first_name', 'last_name')
            ->get()
            ->map(fn($w) => [
                'id' => $w->id,
                'name' => $w->first_name . ' ' . $w->last_name,
            ]);

        $leaveTypes = LeaveTypeModel::where('company_id', $companyId)
            ->select('id', 'name')
            ->get();

        return Inertia::render('CMS/HRReports/Index', [
            'templates' => $templates,
            'savedReports' => $savedReports,
            'departments' => $departments,
            'workers' => $workers,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    /**
     * Generate a report
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:cms_report_templates,id',
            'filters' => 'nullable|array',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $companyId = $request->user()->company_id;
        $template = ReportTemplateModel::findOrFail($validated['template_id']);

        $filters = array_merge($validated['filters'] ?? [], [
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
        ]);

        // Generate report based on category
        $reportData = match ($template->category) {
            'headcount' => $this->hrReportsService->generateHeadcountReport($companyId, $filters),
            'attendance' => $this->hrReportsService->generateAttendanceReport($companyId, $filters),
            'leave' => $this->hrReportsService->generateLeaveReport($companyId, $filters),
            'payroll' => $this->hrReportsService->generatePayrollReport($companyId, $filters),
            'performance' => $this->hrReportsService->generatePerformanceReport($companyId, $filters),
            'training' => $this->hrReportsService->generateTrainingReport($companyId, $filters),
            default => ['error' => 'Unknown report category'],
        };

        // Save report record
        $savedReport = SavedReportModel::create([
            'template_id' => $template->id,
            'generated_by' => $request->user()->id,
            'report_name' => $template->name . ' - ' . now()->format('Y-m-d H:i'),
            'filters_used' => $filters,
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'file_format' => 'json',
        ]);

        return response()->json([
            'success' => true,
            'report' => $reportData,
            'saved_report_id' => $savedReport->id,
        ]);
    }

    /**
     * View a saved report
     */
    public function show(Request $request, int $id)
    {
        $savedReport = SavedReportModel::with('template')
            ->where('generated_by', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'saved_report' => $savedReport,
        ]);
    }

    /**
     * Delete a saved report
     */
    public function destroy(Request $request, int $id)
    {
        $savedReport = SavedReportModel::where('generated_by', $request->user()->id)
            ->findOrFail($id);

        $savedReport->delete();

        return response()->json([
            'success' => true,
            'message' => 'Report deleted successfully',
        ]);
    }
}
