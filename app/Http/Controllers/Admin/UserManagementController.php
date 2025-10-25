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
                ->select(['id', 'name', 'email', 'phone', 'status', 'last_login_at', 'created_at'])
                ->latest()
                ->paginate(50)
                ->through(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
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
        $user->load(['roles', 'profile']);
        
        return Inertia::render('Admin/Users/Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'balance' => $user->balance ?? 0,
                'total_earnings' => $user->total_earnings ?? 0,
                'role' => $user->roles->first()?->name ?? 'user',
                'created_at' => $user->created_at,
                'last_login_at' => $user->last_login_at
            ],
            'profile' => $user->profile ? [
                'phone_number' => $user->profile->phone_number,
                'address' => $user->profile->address,
                'city' => $user->profile->city,
                'country' => $user->profile->country,
                'preferred_payment_method' => $user->profile->preferred_payment_method,
                'payment_details' => $user->profile->payment_details ?? [],
                'avatar' => $user->profile->avatar,
                'kyc_status' => $user->profile->kyc_status ?? 'not_started'
            ] : null,
            'roles' => \App\Models\Role::select('id', 'name')->get()
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'status' => 'required|in:active,inactive,suspended',
            'role' => 'nullable|exists:roles,name',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'preferred_payment_method' => 'nullable|in:bank,mobile_money',
            'payment_details' => 'nullable|array'
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status']
        ]);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        // Update or create profile
        $profileData = [
            'phone_number' => $validated['phone_number'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
            'preferred_payment_method' => $validated['preferred_payment_method'] ?? null,
            'payment_details' => $validated['payment_details'] ?? null
        ];

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return back()->with('success', 'User updated successfully');
    }

    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed'
        ]);

        // Don't use bcrypt() here because the User model has 'password' => 'hashed' cast
        // which automatically hashes the password
        $user->update([
            'password' => $validated['password']
        ]);

        return back()->with('success', 'Password updated successfully');
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
