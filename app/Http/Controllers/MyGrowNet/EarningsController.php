<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EarningsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get earnings breakdown by type (using referral_commissions table)
        // Referral Bonuses = Level 1 commissions (direct referrals)
        $referralBonuses = $user->referralCommissions()
            ->where('status', 'paid')
            ->where('level', 1)
            ->sum('amount') ?? 0;
        
        // Level Commissions = Levels 2-7 commissions (network earnings)
        $levelCommissions = $user->referralCommissions()
            ->where('status', 'paid')
            ->where('level', '>', 1)
            ->sum('amount') ?? 0;
        
        $profitShares = $user->profitShares()->sum('amount') ?? 0;
       
        $earningsByType = [
            'referral_bonuses' => (float) $referralBonuses,
            'level_commissions' => (float) $levelCommissions,
            'profit_sharing' => (float) $profitShares,
            'milestone_rewards' => 0, // To be implemented
        ];
        
        // Get monthly earnings for current year
        $monthlyEarnings = [];
        for ($month = 1; $month <= 12; $month++) {
            $commissions = $user->referralCommissions()
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', $month)
                ->where('status', 'paid')
                ->sum('amount') ?? 0;
                
            $profits = $user->profitShares()
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', $month)
                ->sum('amount') ?? 0;
                
            $monthlyEarnings[] = [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'amount' => (float) ($commissions + $profits),
            ];
        }
        
        // Get current month BP and earnings
        $currentMonthBP = $user->bonus_points ?? 0;
        $currentMonthCommissions = $user->referralCommissions()
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->where('status', 'paid')
            ->sum('amount') ?? 0;
        $currentMonthProfits = $user->profitShares()
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->sum('amount') ?? 0;
        
        return Inertia::render('MyGrowNet/Earnings', [
            'earningsByType' => $earningsByType,
            'totalEarnings' => (float) array_sum($earningsByType),
            'monthlyEarnings' => $monthlyEarnings,
            'currentMonthBP' => $currentMonthBP,
            'currentMonthEarnings' => (float) ($currentMonthCommissions + $currentMonthProfits),
            'lifetimePoints' => $user->lifetime_points ?? 0,
        ]);
    }
}
