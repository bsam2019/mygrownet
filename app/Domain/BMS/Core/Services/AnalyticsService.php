<?php

namespace App\Domain\BMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\BMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\BMS\PaymentModel;
use App\Infrastructure\Persistence\Eloquent\BMS\ExpenseModel;
use App\Infrastructure\Persistence\Eloquent\BMS\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\BMS\WorkerAttendanceModel;
use App\Infrastructure\Persistence\Eloquent\BMS\VendorModel;
use App\Infrastructure\Persistence\Eloquent\BMS\PurchaseOrderModel;
use App\Infrastructure\Persistence\Eloquent\BMS\ContractModel;
use App\Infrastructure\Persistence\Eloquent\BMS\AssetModel;
use App\Infrastructure\Persistence\Eloquent\BMS\AssetDepreciationModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel;
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
            'revenue_expense_trend' => $this->getRevenueExpenseTrend($companyId, $startDate),
        ];
    }

    public function getProcurementMetrics(int $companyId, ?string $period = 'month'): array
    {
        $startDate = $this->getStartDate($period);

        return [
            'total_vendors' => VendorModel::forCompany($companyId)->active()->count(),
            'total_purchase_orders' => PurchaseOrderModel::forCompany($companyId)
                ->where('po_date', '>=', $startDate)->count(),
            'pending_pos' => PurchaseOrderModel::forCompany($companyId)
                ->where('po_date', '>=', $startDate)->byStatus('pending')->count(),
            'approved_pos' => PurchaseOrderModel::forCompany($companyId)
                ->where('po_date', '>=', $startDate)->byStatus('approved')->count(),
            'completed_pos' => PurchaseOrderModel::forCompany($companyId)
                ->where('po_date', '>=', $startDate)->byStatus('completed')->count(),
            'total_po_value' => PurchaseOrderModel::forCompany($companyId)
                ->where('po_date', '>=', $startDate)->sum('total_amount'),
            'spend_by_vendor' => $this->getSpendByVendor($companyId, $startDate),
            'po_by_status' => $this->getPurchaseOrdersByStatus($companyId),
        ];
    }

    public function getContractMetrics(int $companyId): array
    {
        return [
            'total_contracts' => ContractModel::forCompany($companyId)->count(),
            'active_contracts' => ContractModel::forCompany($companyId)->active()->count(),
            'expiring_soon' => ContractModel::forCompany($companyId)->expiringSoon()->count(),
            'overdue_renewals' => ContractModel::forCompany($companyId)->overdue()->count(),
        ];
    }

    public function getAssetMetrics(int $companyId): array
    {
        $assets = AssetModel::forCompany($companyId)->get();
        $totalCost = $assets->sum('purchase_cost');
        $totalDepreciation = AssetDepreciationModel::forCompany($companyId)->sum('depreciation_amount');
        
        return [
            'total_assets' => $assets->count(),
            'total_cost' => $totalCost,
            'total_depreciation' => $totalDepreciation,
            'current_value' => $totalCost - $totalDepreciation,
            'by_status' => $assets->groupBy('status')->map->count()->toArray(),
        ];
    }

    public function getOverviewMetrics(int $companyId): array
    {
        $finance = $this->getFinanceMetrics($companyId, 'month');
        $operations = $this->getOperationsMetrics($companyId, 'month');
        $procurement = $this->getProcurementMetrics($companyId, 'month');

        $totalCustomers = CustomerModel::forCompany($companyId)->count();
        $totalInvoices = InvoiceModel::where('company_id', $companyId)->count();
        $paidInvoices = InvoiceModel::where('company_id', $companyId)->where('status', 'paid')->count();
        $invoicePaidRate = $totalInvoices > 0 ? round(($paidInvoices / $totalInvoices) * 100, 1) : 0;

        return [
            'revenue' => $finance['revenue'],
            'profit' => $finance['profit'],
            'expenses' => $finance['expenses'],
            'outstanding' => $finance['outstanding_invoices']['total_outstanding'],
            'job_completion_rate' => $operations['job_completion_rate'],
            'active_jobs' => $operations['jobs_by_status']['in_progress'] ?? 0,
            'total_vendors' => $procurement['total_vendors'],
            'pending_pos' => $procurement['pending_pos'],
            'total_customers' => $totalCustomers,
            'invoice_paid_rate' => $invoicePaidRate,
            'period' => 'month',
        ];
    }

    public function exportFinanceCsv(int $companyId, ?string $period = 'month'): string
    {
        $startDate = $this->getStartDate($period);
        $payments = PaymentModel::where('company_id', $companyId)
            ->where('payment_date', '>=', $startDate)
            ->where('is_voided', false)
            ->with('customer')
            ->orderBy('payment_date')
            ->get();

        $expenses = ExpenseModel::where('company_id', $companyId)
            ->where('expense_date', '>=', $startDate)
            ->where('approval_status', 'approved')
            ->with('category')
            ->orderBy('expense_date')
            ->get();

        $csv = "Type,Date,Description,Category/Customer,Amount\n";

        foreach ($payments as $p) {
            $customerName = $p->customer ? $p->customer->name : 'N/A';
            $csv .= "Revenue,{$p->payment_date},{$p->description},{$customerName},{$p->amount}\n";
        }

        foreach ($expenses as $e) {
            $categoryName = $e->category ? $e->category->name : 'Uncategorized';
            $csv .= "Expense,{$e->expense_date},{$e->description},{$categoryName},{$e->amount}\n";
        }

        return $csv;
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
        // Use database-agnostic date calculation
        $jobs = JobModel::forCompany($companyId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $startDate)
            ->whereNotNull('completed_at')
            ->select('completed_at', 'created_at')
            ->get();

        if ($jobs->isEmpty()) {
            return 0;
        }

        $totalDays = $jobs->sum(function ($job) {
            return Carbon::parse($job->completed_at)->diffInDays(Carbon::parse($job->created_at));
        });

        $avgDays = $totalDays / $jobs->count();

        return round($avgDays, 1);
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
            ->mapWithKeys(function ($expenses) {
                $category = $expenses->first()->category;
                $categoryName = $category ? $category->name : 'Uncategorized';
                return [$categoryName => $expenses->sum('amount')];
            })
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

    private function getSpendByVendor(int $companyId, Carbon $startDate): array
    {
        return PurchaseOrderModel::forCompany($companyId)
            ->where('po_date', '>=', $startDate)
            ->with('vendor')
            ->get()
            ->groupBy('vendor_id')
            ->map(function ($orders) {
                $vendor = $orders->first()->vendor;
                return [
                    'vendor_name' => $vendor ? $vendor->name : 'Unknown',
                    'total_spend' => $orders->sum('total_amount'),
                    'order_count' => $orders->count(),
                ];
            })
            ->sortByDesc('total_spend')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function getPurchaseOrdersByStatus(int $companyId): array
    {
        return PurchaseOrderModel::forCompany($companyId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->count])
            ->toArray();
    }

    private function getRevenueExpenseTrend(int $companyId, Carbon $startDate): array
    {
        $payments = PaymentModel::where('company_id', $companyId)
            ->where('payment_date', '>=', $startDate)
            ->where('is_voided', false)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $expenses = ExpenseModel::where('company_id', $companyId)
            ->where('expense_date', '>=', $startDate)
            ->where('approval_status', 'approved')
            ->selectRaw('DATE(expense_date) as date, SUM(amount) as expense')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dates = $payments->keys()->merge($expenses->keys())->unique()->sort()->values();

        return [
            'dates' => $dates->toArray(),
            'revenue' => $dates->map(fn($date) => $payments->get($date)?->revenue ?? 0)->toArray(),
            'expenses' => $dates->map(fn($date) => $expenses->get($date)?->expense ?? 0)->toArray(),
            'profit' => $dates->map(fn($date) => 
                ($payments->get($date)?->revenue ?? 0) - ($expenses->get($date)?->expense ?? 0)
            )->toArray(),
        ];
    }
}
