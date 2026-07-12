<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\InvestmentCategory;
use App\Models\InvestmentOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvestorDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get portfolio data
        $portfolio = [
            'total_investment' => $user->investments()->where('status', 'active')->sum('amount'),
            'total_returns' => $user->transactions()->where('transaction_type', 'return')->where('status', 'completed')->sum('amount'),
            'active_referrals' => $user->directReferrals()->whereHas('investments', function($query) {
                $query->where('status', 'active');
            })->count(),
            'referral_earnings' => $user->transactions()->where('transaction_type', 'referral')->where('status', 'completed')->sum('amount')
        ];
        
        // Get investment distribution by category
        $investmentDistribution = DB::table('investments')
            ->join('investment_categories', 'investments.category_id', '=', 'investment_categories.id')
            ->where('investments.user_id', $user->id)
            ->where('investments.status', 'active')
            ->select(
                'investment_categories.id',
                'investment_categories.name',
                DB::raw('SUM(investments.amount) as amount')
            )
            ->groupBy('investment_categories.id', 'investment_categories.name')
            ->get()
            ->map(function ($category) use ($portfolio) {
                $totalInvestment = $portfolio['total_investment'] ?: 1; // Avoid division by zero
                $percentage = round(($category->amount / $totalInvestment) * 100, 1);
                
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'amount' => $category->amount,
                    'percentage' => $percentage
                ];
            });
        
        // Get recent transactions
        $recentTransactions = $user->transactions()
            ->select('id', 'reference_number', 'amount as investment_amount', 'transaction_type', 'status', 'created_at')
            ->latest()
            ->take(5)
            ->get();
        
        // Get investment opportunities
        $investmentOpportunities = InvestmentOpportunity::select('id', 'name', 'description', 'minimum_investment', 'expected_returns')
            ->where('status', 'active')
            ->take(3)
            ->get();
        
        return response()->json([
            'portfolio' => $portfolio,
            'investment_distribution' => $investmentDistribution,
            'recent_transactions' => $recentTransactions,
            'investment_opportunities' => $investmentOpportunities
        ]);
    }
} 