<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NetworkAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get network statistics
        $directReferrals = $user->directReferrals()->count();
        $totalNetwork = $this->getTotalNetworkSize($user);
        $activeMembers = $this->getActiveMembers($user);
        
        // Get level breakdown
        $levelBreakdown = [];
        for ($level = 1; $level <= 7; $level++) {
            $levelBreakdown[] = [
                'level' => $level,
                'count' => $this->getMembersAtLevel($user, $level),
                'active' => $this->getActiveMembersAtLevel($user, $level),
            ];
        }
        
        // Get growth data for last 6 months
        $growthData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $growthData[] = [
                'month' => $date->format('M'),
                'members' => $user->directReferrals()
                    ->whereYear('created_at', '<=', $date->year)
                    ->whereMonth('created_at', '<=', $date->month)
                    ->count(),
            ];
        }
        
        return Inertia::render('MyGrowNet/NetworkAnalytics', [
            'directReferrals' => $directReferrals,
            'totalNetwork' => $totalNetwork,
            'activeMembers' => $activeMembers,
            'levelBreakdown' => $levelBreakdown,
            'growthData' => $growthData,
            'teamPerformance' => $this->getTeamPerformance($user),
        ]);
    }
    
    private function getTotalNetworkSize($user)
    {
        return $user->directReferrals()->count() + 
               $user->directReferrals()->with('directReferrals')->get()->sum(fn($r) => $r->directReferrals->count());
    }
    
    private function getActiveMembers($user)
    {
        return $user->directReferrals()
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();
    }
    
    private function getMembersAtLevel($user, $level)
    {
        // Simplified - would need proper matrix calculation
        return $user->directReferrals()->count() * ($level === 1 ? 1 : 0);
    }
    
    private function getActiveMembersAtLevel($user, $level)
    {
        return $this->getMembersAtLevel($user, $level);
    }
    
    private function getTeamPerformance($user)
    {
        $topPerformers = $user->directReferrals()
            ->withCount('directReferrals')
            ->orderBy('direct_referrals_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($member) {
                return [
                    'name' => $member->name,
                    'referrals' => $member->direct_referrals_count ?? 0,
                    'level' => $member->professional_level ?? 'Associate',
                ];
            });
        
        return [
            'topPerformers' => $topPerformers,
            'averageTeamSize' => $user->directReferrals()->count() > 0 
                ? $user->directReferrals()->withCount('directReferrals')->avg('direct_referrals_count') 
                : 0,
        ];
    }
}
