<?php
declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use DateTimeImmutable;

class CashFlowForecastService
{
    public function __construct(
        private ReportingEngine $reportingEngine,
        private GeneralLedgerEngine $generalLedgerEngine,
        private DashboardWidgetService $widgetService,
        private AccountRepositoryInterface $accountRepo,
    ) {}

    public function forecast(int $businessId, int $monthsAhead = 6, ?DateTimeImmutable $asOf = null): array
    {
        $asOf = $asOf ?? new DateTimeImmutable('now');
        $historyStart = $asOf->modify('-12 months');
        $historyEnd = $asOf;

        $historicalCashFlows = [];
        $current = $historyStart;
        while ($current < $historyEnd) {
            $monthEnd = $current->modify('last day of this month 23:59:59');
            if ($monthEnd > $historyEnd) $monthEnd = $historyEnd;
            $cf = $this->reportingEngine->getCashFlow($businessId, $current, $monthEnd);
            $historicalCashFlows[] = [
                'month' => $current->format('Y-m'),
                'operating' => $cf['total_operating'] ?? 0,
                'investing' => $cf['total_investing'] ?? 0,
                'financing' => $cf['total_financing'] ?? 0,
                'net' => ($cf['total_operating'] ?? 0) + ($cf['total_investing'] ?? 0) + ($cf['total_financing'] ?? 0),
            ];
            $current = $current->modify('first day of next month');
        }

        $cashPosition = $this->widgetService->getCashPosition($businessId, $asOf);
        $currentCash = $cashPosition['total'];

        $operatingValues = array_column($historicalCashFlows, 'operating');
        $investingValues = array_column($historicalCashFlows, 'investing');
        $financingValues = array_column($historicalCashFlows, 'financing');

        $avgOperating = !empty($operatingValues) ? array_sum($operatingValues) / count($operatingValues) : 0;
        $avgInvesting = !empty($investingValues) ? array_sum($investingValues) / count($investingValues) : 0;
        $avgFinancing = !empty($financingValues) ? array_sum($financingValues) / count($financingValues) : 0;

        $stdDev = function (array $values, float $mean): float {
            if (count($values) < 2) return $mean * 0.2;
            $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / count($values);
            return sqrt($variance);
        };

        $stdOperating = $stdDev($operatingValues, $avgOperating);
        $stdInvesting = $stdDev($investingValues, $avgInvesting);
        $stdFinancing = $stdDev($financingValues, $avgFinancing);

        $projectedCash = $currentCash;
        $forecasts = [];

        for ($i = 1; $i <= $monthsAhead; $i++) {
            $forecastDate = $asOf->modify("+{$i} months");
            $month = $forecastDate->format('Y-m');

            $recentWeight = 0.6;
            $recentMonths = array_slice($historicalCashFlows, -3);
            $recentAvgOperating = !empty($recentMonths) ? array_sum(array_column($recentMonths, 'operating')) / count($recentMonths) : 0;
            $adjustedOperating = ($recentAvgOperating * $recentWeight) + ($avgOperating * (1 - $recentWeight));

            $projectedOperating = $adjustedOperating;
            $projectedInvesting = $avgInvesting;
            $projectedFinancing = $avgFinancing;
            $projectedNet = $projectedOperating + $projectedInvesting + $projectedFinancing;
            $projectedCash += $projectedNet;

            $confidenceFactor = 1 - ($i / ($monthsAhead + 1));
            $ciOperating = $stdOperating * 1.96 * (1 + $i * 0.1);
            $ciNet = sqrt($stdOperating**2 + $stdInvesting**2 + $stdFinancing**2) * 1.96 * (1 + $i * 0.1);

            $forecasts[] = [
                'month' => $month,
                'projected_operating' => round($projectedOperating, 2),
                'projected_investing' => round($projectedInvesting, 2),
                'projected_financing' => round($projectedFinancing, 2),
                'projected_net' => round($projectedNet, 2),
                'projected_cash_balance' => round($projectedCash, 2),
                'confidence_interval_lower' => round($projectedCash - $ciNet, 2),
                'confidence_interval_upper' => round($projectedCash + $ciNet, 2),
                'confidence_score' => round($confidenceFactor * 100, 0),
            ];
        }

        return [
            'current_cash' => $currentCash,
            'historical' => $historicalCashFlows,
            'forecast' => $forecasts,
            'metadata' => [
                'as_of' => $asOf->format('Y-m-d'),
                'months_ahead' => $monthsAhead,
                'avg_monthly_operating' => round($avgOperating, 2),
                'avg_monthly_investing' => round($avgInvesting, 2),
                'avg_monthly_financing' => round($avgFinancing, 2),
                'volatility_operating' => round($stdOperating, 2),
                'volatility_investing' => round($stdInvesting, 2),
                'volatility_financing' => round($stdFinancing, 2),
            ],
        ];
    }
}
