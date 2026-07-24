<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Domain\GrowFinance\ValueObjects\InvoiceStatus;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\CustomerRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\VendorRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ProfileRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private CustomerRepositoryInterface $customerRepo,
        private ExpenseRepositoryInterface $expenseRepo,
        private InvoiceRepositoryInterface $invoiceRepo,
        private VendorRepositoryInterface $vendorRepo,
        private ProfileRepositoryInterface $profileRepo,
    ) {}

    public function getFinancialSummary(int $businessId): array
    {
        $now = new \DateTimeImmutable();
        $startOfMonth = $now->format('Y-m-01 00:00:00');
        $endOfMonth = $now->format('Y-m-t 23:59:59');

        $activeAccounts = $this->accountRepo->findActive($businessId);

        $cashAccounts = array_filter(
            $activeAccounts,
            fn($a) => in_array($a->code, ['1000', '1010', '1020'])
        );
        $totalCash = array_sum(array_map(fn($a) => $a->currentBalance, $cashAccounts));

        $monthlyIncome = (float) DB::table('growfinance_invoices')
            ->where('business_id', $businessId)
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', [InvoiceStatus::PAID->value, InvoiceStatus::PARTIAL->value])
            ->sum('amount_paid');

        $monthlyExpenses = (float) DB::table('growfinance_expenses')
            ->where('business_id', $businessId)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $accountsReceivable = (float) DB::table('growfinance_invoices')
            ->where('business_id', $businessId)
            ->whereNotIn('status', [InvoiceStatus::PAID->value, InvoiceStatus::CANCELLED->value])
            ->selectRaw('COALESCE(SUM(total_amount - amount_paid), 0) as total')
            ->value('total');

        $accountsPayable = (float) DB::table('growfinance_vendors')
            ->where('business_id', $businessId)
            ->sum('outstanding_balance');

        return [
            'total_cash' => $totalCash,
            'monthly_income' => $monthlyIncome,
            'monthly_expenses' => $monthlyExpenses,
            'net_income' => $monthlyIncome - $monthlyExpenses,
            'accounts_receivable' => $accountsReceivable,
            'accounts_payable' => $accountsPayable,
        ];
    }

    public function getInvoiceStats(int $businessId): array
    {
        $base = DB::table('growfinance_invoices')->where('business_id', $businessId);

        return [
            'total' => (clone $base)->count(),
            'draft' => (clone $base)->where('status', InvoiceStatus::DRAFT->value)->count(),
            'sent' => (clone $base)->where('status', InvoiceStatus::SENT->value)->count(),
            'paid' => (clone $base)->where('status', InvoiceStatus::PAID->value)->count(),
            'partial' => (clone $base)->where('status', InvoiceStatus::PARTIAL->value)->count(),
            'overdue' => (clone $base)
                ->whereIn('status', [InvoiceStatus::SENT->value, InvoiceStatus::PARTIAL->value])
                ->where('due_date', '<', date('Y-m-d'))
                ->count(),
        ];
    }

    public function getRecentTransactions(int $businessId, int $limit = 10): array
    {
        $invoices = DB::table('growfinance_invoices')
            ->select('id', 'customer_id', 'invoice_number', 'invoice_date', 'total_amount', 'status')
            ->where('business_id', $businessId)
            ->orderBy('invoice_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'type' => 'income',
                'description' => $this->resolveCustomerName($inv->customer_id),
                'amount' => (float) $inv->total_amount,
                'date' => $inv->invoice_date,
                'status' => ucfirst($inv->status),
                'invoice_number' => $inv->invoice_number,
                'category' => null,
            ]);

        $expenses = DB::table('growfinance_expenses')
            ->select('id', 'vendor_id', 'description', 'expense_date', 'amount', 'category')
            ->where('business_id', $businessId)
            ->orderBy('expense_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($exp) => [
                'id' => $exp->id,
                'type' => 'expense',
                'description' => $exp->description ?? $this->resolveVendorName($exp->vendor_id) ?? 'Expense',
                'amount' => (float) $exp->amount,
                'date' => $exp->expense_date,
                'status' => 'Paid',
                'invoice_number' => null,
                'category' => $exp->category ?? 'Expense',
            ]);

        $merged = array_merge($invoices->toArray(), $expenses->toArray());

        usort($merged, fn($a, $b) => strcmp($b['date'], $a['date']));

        return array_slice(array_values($merged), 0, $limit);
    }

    public function getOverdueInvoices(int $businessId, int $limit = 5): array
    {
        $rows = DB::table('growfinance_invoices')
            ->leftJoin('growfinance_customers', 'growfinance_invoices.customer_id', '=', 'growfinance_customers.id')
            ->select(
                'growfinance_invoices.id',
                'growfinance_invoices.invoice_number',
                'growfinance_customers.name as customer_name',
                'growfinance_invoices.total_amount',
                'growfinance_invoices.amount_paid',
                'growfinance_invoices.due_date'
            )
            ->where('growfinance_invoices.business_id', $businessId)
            ->whereIn('growfinance_invoices.status', [InvoiceStatus::SENT->value, InvoiceStatus::PARTIAL->value])
            ->where('growfinance_invoices.due_date', '<', date('Y-m-d'))
            ->orderBy('growfinance_invoices.due_date')
            ->limit($limit)
            ->get();

        return $rows->map(fn($inv) => [
            'id' => $inv->id,
            'invoice_number' => $inv->invoice_number,
            'customer_name' => $inv->customer_name ?? 'Walk-in',
            'total_amount' => (float) $inv->total_amount,
            'balance_due' => (float) ($inv->total_amount - $inv->amount_paid),
            'due_date' => $inv->due_date,
            'days_overdue' => (int) ceil(abs(strtotime($inv->due_date) - time()) / 86400),
        ])->toArray();
    }

    public function getTopCustomers(int $businessId, int $limit = 5): array
    {
        $rows = DB::table('growfinance_customers')
            ->join('growfinance_invoices', 'growfinance_customers.id', '=', 'growfinance_invoices.customer_id')
            ->select(
                'growfinance_customers.id',
                'growfinance_customers.name',
                DB::raw('COALESCE(SUM(growfinance_invoices.total_amount), 0) as total_revenue'),
                'growfinance_customers.outstanding_balance'
            )
            ->where('growfinance_customers.business_id', $businessId)
            ->where('growfinance_customers.is_active', true)
            ->where('growfinance_invoices.status', InvoiceStatus::PAID->value)
            ->groupBy('growfinance_customers.id', 'growfinance_customers.name', 'growfinance_customers.outstanding_balance')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();

        return $rows->map(fn($cust) => [
            'id' => $cust->id,
            'name' => $cust->name,
            'total_revenue' => (float) $cust->total_revenue,
            'outstanding' => (float) $cust->outstanding_balance,
        ])->toArray();
    }

    public function getExpensesByCategory(int $businessId): array
    {
        $now = new \DateTimeImmutable();
        $startOfMonth = $now->format('Y-m-01 00:00:00');
        $endOfMonth = $now->format('Y-m-t 23:59:59');

        $rows = DB::table('growfinance_expenses')
            ->selectRaw('COALESCE(category, ?) as category, SUM(amount) as total', ['Uncategorized'])
            ->where('business_id', $businessId)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        return $rows->map(fn($exp) => [
            'category' => $exp->category ?? 'Uncategorized',
            'total' => (float) $exp->total,
        ])->toArray();
    }

    public function hasSetupCompleted(int $businessId): bool
    {
        return $this->profileRepo->findByUser($businessId) !== null;
    }

    public function getMonthlyTrend(int $businessId, int $months = 6): array
    {
        $trend = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new \DateTimeImmutable("-{$i} months");
            $startOfMonth = $date->format('Y-m-01 00:00:00');
            $endOfMonth = $date->format('Y-m-t 23:59:59');

            $income = (float) DB::table('growfinance_invoices')
                ->where('business_id', $businessId)
                ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                ->whereIn('status', [InvoiceStatus::PAID->value, InvoiceStatus::PARTIAL->value])
                ->sum('amount_paid');

            $expenses = (float) DB::table('growfinance_expenses')
                ->where('business_id', $businessId)
                ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $trend[] = [
                'month' => $date->format('d/m'),
                'income' => $income,
                'expenses' => $expenses,
            ];
        }

        return $trend;
    }

    private function resolveCustomerName(?int $customerId): string
    {
        if (!$customerId) {
            return 'Cash Sale';
        }
        $customer = $this->customerRepo->findById($customerId);
        return $customer?->name ?? 'Cash Sale';
    }

    private function resolveVendorName(?int $vendorId): ?string
    {
        if (!$vendorId) {
            return null;
        }
        $vendor = $this->vendorRepo->findById($vendorId);
        return $vendor?->name;
    }
}
