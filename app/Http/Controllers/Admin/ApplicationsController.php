<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\JobApplicationModel;
use App\Infrastructure\Persistence\Eloquent\JobPostingModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:review-applications']);
    }

    /**
     * Display applications list
     */
    public function index(Request $request): Response
    {
        $query = JobApplicationModel::with(['jobPosting.position', 'jobPosting.department', 'reviewer'])
            ->orderBy('applied_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => JobApplicationModel::count(),
            'pending' => JobApplicationModel::where('status', 'submitted')->count(),
            'under_review' => JobApplicationModel::where('status', 'under_review')->count(),
            'hired' => JobApplicationModel::where('status', 'hired')->count(),
        ];

        return Inertia::render('Admin/Applications/Index', [
            'applications' => $applications,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show application details
     */
    public function show(JobApplicationModel $application): Response
    {
        $application->load(['jobPosting.position', 'jobPosting.department', 'reviewer']);

        return Inertia::render('Admin/Applications/Show', [
            'application' => $application,
        ]);
    }

    /**
     * Download application resume
     */
    public function downloadResume(JobApplicationModel $application): HttpResponse
    {
        if (!$application->hasResume()) {
            abort(404, 'Resume not found');
        }

        return Storage::download(
            $application->resume_path,
            "{$application->full_name}_Resume.pdf"
        );
    }

    /**
     * Update application status
     */
    public function updateStatus(Request $request, JobApplicationModel $application): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:submitted,under_review,interviewed,hired,rejected'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $application->update([
            'status' => $request->status,
            'admin_notes' => $request->notes,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Application status updated successfully.');
    }

    /**
     * Convert application to employee
     */
    public function hire(Request $request, JobApplicationModel $application): RedirectResponse
    {
        $request->validate([
            'create_user_account' => ['boolean'],
            'salary' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Check if email already exists in employees
        $existingEmployee = EmployeeModel::where('email', $application->email)->first();
        if ($existingEmployee) {
            return back()->withErrors(['email' => 'An employee with this email already exists.']);
        }

        $userId = null;

        // Create user account if requested
        if ($request->create_user_account) {
            $existingUser = User::where('email', $application->email)->first();
            if ($existingUser) {
                return back()->withErrors(['email' => 'A user account with this email already exists.']);
            }

            $user = User::create([
                'name' => $application->full_name,
                'email' => $application->email,
                'password' => Hash::make('temporary_password_' . now()->timestamp),
                'email_verified_at' => now(),
            ]);

            // Assign employee role
            $user->assignRole('employee');
            $userId = $user->id;
        }

        // Create employee record
        $employee = EmployeeModel::create([
            'user_id' => $userId,
            'application_id' => $application->id,
            'first_name' => $application->first_name,
            'last_name' => $application->last_name,
            'email' => $application->email,
            'phone' => $application->phone,
            'national_id' => $application->national_id,
            'address' => $application->address,
            'department_id' => $application->jobPosting->department_id,
            'position_id' => $application->jobPosting->position_id,
            'salary' => $request->salary,
            'start_date' => $request->start_date,
            'is_active' => true,
        ]);

        // Update application status
        $application->markAsHired(Auth::id(), $request->notes);

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', 'Application converted to employee successfully!');
    }

    /**
     * Bulk update applications
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'application_ids' => ['required', 'array'],
            'application_ids.*' => ['exists:job_applications,id'],
            'status' => ['required', 'in:under_review,rejected'],
        ]);

        JobApplicationModel::whereIn('id', $request->application_ids)
            ->update([
                'status' => $request->status,
                'reviewed_at' => now(),
                'reviewed_by' => Auth::id(),
            ]);

        $count = count($request->application_ids);
        return back()->with('success', "{$count} applications updated successfully.");
    }
}
