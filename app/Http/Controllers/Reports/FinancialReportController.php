<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\ProfitShare;
use App\Models\WithdrawalPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    public function getInvestmentMetrics(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        
        $metrics = [
            'total_investments' => $this->getTotalInvestments($startDate),
            'tier_distribution' => $this->getTierDistribution(),
            'investment_growth' => $this->getInvestmentGrowth($startDate),
            'profit_distribution' => $this->getProfitDistribution($startDate),
            'withdrawal_metrics' => $this->getWithdrawalMetrics($startDate)
        ];

        return response()->json($metrics);
    }

    public function getTierPerformance()
    {
        $performance = DB::table('investments')
            ->join('profit_shares', 'investments.id', '=', 'profit_shares.investment_id')
            ->select(
                DB::raw('
                    CASE 
                        WHEN investments.amount >= 10000 THEN "Elite"
                        WHEN investments.amount >= 5000 THEN "Leader"
                        WHEN investments.amount >= 2500 THEN "Builder"
                        WHEN investments.amount >= 1000 THEN "Starter"
                        ELSE "Basic"
                    END as tier
                '),
                DB::raw('COUNT(DISTINCT investments.user_id) as investor_count'),
                DB::raw('SUM(investments.amount) as total_investment'),
                DB::raw('SUM(profit_shares.fixed_profit_amount + profit_shares.performance_bonus) as total_profit'),
                DB::raw('AVG(profit_shares.fixed_profit_amount + profit_shares.performance_bonus) as avg_profit')
            )
            ->where('investments.status', 'active')
            ->groupBy('tier')
            ->get();

        return response()->json($performance);
    }

    public function getQuarterlyReport(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $quarter = $request->get('quarter', ceil(date('n')/3));

        $startDate = Carbon::create($year)->startOfYear()->addMonths(($quarter - 1) * 3);
        $endDate = $startDate->copy()->addMonths(3);

        $report = [
            'period' => [
                'year' => $year,
                'quarter' => $quarter,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString()
            ],
            'metrics' => [
                'new_investments' => $this->getNewInvestments($startDate, $endDate),
                'profit_distribution' => $this->getQuarterlyProfitMetrics($startDate, $endDate),
                'withdrawals' => $this->getQuarterlyWithdrawals($startDate, $endDate)
            ]
        ];

        return response()->json($report);
    }

    protected function getTotalInvestments($startDate)
    {
        return [
            'active_investments' => Investment::where('status', 'active')->sum('amount'),
            'new_investments' => Investment::where('created_at', '>=', $startDate)->sum('amount'),
            'total_investors' => Investment::where('status', 'active')->distinct('user_id')->count()
        ];
    }

    protected function getTierDistribution()
    {
        return DB::table('investments')
            ->select(
                DB::raw('
                    CASE 
                        WHEN amount >= 10000 THEN "Elite"
                        WHEN amount >= 5000 THEN "Leader"
                        WHEN amount >= 2500 THEN "Builder"
                        WHEN amount >= 1000 THEN "Starter"
                        ELSE "Basic"
                    END as tier
                '),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('status', 'active')
            ->groupBy('tier')
            ->get();
    }

    protected function getInvestmentGrowth($startDate)
    {
        return Investment::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('date')
            ->get();
    }

    protected function getProfitDistribution($startDate)
    {
        return ProfitShare::where('created_at', '>=', $startDate)
            ->select(
                'tier_at_distribution as tier',
                DB::raw('SUM(fixed_profit_amount) as fixed_profit'),
                DB::raw('SUM(performance_bonus) as performance_bonus'),
                DB::raw('COUNT(*) as distribution_count')
            )
            ->groupBy('tier_at_distribution')
            ->get();
    }

    protected function getWithdrawalMetrics($startDate)
    {
        return WithdrawalPolicy::where('created_at', '>=', $startDate)
            ->select(
                'withdrawal_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(penalty_amount) as total_penalties')
            )
            ->groupBy('withdrawal_type')
            ->get();
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

    protected function getNewInvestments($startDate, $endDate)
    {
        return Investment::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('date')
            ->get();
    }

    protected function getQuarterlyProfitMetrics($startDate, $endDate)
    {
        return ProfitShare::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(fixed_profit_amount) as fixed_profit'),
                DB::raw('SUM(performance_bonus) as performance_bonus')
            )
            ->groupBy('date')
            ->get();
    }

    protected function getQuarterlyWithdrawals($startDate, $endDate)
    {
        return WithdrawalPolicy::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'processed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                'withdrawal_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as amount'),
                DB::raw('SUM(penalty_amount) as penalties')
            )
            ->groupBy('date', 'withdrawal_type')
            ->get();
    }
}