<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use DateTimeImmutable;

class NaturalLanguageQueryService
{
    public function __construct(
        private ReportingEngine $reportingEngine,
        private AnomalyDetectionService $anomalyService,
        private FinancialRatioService $ratioService,
        private CashFlowForecastService $forecastService,
        private DashboardWidgetService $widgetService,
    ) {}

    public function query(int $businessId, string $question): array
    {
        $question = strtolower(trim($question));
        $now = new DateTimeImmutable('now');
        $monthStart = new DateTimeImmutable('first day of this month');
        $yearStart = new DateTimeImmutable('first day of January this year');
        $lastMonthStart = new DateTimeImmutable('first day of last month');
        $lastMonthEnd = new DateTimeImmutable('last day of last month');

        if (preg_match('/(revenue|income|sales).*(this month|current month|month to date)/', $question)) {
            $pnl = $this->reportingEngine->getProfitAndLoss($businessId, $monthStart, $now);
            return [
                'query' => 'Revenue this month',
                'result' => $pnl['total_income'],
                'formatted' => 'Revenue this month: ' . number_format($pnl['total_income'], 2),
                'type' => 'number',
                'data' => $pnl,
            ];
        }

        if (preg_match('/(revenue|income|sales).*(last month)/', $question)) {
            $pnl = $this->reportingEngine->getProfitAndLoss($businessId, $lastMonthStart, $lastMonthEnd);
            return [
                'query' => 'Revenue last month',
                'result' => $pnl['total_income'],
                'formatted' => 'Revenue last month: ' . number_format($pnl['total_income'], 2),
                'type' => 'number',
                'data' => $pnl,
            ];
        }

        if (preg_match('/(revenue|income|sales).*(this year|year to date|ytd)/', $question)) {
            $pnl = $this->reportingEngine->getProfitAndLoss($businessId, $yearStart, $now);
            return [
                'query' => 'Revenue YTD',
                'result' => $pnl['total_income'],
                'formatted' => 'Revenue YTD: ' . number_format($pnl['total_income'], 2),
                'type' => 'number',
                'data' => $pnl,
            ];
        }

        if (preg_match('/(net (income|profit|loss)|profitability)/', $question)) {
            $pnl = $this->reportingEngine->getProfitAndLoss($businessId, $yearStart, $now);
            $net = $pnl['total_income'] - $pnl['total_expenses'];
            $direction = $net >= 0 ? 'profit' : 'loss';
            return [
                'query' => 'Net income',
                'result' => $net,
                'formatted' => 'Net ' . $direction . ' YTD: ' . number_format(abs($net), 2),
                'type' => 'number',
                'data' => $pnl,
            ];
        }

        if (preg_match('/expenses?/', $question)) {
            $pnl = $this->reportingEngine->getProfitAndLoss($businessId, $yearStart, $now);
            return [
                'query' => 'Total expenses',
                'result' => $pnl['total_expenses'],
                'formatted' => 'Total expenses YTD: ' . number_format($pnl['total_expenses'], 2),
                'type' => 'number',
                'data' => $pnl,
            ];
        }

        if (preg_match('/(cash|bank|balance).*(position|current)/', $question)) {
            $cash = $this->widgetService->getCashPosition($businessId, $now);
            return [
                'query' => 'Cash position',
                'result' => $cash['total'],
                'formatted' => 'Current cash position: ' . number_format($cash['total'], 2),
                'type' => 'number',
                'data' => $cash,
            ];
        }

        if (preg_match('/(accounts? receivable|ar|what.*owe.*us)/', $question)) {
            $arAp = $this->widgetService->getArApSummary($businessId);
            return [
                'query' => 'Accounts Receivable',
                'result' => $arAp['total_ar'],
                'formatted' => 'Accounts Receivable: ' . number_format($arAp['total_ar'], 2),
                'type' => 'number',
                'data' => $arAp,
            ];
        }

        if (preg_match('/(accounts? payable|ap|what.*we.*owe|what.*due)/', $question)) {
            $arAp = $this->widgetService->getArApSummary($businessId);
            return [
                'query' => 'Accounts Payable',
                'result' => $arAp['total_ap'],
                'formatted' => 'Accounts Payable: ' . number_format($arAp['total_ap'], 2),
                'type' => 'number',
                'data' => $arAp,
            ];
        }

        if (preg_match('/(current ratio|quick ratio|debt.*equity|roe|roa|profit margin|financial.?ratios)/', $question)) {
            $ratios = $this->ratioService->getAllRatios($businessId, $yearStart, $now);
            return [
                'query' => 'Financial Ratios',
                'result' => $ratios,
                'formatted' => 'Financial ratios calculated',
                'type' => 'table',
                'data' => $ratios,
            ];
        }

        if (preg_match('/(forecast|projection|predict|future).*(cash|revenue|income)/', $question)) {
            $forecast = $this->forecastService->forecast($businessId, 3);
            return [
                'query' => 'Cash flow forecast (3 months)',
                'result' => $forecast['forecast'],
                'formatted' => 'Forecast generated for ' . $forecast['metadata']['months_ahead'] . ' months',
                'type' => 'table',
                'data' => $forecast,
            ];
        }

        if (preg_match('/(anomal|duplicate|unusual|suspicious|flag)/', $question)) {
            $anomalies = $this->anomalyService->runAll($businessId, new DateTimeImmutable('-30 days'), $now);
            $total = $anomalies['summary']['total'] ?? 0;
            $resultType = $total > 0 ? 'table' : 'number';
            return [
                'query' => 'Recent anomalies',
                'result' => $total,
                'formatted' => $total > 0 ? $total . ' anomalies detected in the last 30 days' : 'No anomalies detected',
                'type' => $resultType,
                'data' => $anomalies,
            ];
        }

        if (preg_match('/compare.*(month|period)/', $question)) {
            $current = $this->reportingEngine->getProfitAndLoss($businessId, $monthStart, $now);
            $previous = $this->reportingEngine->getProfitAndLoss($businessId, $lastMonthStart, $lastMonthEnd);
            return [
                'query' => 'Month comparison',
                'result' => [
                    'current_month' => $current,
                    'last_month' => $previous,
                ],
                'formatted' => 'This month: ' . number_format($current['total_income'], 2) . ' revenue, ' . number_format($current['total_expenses'], 2) . ' expenses',
                'type' => 'comparison',
                'data' => compact('current', 'previous'),
            ];
        }

        return [
            'query' => $question,
            'result' => null,
            'formatted' => 'I couldn\'t understand that question. Try asking about: revenue, expenses, cash position, AR/AP, financial ratios, forecasts, or anomalies.',
            'type' => 'help',
            'data' => null,
        ];
    }
}
