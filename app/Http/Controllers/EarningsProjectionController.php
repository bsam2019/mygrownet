<?php

namespace App\Http\Controllers;

use App\Services\EarningsProjectionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EarningsProjectionController extends Controller
{
    public function __construct(
        private EarningsProjectionService $earningsProjectionService
    ) {}

    /**
     * Display the earnings projection calculator
     */
    public function index()
    {
        $earningRanges = $this->earningsProjectionService->getEarningRanges();
        
        return Inertia::render('EarningsProjection/Calculator', [
            'earning_ranges' => $earningRanges,
            'tiers' => ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite']
        ]);
    }

    /**
     * Calculate earnings projection based on parameters
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'tier' => 'required|string|in:Bronze,Silver,Gold,Diamond,Elite',
            'active_referrals' => 'required|integer|min:1|max:100',
            'network_depth' => 'required|integer|min:1|max:5',
            'months' => 'required|integer|min:1|max:24'
        ]);

        try {
            $projection = $this->earningsProjectionService->calculateProjection(
                $request->tier,
                $request->active_referrals,
                $request->network_depth,
                $request->months
            );

            return response()->json([
                'success' => true,
                'projection' => $projection
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating projection: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get multiple scenarios for a tier
     */
    public function scenarios(Request $request)
    {
        $request->validate([
            'tier' => 'required|string|in:Bronze,Silver,Gold,Diamond,Elite'
        ]);

        try {
            $scenarios = $this->earningsProjectionService->generateScenarios($request->tier);

            return response()->json([
                'success' => true,
                'scenarios' => $scenarios
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating scenarios: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get income breakdown visualization data
     */
    public function breakdown(Request $request)
    {
        $request->validate([
            'tier' => 'required|string|in:Bronze,Silver,Gold,Diamond,Elite',
            'active_referrals' => 'integer|min:1|max:100',
            'network_depth' => 'integer|min:1|max:5'
        ]);

        $activeReferrals = $request->get('active_referrals', 5);
        $networkDepth = $request->get('network_depth', 3);

        try {
            $projection = $this->earningsProjectionService->calculateProjection(
                $request->tier,
                $activeReferrals,
                $networkDepth,
                1 // Single month for breakdown
            );

            return response()->json([
                'success' => true,
                'breakdown' => $projection['income_breakdown'],
                'monthly_breakdown' => $projection['monthly_projections'][0] ?? []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating breakdown: ' . $e->getMessage()
            ], 400);
        }
    }
}