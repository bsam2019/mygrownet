<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with(['roles']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Professional level filter
        if ($request->filled('level')) {
            $query->where('current_professional_level', $request->level);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        return Inertia::render('Admin/Users/Index', [
            'users' => $query
                ->select([
                    'id', 'name', 'email', 'phone', 'status', 'current_professional_level', 
                    'last_login_at', 'created_at',
                    'loyalty_points', 'loyalty_points_awarded_total', 'loyalty_points_withdrawn_total',
                    'lgr_custom_withdrawable_percentage', 'lgr_withdrawal_blocked', 'lgr_restriction_reason'
                ])
                ->paginate(50)
                ->withQueryString()
                ->through(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'role' => $user->roles->first()?->name ?? 'user',
                    'level' => $user->current_professional_level,
                    'created_at' => $user->created_at,
                    'last_login_at' => $user->last_login_at,
                    'loyalty_points' => $user->loyalty_points,
                    'loyalty_points_awarded_total' => $user->loyalty_points_awarded_total,
                    'loyalty_points_withdrawn_total' => $user->loyalty_points_withdrawn_total,
                    'lgr_custom_withdrawable_percentage' => $user->lgr_custom_withdrawable_percentage,
                    'lgr_withdrawal_blocked' => $user->lgr_withdrawal_blocked,
                    'lgr_restriction_reason' => $user->lgr_restriction_reason,
                ]),
            'roles' => \App\Models\Role::select('id', 'name')->get(),
            'filters' => $request->only(['search', 'status', 'role', 'level', 'date_from', 'date_to', 'sort', 'direction']),
            'professionalLevels' => [
                'associate' => 'Associate',
                'professional' => 'Professional',
                'senior' => 'Senior',
                'manager' => 'Manager',
                'director' => 'Director',
                'executive' => 'Executive',
                'ambassador' => 'Ambassador',
            ]
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
    
    /**
     * Update LGR withdrawal restrictions for a user
     */
    public function updateLgrRestrictions(Request $request, User $user)
    {
        $validated = $request->validate([
            'lgr_withdrawal_blocked' => 'boolean',
            'lgr_custom_withdrawable_percentage' => 'nullable|numeric|min:0|max:100',
            'lgr_restriction_reason' => 'nullable|string|max:500'
        ]);

        $user->update([
            'lgr_withdrawal_blocked' => $validated['lgr_withdrawal_blocked'] ?? false,
            'lgr_custom_withdrawable_percentage' => $validated['lgr_custom_withdrawable_percentage'],
            'lgr_restriction_reason' => $validated['lgr_restriction_reason']
        ]);

        return back()->with('success', 'LGR restrictions updated successfully');
    }
}
