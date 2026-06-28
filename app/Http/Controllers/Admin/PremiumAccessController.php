<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PremiumAccessController extends Controller
{
    /**
     * Display users with premium access management
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter', 'all'); // all, granted, not_granted

        $query = User::query()
            ->select('id', 'name', 'email', 'premium_template_tier', 'premium_access_granted_at', 'premium_access_granted_by', 'premium_access_notes')
            ->with('premiumAccessGrantedBy:id,name');

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply access filter
        if ($filter === 'granted') {
            $query->whereNotNull('premium_template_tier');
        } elseif ($filter === 'not_granted') {
            $query->whereNull('premium_template_tier');
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        return Inertia::render('Admin/PremiumAccess/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'filter' => $filter,
            ],
            'stats' => [
                'total_users' => User::count(),
                'granted_access' => User::whereNotNull('premium_template_tier')->count(),
            ],
        ]);
    }

    /**
     * Grant premium access to a user
     */
    public function grant(Request $request, User $user)
    {
        $validated = $request->validate([
            'tier' => 'required|in:starter,business,agency',
            'notes' => 'nullable|string|max:500',
        ]);

        $user->update([
            'premium_template_tier' => $validated['tier'],
            'premium_access_granted_at' => now(),
            'premium_access_granted_by' => auth()->id(),
            'premium_access_notes' => $validated['notes'] ?? null,
        ]);

        $tierNames = ['starter' => 'Starter (K120)', 'business' => 'Business (K350)', 'agency' => 'Agency (K900)'];
        $tierName = $tierNames[$validated['tier']] ?? $validated['tier'];

        return back()->with('success', "Premium template access ({$tierName}) granted to {$user->name}");
    }

    /**
     * Revoke premium access from a user
     */
    public function revoke(User $user)
    {
        $user->update([
            'premium_template_tier' => null,
            'premium_access_granted_at' => null,
            'premium_access_granted_by' => null,
            'premium_access_notes' => null,
        ]);

        return back()->with('success', "Premium template access revoked from {$user->name}");
    }

    /**
     * Bulk grant premium access
     */
    public function bulkGrant(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'tier' => 'required|in:starter,business,agency',
            'notes' => 'nullable|string|max:500',
        ]);

        User::whereIn('id', $validated['user_ids'])->update([
            'premium_template_tier' => $validated['tier'],
            'premium_access_granted_at' => now(),
            'premium_access_granted_by' => auth()->id(),
            'premium_access_notes' => $validated['notes'] ?? null,
        ]);

        $tierNames = ['starter' => 'Starter (K120)', 'business' => 'Business (K350)', 'agency' => 'Agency (K900)'];
        $tierName = $tierNames[$validated['tier']] ?? $validated['tier'];
        $count = count($validated['user_ids']);
        
        return back()->with('success', "Premium template access ({$tierName}) granted to {$count} users");
    }

    /**
     * Bulk revoke premium access
     */
    public function bulkRevoke(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        User::whereIn('id', $validated['user_ids'])->update([
            'premium_template_tier' => null,
            'premium_access_granted_at' => null,
            'premium_access_granted_by' => null,
            'premium_access_notes' => null,
        ]);

        $count = count($validated['user_ids']);
        return back()->with('success', "Premium template access revoked from {$count} users");
    }
}
