<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\CompanyUserService;
use App\Domain\StockFlow\Services\CompanyRoleService;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function __construct(
        private CompanyUserService $userService,
        private CompanyRoleService $roleService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $employees = $this->userService->getEmployees($companyId);
        $roles = $this->roleService->getRolesForCompany($companyId);

        return Inertia::render('StockAudit/Employees/Index', [
            'employees' => $employees,
            'roles' => $roles,
            'availablePermissions' => $this->roleService->getAvailablePermissions(),
        ]);
    }

    public function invite(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        
        $validated = $request->validate([
            'user_id' => 'required|exists:sa_users,id',
            'role_id' => 'nullable|exists:sa_company_roles,id',
        ]);

        try {
            $this->userService->inviteUser(
                $companyId,
                $validated['user_id'],
                $validated['role_id'] ?? null,
            );
        } catch (OperationFailedException $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }

        return redirect()->sfRoute('stock-audit.employees.index')
            ->with('success', 'Employee invited successfully');
    }

    public function accept(Request $request, int $employeeUserId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        try {
            $this->userService->acceptInvitation($companyId, $employeeUserId);
        } catch (OperationFailedException $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }

        return redirect()->sfRoute('stock-audit.employees.index')
            ->with('success', 'Invitation accepted');
    }

    public function updateRole(Request $request, int $employeeUserId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        
        $validated = $request->validate([
            'role_id' => 'nullable|exists:sa_company_roles,id',
        ]);

        try {
            $this->userService->updateEmployeeRole($companyId, $employeeUserId, $validated['role_id'] ?? null);
        } catch (OperationFailedException $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }

        return redirect()->sfRoute('stock-audit.employees.index')
            ->with('success', 'Employee role updated');
    }

    public function suspend(Request $request, int $employeeUserId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        try {
            $this->userService->suspendEmployee($companyId, $employeeUserId);
        } catch (OperationFailedException $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }

        return redirect()->sfRoute('stock-audit.employees.index')
            ->with('success', 'Employee suspended');
    }

    public function reactivate(Request $request, int $employeeUserId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        try {
            $this->userService->reactivateEmployee($companyId, $employeeUserId);
        } catch (OperationFailedException $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }

        return redirect()->sfRoute('stock-audit.employees.index')
            ->with('success', 'Employee reactivated');
    }

    public function remove(Request $request, int $employeeUserId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->userService->removeEmployee($companyId, $employeeUserId, $validated['reason'] ?? null);
        } catch (OperationFailedException $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }

        return redirect()->sfRoute('stock-audit.employees.index')
            ->with('success', 'Employee removed');
    }
}