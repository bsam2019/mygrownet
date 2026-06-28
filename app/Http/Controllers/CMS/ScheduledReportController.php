<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\ScheduledReportService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\ScheduledReportModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScheduledReportController extends Controller
{
    public function __construct(
        private ScheduledReportService $scheduledReportService
    ) {}

    public function index(Request $request): Response
    {
        $cmsUser = $request->user()->cmsUser;
        $companyId = $cmsUser->company_id;

        $scheduledReports = ScheduledReportModel::where('company_id', $companyId)
            ->with(['createdBy', 'logs' => function ($query) {
                $query->latest()->limit(5);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/ScheduledReports/Index', [
            'scheduledReports' => $scheduledReports,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CMS/ScheduledReports/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'report_type' => 'required|in:sales,payments,expenses,profitLoss,cashbook,tax',
            'frequency' => 'required|in:daily,weekly,monthly',
            'day_of_week' => 'nullable|required_if:frequency,weekly|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'day_of_month' => 'nullable|required_if:frequency,monthly|integer|min:1|max:31',
            'time_of_day' => 'required|date_format:H:i',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|email',
            'format' => 'required|in:csv,pdf',
            'is_active' => 'nullable|boolean',
        ]);

        $cmsUser = $request->user()->cmsUser;
        
        $scheduledReport = $this->scheduledReportService->createScheduledReport(
            $cmsUser->company_id,
            $validated,
            $cmsUser->id
        );

        return redirect()->route('cms.scheduled-reports.index')
            ->with('success', 'Scheduled report created successfully.');
    }

    public function edit(Request $request, int $id): Response
    {
        $cmsUser = $request->user()->cmsUser;
        
        $scheduledReport = ScheduledReportModel::where('company_id', $cmsUser->company_id)
            ->findOrFail($id);

        return Inertia::render('CMS/ScheduledReports/Edit', [
            'scheduledReport' => $scheduledReport,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'report_type' => 'required|in:sales,payments,expenses,profitLoss,cashbook,tax',
            'frequency' => 'required|in:daily,weekly,monthly',
            'day_of_week' => 'nullable|required_if:frequency,weekly|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'day_of_month' => 'nullable|required_if:frequency,monthly|integer|min:1|max:31',
            'time_of_day' => 'required|date_format:H:i',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|email',
            'format' => 'required|in:csv,pdf',
            'is_active' => 'nullable|boolean',
        ]);

        $cmsUser = $request->user()->cmsUser;
        
        $scheduledReport = ScheduledReportModel::where('company_id', $cmsUser->company_id)
            ->findOrFail($id);

        $this->scheduledReportService->updateScheduledReport($id, $validated);

        return redirect()->route('cms.scheduled-reports.index')
            ->with('success', 'Scheduled report updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $cmsUser = $request->user()->cmsUser;
        
        $scheduledReport = ScheduledReportModel::where('company_id', $cmsUser->company_id)
            ->findOrFail($id);

        $this->scheduledReportService->deleteScheduledReport($id);

        return redirect()->route('cms.scheduled-reports.index')
            ->with('success', 'Scheduled report deleted successfully.');
    }

    public function toggle(Request $request, int $id)
    {
        $cmsUser = $request->user()->cmsUser;
        
        $scheduledReport = ScheduledReportModel::where('company_id', $cmsUser->company_id)
            ->findOrFail($id);

        $scheduledReport->is_active = !$scheduledReport->is_active;
        
        if ($scheduledReport->is_active) {
            $scheduledReport->next_run_at = $this->scheduledReportService->calculateNextRunTime($scheduledReport);
        }
        
        $scheduledReport->save();

        return back()->with('success', 'Scheduled report ' . ($scheduledReport->is_active ? 'activated' : 'deactivated') . ' successfully.');
    }
}
