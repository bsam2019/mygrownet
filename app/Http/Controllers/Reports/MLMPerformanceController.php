<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ReferralCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MLMPerformanceController extends Controller
{
    public function getNetworkMetrics(Request $request)
    {
        $userId = $request->get('user_id');
        $startDate = $this->getStartDate($request->get('period', 'month'));

        return response()->json([
            'network_size' => $this->getNetworkSize($userId),
            'commission_earnings' => $this->getCommissionEarnings($userId, $startDate),
            'level_distribution' => $this->getLevelDistribution($userId),
            'top_performers' => $this->getTopPerformers($userId),
            'network_growth' => $this->getNetworkGrowth($userId, $startDate)
        ]);
    }

    public function getReferralTreeReport($userId)
    {
        $tree = User::with(['directReferrals' => function ($query) {
            $query->with(['investments', 'referralCommissions']);
        }])->findOrFail($userId);

        return response()->json([
            'user' => $tree,
            'metrics' => [
                'direct_referrals' => $this->getDirectReferralMetrics($userId),
                'indirect_referrals' => $this->getIndirectReferralMetrics($userId),
                'total_network_value' => $this->getTotalNetworkValue($userId)
            ]
        ]);
    }

    public function getCommissionReport(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);

        $report = ReferralCommission::where('created_at', '>=', $startDate)
            ->select(
                'level',
                DB::raw('COUNT(*) as total_commissions'),
                DB::raw('SUM(commission_amount) as total_amount'),
                DB::raw('AVG(commission_amount) as average_commission')
            )
            ->groupBy('level')
            ->get();

        return response()->json([
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => now()->toDateString(),
            'commissions' => $report
        ]);
    }

    protected function getNetworkSize($userId)
    {
        return [
            'direct_referrals' => User::where('referrer_id', $userId)->count(),
            'level2_referrals' => $this->getLevel2Count($userId),
            'level3_referrals' => $this->getLevel3Count($userId),
            'total_network' => $this->getTotalNetworkCount($userId)
        ];
    }

    protected function getCommissionEarnings($userId, $startDate)
    {
        return ReferralCommission::where('referrer_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select(
                'level',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(commission_amount) as total_amount')
            )
            ->groupBy('level')
            ->get();
    }

    protected function getLevelDistribution($userId)
    {
        $directReferrals = User::where('referrer_id', $userId)->pluck('id');
        $level2Referrals = User::whereIn('referrer_id', $directReferrals)->pluck('id');
        $level3Referrals = User::whereIn('referrer_id', $level2Referrals)->pluck('id');

        return [
            'level1' => $this->getReferralValuesByIds($directReferrals),
            'level2' => $this->getReferralValuesByIds($level2Referrals),
            'level3' => $this->getReferralValuesByIds($level3Referrals)
        ];
    }

    protected function getTopPerformers($userId)
    {
        return User::where('referrer_id', $userId)
            ->withCount('directReferrals')
            ->withSum('investments', 'amount')
            ->orderBy('direct_referrals_count', 'desc')
            ->limit(5)
            ->get();
    }

    protected function getNetworkGrowth($userId, $startDate)
    {
        return User::where('created_at', '>=', $startDate)
            ->where(function($query) use ($userId) {
                $query->where('referrer_id', $userId)
                    ->orWhereIn('referrer_id', function($q) use ($userId) {
                        $q->select('id')
                            ->from('users')
                            ->where('referrer_id', $userId);
                    });
            })
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as new_referrals')
            )
            ->groupBy('date')
            ->get();
    }

    protected function getDirectReferralMetrics($userId)
    {
        return User::where('referrer_id', $userId)
            ->withCount('directReferrals')
            ->withSum('investments', 'amount')
            ->withSum('referralCommissions', 'commission_amount')
            ->get();
    }

    protected function getIndirectReferralMetrics($userId)
    {
        $directReferrals = User::where('referrer_id', $userId)->pluck('id');
        
        return User::whereIn('referrer_id', $directReferrals)
            ->withCount('directReferrals')
            ->withSum('investments', 'amount')
            ->withSum('referralCommissions', 'commission_amount')
            ->get();
    }

    protected function getTotalNetworkValue($userId)
    {
        $directReferrals = User::where('referrer_id', $userId)->pluck('id');
        $level2Referrals = User::whereIn('referrer_id', $directReferrals)->pluck('id');
        $level3Referrals = User::whereIn('referrer_id', $level2Referrals)->pluck('id');

        $allNetworkIds = collect()->merge($directReferrals)
                                 ->merge($level2Referrals)
                                 ->merge($level3Referrals);

        return [
            'total_investments' => $this->getTotalInvestments($allNetworkIds),
            'total_commissions' => $this->getTotalCommissions($allNetworkIds),
            'active_members' => $this->getActiveMembersCount($allNetworkIds)
        ];
    }

    protected function getStartDate($period)
    {
        return match($period) {
            'week' => now()->subWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };
    }

    private function getLevel2Count($userId)
    {
        $directReferrals = User::where('referrer_id', $userId)->pluck('id');
        return User::whereIn('referrer_id', $directReferrals)->count();
    }

    private function getLevel3Count($userId)
    {
        $directReferrals = User::where('referrer_id', $userId)->pluck('id');
        $level2Referrals = User::whereIn('referrer_id', $directReferrals)->pluck('id');
        return User::whereIn('referrer_id', $level2Referrals)->count();
    }

    private function getTotalNetworkCount($userId)
    {
        return $this->getLevel2Count($userId) + 
               $this->getLevel3Count($userId) + 
               User::where('referrer_id', $userId)->count();
    }

    private function getReferralValuesByIds($ids)
    {
        return [
            'count' => count($ids),
            'total_investment' => $this->getTotalInvestments($ids),
            'total_commission' => $this->getTotalCommissions($ids)
        ];
    }

    private function getTotalInvestments($userIds)
    {
        return DB::table('investments')
            ->whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->sum('amount');
    }

    private function getTotalCommissions($userIds)
    {
        return ReferralCommission::whereIn('referrer_id', $userIds)
            ->sum('commission_amount');
    }

    private function getActiveMembersCount($userIds)
    {
        return DB::table('investments')
            ->whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->distinct('user_id')
            ->count('user_id');
    }
}