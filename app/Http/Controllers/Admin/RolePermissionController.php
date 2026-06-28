<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RolePermissionController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display roles list
     */
    public function rolesIndex()
    {
        // Check if user has permission (optional - can be removed if admin middleware is enough)
        // $this->authorize('view_roles');
        
        return Inertia::render('Admin/Roles/Index', [
            'roles' => Role::withCount('users')
                ->get()
                ->map(fn($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                    'users_count' => $role->users_count,
                    'is_system' => in_array($role->name, ['superadmin', 'admin', 'Administrator', 'employee', 'member']),
                ])
        ]);
    }

    /**
     * Display role details with permissions
     */
    public function rolesShow(Role $role)
    {
        // $this->authorize('view_roles');
        
        return Inertia::render('Admin/Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'is_system' => in_array($role->name, ['superadmin', 'admin', 'Administrator', 'employee', 'member']),
                'permissions' => $role->permissions->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'description' => $p->description,
                ]),
                'users_count' => $role->users()->count(),
            ],
            'all_permissions' => Permission::all()->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
            ]),
        ]);
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        // Check if user is superadmin or Administrator
        if (!auth()->user()->hasRole(['superadmin', 'Administrator', 'admin'])) {
            return back()->with('error', 'Only administrators can manage permissions');
        }
        
        // Prevent modifying superadmin role
        if ($role->name === 'superadmin') {
            return back()->with('error', 'Cannot modify superadmin role permissions');
        }
        
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        
        $role->syncPermissions($validated['permissions']);
        
        return back()->with('success', 'Role permissions updated successfully');
    }

    /**
     * Display permissions list
     */
    public function permissionsIndex()
    {
        // $this->authorize('view_permissions');
        
        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => Permission::withCount('roles')
                ->get()
                ->map(fn($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'slug' => $permission->slug,
                    'description' => $permission->description,
                    'roles_count' => $permission->roles_count,
                ])
        ]);
    }

    /**
     * Create a new permission
     */
    public function createPermission(Request $request)
    {
        // Check if user is superadmin or Administrator
        if (!auth()->user()->hasRole(['superadmin', 'Administrator', 'admin'])) {
            return back()->with('error', 'Only administrators can create permissions');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'description' => 'nullable|string|max:500'
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'description' => $validated['description'] ?? '',
            'guard_name' => 'web'
        ]);

        return back()->with('success', "Permission '{$permission->name}' created successfully");
    }

    /**
     * Update a permission
     */
    public function updatePermission(Request $request, Permission $permission)
    {
        // Check if user is superadmin or Administrator
        if (!auth()->user()->hasRole(['superadmin', 'Administrator', 'admin'])) {
            return back()->with('error', 'Only administrators can update permissions');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'description' => 'nullable|string|max:500'
        ]);

        $permission->update([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'description' => $validated['description'] ?? ''
        ]);

        return back()->with('success', "Permission '{$permission->name}' updated successfully");
    }

    /**
     * Delete a permission
     */
    public function deletePermission(Permission $permission)
    {
        // Check if user is superadmin or Administrator
        if (!auth()->user()->hasRole(['superadmin', 'Administrator', 'admin'])) {
            return back()->with('error', 'Only administrators can delete permissions');
        }

        // Check if permission is assigned to roles
        if ($permission->roles()->count() > 0) {
            return back()->with('error', "Cannot delete permission '{$permission->name}' because it is assigned to {$permission->roles()->count()} role(s)");
        }

        $permissionName = $permission->name;
        $permission->delete();

        return back()->with('success', "Permission '{$permissionName}' deleted successfully");
    }

    /**
     * Display users with role assignment
     */
    public function usersRoles()
    {
        // $this->authorize('assign_roles');
        
        return Inertia::render('Admin/Roles/Users', [
            'users' => User::with('roles')
                ->latest()
                ->paginate(20)
                ->through(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'professional_level' => $user->professional_level,
                    'roles' => $user->roles->pluck('name'),
                ]),
            'roles' => Role::all()->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
            ]),
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        // Check if user is admin or superadmin
        if (!auth()->user()->hasRole(['admin', 'superadmin', 'Administrator'])) {
            return back()->with('error', 'Unauthorized');
        }
        
        $validated = $request->validate([
            'role' => 'required|exists:roles,name'
        ]);
        
        // Prevent assigning superadmin role (only superadmin can do this)
        if ($validated['role'] === 'superadmin' && !auth()->user()->hasRole(['superadmin', 'Administrator'])) {
            return back()->with('error', 'Only superadmin can assign superadmin role');
        }
        
        $user->syncRoles([$validated['role']]);
        
        return back()->with('success', "Role '{$validated['role']}' assigned to {$user->name}");
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request, User $user)
    {
        // Check if user is admin or superadmin
        if (!auth()->user()->hasRole(['admin', 'superadmin', 'Administrator'])) {
            return back()->with('error', 'Unauthorized');
        }
        
        $validated = $request->validate([
            'role' => 'required|exists:roles,name'
        ]);
        
        // Prevent removing superadmin role
        if ($validated['role'] === 'superadmin' && !auth()->user()->hasRole(['superadmin', 'Administrator'])) {
            return back()->with('error', 'Only superadmin can remove superadmin role');
        }
        
        $user->removeRole($validated['role']);
        
        return back()->with('success', "Role '{$validated['role']}' removed from {$user->name}");
    }

    /**
     * Create a new role
     */
    public function createRole(Request $request)
    {
        // Check if user is superadmin or Administrator
        if (!auth()->user()->hasRole(['superadmin', 'Administrator', 'admin'])) {
            return back()->with('error', 'Only administrators can create roles');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500'
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'description' => $validated['description'] ?? '',
            'guard_name' => 'web'
        ]);

        return back()->with('success', "Role '{$role->name}' created successfully");
    }

    /**
     * Update a role
     */
    public function updateRole(Request $request, Role $role)
    {
        // Check if user is superadmin or Administrator
        if (!auth()->user()->hasRole(['superadmin', 'Administrator', 'admin'])) {
            return back()->with('error', 'Only administrators can update roles');
        }

        // Prevent updating system roles
        if (in_array($role->name, ['superadmin', 'admin', 'Administrator', 'employee', 'member'])) {
            return back()->with('error', 'Cannot update system roles');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500'
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'description' => $validated['description'] ?? ''
        ]);

        return back()->with('success', "Role '{$role->name}' updated successfully");
    }

    /**
     * Delete a role
     */
    public function deleteRole(Role $role)
    {
        // Check if user is superadmin or Administrator
        if (!auth()->user()->hasRole(['superadmin', 'Administrator', 'admin'])) {
            return back()->with('error', 'Only administrators can delete roles');
        }

        // Prevent deleting system roles
        if (in_array($role->name, ['superadmin', 'admin', 'Administrator', 'employee', 'member'])) {
            return back()->with('error', 'Cannot delete system roles');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return back()->with('error', "Cannot delete role '{$role->name}' because it has {$role->users()->count()} user(s) assigned");
        }

        $roleName = $role->name;
        $role->delete();

        return back()->with('success', "Role '{$roleName}' deleted successfully");
    }
}
