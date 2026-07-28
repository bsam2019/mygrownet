<?php
declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use DateTimeImmutable;

class RevenuePredictionService
{
    public function __construct(
        private ReportingEngine $reportingEngine,
        private GeneralLedgerEngine $generalLedgerEngine,
        private AccountRepositoryInterface $accountRepo,
    ) {}

    public function predictPnl(int $businessId, int $monthsAhead = 6, ?DateTimeImmutable $asOf = null): array
    {
        $asOf = $asOf ?? new DateTimeImmutable('now');
        $historyStart = $asOf->modify('-12 months');
        $historyEnd = $asOf;

        $monthlyHistory = [];
        $current = $historyStart;
        while ($current < $historyEnd) {
            $monthEnd = $current->modify('last day of this month 23:59:59');
            if ($monthEnd > $historyEnd) $monthEnd = $historyEnd;
            $pnl = $this->reportingEngine->getProfitAndLoss($businessId, $current, $monthEnd);
            $monthlyHistory[] = [
                'month' => $current->format('Y-m'),
                'income' => $pnl['total_income'],
                'expenses' => $pnl['total_expenses'],
                'net' => $pnl['total_income'] - $pnl['total_expenses'],
            ];
            $current = $current->modify('first day of next month');
        }

        $incomeValues = array_column($monthlyHistory, 'income');
        $expenseValues = array_column($monthlyHistory, 'expenses');
        $n = count($monthlyHistory);

        $incomeSlope = $n > 1 ? $this->calculateSlope($incomeValues) : 0;
        $expenseSlope = $n > 1 ? $this->calculateSlope($expenseValues) : 0;

        $lastIncome = $incomeValues[$n - 1] ?? 0;
        $lastExpense = $expenseValues[$n - 1] ?? 0;

        $predictions = [];
        for ($i = 1; $i <= $monthsAhead; $i++) {
            $projectedIncome = max(0, $lastIncome + ($incomeSlope * $i));
            $projectedExpenses = max(0, $lastExpense + ($expenseSlope * $i));
            $projectedNet = $projectedIncome - $projectedExpenses;

            $uncertaintyFactor = 1 + ($i * 0.15);
            $incomeStd = $n > 1 ? $this->stdDev($incomeValues) * $uncertaintyFactor : $projectedIncome * 0.3;
            $expenseStd = $n > 1 ? $this->stdDev($expenseValues) * $uncertaintyFactor : $projectedExpenses * 0.3;

            $forecastDate = $asOf->modify("+{$i} months");
            $predictions[] = [
                'month' => $forecastDate->format('Y-m'),
                'projected_income' => round($projectedIncome, 2),
                'projected_expenses' => round($projectedExpenses, 2),
                'projected_net' => round($projectedNet, 2),
                'income_range_lower' => round(max(0, $projectedIncome - $incomeStd), 2),
                'income_range_upper' => round($projectedIncome + $incomeStd, 2),
                'expense_range_lower' => round(max(0, $projectedExpenses - $expenseStd), 2),
                'expense_range_upper' => round($projectedExpenses + $expenseStd, 2),
            ];
        }

        return [
            'historical' => $monthlyHistory,
            'predictions' => $predictions,
            'metadata' => [
                'as_of' => $asOf->format('Y-m-d'),
                'months_ahead' => $monthsAhead,
                'income_trend' => round($incomeSlope, 2),
                'expense_trend' => round($expenseSlope, 2),
                'avg_income' => $n > 0 ? round(array_sum($incomeValues) / $n, 2) : 0,
                'avg_expenses' => $n > 0 ? round(array_sum($expenseValues) / $n, 2) : 0,
                'income_volatility' => $n > 0 ? round($this->stdDev($incomeValues), 2) : 0,
                'expense_volatility' => $n > 0 ? round($this->stdDev($expenseValues), 2) : 0,
            ],
        ];
    }

    private function calculateSlope(array $values): float
    {
        $n = count($values);
        if ($n < 2) return 0;
        $x = range(0, $n - 1);
        $xMean = ($n - 1) / 2;
        $yMean = array_sum($values) / $n;
        $numerator = 0;
        $denominator = 0;
        for ($i = 0; $i < $n; $i++) {
            $numerator += ($x[$i] - $xMean) * ($values[$i] - $yMean);
            $denominator += ($x[$i] - $xMean) ** 2;
        }
        return $denominator != 0 ? $numerator / $denominator : 0;
    }

    private function stdDev(array $values): float
    {
        $n = count($values);
        if ($n < 2) return 0;
        $mean = array_sum($values) / $n;
        $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / ($n - 1);
        return sqrt($variance);
    }
}
