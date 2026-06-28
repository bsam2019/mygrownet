<?php

namespace App\Http\Controllers\Investment;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Services\ProfitCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfitCalculationController extends Controller
{
    protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }

    /**
     * Display the user's profit dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $earnings = $this->profitService->calculateTotalEarnings($user);
        $investments = $user->investments()->with('tier')->get();
        
        $monthlyProfits = [];
        foreach ($investments as $investment) {
            $monthlyProfits[] = [
                'investment_id' => $investment->id,
                'amount' => $investment->amount,
                'tier_name' => $investment->tier->name,
                'monthly_profit' => $this->profitService->calculateMonthlyProfit($user, $investment)
            ];
        }

        return Inertia::render('Investment/ProfitDashboard', [
            'earnings' => $earnings,
            'monthly_profits' => $monthlyProfits,
            'quarterly_share' => $this->profitService->calculateQuarterlyProfitShare($user)
        ]);
    }

    /**
     * Get the user's profit details
     */
    public function getProfitDetails()
    {
        $user = Auth::user();
        $earnings = $this->profitService->calculateTotalEarnings($user);
        
        return response()->json([
            'success' => true,
            'data' => $earnings
        ]);
    }

    /**
     * Calculate potential profit for a given investment amount and tier
     */
    public function calculatePotentialProfit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'tier_id' => 'required|exists:investment_tiers,id'
        ]);

        $amount = $request->input('amount');
        $tierId = $request->input('tier_id');
        
        $tier = InvestmentTier::findOrFail($tierId);
        $monthlyProfit = $tier->calculateMonthlyProfit($amount);
        
        // Calculate annual profit
        $annualProfit = $monthlyProfit * 12;
        
        // Calculate ROI percentage
        $roiPercentage = ($annualProfit / $amount) * 100;
        
        return response()->json([
            'success' => true,
            'data' => [
                'monthly_profit' => $monthlyProfit,
                'annual_profit' => $annualProfit,
                'roi_percentage' => $roiPercentage
            ]
        ]);
    }

    /**
     * Check if a withdrawal is allowed
     */
    public function checkWithdrawalEligibility(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0'
        ]);

        $user = Auth::user();
        $amount = $request->input('amount');
        
        $result = $this->profitService->isWithdrawalAllowed($user, $amount);
        
        if ($result['allowed']) {
            $penalty = $this->profitService->calculateEarlyWithdrawalPenalty($user, $amount);
            $result['penalty'] = $penalty;
            $result['net_amount'] = $amount - $penalty;
        }
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Get profit distribution history
     */
    public function getProfitHistory()
    {
        $user = Auth::user();
        $profitTransactions = $user->profitTransactions()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $profitTransactions
        ]);
    }

    /**
     * Admin: Process profit distribution for all users
     */
    public function processProfitDistribution()
    {
        $this->authorize('admin');
        
        $result = $this->profitService->processProfitDistribution();
        
        return response()->json($result);
    }

    /**
     * Admin: Process quarterly profit distribution
     */
    public function processQuarterlyProfitDistribution()
    {
        $this->authorize('admin');
        
        $result = $this->profitService->processQuarterlyProfitDistribution();
        
        return response()->json($result);
    }

    /**
     * Admin: Get profit distribution statistics
     */
    public function getProfitDistributionStats()
    {
        $this->authorize('admin');
        
        $totalDistributed = \App\Models\ProfitTransaction::sum('amount');
        $monthlyDistributed = \App\Models\ProfitTransaction::where('type', 'monthly_profit')->sum('amount');
        $quarterlyDistributed = \App\Models\ProfitTransaction::where('type', 'quarterly_share')->sum('amount');
        
        $userCount = \App\Models\User::whereNotNull('current_investment_tier_id')->count();
        $activeInvestments = \App\Models\Investment::where('status', 'active')->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_distributed' => $totalDistributed,
                'monthly_distributed' => $monthlyDistributed,
                'quarterly_distributed' => $quarterlyDistributed,
                'user_count' => $userCount,
                'active_investments' => $activeInvestments
            ]
        ]);
    }
}