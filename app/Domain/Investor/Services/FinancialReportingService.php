<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Entities\FinancialReport;
use App\Domain\Investor\Repositories\FinancialReportRepositoryInterface;
use App\Domain\Investor\ValueObjects\ReportType;

class FinancialReportingService
{
    public function __construct(
        private FinancialReportRepositoryInterface $reportRepository
    ) {}

    public function getLatestReports(int $limit = 5): array
    {
        return $this->reportRepository->findLatestPublished($limit);
    }

    public function getReportsByType(ReportType $type): array
    {
        return $this->reportRepository->findByType($type);
    }

    public function getFinancialSummary(): array
    {
        $latestReport = $this->reportRepository->getLatestReport();
        
        if (!$latestReport) {
            return $this->getDefaultSummary();
        }

        return [
            'latest_period' => $latestReport->getReportPeriod(),
            'latest_period_display' => $latestReport->getReportType()->getDisplayPeriod($latestReport->getReportPeriod()),
            'total_revenue' => $latestReport->getTotalRevenue(),
            'total_expenses' => $latestReport->getTotalExpenses(),
            'net_profit' => $latestReport->getNetProfit(),
            'profit_margin' => $latestReport->getProfitMargin(),
            'growth_rate' => $latestReport->getGrowthRate(),
            'growth_status' => $latestReport->getRevenueGrowthStatus(),
            'health_score' => $latestReport->getFinancialHealthScore(),
            'health_score_label' => $latestReport->getHealthScoreLabel(),
            'health_score_color' => $latestReport->getHealthScoreColor(),
            'total_members' => $latestReport->getTotalMembers(),
            'active_members' => $latestReport->getActiveMembers(),
            'mrr' => $latestReport->getMonthlyRecurringRevenue(),
            'cash_flow' => $latestReport->getCashFlow(),
            'revenue_breakdown' => $latestReport->getRevenueBreakdown(),
            'report_date' => $latestReport->getReportDate()->format('Y-m-d'),
            'report_date_formatted' => $latestReport->getReportDate()->format('F j, Y'),
        ];
    }

    public function getPerformanceMetrics(): array
    {
        $reports = $this->reportRepository->findLatestPublished(6);
        
        if (empty($reports)) {
            return $this->getDefaultMetrics();
        }

        return [
            'revenue_trend' => $this->calculateRevenueTrend($reports),
            'profit_trend' => $this->calculateProfitTrend($reports),
            'member_growth' => $this->calculateMemberGrowth($reports),
            'margin_analysis' => $this->calculateMarginAnalysis($reports),
            'health_score_trend' => $this->calculateHealthScoreTrend($reports),
        ];
    }

    public function createReport(
        string $title,
        ReportType $reportType,
        string $reportPeriod,
        \DateTimeImmutable $reportDate,
        float $totalRevenue,
        float $totalExpenses,
        array $additionalData = [],
        array $revenueBreakdown = []
    ): FinancialReport {
        $netProfit = $totalRevenue - $totalExpenses;
        
        $report = new FinancialReport(
            id: 0, // Will be set by repository
            title: $title,
            reportType: $reportType,
            reportPeriod: $reportPeriod,
            reportDate: $reportDate,
            totalRevenue: $totalRevenue,
            totalExpenses: $totalExpenses,
            netProfit: $netProfit,
            grossMargin: $additionalData['gross_margin'] ?? null,
            operatingMargin: $additionalData['operating_margin'] ?? null,
            netMargin: $additionalData['net_margin'] ?? null,
            cashFlow: $additionalData['cash_flow'] ?? null,
            totalMembers: $additionalData['total_members'] ?? null,
            activeMembers: $additionalData['active_members'] ?? null,
            monthlyRecurringRevenue: $additionalData['monthly_recurring_revenue'] ?? null,
            customerAcquisitionCost: $additionalData['customer_acquisition_cost'] ?? null,
            lifetimeValue: $additionalData['lifetime_value'] ?? null,
            churnRate: $additionalData['churn_rate'] ?? null,
            growthRate: $additionalData['growth_rate'] ?? null,
            notes: $additionalData['notes'] ?? null,
            publishedAt: null, // Reports start as drafts
            revenueBreakdown: $revenueBreakdown
        );

        return $this->reportRepository->save($report);
    }

    public function publishReport(int $reportId): FinancialReport
    {
        $report = $this->reportRepository->findById($reportId);
        
        if (!$report) {
            throw new \Exception('Financial report not found');
        }

        $report->publish();
        return $this->reportRepository->save($report);
    }

    public function unpublishReport(int $reportId): FinancialReport
    {
        $report = $this->reportRepository->findById($reportId);
        
        if (!$report) {
            throw new \Exception('Financial report not found');
        }

        $report->unpublish();
        return $this->reportRepository->save($report);
    }

    public function deleteReport(int $reportId): void
    {
        $this->reportRepository->delete($reportId);
    }

    public function getReportById(int $reportId): ?FinancialReport
    {
        return $this->reportRepository->findById($reportId);
    }

    public function getAllReports(): array
    {
        return $this->reportRepository->findAll();
    }

    public function getReportingStats(): array
    {
        $allReports = $this->reportRepository->findAll();
        $publishedReports = $this->reportRepository->findPublished();
        
        return [
            'total_reports' => count($allReports),
            'published_reports' => count($publishedReports),
            'draft_reports' => count($allReports) - count($publishedReports),
            'latest_report' => $this->reportRepository->getLatestReport()?->toArray(),
        ];
    }

    private function calculateRevenueTrend(array $reports): array
    {
        $reports = array_reverse($reports); // Oldest first
        
        return [
            'labels' => array_map(fn($r) => $r->getReportType()->getDisplayPeriod($r->getReportPeriod()), $reports),
            'data' => array_map(fn($r) => $r->getTotalRevenue(), $reports),
        ];
    }

    private function calculateProfitTrend(array $reports): array
    {
        $reports = array_reverse($reports); // Oldest first
        
        return [
            'labels' => array_map(fn($r) => $r->getReportType()->getDisplayPeriod($r->getReportPeriod()), $reports),
            'data' => array_map(fn($r) => $r->getNetProfit(), $reports),
        ];
    }

    private function calculateMemberGrowth(array $reports): array
    {
        $reports = array_reverse($reports); // Oldest first
        
        return [
            'labels' => array_map(fn($r) => $r->getReportType()->getDisplayPeriod($r->getReportPeriod()), $reports),
            'data' => array_map(fn($r) => $r->getTotalMembers() ?? 0, $reports),
        ];
    }

    private function calculateMarginAnalysis(array $reports): array
    {
        if (count($reports) < 2) {
            return [
                'current_margin' => $reports[0]->getProfitMargin(),
                'previous_margin' => 0,
                'margin_change' => 0,
                'trend' => 'stable',
            ];
        }

        $latest = $reports[0];
        $previous = $reports[1];
        
        $currentMargin = $latest->getProfitMargin();
        $previousMargin = $previous->getProfitMargin();
        $change = $currentMargin - $previousMargin;
        
        return [
            'current_margin' => $currentMargin,
            'previous_margin' => $previousMargin,
            'margin_change' => $change,
            'trend' => $change > 1 ? 'improving' : ($change < -1 ? 'declining' : 'stable'),
        ];
    }

    private function calculateHealthScoreTrend(array $reports): array
    {
        $reports = array_reverse($reports); // Oldest first
        
        return [
            'labels' => array_map(fn($r) => $r->getReportType()->getDisplayPeriod($r->getReportPeriod()), $reports),
            'data' => array_map(fn($r) => $r->getFinancialHealthScore(), $reports),
        ];
    }

    private function getDefaultSummary(): array
    {
        return [
            'latest_period' => 'No data',
            'latest_period_display' => 'No reports available',
            'total_revenue' => 0,
            'total_expenses' => 0,
            'net_profit' => 0,
            'profit_margin' => 0,
            'growth_rate' => 0,
            'growth_status' => 'unknown',
            'health_score' => 0,
            'health_score_label' => 'No data',
            'health_score_color' => 'gray',
            'total_members' => 0,
            'active_members' => 0,
            'mrr' => 0,
            'cash_flow' => 0,
            'revenue_breakdown' => [],
            'report_date' => null,
            'report_date_formatted' => 'No reports',
        ];
    }

    private function getDefaultMetrics(): array
    {
        return [
            'revenue_trend' => ['labels' => [], 'data' => []],
            'profit_trend' => ['labels' => [], 'data' => []],
            'member_growth' => ['labels' => [], 'data' => []],
            'margin_analysis' => [
                'current_margin' => 0,
                'previous_margin' => 0,
                'margin_change' => 0,
                'trend' => 'stable',
            ],
            'health_score_trend' => ['labels' => [], 'data' => []],
        ];
    }
}