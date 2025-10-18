<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferralController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function index(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        $stats = $this->referralService->getAdminReferralStats();
        $topReferrers = $this->referralService->getTopReferrers();

        return Inertia::render('Admin/Referrals/Index', [
            'stats' => $stats,
            'topReferrers' => $topReferrers,
        ]);
    }

    public function analytics(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        $monthlyStats = $this->referralService->getMonthlyReferralStats();

        return Inertia::render('Admin/Referrals/Analytics', [
            'monthlyStats' => $monthlyStats,
        ]);
    }

    public function processPendingCommissions(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        try {
            $result = $this->referralService->processPendingCommissions();

            return back()->with('success', 
                "Processed {$result['processed_count']} commissions totaling {$result['total_amount']}"
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process commissions: ' . $e->getMessage());
        }
    }

    public function topReferrers(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        try {
            $topReferrers = $this->referralService->getTopReferrers();
            return response()->json($topReferrers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function monthlyStats(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        try {
            $monthlyStats = $this->referralService->getMonthlyReferralStats();
            return response()->json($monthlyStats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportReferrals(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        // Simple export functionality - can be enhanced later
        return response()->json([
            'message' => 'Export functionality will be implemented',
            'data' => []
        ]);
    }
}