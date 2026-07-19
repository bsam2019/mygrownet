<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\LeaveManagementService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\LeaveRequestModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LeaveTypeModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaveController extends Controller
{
    public function __construct(
        private LeaveManagementService $leaveService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $leaveRequests = LeaveRequestModel::with(['worker', 'leaveType', 'approvedBy', 'rejectedBy'])
            ->where('company_id', $companyId)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->worker_id, fn($q) => $q->where('worker_id', $request->worker_id))
            ->latest()
            ->paginate(20);

        $workers = WorkerModel::where('company_id', $companyId)
            ->where('employment_status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'worker_number']);

        return Inertia::render('CMS/Leave/Index', [
            'leaveRequests' => $leaveRequests,
            'workers' => $workers,
            'filters' => $request->only(['status', 'worker_id']),
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $workers = WorkerModel::where('company_id', $companyId)
            ->where('employment_status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'worker_number', 'job_title']);

        $leaveTypes = LeaveTypeModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        return Inertia::render('CMS/Leave/Create', [
            'workers' => $workers,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'leave_type_id' => 'required|exists:cms_leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'contact_during_leave' => 'nullable|string',
        ]);

        $validated['company_id'] = $request->user()->company_id;
        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'pending';

        $leaveRequest = $this->leaveService->createLeaveRequest($validated);

        return redirect()
            ->route('cms.leave.show', $leaveRequest->id)
            ->with('success', 'Leave request created successfully');
    }

    public function show(LeaveRequestModel $leave)
    {
        $leave->load(['worker', 'leaveType', 'approvedBy', 'rejectedBy', 'createdBy']);
        
        // Get leave balance
        $balance = $this->leaveService->getLeaveBalance(
            $leave->worker_id,
            $leave->leave_type_id,
            date('Y')
        );

        return Inertia::render('CMS/Leave/Show', [
            'leaveRequest' => $leave,
            'leaveBalance' => $balance,
        ]);
    }

    public function approve(Request $request, LeaveRequestModel $leave)
    {
        $validated = $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $this->leaveService->approveLeaveRequest(
            $leave->id,
            $request->user()->id,
            $validated['approval_notes'] ?? null
        );

        return redirect()
            ->route('cms.leave.show', $leave->id)
            ->with('success', 'Leave request approved successfully');
    }

    public function reject(Request $request, LeaveRequestModel $leave)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $this->leaveService->rejectLeaveRequest(
            $leave->id,
            $request->user()->id,
            $validated['rejection_reason']
        );

        return redirect()
            ->route('cms.leave.show', $leave->id)
            ->with('success', 'Leave request rejected');
    }

    public function balance(Request $request)
    {
        $companyId = $request->user()->company_id;
        $year = $request->get('year', date('Y'));
        
        $workers = WorkerModel::with(['leaveBalances' => function($q) use ($year) {
            $q->where('year', $year)->with('leaveType');
        }])
            ->where('company_id', $companyId)
            ->where('employment_status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::render('CMS/Leave/Balance', [
            'workers' => $workers,
            'year' => $year,
        ]);
    }
}
