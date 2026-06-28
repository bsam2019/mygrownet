<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Installation\Services\InstallationService;
use App\Infrastructure\Persistence\Eloquent\CMS\InstallationScheduleModel;
use App\Infrastructure\Persistence\Eloquent\CMS\DefectModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstallationController extends Controller
{
    public function __construct(
        private InstallationService $installationService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->current_company_id;
        
        $schedules = InstallationScheduleModel::where('company_id', $companyId)
            ->with(['job.customer', 'teamLeader'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->latest('scheduled_date')
            ->paginate(20);

        $statistics = $this->installationService->getScheduleStatistics($companyId);

        return Inertia::render('CMS/Installation/Index', [
            'schedules' => $schedules,
            'statistics' => $statistics,
            'filters' => $request->only('status'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|exists:cms_jobs,id',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'team_leader_id' => 'required|exists:users,id',
            'site_contact_name' => 'nullable|string|max:255',
            'site_contact_phone' => 'nullable|string|max:50',
            'equipment_required' => 'nullable|string',
            'materials_required' => 'nullable|string',
            'estimated_hours' => 'nullable|numeric|min:0',
            'special_instructions' => 'nullable|string',
            'team_members' => 'nullable|array',
            'team_members.*.user_id' => 'required|exists:users,id',
            'team_members.*.role' => 'required|in:leader,technician,helper,driver',
        ]);

        $schedule = $this->installationService->createSchedule(
            $request->user()->current_company_id,
            $validated
        );

        return redirect()->route('cms.installation.show', $schedule->id)
            ->with('success', 'Installation scheduled successfully');
    }

    public function show(int $id)
    {
        $schedule = InstallationScheduleModel::with([
            'job.customer',
            'teamLeader',
            'teamMembers.user',
            'siteVisits.photos',
            'photos',
            'signoff',
            'defects.photos'
        ])->findOrFail($id);

        return Inertia::render('CMS/Installation/Show', [
            'schedule' => $schedule,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'scheduled_date' => 'sometimes|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'team_leader_id' => 'sometimes|exists:users,id',
            'site_contact_name' => 'nullable|string|max:255',
            'site_contact_phone' => 'nullable|string|max:50',
            'equipment_required' => 'nullable|string',
            'materials_required' => 'nullable|string',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'special_instructions' => 'nullable|string',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
        ]);

        $schedule = $this->installationService->updateSchedule($id, $validated);

        return back()->with('success', 'Installation updated successfully');
    }

    public function recordVisit(Request $request, int $scheduleId)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'visit_type' => 'required|in:installation,inspection,repair,maintenance,measurement',
            'arrival_time' => 'nullable|date_format:H:i',
            'departure_time' => 'nullable|date_format:H:i',
            'work_performed' => 'nullable|string',
            'issues_encountered' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*.photo_type' => 'required|in:before,during,after,issue,completion',
            'photos.*.file_path' => 'required|string',
            'photos.*.caption' => 'nullable|string',
        ]);

        $visit = $this->installationService->recordSiteVisit($scheduleId, $validated);

        return back()->with('success', 'Site visit recorded successfully');
    }

    public function recordSignoff(Request $request, int $scheduleId)
    {
        $validated = $request->validate([
            'signoff_date' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string|max:50',
            'signature_data' => 'required|string',
            'satisfaction_rating' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string',
        ]);

        $signoff = $this->installationService->recordCustomerSignoff($scheduleId, $validated);

        return back()->with('success', 'Customer sign-off recorded successfully');
    }

    public function defects(Request $request)
    {
        $companyId = $request->user()->current_company_id;
        
        $defects = DefectModel::where('company_id', $companyId)
            ->with(['job.customer', 'reportedBy', 'assignedTo'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->severity, fn($q, $severity) => $q->where('severity', $severity))
            ->latest('reported_date')
            ->paginate(20);

        return Inertia::render('CMS/Installation/Defects/Index', [
            'defects' => $defects,
            'filters' => $request->only(['status', 'severity']),
        ]);
    }

    public function storeDefect(Request $request)
    {
        $validated = $request->validate([
            'installation_schedule_id' => 'nullable|exists:cms_installation_schedules,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:minor,moderate,major,critical',
            'location' => 'nullable|string|max:255',
            'reported_by' => 'required|exists:users,id',
            'reported_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'target_resolution_date' => 'nullable|date',
            'photos' => 'nullable|array',
            'photos.*.file_path' => 'required|string',
            'photos.*.caption' => 'nullable|string',
        ]);

        $defect = $this->installationService->createDefect(
            $request->user()->current_company_id,
            $validated
        );

        return back()->with('success', 'Defect recorded successfully');
    }

    public function resolveDefect(Request $request, int $defectId)
    {
        $validated = $request->validate([
            'resolved_by' => 'required|exists:users,id',
            'resolved_date' => 'required|date',
            'resolution_notes' => 'nullable|string',
        ]);

        $defect = $this->installationService->resolveDefect($defectId, $validated);

        return back()->with('success', 'Defect resolved successfully');
    }
}
