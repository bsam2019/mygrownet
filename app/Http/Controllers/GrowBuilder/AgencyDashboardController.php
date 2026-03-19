<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Services\GrowBuilder\QuotaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgencyDashboardController extends Controller
{
    public function __construct(
        private QuotaService $quotaService
    ) {}

    /**
     * Display the agency dashboard
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $agency = $user->currentAgency;

        if (!$agency) {
            return Inertia::render('GrowBuilder/Agency/Create');
        }

        // Get quota summary
        $quotaSummary = $this->quotaService->getQuotaSummary($agency);

        // Get recent activity (placeholder for now)
        $recentActivity = [];

        // Get team members
        $teamMembers = $agency->agencyUsers()
            ->with(['user', 'role'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($agencyUser) {
                return [
                    'id' => $agencyUser->id,
                    'name' => $agencyUser->user->name,
                    'email' => $agencyUser->user->email,
                    'role' => $agencyUser->role->role_name,
                    'status' => $agencyUser->status,
                    'joined_at' => $agencyUser->joined_at?->format('M d, Y'),
                ];
            });

        // Get stats
        $stats = [
            'total_sites' => $agency->sites_used,
            'total_clients' => 0, // Will be populated when clients table exists
            'active_team_members' => $agency->agencyUsers()->where('status', 'active')->count(),
            'storage_used_gb' => round($agency->storage_used_mb / 1024, 2),
        ];

        return Inertia::render('GrowBuilder/Agency/Dashboard', [
            'agency' => [
                'id' => $agency->id,
                'name' => $agency->agency_name,
                'status' => $agency->status,
                'plan' => $agency->subscriptionPlan?->name,
                'is_trial' => $agency->isOnTrial(),
                'trial_ends_at' => $agency->trial_ends_at?->format('M d, Y'),
            ],
            'quota' => $quotaSummary,
            'stats' => $stats,
            'team_members' => $teamMembers,
            'recent_activity' => $recentActivity,
        ]);
    }

    /**
     * Store a new agency
     */
    public function store(Request $request)
    {
        $request->validate([
            'agency_name' => 'required|string|max:255',
            'business_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'country' => 'required|string|max:100',
        ]);

        $user = $request->user();

        // Check if user already has an agency
        if ($user->currentAgency) {
            return redirect()->route('growbuilder.agency.dashboard')
                ->with('error', 'You already have an agency.');
        }

        // Create the agency
        $agency = Agency::create([
            'agency_name' => $request->agency_name,
            'slug' => \Str::slug($request->agency_name),
            'owner_user_id' => $user->id,
            'business_email' => $request->business_email,
            'phone' => $request->phone,
            'country' => $request->country,
            'status' => 'active',
            'storage_limit_mb' => 51200, // 50GB for agency tier
            'storage_used_mb' => 0,
            'site_limit' => 20,
            'sites_used' => 0,
            'team_member_limit' => 10,
        ]);

        // Set as current agency
        $user->setCurrentAgency($agency->id);

        return redirect()->route('growbuilder.agency.dashboard')
            ->with('success', "Agency '{$agency->agency_name}' created successfully!");
    }

    /**
     * Switch current agency
     */
    public function switchAgency(Request $request, int $agencyId)
    {
        $user = $request->user();

        // Verify user has access to this agency
        $agency = $user->agencies()->where('agencies.id', $agencyId)->first();

        if (!$agency) {
            return back()->with('error', 'You do not have access to this agency.');
        }

        $user->setCurrentAgency($agencyId);

        return redirect()->route('growbuilder.agency.dashboard')
            ->with('success', "Switched to {$agency->agency_name}");
    }
}
