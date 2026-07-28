<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ScenarioRepositoryInterface;
use DateTimeImmutable;

class ScenarioModellingService
{
    public function __construct(
        private ReportingEngine $reportingEngine,
        private AccountRepositoryInterface $accountRepo,
        private ScenarioRepositoryInterface $scenarioRepo,
    ) {}

    public function modelRevenueChange(int $businessId, float $percentage, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        $from = $from ?? new DateTimeImmutable('first day of this month');
        $to = $to ?? new DateTimeImmutable('now');

        $basePnl = $this->reportingEngine->getProfitAndLoss($businessId, $from, $to);

        $projectedIncome = $basePnl['total_income'] * (1 + $percentage / 100);
        $projectedExpenses = $basePnl['total_expenses'];
        $projectedNet = $projectedIncome - $projectedExpenses;

        return $this->buildResult($basePnl, $projectedIncome, $projectedExpenses, $projectedNet, [
            'type' => 'revenue',
            'percentage' => $percentage,
        ]);
    }

    public function modelExpenseChange(int $businessId, float $percentage, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        $from = $from ?? new DateTimeImmutable('first day of this month');
        $to = $to ?? new DateTimeImmutable('now');

        $basePnl = $this->reportingEngine->getProfitAndLoss($businessId, $from, $to);

        $projectedIncome = $basePnl['total_income'];
        $projectedExpenses = $basePnl['total_expenses'] * (1 + $percentage / 100);
        $projectedNet = $projectedIncome - $projectedExpenses;

        return $this->buildResult($basePnl, $projectedIncome, $projectedExpenses, $projectedNet, [
            'type' => 'expense',
            'percentage' => $percentage,
        ]);
    }

    public function modelAccountChange(int $businessId, string $accountCode, float $newAmount, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        $from = $from ?? new DateTimeImmutable('first day of this month');
        $to = $to ?? new DateTimeImmutable('now');

        $basePnl = $this->reportingEngine->getProfitAndLoss($businessId, $from, $to);
        $account = $this->accountRepo->findByCode($businessId, $accountCode);

        if (!$account) {
            return $this->buildResult($basePnl, $basePnl['total_income'], $basePnl['total_expenses'], $basePnl['net_income'], [
                'type' => 'account',
                'account_code' => $accountCode,
                'new_amount' => $newAmount,
                'error' => 'Account not found',
            ]);
        }

        $currentAmount = 0.0;
        foreach (array_merge($basePnl['income'], $basePnl['expenses']) as $line) {
            if (($line['account_code'] ?? '') === $accountCode) {
                $currentAmount = $line['amount'];
                break;
            }
        }

        $delta = $newAmount - $currentAmount;

        $projectedIncome = $basePnl['total_income'];
        $projectedExpenses = $basePnl['total_expenses'];

        if ($account->type->value === 'income') {
            $projectedIncome += $delta;
        } else {
            $projectedExpenses += $delta;
        }

        $projectedNet = $projectedIncome - $projectedExpenses;

        return $this->buildResult($basePnl, $projectedIncome, $projectedExpenses, $projectedNet, [
            'type' => 'account',
            'account_code' => $accountCode,
            'account_name' => $account->name,
            'current_amount' => $currentAmount,
            'new_amount' => $newAmount,
        ]);
    }

    public function modelCombined(int $businessId, array $scenarios, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        $from = $from ?? new DateTimeImmutable('first day of this month');
        $to = $to ?? new DateTimeImmutable('now');

        $basePnl = $this->reportingEngine->getProfitAndLoss($businessId, $from, $to);

        $projectedIncome = $basePnl['total_income'];
        $projectedExpenses = $basePnl['total_expenses'];
        $details = [];

        foreach ($scenarios as $s) {
            $type = $s['type'] ?? 'revenue';
            $details[] = match ($type) {
                'revenue' => $this->applyRevenueComponent($s, $projectedIncome, $projectedExpenses, $details),
                'expense' => $this->applyExpenseComponent($s, $projectedIncome, $projectedExpenses, $details),
                'account' => $this->applyAccountComponent($businessId, $s, $projectedIncome, $projectedExpenses, $details, $basePnl),
                default => null,
            };
        }

        $projectedNet = $projectedIncome - $projectedExpenses;

        return $this->buildResult($basePnl, $projectedIncome, $projectedExpenses, $projectedNet, [
            'type' => 'combined',
            'scenarios' => $scenarios,
        ]);
    }

    public function saveScenario(int $businessId, string $name, array $parameters, array $results): array
    {
        return $this->scenarioRepo->save($businessId, $name, $parameters, $results);
    }

    public function listScenarios(int $businessId): array
    {
        return $this->scenarioRepo->findByBusiness($businessId);
    }

    public function getScenario(int $id): ?array
    {
        return $this->scenarioRepo->findById($id);
    }

    public function getAccountsForModelling(int $businessId): array
    {
        return $this->accountRepo->findActive($businessId);
    }

    private function buildResult(array $basePnl, float $projectedIncome, float $projectedExpenses, float $projectedNet, array $parameters): array
    {
        $incomeImpact = $projectedIncome - $basePnl['total_income'];
        $expenseImpact = $projectedExpenses - $basePnl['total_expenses'];
        $netImpact = $projectedNet - $basePnl['net_income'];

        return [
            'parameters' => $parameters,
            'base_pnl' => [
                'total_income' => $basePnl['total_income'],
                'total_expenses' => $basePnl['total_expenses'],
                'net_income' => $basePnl['net_income'],
            ],
            'projected_pnl' => [
                'total_income' => round($projectedIncome, 2),
                'total_expenses' => round($projectedExpenses, 2),
                'net_income' => round($projectedNet, 2),
            ],
            'impact' => [
                'income' => round($incomeImpact, 2),
                'expenses' => round($expenseImpact, 2),
                'net_income' => round($netImpact, 2),
                'income_pct' => $basePnl['total_income'] != 0 ? round(($incomeImpact / $basePnl['total_income']) * 100, 2) : 0,
                'expenses_pct' => $basePnl['total_expenses'] != 0 ? round(($expenseImpact / $basePnl['total_expenses']) * 100, 2) : 0,
                'net_income_pct' => $basePnl['net_income'] != 0 ? round(($netImpact / $basePnl['net_income']) * 100, 2) : 0,
            ],
            'period' => [
                'from' => $basePnl['from_date'],
                'to' => $basePnl['to_date'],
            ],
        ];
    }

    private function applyRevenueComponent(array $s, float &$income, float &$expenses, array &$details): array
    {
        $pct = (float) ($s['percentage'] ?? 0);
        $oldIncome = $income;
        $income *= (1 + $pct / 100);
        $detail = ['type' => 'revenue', 'percentage' => $pct, 'impact' => $income - $oldIncome];
        return $detail;
    }

    private function applyExpenseComponent(array $s, float &$income, float &$expenses, array &$details): array
    {
        $pct = (float) ($s['percentage'] ?? 0);
        $oldExpenses = $expenses;
        $expenses *= (1 + $pct / 100);
        $detail = ['type' => 'expense', 'percentage' => $pct, 'impact' => $expenses - $oldExpenses];
        return $detail;
    }

    private function applyAccountComponent(int $businessId, array $s, float &$income, float &$expenses, array &$details, array $basePnl): array
    {
        $code = $s['account_code'] ?? '';
        $newAmount = (float) ($s['new_amount'] ?? 0);
        $account = $this->accountRepo->findByCode($businessId, $code);

        $currentAmount = 0.0;
        foreach (array_merge($basePnl['income'], $basePnl['expenses']) as $line) {
            if (($line['account_code'] ?? '') === $code) {
                $currentAmount = $line['amount'];
                break;
            }
        }

        $delta = $newAmount - $currentAmount;

        if ($account && $account->type->value === 'income') {
            $income += $delta;
        } else {
            $expenses += $delta;
        }

        return [
            'type' => 'account',
            'account_code' => $code,
            'current_amount' => $currentAmount,
            'new_amount' => $newAmount,
            'impact' => $delta,
        ];
    }
}
