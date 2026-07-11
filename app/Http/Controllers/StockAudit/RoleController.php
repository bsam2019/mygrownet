<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\CompanyRoleService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function __construct(
        private CompanyRoleService $roleService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $roles = $this->roleService->getRolesForCompany($companyId);

        return Inertia::render('StockAudit/Roles/Index', [
            'roles' => $roles,
            'availablePermissions' => $this->roleService->getAvailablePermissions(),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|regex:/^[a-z0-9_]+$/',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $role = $this->roleService->createRole(
            $companyId,
            $validated['name'],
            $validated['slug'],
            $validated['description'] ?? '',
            $validated['permissions'] ?? [],
        );

        return redirect()->route('stock-audit.roles.index')
            ->with('success', "Role '{$role['name']}' created successfully");
    }

    public function update(Request $request, int $roleId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $role = $this->roleService->updateRole(
            $roleId,
            $validated['name'],
            $validated['description'] ?? '',
            $validated['permissions'] ?? [],
        );

        return redirect()->route('stock-audit.roles.index')
            ->with('success', "Role '{$role['name']}' updated successfully");
    }

    public function destroy(Request $request, int $roleId)
    {
        $this->roleService->deleteRole($roleId);

        return redirect()->route('stock-audit.roles.index')
            ->with('success', 'Role deleted successfully');
    }
}