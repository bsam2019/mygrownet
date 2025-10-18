<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Investment\Repositories\InvestmentRepositoryInterface;
use App\Models\User;
use App\Models\Investment;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class EloquentInvestmentRepository implements InvestmentRepositoryInterface
{
    public function findActiveInvestmentsByUser(User $user): Collection
    {
        return $user->investments()
            ->where('status', 'active')
            ->with(['referralCommissions', 'profitShares'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTotalInvestmentPool(): float
    {
        return Investment::where('status', 'active')
            ->sum('amount');
    }

    public function getInvestmentsByDateRange(Carbon $start, Carbon $end): Collection
    {
        return Investment::whereBetween('created_at', [$start, $end])
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function calculateUserPoolPercentage(User $user): float
    {
        $totalPool = $this->getTotalInvestmentPool();
        
        if ($totalPool <= 0) {
            return 0.0;
        }

        $userTotal = $this->getUserTotalInvestmentAmount($user);
        
        return ($userTotal / $totalPool) * 100;
    }

    public function getTotalActiveInvestmentsAmount(): float
    {
        return Investment::where('status', 'active')
            ->sum('amount');
    }

    public function findInvestmentsByStatus(string $status): Collection
    {
        return Investment::where('status', $status)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUserTotalInvestmentAmount(User $user): float
    {
        return $user->investments()
            ->where('status', 'active')
            ->sum('amount');
    }

    public function findEligibleForProfitDistribution(): Collection
    {
        return Investment::where('status', 'active')
            ->whereNotNull('investment_date')
            ->with(['user'])
            ->get();
    }

    public function getInvestmentStatistics(Carbon $start, Carbon $end): array
    {
        $investments = $this->getInvestmentsByDateRange($start, $end);
        
        $totalAmount = $investments->sum('amount');
        $totalCount = $investments->count();
        $activeCount = $investments->where('status', 'active')->count();
        $pendingCount = $investments->where('status', 'pending')->count();
        $rejectedCount = $investments->where('status', 'rejected')->count();

        // Group by tier
        $tierBreakdown = $investments->groupBy('tier')->map(function ($tierInvestments) {
            return [
                'count' => $tierInvestments->count(),
                'total_amount' => $tierInvestments->sum('amount'),
                'average_amount' => $tierInvestments->avg('amount')
            ];
        });

        // Daily breakdown
        $dailyBreakdown = $investments->groupBy(function ($investment) {
            return $investment->created_at->format('Y-m-d');
        })->map(function ($dayInvestments) {
            return [
                'count' => $dayInvestments->count(),
                'total_amount' => $dayInvestments->sum('amount')
            ];
        });

        return [
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString()
            ],
            'totals' => [
                'count' => $totalCount,
                'amount' => $totalAmount,
                'average_amount' => $totalCount > 0 ? $totalAmount / $totalCount : 0
            ],
            'status_breakdown' => [
                'active' => $activeCount,
                'pending' => $pendingCount,
                'rejected' => $rejectedCount
            ],
            'tier_breakdown' => $tierBreakdown->toArray(),
            'daily_breakdown' => $dailyBreakdown->toArray()
        ];
    }
}