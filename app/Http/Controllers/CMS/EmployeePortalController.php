<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\EmployeeSelfServiceService;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeePortalController extends Controller
{
    public function __construct(private EmployeeSelfServiceService $service)
    {
    }

    public function dashboard(Request $request)
    {
        $workerId = session('employee_worker_id');
        
        if (!$workerId) {
            return redirect()->route('cms.employee.login');
        }

        $data = $this->service->getEmployeeDashboard($workerId);

        return Inertia::render('CMS/Employee/Dashboard', $data);
    }

    public function profile(Request $request)
    {
        $workerId = session('employee_worker_id');
        $worker = WorkerModel::with(['department', 'leaveBalances', 'skills.skill', 'certifications'])
            ->findOrFail($workerId);

        return Inertia::render('CMS/Employee/Profile', [
            'worker' => $worker,
        ]);
    }

    public function documents(Request $request)
    {
        $workerId = session('employee_worker_id');
        
        $documents = \DB::table('cms_worker_documents')
            ->where('worker_id', $workerId)
            ->where('is_visible_to_employee', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $requests = \DB::table('cms_document_requests')
            ->where('worker_id', $workerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('CMS/Employee/Documents', [
            'documents' => $documents,
            'requests' => $requests,
        ]);
    }

    public function requestDocument(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:payslip,employment_letter,tax_certificate,leave_balance,attendance_report,other',
            'description' => 'nullable|string',
        ]);

        $workerId = session('employee_worker_id');
        $this->service->requestDocument($workerId, $validated);

        return redirect()->back()->with('success', 'Document request submitted successfully');
    }

    public function submitFeedback(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:feedback,suggestion,complaint,appreciation',
            'category' => 'required|in:workplace,management,facilities,benefits,training,other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'is_anonymous' => 'boolean',
        ]);

        $workerId = session('employee_worker_id');
        $this->service->submitFeedback($workerId, $validated);

        return redirect()->back()->with('success', 'Feedback submitted successfully');
    }
}
