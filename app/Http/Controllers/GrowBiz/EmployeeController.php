<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Domain\GrowBiz\Services\EmployeeManagementService;
use App\Domain\GrowBiz\Services\EmployeeInvitationService;
use App\Domain\GrowBiz\ValueObjects\EmployeeStatus;
use App\Domain\GrowBiz\Exceptions\DuplicateEmployeeException;
use App\Domain\GrowBiz\Exceptions\EmployeeHasActiveTasksException;
use App\Domain\GrowBiz\Exceptions\EmployeeNotFoundException;
use App\Domain\GrowBiz\Exceptions\OperationFailedException;
use App\Domain\GrowBiz\Exceptions\UnauthorizedAccessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeManagementService $employeeService,
        private EmployeeInvitationService $invitationService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = [
            'status' => $request->get('status'),
            'department' => $request->get('department'),
            'search' => $request->get('search'),
        ];

        $employees = $this->employeeService->getEmployeesForOwner($user->id, $filters);
        $stats = $this->employeeService->getEmployeeStatistics($user->id);
        $departments = $this->employeeService->getDepartments($user->id);

        // Convert Employee entities to arrays for the frontend
        $employeesArray = array_map(fn($employee) => $employee->toArray(), $employees);

        return Inertia::render('GrowBiz/Employees/Index', [
            'employees' => $employeesArray,
            'stats' => $stats,
            'departments' => $departments,
            'filters' => $filters,
            'statuses' => EmployeeStatus::all(),
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $departments = $this->employeeService->getDepartments($user->id);

        return Inertia::render('GrowBiz/Employees/Create', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'hourly_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        
        try {
            $employee = $this->employeeService->createEmployee(
                ownerId: $user->id,
                firstName: $validated['first_name'],
                lastName: $validated['last_name'],
                email: $validated['email'],
                phone: $validated['phone'] ?? null,
                position: $validated['position'] ?? null,
                department: $validated['department'] ?? null,
                hireDate: $validated['hire_date'] ?? null,
                hourlyRate: $validated['hourly_rate'] ?? null,
                notes: $validated['notes'] ?? null
            );

            return redirect()->route('growbiz.employees.show', $employee->id())
                ->with('success', 'Employee added successfully.');
        } catch (DuplicateEmployeeException $e) {
            return back()
                ->withInput()
                ->withErrors(['email' => $e->getMessage()]);
        } catch (OperationFailedException $e) {
            Log::error('Employee creation failed in controller', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', 'Failed to create employee. Please try again.');
        }
    }

    public function show(int $id)
    {
        try {
            $employee = $this->employeeService->getEmployeeById($id);
            $this->authorizeEmployee($employee);

            $taskStats = $this->employeeService->getEmployeeTaskStats($id);

            return Inertia::render('GrowBiz/Employees/Show', [
                'employee' => $employee->toArray(),
                'taskStats' => $taskStats,
            ]);
        } catch (EmployeeNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        }
    }

    public function edit(int $id)
    {
        try {
            $employee = $this->employeeService->getEmployeeById($id);
            $this->authorizeEmployee($employee);
            
            $user = Auth::user();
            $departments = $this->employeeService->getDepartments($user->id);

            return Inertia::render('GrowBiz/Employees/Edit', [
                'employee' => $employee->toArray(),
                'departments' => $departments,
                'statuses' => EmployeeStatus::all(),
            ]);
        } catch (EmployeeNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'status' => 'required|string|in:active,inactive,on_leave,terminated',
            'hire_date' => 'nullable|date',
            'hourly_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $employee = $this->employeeService->getEmployeeById($id);
            $this->authorizeEmployee($employee);

            $this->employeeService->updateEmployee($id, $validated);

            return redirect()->route('growbiz.employees.show', $id)
                ->with('success', 'Employee updated successfully.');
        } catch (EmployeeNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (OperationFailedException $e) {
            Log::error('Employee update failed in controller', [
                'employee_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', 'Failed to update employee. Please try again.');
        }
    }

    public function destroy(int $id)
    {
        try {
            $employee = $this->employeeService->getEmployeeById($id);
            $this->authorizeEmployee($employee);

            $this->employeeService->deleteEmployee($id);

            return redirect()->route('growbiz.employees.index')
                ->with('success', 'Employee removed successfully.');
        } catch (EmployeeNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (EmployeeHasActiveTasksException $e) {
            return back()->with('error', $e->getMessage());
        } catch (OperationFailedException $e) {
            Log::error('Employee deletion failed in controller', [
                'employee_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to delete employee. Please try again.');
        }
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:active,inactive,on_leave,terminated',
        ]);

        try {
            $employee = $this->employeeService->getEmployeeById($id);
            $this->authorizeEmployee($employee);

            $this->employeeService->updateEmployeeStatus($id, $validated['status']);

            return back()->with('success', 'Employee status updated.');
        } catch (EmployeeNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (OperationFailedException $e) {
            Log::error('Employee status update failed in controller', [
                'employee_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update status. Please try again.');
        }
    }

    public function tasks(int $id)
    {
        try {
            $employee = $this->employeeService->getEmployeeById($id);
            $this->authorizeEmployee($employee);

            $tasks = $this->employeeService->getEmployeeTasks($id);

            return Inertia::render('GrowBiz/Employees/Tasks', [
                'employee' => $employee->toArray(),
                'tasks' => $tasks,
            ]);
        } catch (EmployeeNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        }
    }

    /**
     * Send email invitation to employee
     */
    public function sendInvitation(Request $request, int $id)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        try {
            $employee = $this->employeeService->getEmployeeById($id);
            $this->authorizeEmployee($employee);

            $invitation = $this->invitationService->sendEmailInvitation(
                $id,
                Auth::id(),
                $validated['email']
            );

            return back()->with('success', 'Invitation sent to ' . $validated['email']);
        } catch (EmployeeNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (OperationFailedException $e) {
            return back()->with('error', 'Failed to send invitation. Please try again.');
        }
    }

    /**
     * Generate code invitation for employee
     */
    public function generateInvitationCode(int $id)
    {
        try {
            $employee = $this->employeeService->getEmployeeById($id);
            $this->authorizeEmployee($employee);

            $invitation = $this->invitationService->generateCodeInvitation($id, Auth::id());
            $code = $invitation->code()->value();

            // Return as JSON for AJAX requests
            if (request()->wantsJson() || request()->header('X-Inertia')) {
                return back()->with([
                    'success' => 'Invitation code generated',
                    'invitation_code' => $code,
                ]);
            }

            return back()->with([
                'success' => 'Invitation code generated',
                'invitation_code' => $code,
            ]);
        } catch (EmployeeNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        } catch (OperationFailedException $e) {
            return back()->with('error', 'Failed to generate code. Please try again.');
        }
    }

    /**
     * Get pending invitation for employee
     */
    public function getInvitation(int $id)
    {
        try {
            $employee = $this->employeeService->getEmployeeById($id);
            $this->authorizeEmployee($employee);

            $invitation = $this->invitationService->getPendingInvitation($id);

            return response()->json([
                'invitation' => $invitation?->toArray(),
            ]);
        } catch (EmployeeNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        }
    }

    /**
     * Revoke pending invitation
     */
    public function revokeInvitation(int $id, int $invitationId)
    {
        try {
            $employee = $this->employeeService->getEmployeeById($id);
            $this->authorizeEmployee($employee);

            $this->invitationService->revokeInvitation($invitationId);

            return back()->with('success', 'Invitation revoked');
        } catch (EmployeeNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (UnauthorizedAccessException $e) {
            abort(403, $e->getMessage());
        }
    }

    /**
     * @throws UnauthorizedAccessException
     */
    private function authorizeEmployee($employee): void
    {
        $user = Auth::user();
        
        if ($employee->ownerId() === $user->id) {
            return;
        }

        if ($user->is_admin) {
            return;
        }

        throw new UnauthorizedAccessException('employee', $employee->id(), $user->id);
    }
}
