<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\AttendanceService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\AttendanceRecordModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    /**
     * Get the company ID for the authenticated CMS user
     */
    private function getCompanyId(Request $request): ?int
    {
        $user = $request->user();
        $cmsUser = \App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel::where('user_id', $user->id)->first();
        
        return $cmsUser?->company_id;
    }

    /**
     * Ensure user has a company association or redirect
     */
    private function ensureCompanyAccess(Request $request)
    {
        $companyId = $this->getCompanyId($request);
        
        if (!$companyId) {
            return redirect()->route('cms.dashboard')
                ->with('error', 'You must be associated with a company to access this feature.');
        }
        
        return $companyId;
    }

    public function index(Request $request)
    {
        $companyId = $this->ensureCompanyAccess($request);
        if ($companyId instanceof \Illuminate\Http\RedirectResponse) {
            return $companyId;
        }
        
        $filters = [
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
            'start_date' => $request->input('start_date', now()->startOfMonth()),
            'end_date' => $request->input('end_date', now()->endOfMonth()),
        ];

        $records = $this->attendanceService->getAttendanceRecords($companyId, $filters);
        
        $workers = \App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel::where('company_id', $companyId)
            ->active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('CMS/Attendance/Index', [
            'records' => $records,
            'workers' => $workers,
            'filters' => $filters,
        ]);
    }

    public function clockIn(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'location' => 'nullable|array',
            'location.lat' => 'nullable|numeric',
            'location.lng' => 'nullable|numeric',
            'location.address' => 'nullable|string',
            'photo_path' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $companyId = $this->getCompanyId($request);
            if (!$companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be associated with a company.',
                ], 403);
            }
            
            $record = $this->attendanceService->clockIn([
                ...$validated,
                'company_id' => $companyId,
                'device' => $request->input('device', 'web'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clocked in successfully.',
                'record' => $record,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function clockOut(Request $request, AttendanceRecordModel $record)
    {
        $validated = $request->validate([
            'location' => 'nullable|array',
            'location.lat' => 'nullable|numeric',
            'location.lng' => 'nullable|numeric',
            'location.address' => 'nullable|string',
            'photo_path' => 'nullable|string',
        ]);

        try {
            $record = $this->attendanceService->clockOut($record, [
                ...$validated,
                'device' => $request->input('device', 'web'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clocked out successfully.',
                'record' => $record,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'attendance_date' => 'required|date',
            'clock_in_time' => 'required|date_format:Y-m-d H:i:s',
            'clock_out_time' => 'nullable|date_format:Y-m-d H:i:s|after:clock_in_time',
            'status' => 'required|in:present,absent,late,half_day,on_leave',
            'notes' => 'nullable|string',
        ]);

        try {
            $companyId = $this->getCompanyId($request);
            if (!$companyId) {
                return back()->with('error', 'You must be associated with a company.');
            }
            
            $record = $this->attendanceService->createManualAttendance([
                ...$validated,
                'company_id' => $companyId,
            ]);

            return redirect()->route('cms.attendance.index')
                ->with('success', 'Attendance record created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function summary(Request $request)
    {
        $companyId = $this->getCompanyId($request);
        if (!$companyId) {
            return response()->json(['error' => 'Company not found'], 403);
        }
        
        $workerId = $request->input('worker_id', $request->user()->id);
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->input('end_date', now()->endOfMonth()));

        $summary = $this->attendanceService->getAttendanceSummary(
            $companyId,
            $workerId,
            $startDate,
            $endDate
        );

        return response()->json($summary);
    }

    public function calendar(Request $request)
    {
        $companyId = $this->ensureCompanyAccess($request);
        if ($companyId instanceof \Illuminate\Http\RedirectResponse) {
            return $companyId;
        }
        
        $month = $request->input('month', now()->format('Y-m'));
        
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $records = $this->attendanceService->getAttendanceRecords($companyId, [
            'worker_id' => $request->input('worker_id'),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return Inertia::render('CMS/Attendance/Calendar', [
            'records' => $records,
            'month' => $month,
        ]);
    }
}
