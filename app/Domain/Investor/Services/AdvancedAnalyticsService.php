<?php

namespace App\Domain\Investor\Services;

use App\Models\CompanyValuation;
use App\Models\RiskAssessment;
use App\Models\ScenarioModel;
use App\Models\ExitProjection;
use App\Models\InvestorAccount;
use Illuminate\Support\Collection;

class AdvancedAnalyticsService
{
    public function getValuationHistory(int $months = 24): Collection
    {
        return CompanyValuation::where('valuation_date', '>=', now()->subMonths($months))
            ->orderBy('valuation_date', 'asc')
            ->get();
    }

    public function getCurrentValuation(): ?CompanyValuation
    {
        return CompanyValuation::getLatest();
    }

    public function getValuationChartData(int $months = 24): array
    {
        $valuations = $this->getValuationHistory($months);
        
        return [
            'labels' => $valuations->pluck('valuation_date')->map(fn($d) => $d->format('M Y'))->toArray(),
            'values' => $valuations->pluck('valuation_amount')->toArray(),
            'methods' => $valuations->pluck('valuation_method')->toArray(),
        ];
    }

    public function calculateInvestorShareValue(int $investorAccountId): array
    {
        $investor = InvestorAccount::findOrFail($investorAccountId);
        $currentValuation = $this->getCurrentValuation();
        
        if (!$currentValuation) {
            return [
                'current_value' => $investor->investment_amount ?? 0,
                'gain_loss' => 0,
                'gain_loss_percentage' => 0,
                'valuation_date' => null,
            ];
        }

        $equityPercentage = $investor->equity_percentage ?? 0;
        $currentValue = $currentValuation->valuation_amount * ($equityPercentage / 100);
        $investmentAmount = $investor->investment_amount ?? 0;
        $gainLoss = $currentValue - $investmentAmount;
        $gainLossPercentage = $investmentAmount > 0 
            ? (($currentValue - $investmentAmount) / $investmentAmount) * 100 
            : 0;

        return [
            'current_value' => round($currentValue, 2),
            'gain_loss' => round($gainLoss, 2),
            'gain_loss_percentage' => round($gainLossPercentage, 2),
            'valuation_date' => $currentValuation->valuation_date,
        ];
    }

    public function getLatestRiskAssessment(): ?RiskAssessment
    {
        return RiskAssessment::getLatest();
    }

    public function getRiskHistory(int $limit = 12): Collection
    {
        return RiskAssessment::orderBy('assessment_date', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getScenarioModels(): Collection
    {
        return ScenarioModel::getActiveScenarios();
    }

    public function calculateScenarioForInvestor(int $investorAccountId): array
    {
        $investor = InvestorAccount::findOrFail($investorAccountId);
        $scenarios = $this->getScenarioModels();
        $equityPercentage = $investor->equity_percentage ?? 0;
        $investmentAmount = $investor->investment_amount ?? 0;

        return $scenarios->map(function ($scenario) use ($equityPercentage, $investmentAmount) {
            return [
                'name' => $scenario->name,
                'type' => $scenario->scenario_type,
                'projections' => [
                    '1_year' => [
                        'value' => round(($scenario->projected_valuation_1y ?? 0) * ($equityPercentage / 100), 2),
                        'roi' => $scenario->projected_roi_1y,
                    ],
                    '3_year' => [
                        'value' => round(($scenario->projected_valuation_3y ?? 0) * ($equityPercentage / 100), 2),
                        'roi' => $scenario->projected_roi_3y,
                    ],
                    '5_year' => [
                        'value' => round(($scenario->projected_valuation_5y ?? 0) * ($equityPercentage / 100), 2),
                        'roi' => $scenario->projected_roi_5y,
                    ],
                ],
                'assumptions' => $scenario->assumptions,
            ];
        })->toArray();
    }

    public function getExitProjections(): Collection
    {
        return ExitProjection::orderBy('probability_percentage', 'desc')->get();
    }

    public function calculateExitValueForInvestor(int $investorAccountId): array
    {
        $investor = InvestorAccount::findOrFail($investorAccountId);
        $projections = $this->getExitProjections();
        $equityPercentage = $investor->equity_percentage ?? 0;

        return $projections->map(function ($projection) use ($equityPercentage) {
            $investorValue = ($projection->projected_valuation ?? 0) * ($equityPercentage / 100);
            
            return [
                'exit_type' => $projection->exit_type,
                'title' => $projection->title,
                'projected_date' => $projection->projected_date,
                'investor_value' => round($investorValue, 2),
                'multiple' => $projection->projected_multiple,
                'probability' => $projection->probability_percentage,
            ];
        })->toArray();
    }

    public function getAnalyticsSummary(int $investorAccountId): array
    {
        return [
            'valuation' => $this->calculateInvestorShareValue($investorAccountId),
            'risk' => $this->getLatestRiskAssessment(),
            'scenarios' => $this->calculateScenarioForInvestor($investorAccountId),
            'exit_projections' => $this->calculateExitValueForInvestor($investorAccountId),
        ];
    }
}
