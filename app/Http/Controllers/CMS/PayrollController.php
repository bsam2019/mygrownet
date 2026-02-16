<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\PayrollService;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerAttendanceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CommissionModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PayrollRunModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    public function __construct(
        private PayrollService $payrollService
    ) {}

    // Workers Management
    public function workersIndex(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $workers = WorkerModel::forCompany($companyId)
            ->when($request->search, fn($q, $search) => 
                $q->where(function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('worker_number', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                })
            )
            ->when($request->worker_type, fn($q) => $q->where('worker_type', $request->worker_type))
            ->when($request->status, fn($q) => $q->where('employment_status', $request->status))
            ->with(['createdBy.user', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/Workers/Index', [
            'workers' => $workers,
            'filters' => $request->only(['search', 'worker_type', 'status']),
        ]);
    }

    public function workersCreate(): Response
    {
        $companyId = auth()->user()->cmsUser->company_id;
        
        $departments = \App\Infrastructure\Persistence\Eloquent\CMS\DepartmentModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'department_name as name']);

        return Inertia::render('CMS/Workers/Create', [
            'departments' => $departments,
        ]);
    }

    public function workersStore(Request $request)
    {
        $validated = $request->validate([
            // Basic Info
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            
            // Personal Details
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            
            // Emergency Contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            
            // Employment Details
            'job_title' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:cms_departments,id',
            'hire_date' => 'nullable|date',
            'employment_type' => 'nullable|in:full_time,part_time,contract,temporary,intern',
            'employment_status' => 'nullable|in:active,on_leave,suspended,terminated',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'probation_end_date' => 'nullable|date',
            
            // Compensation
            'worker_type' => 'required|in:casual,contract,permanent',
            'hourly_rate' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'monthly_salary' => 'nullable|numeric|min:0',
            'salary_currency' => 'nullable|string|max:3',
            
            // Tax & Benefits
            'tax_number' => 'nullable|string|max:50',
            'napsa_number' => 'nullable|string|max:50',
            'nhima_number' => 'nullable|string|max:50',
            
            // Payment Method
            'payment_method' => 'required|in:cash,mobile_money,bank_transfer',
            'mobile_money_number' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $userId = $request->user()->cmsUser->id;

        // Combine first and last name for legacy 'name' field
        $validated['name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);
        $validated['company_id'] = $companyId;
        $validated['created_by'] = $userId;
        $validated['employment_status'] = $validated['employment_status'] ?? 'active';

        $worker = $this->payrollService->createWorker($validated);

        return redirect()
            ->route('cms.payroll.workers.show', $worker->id)
            ->with('success', 'Worker registered successfully');
    }

    public function workersShow(WorkerModel $worker): Response
    {
        $worker->load('createdBy.user');

        // Get attendance history
        $attendance = WorkerAttendanceModel::where('worker_id', $worker->id)
            ->with('job')
            ->orderBy('work_date', 'desc')
            ->paginate(20);

        // Get commissions
        $commissions = CommissionModel::where('worker_id', $worker->id)
            ->with(['job', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/Workers/Show', [
            'worker' => $worker,
            'attendance' => $attendance,
            'commissions' => $commissions,
        ]);
    }

    // Attendance Management
    public function attendanceStore(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'work_date' => 'required|date',
            'hours_worked' => 'nullable|numeric|min:0',
            'days_worked' => 'nullable|numeric|min:0',
            'work_description' => 'nullable|string',
        ]);

        $userId = $request->user()->cmsUser->id;

        $attendance = $this->payrollService->recordAttendance([
            ...$validated,
            'created_by' => $userId,
        ]);

        return back()->with('success', 'Attendance recorded successfully');
    }

    public function attendanceApprove(WorkerAttendanceModel $attendance)
    {
        $userId = request()->user()->cmsUser->id;

        $this->payrollService->approveAttendance($attendance, $userId);

        return back()->with('success', 'Attendance approved successfully');
    }

    // Commission Management
    public function commissionStore(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'nullable|exists:cms_workers,id',
            'cms_user_id' => 'nullable|exists:cms_users,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'invoice_id' => 'nullable|exists:cms_invoices,id',
            'commission_type' => 'required|in:sales,referral,performance,other',
            'base_amount' => 'required|numeric|min:0',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $userId = $request->user()->cmsUser->id;

        $commission = $this->payrollService->calculateCommission([
            ...$validated,
            'company_id' => $companyId,
            'created_by' => $userId,
        ]);

        return back()->with('success', 'Commission calculated successfully');
    }

    public function commissionApprove(CommissionModel $commission)
    {
        $userId = request()->user()->cmsUser->id;

        $this->payrollService->approveCommission($commission, $userId);

        return back()->with('success', 'Commission approved successfully');
    }

    // Payroll Runs
    public function payrollIndex(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $payrollRuns = PayrollRunModel::forCompany($companyId)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->period_type, fn($q) => $q->where('period_type', $request->period_type))
            ->with(['createdBy.user', 'approvedBy.user'])
            ->orderBy('period_end', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/Payroll/Index', [
            'payrollRuns' => $payrollRuns,
            'filters' => $request->only(['status', 'period_type']),
        ]);
    }

    public function payrollCreate(): Response
    {
        return Inertia::render('CMS/Payroll/Create');
    }

    public function payrollStore(Request $request)
    {
        $validated = $request->validate([
            'period_type' => 'required|in:weekly,bi-weekly,monthly',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $userId = $request->user()->cmsUser->id;

        $payrollRun = $this->payrollService->createPayrollRun([
            ...$validated,
            'company_id' => $companyId,
            'created_by' => $userId,
        ]);

        return redirect()
            ->route('cms.payroll.show', $payrollRun->id)
            ->with('success', 'Payroll run created successfully');
    }

    public function payrollShow(PayrollRunModel $payrollRun): Response
    {
        $payrollRun->load([
            'items.worker',
            'items.cmsUser.user',
            'createdBy.user',
            'approvedBy.user',
        ]);

        return Inertia::render('CMS/Payroll/Show', [
            'payrollRun' => $payrollRun,
        ]);
    }

    public function payrollApprove(PayrollRunModel $payrollRun)
    {
        $userId = request()->user()->cmsUser->id;

        $this->payrollService->approvePayrollRun($payrollRun, $userId);

        return back()->with('success', 'Payroll run approved successfully');
    }

    public function payrollMarkPaid(Request $request, PayrollRunModel $payrollRun)
    {
        $validated = $request->validate([
            'paid_date' => 'required|date',
        ]);

        $this->payrollService->markPayrollAsPaid($payrollRun, $validated['paid_date']);

        return back()->with('success', 'Payroll marked as paid successfully');
    }
}
