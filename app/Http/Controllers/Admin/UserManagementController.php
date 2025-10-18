<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()
                ->with(['roles'])
                ->select(['id', 'name', 'email', 'status', 'last_login_at', 'created_at'])
                ->latest()
                ->paginate(10)
                ->through(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'role' => $user->roles->first()?->name ?? 'user',
                    'created_at' => $user->created_at,
                    'last_login_at' => $user->last_login_at
                ]),
            'roles' => \App\Models\Role::select('id', 'name')->get()
        ]);
    }

    public function show(User $user)
    {
        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'role' => $user->roles->first()?->name ?? 'user',
                'created_at' => $user->created_at,
                'last_login_at' => $user->last_login_at
            ]
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'status' => 'required|in:active,inactive,suspended',
            'role' => 'required|exists:roles,name'
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status']
        ]);

        $user->syncRoles([$validated['role']]);

        return back()->with('success', 'User updated successfully');
    }

    public function toggleStatus(User $user)
    {
        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        return back()->with('success', 'User status updated successfully');
    }

    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user->syncRoles([$validated['role']]);

        return back()->with('success', 'Role assigned successfully');
    }
}
