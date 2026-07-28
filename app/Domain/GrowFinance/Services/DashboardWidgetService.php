<?php
declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\CustomerRepositoryInterface;
use App\Domain\GrowFinance\Repositories\VendorRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BudgetRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use DateTimeImmutable;

class DashboardWidgetService
{
    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private GeneralLedgerEngine $generalLedgerEngine,
        private ReportingEngine $reportingEngine,
        private CustomerRepositoryInterface $customerRepo,
        private VendorRepositoryInterface $vendorRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private BudgetRepositoryInterface $budgetRepo,
    ) {}

    public function getAll(int $businessId, ?DateTimeImmutable $asOf = null): array
    {
        $asOf = $asOf ?? new DateTimeImmutable('now');
        $yearStart = new DateTimeImmutable('first day of January this year midnight');

        return [
            'cash_position' => $this->getCashPosition($businessId, $asOf),
            'revenue_trend' => $this->getRevenueTrend($businessId, $yearStart, $asOf),
            'expense_breakdown' => $this->getExpenseBreakdown($businessId, $yearStart, $asOf),
            'ar_ap_summary' => $this->getArApSummary($businessId),
            'cash_flow' => $this->getCashFlowSummary($businessId, $yearStart, $asOf),
            'budget_variance' => $this->getBudgetVariance($businessId, $yearStart, $asOf),
        ];
    }

    public function getCashPosition(int $businessId, DateTimeImmutable $asOf): array
    {
        $cashAccounts = $this->accountRepo->findByCodes($businessId, ['1110', '1120', '1130', '1140', '1150']);
        $total = 0;
        $accounts = [];
        foreach ($cashAccounts as $account) {
            $balance = $this->generalLedgerEngine->getAccountBalance($businessId, $account->id, $asOf);
            $total += $balance;
            $accounts[] = [
                'account_code' => $account->code,
                'account_name' => $account->name,
                'balance' => $balance,
            ];
        }
        return ['total' => round($total, 2), 'accounts' => $accounts];
    }

    public function getRevenueTrend(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $monthly = [];
        $current = $from;
        while ($current <= $to) {
            $monthEnd = $current->modify('last day of this month 23:59:59');
            if ($monthEnd > $to) {
                $monthEnd = $to;
            }
            $monthPnl = $this->reportingEngine->getProfitAndLoss($businessId, $current, $monthEnd);
            $monthly[] = [
                'month' => $current->format('Y-m'),
                'income' => round($monthPnl['total_income'], 2),
                'expenses' => round($monthPnl['total_expenses'], 2),
                'net' => round($monthPnl['total_income'] - $monthPnl['total_expenses'], 2),
            ];
            $current = $current->modify('first day of next month');
        }
        return $monthly;
    }

    public function getExpenseBreakdown(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $pnl = $this->reportingEngine->getProfitAndLoss($businessId, $from, $to);
        $total = $pnl['total_expenses'] ?: 1;
        $categories = [];
        foreach ($pnl['expenses'] as $expense) {
            $categories[] = [
                'account_code' => $expense['account_code'],
                'account_name' => $expense['account_name'],
                'amount' => round($expense['amount'], 2),
                'percentage' => round(($expense['amount'] / $total) * 100, 1),
            ];
        }
        usort($categories, fn($a, $b) => $b['amount'] <=> $a['amount']);
        return $categories;
    }

    public function getArApSummary(int $businessId): array
    {
        $arAccounts = $this->accountRepo->findByCodes($businessId, ['1200', '1210', '1220']);
        $apAccounts = $this->accountRepo->findByCodes($businessId, ['2100', '2110', '2120']);

        $totalAr = 0;
        foreach ($arAccounts as $a) {
            $totalAr += $this->generalLedgerEngine->getAccountBalance($businessId, $a->id, new DateTimeImmutable('now'));
        }

        $totalAp = 0;
        foreach ($apAccounts as $a) {
            $totalAp += abs($this->generalLedgerEngine->getAccountBalance($businessId, $a->id, new DateTimeImmutable('now')));
        }

        $customers = $this->customerRepo->findActive($businessId);
        $vendors = $this->vendorRepo->findActive($businessId);

        return [
            'total_ar' => round($totalAr, 2),
            'total_ap' => round($totalAp, 2),
            'customer_count' => count($customers),
            'vendor_count' => count($vendors),
        ];
    }

    public function getCashFlowSummary(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return $this->reportingEngine->getCashFlow($businessId, $from, $to);
    }

    public function getBudgetVariance(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $budgets = $this->budgetRepo->findActiveByPeriod($businessId, $from, $to);
        $accounts = $this->accountRepo->findActive($businessId);
        $accountMap = [];
        foreach ($accounts as $a) {
            $accountMap[$a->id] = $a;
        }

        $actuals = $this->reportingEngine->getProfitAndLoss($businessId, $from, $to);

        $variance = [];
        foreach ($budgets as $budget) {
            $accountCode = null;
            $accountName = $budget->name;
            if ($budget->accountId && isset($accountMap[$budget->accountId])) {
                $account = $accountMap[$budget->accountId];
                $accountCode = $account->code;
                $accountName = $account->name;
            }

            $actualAmount = 0;
            if ($accountCode) {
                foreach ($actuals['expenses'] as $expense) {
                    if ($expense['account_code'] === $accountCode) {
                        $actualAmount = $expense['amount'];
                        break;
                    }
                }
            }

            $budgeted = $budget->budgetedAmount;
            $diff = $actualAmount - $budgeted;
            $variance[] = [
                'account_code' => $accountCode ?? 'N/A',
                'account_name' => $accountName,
                'budgeted' => round($budgeted, 2),
                'actual' => round($actualAmount, 2),
                'variance' => round($diff, 2),
                'variance_pct' => $budgeted ? round(($diff / $budgeted) * 100, 1) : 0,
            ];
        }
        return $variance;
    }
}
