<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Domain\GrowFinance\ValueObjects\InvoiceStatus;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceCustomerModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceExpenseModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceVendorModel;
use Carbon\Carbon;

class DashboardService
{
    public function getFinancialSummary(int $businessId): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Cash balances
        $cashAccounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->active()
            ->whereIn('code', ['1000', '1010', '1020'])
            ->get();

        $totalCash = $cashAccounts->sum('current_balance');

        // Monthly income (from invoices)
        $monthlyIncome = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', [InvoiceStatus::PAID->value, InvoiceStatus::PARTIAL->value])
            ->sum('amount_paid');

        // Monthly expenses
        $monthlyExpenses = GrowFinanceExpenseModel::forBusiness($businessId)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Accounts receivable
        $accountsReceivable = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->whereNotIn('status', [InvoiceStatus::PAID->value, InvoiceStatus::CANCELLED->value])
            ->selectRaw('SUM(total_amount - amount_paid) as total')
            ->value('total') ?? 0;

        // Accounts payable (vendor outstanding)
        $accountsPayable = GrowFinanceVendorModel::forBusiness($businessId)
            ->sum('outstanding_balance');

        return [
            'total_cash' => (float) $totalCash,
            'monthly_income' => (float) $monthlyIncome,
            'monthly_expenses' => (float) $monthlyExpenses,
            'net_income' => (float) ($monthlyIncome - $monthlyExpenses),
            'accounts_receivable' => (float) $accountsReceivable,
            'accounts_payable' => (float) $accountsPayable,
        ];
    }

    public function getInvoiceStats(int $businessId): array
    {
        $invoices = GrowFinanceInvoiceModel::forBusiness($businessId);

        return [
            'total' => (clone $invoices)->count(),
            'draft' => (clone $invoices)->status(InvoiceStatus::DRAFT)->count(),
            'sent' => (clone $invoices)->status(InvoiceStatus::SENT)->count(),
            'paid' => (clone $invoices)->status(InvoiceStatus::PAID)->count(),
            'partial' => (clone $invoices)->status(InvoiceStatus::PARTIAL)->count(),
            'overdue' => (clone $invoices)->overdue()->count(),
        ];
    }

    public function getRecentTransactions(int $businessId, int $limit = 10): array
    {
        $invoices = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->with('customer')
            ->latest('invoice_date')
            ->limit($limit)
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'type' => 'income',
                'description' => $inv->customer?->name ?? 'Cash Sale',
                'amount' => (float) $inv->total_amount,
                'date' => $inv->invoice_date->format('Y-m-d'),
                'status' => ucfirst($inv->status->value),
                'invoice_number' => $inv->invoice_number,
                'category' => null,
            ]);

        $expenses = GrowFinanceExpenseModel::forBusiness($businessId)
            ->with('vendor')
            ->latest('expense_date')
            ->limit($limit)
            ->get()
            ->map(fn($exp) => [
                'id' => $exp->id,
                'type' => 'expense',
                'description' => $exp->description ?? $exp->vendor?->name ?? 'Expense',
                'amount' => (float) $exp->amount,
                'date' => $exp->expense_date->format('Y-m-d'),
                'status' => 'Paid',
                'invoice_number' => null,
                'category' => $exp->category ?? 'Expense',
            ]);

        return $invoices->concat($expenses)
            ->sortByDesc('date')
            ->take($limit)
            ->values()
            ->toArray();
    }

    public function getOverdueInvoices(int $businessId, int $limit = 5): array
    {
        return GrowFinanceInvoiceModel::forBusiness($businessId)
            ->with('customer')
            ->overdue()
            ->orderBy('due_date')
            ->limit($limit)
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'customer_name' => $inv->customer?->name ?? 'Walk-in',
                'total_amount' => (float) $inv->total_amount,
                'balance_due' => (float) $inv->balance_due,
                'due_date' => $inv->due_date->format('Y-m-d'),
                'days_overdue' => $inv->due_date->diffInDays(now()),
            ])
            ->toArray();
    }

    public function getTopCustomers(int $businessId, int $limit = 5): array
    {
        return GrowFinanceCustomerModel::forBusiness($businessId)
            ->active()
            ->withSum(['invoices' => fn($q) => $q->status(InvoiceStatus::PAID)], 'total_amount')
            ->orderByDesc('invoices_sum_total_amount')
            ->limit($limit)
            ->get()
            ->map(fn($cust) => [
                'id' => $cust->id,
                'name' => $cust->name,
                'total_revenue' => (float) ($cust->invoices_sum_total_amount ?? 0),
                'outstanding' => (float) $cust->outstanding_balance,
            ])
            ->toArray();
    }

    public function getExpensesByCategory(int $businessId): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return GrowFinanceExpenseModel::forBusiness($businessId)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($exp) => [
                'category' => $exp->category ?? 'Uncategorized',
                'total' => (float) $exp->total,
            ])
            ->toArray();
    }

    public function hasSetupCompleted(int $businessId): bool
    {
        return GrowFinanceAccountModel::forBusiness($businessId)->exists();
    }

    public function getMonthlyTrend(int $businessId, int $months = 6): array
    {
        $trend = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $income = GrowFinanceInvoiceModel::forBusiness($businessId)
                ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                ->whereIn('status', [InvoiceStatus::PAID->value, InvoiceStatus::PARTIAL->value])
                ->sum('amount_paid');

            $expenses = GrowFinanceExpenseModel::forBusiness($businessId)
                ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $trend[] = [
                'month' => $date->format('d/m'),
                'income' => (float) $income,
                'expenses' => (float) $expenses,
            ];
        }

        return $trend;
    }
}
