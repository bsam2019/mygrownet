<?php

namespace App\Domain\Investor\Entities;

use App\Domain\Investor\ValueObjects\ReportType;
use DateTimeImmutable;

class FinancialReport
{
    public function __construct(
        private int $id,
        private string $title,
        private ReportType $reportType,
        private string $reportPeriod,
        private DateTimeImmutable $reportDate,
        private float $totalRevenue,
        private float $totalExpenses,
        private float $netProfit,
        private ?float $grossMargin,
        private ?float $operatingMargin,
        private ?float $netMargin,
        private ?float $cashFlow,
        private ?int $totalMembers,
        private ?int $activeMembers,
        private ?float $monthlyRecurringRevenue,
        private ?float $customerAcquisitionCost,
        private ?float $lifetimeValue,
        private ?float $churnRate,
        private ?float $growthRate,
        private ?string $notes,
        private ?DateTimeImmutable $publishedAt,
        private array $revenueBreakdown = []
    ) {}

    // Getters
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getReportType(): ReportType { return $this->reportType; }
    public function getReportPeriod(): string { return $this->reportPeriod; }
    public function getReportDate(): DateTimeImmutable { return $this->reportDate; }
    public function getTotalRevenue(): float { return $this->totalRevenue; }
    public function getTotalExpenses(): float { return $this->totalExpenses; }
    public function getNetProfit(): float { return $this->netProfit; }
    public function getGrossMargin(): ?float { return $this->grossMargin; }
    public function getOperatingMargin(): ?float { return $this->operatingMargin; }
    public function getNetMargin(): ?float { return $this->netMargin; }
    public function getCashFlow(): ?float { return $this->cashFlow; }
    public function getTotalMembers(): ?int { return $this->totalMembers; }
    public function getActiveMembers(): ?int { return $this->activeMembers; }
    public function getMonthlyRecurringRevenue(): ?float { return $this->monthlyRecurringRevenue; }
    public function getCustomerAcquisitionCost(): ?float { return $this->customerAcquisitionCost; }
    public function getLifetimeValue(): ?float { return $this->lifetimeValue; }
    public function getChurnRate(): ?float { return $this->churnRate; }
    public function getGrowthRate(): ?float { return $this->growthRate; }
    public function getNotes(): ?string { return $this->notes; }
    public function getPublishedAt(): ?DateTimeImmutable { return $this->publishedAt; }
    public function getRevenueBreakdown(): array { return $this->revenueBreakdown; }

    // Business methods
    public function isPublished(): bool
    {
        return $this->publishedAt !== null;
    }

    public function getProfitMargin(): float
    {
        return $this->totalRevenue > 0 ? ($this->netProfit / $this->totalRevenue) * 100 : 0;
    }

    public function getRevenueGrowthStatus(): string
    {
        if ($this->growthRate === null) return 'unknown';
        if ($this->growthRate > 15) return 'excellent';
        if ($this->growthRate > 10) return 'good';
        if ($this->growthRate > 5) return 'moderate';
        if ($this->growthRate > 0) return 'positive';
        return 'negative';
    }

    public function getFinancialHealthScore(): int
    {
        $score = 0;
        
        // Profitability (40 points)
        if ($this->netProfit > 0) {
            $profitMargin = $this->getProfitMargin();
            if ($profitMargin > 20) $score += 40;
            elseif ($profitMargin > 15) $score += 35;
            elseif ($profitMargin > 10) $score += 30;
            elseif ($profitMargin > 5) $score += 25;
            else $score += 20;
        } elseif ($this->netProfit > -($this->totalRevenue * 0.1)) {
            $score += 10; // Small loss acceptable
        }
        
        // Growth (30 points)
        if ($this->growthRate !== null) {
            if ($this->growthRate > 20) $score += 30;
            elseif ($this->growthRate > 15) $score += 25;
            elseif ($this->growthRate > 10) $score += 20;
            elseif ($this->growthRate > 5) $score += 15;
            elseif ($this->growthRate > 0) $score += 10;
        }
        
        // Cash Flow (30 points)
        if ($this->cashFlow !== null) {
            if ($this->cashFlow > $this->totalRevenue * 0.2) $score += 30;
            elseif ($this->cashFlow > $this->totalRevenue * 0.1) $score += 25;
            elseif ($this->cashFlow > 0) $score += 20;
            elseif ($this->cashFlow > -($this->totalRevenue * 0.05)) $score += 10;
        }
        
        return min(100, $score);
    }

    public function getHealthScoreLabel(): string
    {
        $score = $this->getFinancialHealthScore();
        
        if ($score >= 90) return 'Excellent';
        if ($score >= 80) return 'Very Good';
        if ($score >= 70) return 'Good';
        if ($score >= 60) return 'Fair';
        if ($score >= 50) return 'Poor';
        return 'Critical';
    }

    public function getHealthScoreColor(): string
    {
        $score = $this->getFinancialHealthScore();
        
        if ($score >= 80) return 'green';
        if ($score >= 60) return 'yellow';
        if ($score >= 40) return 'orange';
        return 'red';
    }

    public function setRevenueBreakdown(array $breakdown): void
    {
        $this->revenueBreakdown = $breakdown;
    }

    public function publish(): void
    {
        $this->publishedAt = new DateTimeImmutable();
    }

    public function unpublish(): void
    {
        $this->publishedAt = null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'report_type' => $this->reportType->value,
            'report_type_label' => $this->reportType->label(),
            'report_period' => $this->reportPeriod,
            'report_period_display' => $this->reportType->getDisplayPeriod($this->reportPeriod),
            'report_date' => $this->reportDate->format('Y-m-d'),
            'report_date_formatted' => $this->reportDate->format('F j, Y'),
            'total_revenue' => $this->totalRevenue,
            'total_expenses' => $this->totalExpenses,
            'net_profit' => $this->netProfit,
            'gross_margin' => $this->grossMargin,
            'operating_margin' => $this->operatingMargin,
            'net_margin' => $this->netMargin,
            'profit_margin' => $this->getProfitMargin(),
            'cash_flow' => $this->cashFlow,
            'total_members' => $this->totalMembers,
            'active_members' => $this->activeMembers,
            'monthly_recurring_revenue' => $this->monthlyRecurringRevenue,
            'customer_acquisition_cost' => $this->customerAcquisitionCost,
            'lifetime_value' => $this->lifetimeValue,
            'churn_rate' => $this->churnRate,
            'growth_rate' => $this->growthRate,
            'growth_status' => $this->getRevenueGrowthStatus(),
            'notes' => $this->notes,
            'published_at' => $this->publishedAt?->format('Y-m-d H:i:s'),
            'is_published' => $this->isPublished(),
            'financial_health_score' => $this->getFinancialHealthScore(),
            'health_score_label' => $this->getHealthScoreLabel(),
            'health_score_color' => $this->getHealthScoreColor(),
            'revenue_breakdown' => $this->revenueBreakdown,
        ];
    }
}