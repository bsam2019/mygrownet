<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\SiteRole;
use App\Infrastructure\GrowBuilder\Models\SiteUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class SiteUserManagementController extends Controller
{
    /**
     * List users
     */
    public function index(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $query = SiteUser::forSite($site->id)->with('role');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('site_role_id', $request->role);
        }

        $users = $query->latest()->paginate(15);
        $roles = SiteRole::forSite($site->id)->orderBy('level', 'desc')->get();

        return Inertia::render('SiteMember/Users/Index', [
            'site' => $this->getSiteData($site),
            'user' => $this->getUserData($user),
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'status', 'role']),
        ]);
    }

    /**
     * Show user details
     */
    public function show(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $targetUser = SiteUser::forSite($site->id)
            ->with(['role', 'orders'])
            ->findOrFail($id);

        $roles = SiteRole::forSite($site->id)->orderBy('level', 'desc')->get();

        return Inertia::render('SiteMember/Users/Show', [
            'site' => $this->getSiteData($site),
            'user' => $this->getUserData($user),
            'targetUser' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'phone' => $targetUser->phone,
                'status' => $targetUser->status,
                'role' => $targetUser->role,
                'created_at' => $targetUser->created_at,
                'last_login_at' => $targetUser->last_login_at,
                'login_count' => $targetUser->login_count,
                'orders_count' => $targetUser->orders->count(),
            ],
            'roles' => $roles,
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $targetUser = SiteUser::forSite($site->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,pending,suspended',
        ]);

        // Check email uniqueness
        if (SiteUser::forSite($site->id)
            ->where('email', $request->email)
            ->where('id', '!=', $id)
            ->exists()
        ) {
            return back()->withErrors(['email' => 'This email is already in use.']);
        }

        $targetUser->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
        ]);

        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Update user role
     */
    public function updateRole(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $targetUser = SiteUser::forSite($site->id)->findOrFail($id);

        $request->validate([
            'site_role_id' => 'required|exists:site_roles,id',
        ]);

        // Verify role belongs to this site
        $role = SiteRole::forSite($site->id)->findOrFail($request->site_role_id);

        // Prevent assigning higher role than current user's role
        if ($role->level > ($user->role?->level ?? 0)) {
            return back()->withErrors(['site_role_id' => 'Cannot assign a role higher than your own.']);
        }

        $targetUser->update(['site_role_id' => $role->id]);

        return back()->with('success', 'User role updated successfully.');
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');

        $targetUser = SiteUser::forSite($site->id)->findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $targetUser->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password reset successfully.');
    }

    /**
     * Delete user
     */
    public function destroy(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $targetUser = SiteUser::forSite($site->id)->findOrFail($id);

        // Prevent self-deletion
        if ($targetUser->id === $user->id) {
            return back()->withErrors(['error' => 'Cannot delete your own account.']);
        }

        // Prevent deleting users with higher role
        if (($targetUser->role?->level ?? 0) >= ($user->role?->level ?? 0)) {
            return back()->withErrors(['error' => 'Cannot delete a user with equal or higher role.']);
        }

        $targetUser->delete();

        return redirect()
            ->route('site.member.users.index', ['subdomain' => $subdomain])
            ->with('success', 'User deleted successfully.');
    }

    protected function getSiteData($site): array
    {
        return [
            'id' => $site->id,
            'name' => $site->name,
            'subdomain' => $site->subdomain,
            'logo' => $site->logo,
            'theme' => $site->theme,
        ];
    }

    protected function getUserData($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ? [
                'name' => $user->role->name,
                'slug' => $user->role->slug,
                'level' => $user->role->level,
            ] : null,
            'permissions' => $user->role 
                ? $user->role->permissions->pluck('slug')->toArray() 
                : [],
        ];
    }
}
