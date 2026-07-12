<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\User;

class PortfolioController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get user's investments with tier information
        $investments = Investment::with(['tier'])
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->get();

        // Calculate portfolio metrics
        $totalInvested = $investments->sum('amount');
        $currentValue = $investments->sum('current_value');
        $totalReturns = $currentValue - $totalInvested;
        $averageROI = $totalInvested > 0 ? (($totalReturns / $totalInvested) * 100) : 0;

        // Get tier distribution
        $tierDistribution = $investments->groupBy('tier.name')
            ->map(function ($tierInvestments, $tierName) {
                return [
                    'tier' => $tierName,
                    'count' => $tierInvestments->count(),
                    'total_amount' => $tierInvestments->sum('amount'),
                    'current_value' => $tierInvestments->sum('current_value'),
                    'percentage' => 0 // Will be calculated below
                ];
            })->values();

        // Calculate percentages
        if ($totalInvested > 0) {
            $tierDistribution = $tierDistribution->map(function ($tier) use ($totalInvested) {
                $tier['percentage'] = ($tier['total_amount'] / $totalInvested) * 100;
                return $tier;
            });
        }

        // Get performance history (last 12 months)
        $performanceHistory = $this->getPerformanceHistory($user->id);

        // Get recent transactions
        $recentTransactions = $user->transactions()
            ->with(['investment.tier'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at->format('M d, Y'),
                    'investment_tier' => $transaction->investment?->tier?->name
                ];
            });

        return Inertia::render('Portfolio/Index', [
            'portfolio' => [
                'total_invested' => $totalInvested,
                'current_value' => $currentValue,
                'total_returns' => $totalReturns,
                'average_roi' => round($averageROI, 2),
                'investment_count' => $investments->count(),
                'tier_distribution' => $tierDistribution,
                'performance_history' => $performanceHistory
            ],
            'investments' => $investments->map(function ($investment) {
                return [
                    'id' => $investment->id,
                    'amount' => $investment->amount,
                    'current_value' => $investment->current_value,
                    'returns' => $investment->current_value - $investment->amount,
                    'roi' => $investment->amount > 0 ? ((($investment->current_value - $investment->amount) / $investment->amount) * 100) : 0,
                    'tier' => [
                        'name' => $investment->tier->name,
                        'profit_rate' => $investment->tier->profit_share_rate
                    ],
                    'investment_date' => $investment->created_at->format('M d, Y'),
                    'status' => $investment->status
                ];
            }),
            'recentTransactions' => $recentTransactions
        ]);
    }

    private function getPerformanceHistory($userId)
    {
        // Generate mock performance data for the last 12 months
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'value' => rand(95, 115), // Mock portfolio value percentage
                'returns' => rand(-5, 15) // Mock returns percentage
            ];
        }
        return $months;
    }
}