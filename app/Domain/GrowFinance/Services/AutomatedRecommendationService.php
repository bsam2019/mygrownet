<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use DateTimeImmutable;

class AutomatedRecommendationService
{
    public function __construct(
        private AnomalyDetectionService $anomalyService,
        private CashFlowForecastService $forecastService,
        private FinancialRatioService $ratioService,
        private DashboardWidgetService $widgetService,
        private ReportingEngine $reportingEngine,
    ) {}

    public function generateAll(int $businessId): array
    {
        $now = new DateTimeImmutable('now');
        $yearStart = new DateTimeImmutable('first day of January this year');

        return [
            'alerts' => $this->generateAlerts($businessId),
            'cash_flow_warnings' => $this->generateCashFlowWarnings($businessId, $now),
            'budget_alerts' => $this->generateBudgetAlerts($businessId, $yearStart, $now),
            'anomaly_notifications' => $this->generateAnomalyNotifications($businessId),
            'suggestions' => $this->generateSuggestions($businessId, $yearStart, $now),
        ];
    }

    private function generateAlerts(int $businessId): array
    {
        $now = new DateTimeImmutable('now');
        $ratios = $this->ratioService->getAllRatios($businessId, new DateTimeImmutable('-12 months'), $now);
        $alerts = [];

        if (($ratios['current_ratio'] ?? 2) < 1.5) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Current Ratio',
                'message' => 'Current ratio is ' . number_format($ratios['current_ratio'] ?? 0, 2) . '. Consider improving short-term liquidity.',
                'severity' => ($ratios['current_ratio'] ?? 2) < 1 ? 'critical' : 'warning',
            ];
        }

        if (($ratios['quick_ratio'] ?? 1.5) < 1) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Quick Ratio',
                'message' => 'Quick ratio is ' . number_format($ratios['quick_ratio'] ?? 0, 2) . '. Liquid assets may not cover immediate liabilities.',
                'severity' => 'warning',
            ];
        }

        if (($ratios['debt_to_equity'] ?? 0) > 2) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'High Debt-to-Equity',
                'message' => 'D/E ratio is ' . number_format($ratios['debt_to_equity'] ?? 0, 2) . '. Consider reducing leverage.',
                'severity' => 'warning',
            ];
        }

        if (($ratios['profit_margin'] ?? 0.05) < 0) {
            $alerts[] = [
                'type' => 'critical',
                'title' => 'Negative Profit Margin',
                'message' => 'Profit margin is negative. Review cost structure and pricing strategy.',
                'severity' => 'critical',
            ];
        }

        return $alerts;
    }

    private function generateCashFlowWarnings(int $businessId, DateTimeImmutable $now): array
    {
        $forecast = $this->forecastService->forecast($businessId, 3, $now);
        $warnings = [];

        foreach ($forecast['forecast'] as $month) {
            if ($month['projected_cash_balance'] < 0) {
                $warnings[] = [
                    'type' => 'critical',
                    'title' => 'Cash Runway Alert',
                    'message' => "Cash projected to go negative by {$month['month']}. Projected balance: " . number_format($month['projected_cash_balance'], 2),
                    'severity' => 'critical',
                    'month' => $month['month'],
                    'projected_balance' => $month['projected_cash_balance'],
                ];
                break;
            }
        }

        $currentCash = $forecast['current_cash'];
        $forecastMonths = $forecast['forecast'];
        $endingCash = !empty($forecastMonths) ? end($forecastMonths)['projected_cash_balance'] : $currentCash;
        if ($endingCash < $currentCash * 0.8) {
            $warnings[] = [
                'type' => 'warning',
                'title' => 'Declining Cash Trend',
                'message' => 'Cash is projected to decline by ' . number_format((1 - $endingCash / $currentCash) * 100, 1) . '% over the forecast period.',
                'severity' => 'warning',
            ];
        }

        return $warnings;
    }

    private function generateBudgetAlerts(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $pnl = $this->reportingEngine->getProfitAndLoss($businessId, $from, $to);
        $alerts = [];

        if ($pnl['total_income'] > 0) {
            $expenseRatio = $pnl['total_expenses'] / $pnl['total_income'] * 100;
            if ($expenseRatio > 95) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'High Expense Ratio',
                    'message' => 'Expenses are ' . number_format($expenseRatio, 1) . '% of revenue. Consider cost reduction measures.',
                    'severity' => 'warning',
                ];
            }
        }

        return $alerts;
    }

    private function generateAnomalyNotifications(int $businessId): array
    {
        $now = new DateTimeImmutable('now');
        $anomalies = $this->anomalyService->runAll($businessId, new DateTimeImmutable('-7 days'), $now);
        $notifications = [];

        foreach (($anomalies['duplicate_invoices'] ?? []) as $dup) {
            $notifications[] = [
                'type' => 'flag',
                'title' => 'Duplicate Invoice Detected',
                'message' => $dup['message'],
                'severity' => 'high',
                'reference' => $dup,
            ];
        }

        return $notifications;
    }

    private function generateSuggestions(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $pnl = $this->reportingEngine->getProfitAndLoss($businessId, $from, $to);
        $suggestions = [];

        if ($pnl['total_income'] > 0) {
            $expenseRatio = $pnl['total_expenses'] / $pnl['total_income'] * 100;
            if ($expenseRatio > 80) {
                $suggestions[] = [
                    'type' => 'suggestion',
                    'title' => 'Review Top Expenses',
                    'message' => 'Your expense ratio is ' . number_format($expenseRatio, 1) . '%. Review the top 5 expense categories for potential savings.',
                    'severity' => 'info',
                ];
            }
        }

        $suggestions[] = [
            'type' => 'suggestion',
            'title' => 'Try What-If Scenarios',
            'message' => 'Use the What-If Scenarios tool to model the impact of revenue or expense changes on your profitability.',
            'severity' => 'info',
        ];

        return $suggestions;
    }
}
