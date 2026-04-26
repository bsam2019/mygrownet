<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Labour\Services\LabourService;
use App\Infrastructure\Persistence\Eloquent\CMS\CrewModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LabourTimesheetModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LabourController extends Controller
{
    public function __construct(
        private LabourService $labourService
    ) {}

    // Crews
    public function crewsIndex(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $crews = CrewModel::where('company_id', $companyId)
            ->with(['supervisor', 'members.employee'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('CMS/Labour/Crews/Index', [
            'crews' => $crews,
            'filters' => $request->only(['search']),
        ]);
    }

    public function crewsCreate()
    {
        return Inertia::render('CMS/Labour/Crews/Create');
    }

    public function crewsStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'supervisor_id' => 'required|exists:cms_employees,id',
            'specialization' => 'nullable|string|max:100',
            'members' => 'nullable|array',
            'members.*.employee_id' => 'required|exists:cms_employees,id',
            'members.*.role' => 'required|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $validated['company_id'] = $companyId;

        $crew = $this->labourService->createCrew($validated);

        return redirect()->route('cms.labour.crews.show', $crew->id)
            ->with('success', 'Crew created successfully');
    }

    public function crewsShow(CrewModel $crew)
    {
        $crew->load(['supervisor', 'members.employee', 'timesheets']);
        $stats = $this->labourService->getCrewStats($crew);

        return Inertia::render('CMS/Labour/Crews/Show', [
            'crew' => $crew,
            'stats' => $stats,
        ]);
    }

    public function crewsUpdate(Request $request, CrewModel $crew)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'supervisor_id' => 'required|exists:cms_employees,id',
            'specialization' => 'nullable|string|max:100',
        ]);

        $this->labourService->updateCrew($crew, $validated);

        return back()->with('success', 'Crew updated successfully');
    }

    public function addCrewMember(Request $request, CrewModel $crew)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:cms_employees,id',
            'role' => 'required|string',
        ]);

        $this->labourService->addCrewMember($crew, $validated);

        return back()->with('success', 'Crew member added successfully');
    }

    public function removeCrewMember(CrewModel $crew, $memberId)
    {
        $this->labourService->removeCrewMember($memberId);

        return back()->with('success', 'Crew member removed successfully');
    }

    // Timesheets
    public function timesheetsIndex(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $timesheets = LabourTimesheetModel::whereHas('crew', fn($q) => $q->where('company_id', $companyId))
            ->with(['crew', 'project', 'job', 'approvedBy'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->where('work_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->where('work_date', '<=', $request->date_to))
            ->orderBy('work_date', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/Labour/Timesheets/Index', [
            'timesheets' => $timesheets,
            'filters' => $request->only(['status', 'date_from', 'date_to']),
        ]);
    }

    public function timesheetsCreate()
    {
        return Inertia::render('CMS/Labour/Timesheets/Create');
    }

    public function timesheetsStore(Request $request)
    {
        $validated = $request->validate([
            'crew_id' => 'required|exists:cms_crews,id',
            'project_id' => 'nullable|exists:cms_projects,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'work_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'break_minutes' => 'nullable|integer|min:0',
            'work_description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $timesheet = $this->labourService->createTimesheet($validated);

        return redirect()->route('cms.labour.timesheets.index')
            ->with('success', 'Timesheet created successfully');
    }

    public function timesheetsApprove(Request $request, LabourTimesheetModel $timesheet)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $this->labourService->approveTimesheet($timesheet, $request->user()->cmsUser->id, $validated['notes'] ?? null);

        return back()->with('success', 'Timesheet approved successfully');
    }

    public function timesheetsReject(Request $request, LabourTimesheetModel $timesheet)
    {
        $validated = $request->validate([
            'notes' => 'required|string',
        ]);

        $this->labourService->rejectTimesheet($timesheet, $request->user()->cmsUser->id, $validated['notes']);

        return back()->with('success', 'Timesheet rejected');
    }

    // Productivity Reports
    public function productivity(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        
        $report = $this->labourService->getProductivityReport($companyId, [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'crew_id' => $request->crew_id,
        ]);

        return Inertia::render('CMS/Labour/Productivity', [
            'report' => $report,
            'filters' => $request->only(['date_from', 'date_to', 'crew_id']),
        ]);
    }
}
