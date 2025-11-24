<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\FinancialReport;
use App\Domain\Investor\Repositories\FinancialReportRepositoryInterface;
use App\Domain\Investor\ValueObjects\ReportType;
use App\Infrastructure\Persistence\Eloquent\Investor\FinancialReportModel;
use App\Infrastructure\Persistence\Eloquent\Investor\RevenueBreakdownModel;

class EloquentFinancialReportRepository implements FinancialReportRepositoryInterface
{
    public function findById(int $id): ?FinancialReport
    {
        $model = FinancialReportModel::with('revenueBreakdown')->find($id);
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByType(ReportType $type): array
    {
        $models = FinancialReportModel::with('revenueBreakdown')
            ->byType($type->value)
            ->published()
            ->latest()
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findLatestPublished(int $limit = 5): array
    {
        $models = FinancialReportModel::with('revenueBreakdown')
            ->published()
            ->latest()
            ->limit($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findAll(): array
    {
        $models = FinancialReportModel::with('revenueBreakdown')
            ->latest()
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findPublished(): array
    {
        $models = FinancialReportModel::with('revenueBreakdown')
            ->published()
            ->latest()
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function save(FinancialReport $report): FinancialReport
    {
        $data = [
            'title' => $report->getTitle(),
            'report_type' => $report->getReportType()->value,
            'report_period' => $report->getReportPeriod(),
            'report_date' => $report->getReportDate(),
            'total_revenue' => $report->getTotalRevenue(),
            'total_expenses' => $report->getTotalExpenses(),
            'net_profit' => $report->getNetProfit(),
            'gross_margin' => $report->getGrossMargin(),
            'operating_margin' => $report->getOperatingMargin(),
            'net_margin' => $report->getNetMargin(),
            'cash_flow' => $report->getCashFlow(),
            'total_members' => $report->getTotalMembers(),
            'active_members' => $report->getActiveMembers(),
            'monthly_recurring_revenue' => $report->getMonthlyRecurringRevenue(),
            'customer_acquisition_cost' => $report->getCustomerAcquisitionCost(),
            'lifetime_value' => $report->getLifetimeValue(),
            'churn_rate' => $report->getChurnRate(),
            'growth_rate' => $report->getGrowthRate(),
            'notes' => $report->getNotes(),
            'published_at' => $report->getPublishedAt(),
        ];

        if ($report->getId() === 0) {
            // Create new report
            $model = FinancialReportModel::create($data);
        } else {
            // Update existing report
            $model = FinancialReportModel::findOrFail($report->getId());
            $model->update($data);
        }

        // Save revenue breakdown if provided
        if (!empty($report->getRevenueBreakdown())) {
            $this->saveRevenueBreakdown($model->id, $report->getRevenueBreakdown());
        }

        return $this->toDomainEntity($model->load('revenueBreakdown'));
    }

    public function delete(int $id): void
    {
        FinancialReportModel::destroy($id);
    }

    public function getTotalReports(): int
    {
        return FinancialReportModel::count();
    }

    public function getLatestReport(): ?FinancialReport
    {
        $model = FinancialReportModel::with('revenueBreakdown')
            ->published()
            ->latest()
            ->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function getReportsByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $models = FinancialReportModel::with('revenueBreakdown')
            ->whereBetween('report_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->published()
            ->latest()
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function getRevenueBreakdown(int $reportId): array
    {
        return RevenueBreakdownModel::where('financial_report_id', $reportId)
            ->orderBy('percentage', 'desc')
            ->get()
            ->map(fn($model) => [
                'source' => $model->revenue_source,
                'amount' => $model->amount,
                'percentage' => $model->percentage,
                'growth_rate' => $model->growth_rate,
                'notes' => $model->notes,
            ])
            ->toArray();
    }

    public function saveRevenueBreakdown(int $reportId, array $breakdown): void
    {
        // Delete existing breakdown
        RevenueBreakdownModel::where('financial_report_id', $reportId)->delete();

        // Insert new breakdown
        foreach ($breakdown as $item) {
            RevenueBreakdownModel::create([
                'financial_report_id' => $reportId,
                'revenue_source' => $item['source'],
                'amount' => $item['amount'],
                'percentage' => $item['percentage'],
                'growth_rate' => $item['growth_rate'] ?? null,
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }

    private function toDomainEntity(FinancialReportModel $model): FinancialReport
    {
        $revenueBreakdown = $model->revenueBreakdown->map(fn($breakdown) => [
            'source' => $breakdown->revenue_source,
            'amount' => (float) $breakdown->amount,
            'percentage' => (float) $breakdown->percentage,
            'growth_rate' => $breakdown->growth_rate ? (float) $breakdown->growth_rate : null,
            'notes' => $breakdown->notes,
        ])->toArray();

        return new FinancialReport(
            id: $model->id,
            title: $model->title,
            reportType: ReportType::from($model->report_type),
            reportPeriod: $model->report_period,
            reportDate: $model->report_date->toDateTimeImmutable(),
            totalRevenue: (float) $model->total_revenue,
            totalExpenses: (float) $model->total_expenses,
            netProfit: (float) $model->net_profit,
            grossMargin: $model->gross_margin ? (float) $model->gross_margin : null,
            operatingMargin: $model->operating_margin ? (float) $model->operating_margin : null,
            netMargin: $model->net_margin ? (float) $model->net_margin : null,
            cashFlow: $model->cash_flow ? (float) $model->cash_flow : null,
            totalMembers: $model->total_members,
            activeMembers: $model->active_members,
            monthlyRecurringRevenue: $model->monthly_recurring_revenue ? (float) $model->monthly_recurring_revenue : null,
            customerAcquisitionCost: $model->customer_acquisition_cost ? (float) $model->customer_acquisition_cost : null,
            lifetimeValue: $model->lifetime_value ? (float) $model->lifetime_value : null,
            churnRate: $model->churn_rate ? (float) $model->churn_rate : null,
            growthRate: $model->growth_rate ? (float) $model->growth_rate : null,
            notes: $model->notes,
            publishedAt: $model->published_at?->toDateTimeImmutable(),
            revenueBreakdown: $revenueBreakdown
        );
    }
}