<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as FacadeResponse;
use Inertia\Inertia;
use Inertia\Response;

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

        return Inertia::render('CMS/Reports/Index', [
            'salesSummary' => $salesSummary,
            'paymentSummary' => $paymentSummary,
            'outstandingInvoices' => $outstandingInvoices,
            'jobProfitability' => $jobProfitability,
            'profitLoss' => $profitLoss,
            'cashbook' => $cashbook,
            'expenseSummary' => $expenseSummary,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
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
        $expenses = \App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel::where('company_id', $companyId)
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

        $openingExpenses = \App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel::where('company_id', $companyId)
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
        $cashOut = \App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel::where('company_id', $companyId)
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
        $expenses = \App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel::where('company_id', $companyId)
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
}
