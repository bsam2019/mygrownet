<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as FacadeResponse;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $cmsUser = $request->user()->cmsUser;
        $companyId = $cmsUser->company_id;

        // Date range (default: current month)
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        // Sales Summary
        $salesSummary = $this->getSalesSummary($companyId, $startDate, $endDate);

        // Payment Summary
        $paymentSummary = $this->getPaymentSummary($companyId, $startDate, $endDate);

        // Outstanding Invoices
        $outstandingInvoices = $this->getOutstandingInvoices($companyId);

        // Job Profitability
        $jobProfitability = $this->getJobProfitability($companyId, $startDate, $endDate);

        // Profit & Loss Statement
        $profitLoss = $this->getProfitLossStatement($companyId, $startDate, $endDate);

        // Cashbook Report
        $cashbook = $this->getCashbookReport($companyId, $startDate, $endDate);

        // Expense Summary
        $expenseSummary = $this->getExpenseSummary($companyId, $startDate, $endDate);

        // Tax Report
        $taxReport = $this->getTaxReport($companyId, $startDate, $endDate);

        // Comparative Analysis
        $comparative = $this->getComparativeAnalysis($companyId, $startDate, $endDate);

        // Get active budgets for the period
        $activeBudgets = \App\Infrastructure\Persistence\Eloquent\CMS\BudgetModel::where('company_id', $companyId)
            ->active()
            ->forPeriod($startDate, $endDate)
            ->get(['id', 'name', 'start_date', 'end_date']);

        return Inertia::render('CMS/Reports/Index', [
            'salesSummary' => $salesSummary,
            'paymentSummary' => $paymentSummary,
            'outstandingInvoices' => $outstandingInvoices,
            'jobProfitability' => $jobProfitability,
            'profitLoss' => $profitLoss,
            'cashbook' => $cashbook,
            'expenseSummary' => $expenseSummary,
            'taxReport' => $taxReport,
            'comparative' => $comparative,
            'activeBudgets' => $activeBudgets,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $cmsUser = $request->user()->cmsUser;
        $companyId = $cmsUser->company_id;
        
        $reportType = $request->input('report_type');
        $format = $request->input('format', 'csv'); // csv or excel
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $data = match($reportType) {
            'sales' => $this->getSalesSummary($companyId, $startDate, $endDate),
            'payments' => $this->getPaymentSummary($companyId, $startDate, $endDate),
            'expenses' => $this->getExpenseSummary($companyId, $startDate, $endDate),
            'profitLoss' => $this->getProfitLossStatement($companyId, $startDate, $endDate),
            'cashbook' => $this->getCashbookReport($companyId, $startDate, $endDate),
            'tax' => $this->getTaxReport($companyId, $startDate, $endDate),
            default => []
        };

        if ($format === 'csv') {
            return $this->exportToCsv($data, $reportType, $startDate, $endDate);
        }

        // For now, only CSV is implemented
        return $this->exportToCsv($data, $reportType, $startDate, $endDate);
    }

    private function exportToCsv(array $data, string $reportType, string $startDate, string $endDate)
    {
        $filename = "{$reportType}_report_{$startDate}_to_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data, $reportType) {
            $file = fopen('php://output', 'w');
            
            // Add report header
            fputcsv($file, [ucfirst($reportType) . ' Report']);
            fputcsv($file, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []); // Empty line

            // Export based on report type
            match($reportType) {
                'sales' => $this->exportSalesData($file, $data),
                'payments' => $this->exportPaymentsData($file, $data),
                'expenses' => $this->exportExpensesData($file, $data),
                'profitLoss' => $this->exportProfitLossData($file, $data),
                'cashbook' => $this->exportCashbookData($file, $data),
                'tax' => $this->exportTaxData($file, $data),
                default => null
            };

            fclose($file);
        };

        return FacadeResponse::stream($callback, 200, $headers);
    }

    private function exportSalesData($file, array $data)
    {
        fputcsv($file, ['Metric', 'Value']);
        fputcsv($file, ['Total Invoices', $data['total_invoices']]);
        fputcsv($file, ['Total Value', 'K' . number_format($data['total_value'], 2)]);
        fputcsv($file, ['Total Paid', 'K' . number_format($data['total_paid'], 2)]);
        fputcsv($file, ['Outstanding', 'K' . number_format($data['total_outstanding'], 2)]);
    }

    private function exportPaymentsData($file, array $data)
    {
        fputcsv($file, ['Metric', 'Value']);
        fputcsv($file, ['Total Payments', $data['total_payments']]);
        fputcsv($file, ['Total Amount', 'K' . number_format($data['total_amount'], 2)]);
        fputcsv($file, []);
        fputcsv($file, ['Payment Method', 'Count', 'Total']);
        foreach ($data['by_method'] as $method => $info) {
            fputcsv($file, [$method, $info['count'], 'K' . number_format($info['total'], 2)]);
        }
    }

    private function exportExpensesData($file, array $data)
    {
        fputcsv($file, ['Category', 'Count', 'Total', 'Approved', 'Pending']);
        foreach ($data['by_category'] as $category => $info) {
            fputcsv($file, [
                $category,
                $info['count'],
                'K' . number_format($info['total'], 2),
                'K' . number_format($info['approved'], 2),
                'K' . number_format($info['pending'], 2)
            ]);
        }
    }

    private function exportProfitLossData($file, array $data)
    {
        fputcsv($file, ['Item', 'Amount']);
        fputcsv($file, ['Revenue', 'K' . number_format($data['revenue'], 2)]);
        fputcsv($file, ['Cost of Goods Sold', 'K' . number_format($data['cogs'], 2)]);
        fputcsv($file, ['Gross Profit', 'K' . number_format($data['gross_profit'], 2)]);
        fputcsv($file, ['Operating Expenses', 'K' . number_format($data['operating_expenses']['total'], 2)]);
        fputcsv($file, ['Labor Costs', 'K' . number_format($data['labor_costs'], 2)]);
        fputcsv($file, ['Operating Profit', 'K' . number_format($data['operating_profit'], 2)]);
        fputcsv($file, ['Net Profit', 'K' . number_format($data['net_profit'], 2)]);
    }

    private function exportCashbookData($file, array $data)
    {
        fputcsv($file, ['Date', 'Description', 'Reference', 'Method', 'Cash In', 'Cash Out']);
        foreach ($data['transactions'] as $transaction) {
            fputcsv($file, [
                $transaction['date'],
                $transaction['description'],
                $transaction['reference'],
                $transaction['method'],
                $transaction['type'] === 'in' ? 'K' . number_format($transaction['amount'], 2) : '',
                $transaction['type'] === 'out' ? 'K' . number_format($transaction['amount'], 2) : ''
            ]);
        }
    }

    private function exportTaxData($file, array $data)
    {
        fputcsv($file, ['Item', 'Amount']);
        fputcsv($file, ['VAT Collected', 'K' . number_format($data['vat_collected'], 2)]);
        fputcsv($file, ['VAT Paid', 'K' . number_format($data['vat_paid'], 2)]);
        fputcsv($file, ['Net VAT Position', 'K' . number_format($data['net_vat_position'], 2)]);
        fputcsv($file, ['Taxable Revenue', 'K' . number_format($data['taxable_revenue'], 2)]);
        fputcsv($file, ['Taxable Expenses', 'K' . number_format($data['taxable_expenses'], 2)]);
    }

    private function getSalesSummary(int $companyId, string $startDate, string $endDate): array
    {
        $invoices = InvoiceModel::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get();

        return [
            'total_invoices' => $invoices->count(),
            'total_value' => $invoices->sum('total_amount'),
            'total_paid' => $invoices->sum('amount_paid'),
            'total_outstanding' => $invoices->sum(fn($inv) => $inv->total_amount - $inv->amount_paid),
            'by_status' => [
                'draft' => $invoices->where('status', 'draft')->count(),
                'sent' => $invoices->where('status', 'sent')->count(),
                'partial' => $invoices->where('status', 'partial')->count(),
                'paid' => $invoices->where('status', 'paid')->count(),
            ],
        ];
    }

    private function getPaymentSummary(int $companyId, string $startDate, string $endDate): array
    {
        $payments = PaymentModel::where('company_id', $companyId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('is_voided', false)
            ->get();

        $byMethod = $payments->groupBy('payment_method')->map(fn($group) => [
            'count' => $group->count(),
            'total' => $group->sum('amount'),
        ]);

        return [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'by_method' => $byMethod,
        ];
    }

    private function getOutstandingInvoices(int $companyId): array
    {
        $invoices = InvoiceModel::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'partial'])
            ->with('customer')
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'customer_name' => $inv->customer->name,
                'invoice_date' => $inv->invoice_date,
                'due_date' => $inv->due_date,
                'total_amount' => $inv->total_amount,
                'amount_paid' => $inv->amount_paid,
                'balance_due' => $inv->total_amount - $inv->amount_paid,
                'days_overdue' => $inv->due_date->isPast() ? now()->diffInDays($inv->due_date) : 0,
            ]);

        return [
            'invoices' => $invoices->values()->toArray(),
            'total_outstanding' => $invoices->sum('balance_due'),
            'overdue_count' => $invoices->where('days_overdue', '>', 0)->count(),
            'overdue_amount' => $invoices->where('days_overdue', '>', 0)->sum('balance_due'),
        ];
    }

    private function getJobProfitability(int $companyId, string $startDate, string $endDate): array
    {
        $jobs = JobModel::where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->get();

        $totalRevenue = $jobs->sum('actual_value');
        $totalCost = $jobs->sum('total_cost');
        $totalProfit = $totalRevenue - $totalCost;

        return [
            'total_jobs' => $jobs->count(),
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'profit_margin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0,
        ];
    }

    private function getProfitLossStatement(int $companyId, string $startDate, string $endDate): array
    {
        // Revenue (from paid invoices)
        $revenue = InvoiceModel::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('amount_paid');

        // Cost of Goods Sold (from completed jobs)
        $cogs = JobModel::where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->sum('material_cost');

        // Operating Expenses (from approved expenses)
        $expenses = ExpenseModel::where('company_id', $companyId)
            ->where('approval_status', 'approved')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with('category')
            ->get();

        $expensesByCategory = $expenses->groupBy('category.name')->map(fn($group) => $group->sum('amount'));

        $totalExpenses = $expenses->sum('amount');

        // Labor Costs (from payroll)
        $laborCosts = \App\Infrastructure\Persistence\Eloquent\CMS\PayrollRunModel::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('period_end', [$startDate, $endDate])
            ->sum('total_net_pay');

        // Calculations
        $grossProfit = $revenue - $cogs;
        $operatingProfit = $grossProfit - $totalExpenses - $laborCosts;
        $netProfit = $operatingProfit; // Simplified (no tax/interest for now)

        return [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'gross_margin' => $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0,
            'operating_expenses' => [
                'total' => $totalExpenses,
                'by_category' => $expensesByCategory,
            ],
            'labor_costs' => $laborCosts,
            'total_operating_expenses' => $totalExpenses + $laborCosts,
            'operating_profit' => $operatingProfit,
            'operating_margin' => $revenue > 0 ? ($operatingProfit / $revenue) * 100 : 0,
            'net_profit' => $netProfit,
            'net_margin' => $revenue > 0 ? ($netProfit / $revenue) * 100 : 0,
        ];
    }

    private function getCashbookReport(int $companyId, string $startDate, string $endDate): array
    {
        // Opening Balance (payments before start date minus expenses before start date)
        $openingPayments = PaymentModel::where('company_id', $companyId)
            ->where('payment_date', '<', $startDate)
            ->where('is_voided', false)
            ->sum('amount');

        $openingExpenses = ExpenseModel::where('company_id', $companyId)
            ->where('expense_date', '<', $startDate)
            ->where('approval_status', 'approved')
            ->sum('amount');

        $openingBalance = $openingPayments - $openingExpenses;

        // Cash In (payments received)
        $cashIn = PaymentModel::where('company_id', $companyId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('is_voided', false)
            ->with('invoice.customer')
            ->get()
            ->map(fn($payment) => [
                'date' => $payment->payment_date,
                'description' => "Payment from {$payment->invoice->customer->name} - Invoice {$payment->invoice->invoice_number}",
                'reference' => $payment->reference_number,
                'method' => $payment->payment_method,
                'amount' => $payment->amount,
            ]);

        $totalCashIn = $cashIn->sum('amount');

        // Cash Out (expenses paid)
        $cashOut = ExpenseModel::where('company_id', $companyId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->where('approval_status', 'approved')
            ->with('category')
            ->get()
            ->map(fn($expense) => [
                'date' => $expense->expense_date,
                'description' => "{$expense->category->name} - {$expense->description}",
                'reference' => $expense->receipt_number ?? $expense->expense_number,
                'method' => $expense->payment_method,
                'amount' => $expense->amount,
            ]);

        $totalCashOut = $cashOut->sum('amount');

        // Closing Balance
        $closingBalance = $openingBalance + $totalCashIn - $totalCashOut;

        // Combine and sort transactions
        $transactions = $cashIn->map(fn($item) => array_merge($item, ['type' => 'in']))
            ->concat($cashOut->map(fn($item) => array_merge($item, ['type' => 'out'])))
            ->sortBy('date')
            ->values();

        return [
            'opening_balance' => $openingBalance,
            'total_cash_in' => $totalCashIn,
            'total_cash_out' => $totalCashOut,
            'closing_balance' => $closingBalance,
            'transactions' => $transactions->toArray(),
            'cash_in_by_method' => $cashIn->groupBy('method')->map(fn($group) => $group->sum('amount')),
            'cash_out_by_method' => $cashOut->groupBy('method')->map(fn($group) => $group->sum('amount')),
        ];
    }

    private function getExpenseSummary(int $companyId, string $startDate, string $endDate): array
    {
        $expenses = ExpenseModel::where('company_id', $companyId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with(['category', 'job'])
            ->get();

        // By Category
        $byCategory = $expenses->groupBy('category.name')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('amount'),
                'approved' => $group->where('approval_status', 'approved')->sum('amount'),
                'pending' => $group->where('approval_status', 'pending')->sum('amount'),
                'rejected' => $group->where('approval_status', 'rejected')->sum('amount'),
            ];
        })->sortByDesc('total');

        // By Payment Method
        $byMethod = $expenses->where('approval_status', 'approved')
            ->groupBy('payment_method')
            ->map(fn($group) => [
                'count' => $group->count(),
                'total' => $group->sum('amount'),
            ]);

        // By Status
        $byStatus = [
            'approved' => [
                'count' => $expenses->where('approval_status', 'approved')->count(),
                'total' => $expenses->where('approval_status', 'approved')->sum('amount'),
            ],
            'pending' => [
                'count' => $expenses->where('approval_status', 'pending')->count(),
                'total' => $expenses->where('approval_status', 'pending')->sum('amount'),
            ],
            'rejected' => [
                'count' => $expenses->where('approval_status', 'rejected')->count(),
                'total' => $expenses->where('approval_status', 'rejected')->sum('amount'),
            ],
        ];

        // Job-Related vs General
        $jobRelated = $expenses->whereNotNull('job_id');
        $general = $expenses->whereNull('job_id');

        // Top Expenses
        $topExpenses = $expenses->where('approval_status', 'approved')
            ->sortByDesc('amount')
            ->take(10)
            ->map(fn($expense) => [
                'id' => $expense->id,
                'expense_number' => $expense->expense_number,
                'date' => $expense->expense_date,
                'category' => $expense->category->name,
                'description' => $expense->description,
                'amount' => $expense->amount,
                'job' => $expense->job ? $expense->job->job_number : null,
            ])
            ->values();

        return [
            'total_expenses' => $expenses->count(),
            'total_amount' => $expenses->sum('amount'),
            'approved_amount' => $expenses->where('approval_status', 'approved')->sum('amount'),
            'pending_amount' => $expenses->where('approval_status', 'pending')->sum('amount'),
            'by_category' => $byCategory->toArray(),
            'by_method' => $byMethod->toArray(),
            'by_status' => $byStatus,
            'job_related' => [
                'count' => $jobRelated->count(),
                'total' => $jobRelated->sum('amount'),
            ],
            'general' => [
                'count' => $general->count(),
                'total' => $general->sum('amount'),
            ],
            'top_expenses' => $topExpenses->toArray(),
        ];
    }

    private function getTaxReport(int $companyId, string $startDate, string $endDate): array
    {
        // VAT Collected from Invoices
        $invoices = InvoiceModel::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get();

        $vatCollected = $invoices->sum('tax_amount');
        $taxableRevenue = $invoices->sum('subtotal');

        // VAT Paid on Expenses
        $expenses = ExpenseModel::where('company_id', $companyId)
            ->where('approval_status', 'approved')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();

        // Assuming 16% VAT rate (adjust based on company settings)
        $vatRate = 0.16;
        $vatPaidOnExpenses = $expenses->sum(function ($expense) use ($vatRate) {
            // Calculate VAT from gross amount
            return $expense->amount * ($vatRate / (1 + $vatRate));
        });

        $netVatPosition = $vatCollected - $vatPaidOnExpenses;

        // Group by tax rate (for future multi-rate support)
        $byTaxRate = [
            '16%' => [
                'taxable_revenue' => $taxableRevenue,
                'vat_collected' => $vatCollected,
                'taxable_expenses' => $expenses->sum('amount'),
                'vat_paid' => $vatPaidOnExpenses,
            ],
        ];

        return [
            'vat_collected' => $vatCollected,
            'vat_paid' => $vatPaidOnExpenses,
            'net_vat_position' => $netVatPosition,
            'taxable_revenue' => $taxableRevenue,
            'taxable_expenses' => $expenses->sum('amount'),
            'by_tax_rate' => $byTaxRate,
            'vat_rate' => $vatRate * 100, // 16%
        ];
    }

    private function getComparativeAnalysis(int $companyId, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDiff = $start->diffInDays($end) + 1;

        // Calculate previous period (same length)
        $prevStartDate = $start->copy()->subDays($daysDiff)->toDateString();
        $prevEndDate = $start->copy()->subDay()->toDateString();

        // Calculate year-over-year period
        $yoyStartDate = $start->copy()->subYear()->toDateString();
        $yoyEndDate = $end->copy()->subYear()->toDateString();

        // Current Period Data
        $currentRevenue = InvoiceModel::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('amount_paid');

        $currentExpenses = ExpenseModel::where('company_id', $companyId)
            ->where('approval_status', 'approved')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $currentProfit = $currentRevenue - $currentExpenses;

        // Previous Period Data (Month-over-Month)
        $prevRevenue = InvoiceModel::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$prevStartDate, $prevEndDate])
            ->sum('amount_paid');

        $prevExpenses = ExpenseModel::where('company_id', $companyId)
            ->where('approval_status', 'approved')
            ->whereBetween('expense_date', [$prevStartDate, $prevEndDate])
            ->sum('amount');

        $prevProfit = $prevRevenue - $prevExpenses;

        // Year-over-Year Data
        $yoyRevenue = InvoiceModel::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$yoyStartDate, $yoyEndDate])
            ->sum('amount_paid');

        $yoyExpenses = ExpenseModel::where('company_id', $companyId)
            ->where('approval_status', 'approved')
            ->whereBetween('expense_date', [$yoyStartDate, $yoyEndDate])
            ->sum('amount');

        $yoyProfit = $yoyRevenue - $yoyExpenses;

        // Calculate growth percentages
        $revenueGrowthMoM = $prevRevenue > 0 ? (($currentRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
        $expenseGrowthMoM = $prevExpenses > 0 ? (($currentExpenses - $prevExpenses) / $prevExpenses) * 100 : 0;
        $profitGrowthMoM = $prevProfit != 0 ? (($currentProfit - $prevProfit) / abs($prevProfit)) * 100 : 0;

        $revenueGrowthYoY = $yoyRevenue > 0 ? (($currentRevenue - $yoyRevenue) / $yoyRevenue) * 100 : 0;
        $expenseGrowthYoY = $yoyExpenses > 0 ? (($currentExpenses - $yoyExpenses) / $yoyExpenses) * 100 : 0;
        $profitGrowthYoY = $yoyProfit != 0 ? (($currentProfit - $yoyProfit) / abs($yoyProfit)) * 100 : 0;

        return [
            'current_period' => [
                'revenue' => $currentRevenue,
                'expenses' => $currentExpenses,
                'profit' => $currentProfit,
            ],
            'previous_period' => [
                'revenue' => $prevRevenue,
                'expenses' => $prevExpenses,
                'profit' => $prevProfit,
                'start_date' => $prevStartDate,
                'end_date' => $prevEndDate,
            ],
            'year_over_year' => [
                'revenue' => $yoyRevenue,
                'expenses' => $yoyExpenses,
                'profit' => $yoyProfit,
                'start_date' => $yoyStartDate,
                'end_date' => $yoyEndDate,
            ],
            'month_over_month_growth' => [
                'revenue' => $revenueGrowthMoM,
                'expenses' => $expenseGrowthMoM,
                'profit' => $profitGrowthMoM,
            ],
            'year_over_year_growth' => [
                'revenue' => $revenueGrowthYoY,
                'expenses' => $expenseGrowthYoY,
                'profit' => $profitGrowthYoY,
            ],
        ];
    }
}
