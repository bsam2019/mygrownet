<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerAttendanceModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    public function getOperationsMetrics(int $companyId, ?string $period = 'month'): array
    {
        $startDate = $this->getStartDate($period);

        return [
            'job_completion_rate' => $this->getJobCompletionRate($companyId, $startDate),
            'average_job_duration' => $this->getAverageJobDuration($companyId, $startDate),
            'jobs_by_status' => $this->getJobsByStatus($companyId),
            'jobs_by_type' => $this->getJobsByType($companyId, $startDate),
            'worker_productivity' => $this->getWorkerProductivity($companyId, $startDate),
            'inventory_turnover' => $this->getInventoryTurnover($companyId, $startDate),
            'jobs_timeline' => $this->getJobsTimeline($companyId, $startDate),
        ];
    }

    public function getFinanceMetrics(int $companyId, ?string $period = 'month'): array
    {
        $startDate = $this->getStartDate($period);

        return [
            'revenue' => $this->getRevenue($companyId, $startDate),
            'expenses' => $this->getExpenses($companyId, $startDate),
            'profit' => $this->getProfit($companyId, $startDate),
            'cash_flow' => $this->getCashFlow($companyId, $startDate),
            'outstanding_invoices' => $this->getOutstandingInvoices($companyId),
            'revenue_by_customer' => $this->getRevenueByCustomer($companyId, $startDate),
            'expense_breakdown' => $this->getExpenseBreakdown($companyId, $startDate),
            'payment_trends' => $this->getPaymentTrends($companyId, $startDate),
            'profit_margin_trend' => $this->getProfitMarginTrend($companyId, $startDate),
        ];
    }

    private function getStartDate(string $period): Carbon
    {
        return match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };
    }

    private function getJobCompletionRate(int $companyId, Carbon $startDate): float
    {
        $total = JobModel::forCompany($companyId)
            ->where('created_at', '>=', $startDate)
            ->count();

        if ($total === 0) return 0;

        $completed = JobModel::forCompany($companyId)
            ->where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    private function getAverageJobDuration(int $companyId, Carbon $startDate): float
    {
        $avgDays = JobModel::forCompany($companyId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $startDate)
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(DATEDIFF(completed_at, created_at)) as avg_duration')
            ->value('avg_duration');

        return round($avgDays ?? 0, 1);
    }

    private function getJobsByStatus(int $companyId): array
    {
        return JobModel::forCompany($companyId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->count])
            ->toArray();
    }

    private function getJobsByType(int $companyId, Carbon $startDate): array
    {
        return JobModel::forCompany($companyId)
            ->where('created_at', '>=', $startDate)
            ->select('job_type', DB::raw('count(*) as count'))
            ->groupBy('job_type')
            ->get()
            ->mapWithKeys(fn($item) => [$item->job_type => $item->count])
            ->toArray();
    }

    private function getWorkerProductivity(int $companyId, Carbon $startDate): array
    {
        return WorkerAttendanceModel::where('company_id', $companyId)
            ->where('work_date', '>=', $startDate)
            ->where('status', 'approved')
            ->with('worker')
            ->get()
            ->groupBy('worker_id')
            ->map(function ($records) {
                $worker = $records->first()->worker;
                return [
                    'worker_name' => $worker->name,
                    'total_hours' => $records->sum('hours_worked'),
                    'total_days' => $records->sum('days_worked'),
                    'total_earned' => $records->sum('amount_earned'),
                ];
            })
            ->values()
            ->toArray();
    }

    private function getInventoryTurnover(int $companyId, Carbon $startDate): float
    {
        // Simplified inventory turnover calculation
        $totalValue = InventoryItemModel::forCompany($companyId)
            ->sum(DB::raw('current_stock * unit_cost'));

        if ($totalValue == 0) return 0;

        // This is a simplified calculation - in reality you'd track COGS
        return round($totalValue / 30, 2); // Assuming monthly turnover
    }

    private function getJobsTimeline(int $companyId, Carbon $startDate): array
    {
        return JobModel::forCompany($companyId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn($item) => [$item->date => $item->count])
            ->toArray();
    }

    private function getRevenue(int $companyId, Carbon $startDate): float
    {
        return PaymentModel::where('company_id', $companyId)
            ->where('payment_date', '>=', $startDate)
            ->where('is_voided', false)
            ->sum('amount');
    }

    private function getExpenses(int $companyId, Carbon $startDate): float
    {
        return ExpenseModel::where('company_id', $companyId)
            ->where('expense_date', '>=', $startDate)
            ->where('approval_status', 'approved')
            ->sum('amount');
    }

    private function getProfit(int $companyId, Carbon $startDate): float
    {
        $revenue = $this->getRevenue($companyId, $startDate);
        $expenses = $this->getExpenses($companyId, $startDate);
        return $revenue - $expenses;
    }

    private function getCashFlow(int $companyId, Carbon $startDate): array
    {
        $payments = PaymentModel::where('company_id', $companyId)
            ->where('payment_date', '>=', $startDate)
            ->where('is_voided', false)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as inflow')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $expenses = ExpenseModel::where('company_id', $companyId)
            ->where('expense_date', '>=', $startDate)
            ->where('approval_status', 'approved')
            ->selectRaw('DATE(expense_date) as date, SUM(amount) as outflow')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dates = $payments->keys()->merge($expenses->keys())->unique()->sort();

        return $dates->mapWithKeys(function ($date) use ($payments, $expenses) {
            return [$date => [
                'inflow' => $payments->get($date)?->inflow ?? 0,
                'outflow' => $expenses->get($date)?->outflow ?? 0,
                'net' => ($payments->get($date)?->inflow ?? 0) - ($expenses->get($date)?->outflow ?? 0),
            ]];
        })->toArray();
    }

    private function getOutstandingInvoices(int $companyId): array
    {
        $invoices = InvoiceModel::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'partial'])
            ->with('customer')
            ->get();

        return [
            'total_amount' => $invoices->sum('total_amount'),
            'total_outstanding' => $invoices->sum('outstanding_amount'),
            'count' => $invoices->count(),
            'by_customer' => $invoices->groupBy('customer_id')->map(function ($items) {
                $customer = $items->first()->customer;
                return [
                    'customer_name' => $customer->name,
                    'outstanding' => $items->sum('outstanding_amount'),
                    'count' => $items->count(),
                ];
            })->values()->toArray(),
        ];
    }

    private function getRevenueByCustomer(int $companyId, Carbon $startDate): array
    {
        return PaymentModel::where('company_id', $companyId)
            ->where('payment_date', '>=', $startDate)
            ->where('is_voided', false)
            ->with('customer')
            ->get()
            ->groupBy('customer_id')
            ->map(function ($payments) {
                $customer = $payments->first()->customer;
                return [
                    'customer_name' => $customer->name,
                    'total_revenue' => $payments->sum('amount'),
                    'payment_count' => $payments->count(),
                ];
            })
            ->sortByDesc('total_revenue')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function getExpenseBreakdown(int $companyId, Carbon $startDate): array
    {
        return ExpenseModel::where('company_id', $companyId)
            ->where('expense_date', '>=', $startDate)
            ->where('approval_status', 'approved')
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($expenses) {
                $category = $expenses->first()->category;
                return [
                    'category_name' => $category ? $category->name : 'Uncategorized',
                    'total' => $expenses->sum('amount'),
                    'count' => $expenses->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    private function getPaymentTrends(int $companyId, Carbon $startDate): array
    {
        return PaymentModel::where('company_id', $companyId)
            ->where('payment_date', '>=', $startDate)
            ->where('is_voided', false)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn($item) => [$item->date => [
                'total' => $item->total,
                'count' => $item->count,
            ]])
            ->toArray();
    }

    private function getProfitMarginTrend(int $companyId, Carbon $startDate): array
    {
        // Calculate profit margin for each month
        $months = [];
        $current = $startDate->copy();
        
        while ($current <= now()) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();
            
            $revenue = $this->getRevenue($companyId, $monthStart);
            $expenses = $this->getExpenses($companyId, $monthStart);
            
            $margin = $revenue > 0 ? (($revenue - $expenses) / $revenue) * 100 : 0;
            
            $months[$current->format('Y-m')] = round($margin, 2);
            
            $current->addMonth();
        }
        
        return $months;
    }
}
