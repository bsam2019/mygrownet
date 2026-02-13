<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\TimeTrackingService;
use App\Infrastructure\Persistence\Eloquent\CMS\TimeEntryModel;
use App\Infrastructure\Persistence\Eloquent\CMS\TimesheetModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimeTrackingController extends Controller
{
    public function __construct(
        private TimeTrackingService $timeTrackingService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $entries = TimeEntryModel::where('company_id', $companyId)
            ->with(['worker', 'job', 'createdBy'])
            ->when($request->worker_id, fn($q) => $q->where('worker_id', $request->worker_id))
            ->when($request->job_id, fn($q) => $q->where('job_id', $request->job_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->is_running !== null, fn($q) => $q->where('is_running', $request->is_running))
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        $workers = WorkerModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'worker_number']);

        $jobs = JobModel::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('job_number')
            ->get(['id', 'job_number', 'description']);

        return Inertia::render('CMS/TimeTracking/Index', [
            'entries' => $entries,
            'workers' => $workers,
            'jobs' => $jobs,
            'filters' => $request->only(['worker_id', 'job_id', 'status', 'is_running']),
        ]);
    }

    public function startTimer(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'description' => 'nullable|string|max:500',
            'is_billable' => 'boolean',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $entry = $this->timeTrackingService->startTimer([
            ...$validated,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->back()->with('success', 'Timer started successfully.');
    }

    public function stopTimer(Request $request, TimeEntryModel $entry)
    {
        $this->authorize('update', $entry);

        $this->timeTrackingService->stopTimer($entry, $request->user()->id);

        return redirect()->back()->with('success', 'Timer stopped successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'is_billable' => 'boolean',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $entry = $this->timeTrackingService->createManualEntry([
            ...$validated,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->back()->with('success', 'Time entry created successfully.');
    }

    public function update(Request $request, TimeEntryModel $entry)
    {
        $this->authorize('update', $entry);

        $validated = $request->validate([
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'is_billable' => 'boolean',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $this->timeTrackingService->updateEntry($entry, $validated, $request->user()->id);

        return redirect()->back()->with('success', 'Time entry updated successfully.');
    }

    public function submit(Request $request, TimeEntryModel $entry)
    {
        $this->authorize('update', $entry);

        $this->timeTrackingService->submitEntry($entry, $request->user()->id);

        return redirect()->back()->with('success', 'Time entry submitted for approval.');
    }

    public function approve(Request $request, TimeEntryModel $entry)
    {
        $this->authorize('approve', $entry);

        $this->timeTrackingService->approveEntry($entry, $request->user()->id);

        return redirect()->back()->with('success', 'Time entry approved.');
    }

    public function reject(Request $request, TimeEntryModel $entry)
    {
        $this->authorize('approve', $entry);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $this->timeTrackingService->rejectEntry($entry, $request->user()->id, $validated['rejection_reason']);

        return redirect()->back()->with('success', 'Time entry rejected.');
    }

    public function destroy(TimeEntryModel $entry)
    {
        $this->authorize('delete', $entry);

        if ($entry->status !== 'draft') {
            return redirect()->back()->with('error', 'Can only delete draft entries.');
        }

        $entry->delete();

        return redirect()->back()->with('success', 'Time entry deleted.');
    }

    public function timesheets(Request $request)
    {
        $companyId = $request->user()->company_id;

        $timesheets = TimesheetModel::where('company_id', $companyId)
            ->with(['worker', 'submittedBy', 'approvedBy'])
            ->when($request->worker_id, fn($q) => $q->where('worker_id', $request->worker_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('start_date', 'desc')
            ->paginate(20);

        $workers = WorkerModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'worker_number']);

        return Inertia::render('CMS/TimeTracking/Timesheets', [
            'timesheets' => $timesheets,
            'workers' => $workers,
            'filters' => $request->only(['worker_id', 'status']),
        ]);
    }

    public function generateTimesheet(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'period_type' => 'required|in:weekly,biweekly,monthly',
        ]);

        $timesheet = $this->timeTrackingService->generateTimesheet(
            $request->user()->company_id,
            $validated['worker_id'],
            $validated['start_date'],
            $validated['end_date'],
            $validated['period_type']
        );

        return redirect()->back()->with('success', 'Timesheet generated successfully.');
    }

    public function submitTimesheet(Request $request, TimesheetModel $timesheet)
    {
        $this->authorize('update', $timesheet);

        $this->timeTrackingService->submitTimesheet($timesheet, $request->user()->id);

        return redirect()->back()->with('success', 'Timesheet submitted for approval.');
    }

    public function approveTimesheet(Request $request, TimesheetModel $timesheet)
    {
        $this->authorize('approve', $timesheet);

        $this->timeTrackingService->approveTimesheet($timesheet, $request->user()->id);

        return redirect()->back()->with('success', 'Timesheet approved.');
    }

    public function rejectTimesheet(Request $request, TimesheetModel $timesheet)
    {
        $this->authorize('approve', $timesheet);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $this->timeTrackingService->rejectTimesheet($timesheet, $request->user()->id, $validated['rejection_reason']);

        return redirect()->back()->with('success', 'Timesheet rejected.');
    }

    public function reports(Request $request)
    {
        $companyId = $request->user()->company_id;

        $filters = $request->validate([
            'worker_id' => 'nullable|exists:cms_workers,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $report = $this->timeTrackingService->getTimeReport($companyId, $filters);

        $workers = WorkerModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'worker_number']);

        $jobs = JobModel::where('company_id', $companyId)
            ->orderBy('job_number')
            ->get(['id', 'job_number', 'description']);

        return Inertia::render('CMS/TimeTracking/Reports', [
            'report' => $report,
            'workers' => $workers,
            'jobs' => $jobs,
            'filters' => $filters,
        ]);
    }
}
