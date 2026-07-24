<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Repositories\CompanyValuationRepositoryInterface;
use App\Domain\Investor\Repositories\RiskAssessmentRepositoryInterface;
use App\Domain\Investor\Repositories\ScenarioModelRepositoryInterface;
use App\Domain\Investor\Repositories\ExitProjectionRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Domain\Investor\Entities\CompanyValuation;

class AdvancedAnalyticsService
{
    public function __construct(
        private readonly CompanyValuationRepositoryInterface $valuationRepository,
        private readonly RiskAssessmentRepositoryInterface $riskRepository,
        private readonly ScenarioModelRepositoryInterface $scenarioRepository,
        private readonly ExitProjectionRepositoryInterface $exitRepository,
        private readonly InvestorAccountRepositoryInterface $accountRepository
    ) {}

    public function getValuationHistory(int $months = 24): array
    {
        return $this->valuationRepository->findHistory($months);
    }

    public function getCurrentValuation(): ?CompanyValuation
    {
        return $this->valuationRepository->findLatest();
    }

    public function getValuationChartData(int $months = 24): array
    {
        $valuations = $this->valuationRepository->findHistory($months);

        return [
            'labels' => array_map(fn($v) => $v->getValuationDate()->format('M Y'), $valuations),
            'values' => array_map(fn($v) => $v->getValuationAmount(), $valuations),
            'methods' => array_map(fn($v) => $v->getValuationMethod(), $valuations),
        ];
    }

    public function calculateInvestorShareValue(int $investorAccountId): array
    {
        $investor = $this->accountRepository->findById($investorAccountId);

        if (!$investor) {
            return [
                'current_value' => 0,
                'gain_loss' => 0,
                'gain_loss_percentage' => 0,
                'valuation_date' => null,
            ];
        }

        $currentValuation = $this->valuationRepository->findLatest();

        if (!$currentValuation) {
            return [
                'current_value' => $investor->getInvestmentAmount(),
                'gain_loss' => 0,
                'gain_loss_percentage' => 0,
                'valuation_date' => null,
            ];
        }

        $equityPercentage = $investor->getEquityPercentage();
        $currentValue = $currentValuation->getValuationAmount() * ($equityPercentage / 100);
        $investmentAmount = $investor->getInvestmentAmount();
        $gainLoss = $currentValue - $investmentAmount;
        $gainLossPercentage = $investmentAmount > 0
            ? (($currentValue - $investmentAmount) / $investmentAmount) * 100
            : 0;

        return [
            'current_value' => round($currentValue, 2),
            'gain_loss' => round($gainLoss, 2),
            'gain_loss_percentage' => round($gainLossPercentage, 2),
            'valuation_date' => $currentValuation->getValuationDate()->format('Y-m-d'),
        ];
    }

    public function getLatestRiskAssessment(): array
    {
        $assessment = $this->riskRepository->findLatest();

        if (!$assessment) {
            return [];
        }

        return [
            'id' => $assessment->getId(),
            'risk_level' => $assessment->getRiskLevel(),
            'risk_score' => $assessment->getRiskScore(),
            'assessment_date' => $assessment->getAssessmentDate()->format('Y-m-d'),
            'factors' => $assessment->getFactors(),
            'notes' => $assessment->getNotes(),
        ];
    }

    public function getRiskHistory(int $limit = 12): array
    {
        return $this->riskRepository->findHistory($limit);
    }

    public function getScenarioModels(): array
    {
        return $this->scenarioRepository->findActive();
    }

    public function calculateScenarioForInvestor(int $investorAccountId): array
    {
        $investor = $this->accountRepository->findById($investorAccountId);

        if (!$investor) {
            return [];
        }

        $scenarios = $this->scenarioRepository->findActive();
        $equityPercentage = $investor->getEquityPercentage();
        $investmentAmount = $investor->getInvestmentAmount();

        return array_map(function ($scenario) use ($equityPercentage, $investmentAmount) {
            return [
                'name' => $scenario->getName(),
                'type' => $scenario->getScenarioType(),
                'projections' => [
                    '1_year' => [
                        'value' => round(($scenario->getProjectedValuation1y() ?? 0) * ($equityPercentage / 100), 2),
                        'roi' => $scenario->getProjectedRoi1y(),
                    ],
                    '3_year' => [
                        'value' => round(($scenario->getProjectedValuation3y() ?? 0) * ($equityPercentage / 100), 2),
                        'roi' => $scenario->getProjectedRoi3y(),
                    ],
                    '5_year' => [
                        'value' => round(($scenario->getProjectedValuation5y() ?? 0) * ($equityPercentage / 100), 2),
                        'roi' => $scenario->getProjectedRoi5y(),
                    ],
                ],
                'assumptions' => $scenario->getAssumptions(),
            ];
        }, $scenarios);
    }

    public function getExitProjections(): array
    {
        return $this->exitRepository->findAll();
    }

    public function calculateExitValueForInvestor(int $investorAccountId): array
    {
        $investor = $this->accountRepository->findById($investorAccountId);

        if (!$investor) {
            return [];
        }

        $projections = $this->exitRepository->findAll();
        $equityPercentage = $investor->getEquityPercentage();

        return array_map(function ($projection) use ($equityPercentage) {
            $investorValue = ($projection->getProjectedValuation() ?? 0) * ($equityPercentage / 100);

            return [
                'exit_type' => $projection->getExitType(),
                'title' => $projection->getTitle(),
                'projected_date' => $projection->getProjectedDate()?->format('Y-m-d'),
                'investor_value' => round($investorValue, 2),
                'multiple' => $projection->getProjectedMultiple(),
                'probability' => $projection->getProbabilityPercentage(),
            ];
        }, $projections);
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
