<?php

declare(strict_types=1);

namespace App\Domain\Employee\Services;

use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Exceptions\CommissionCalculationException;
use App\Domain\Employee\Exceptions\InvalidCommissionRateException;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Investment\Services\InvestmentTierService;
use App\Domain\Reward\Services\ReferralMatrixService;
use App\Models\Investment;
use App\Models\User;
use DateTimeImmutable;
use DateInterval;

class CommissionCalculationService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private InvestmentTierService $investmentTierService,
        private ReferralMatrixService $referralMatrixService
    ) {}

    public function calculateFieldAgentCommission(Employee $fieldAgent, Investment $investment): CommissionCalculationResult
    {
        $this->validateFieldAgentEligibility($fieldAgent);
        $this->validateInvestment($investment);

        // Get the field agent's commission rate from their position
        $baseCommissionRate = $fieldAgent->getPosition()->getCommissionRate();
        if ($baseCommissionRate <= 0) {
            throw InvalidCommissionRateException::zeroCommissionRate($fieldAgent->getId()->toString());
        }

        // Calculate base commission
        $baseCommission = ($investment->amount * $baseCommissionRate) / 100;

        // Apply tier-based multipliers
        $tierMultiplier = $this->getTierMultiplier($investment);
        $adjustedCommission = $baseCommission * $tierMultiplier;

        // Apply performance-based adjustments
        $performanceMultiplier = $this->getPerformanceMultiplier($fieldAgent);
        $finalCommission = $adjustedCommission * $performanceMultiplier;

        // Apply any caps or limits
        $cappedCommission = $this->applyCommissionCaps($finalCommission, $investment, $fieldAgent);

        return new CommissionCalculationResult(
            $fieldAgent,
            $investment,
            $cappedCommission,
            $baseCommissionRate,
            $tierMultiplier,
            $performanceMultiplier,
            new DateTimeImmutable(),
            [
                'base_commission' => $baseCommission,
                'tier_multiplier' => $tierMultiplier,
                'performance_multiplier' => $performanceMultiplier,
                'final_commission' => $cappedCommission,
                'calculation_method' => 'field_agent_investment_facilitation'
            ]
        );
    }

    /**
     * Calculate commission for investment facilitation (simplified version for integration testing)
     */
    public function calculateInvestmentFacilitationCommission($fieldAgent, Investment $investment): float
    {
        // Handle both Employee entity and EmployeeModel for integration testing
        if (is_object($fieldAgent) && method_exists($fieldAgent, 'position')) {
            $position = $fieldAgent->position;
            $commissionRate = $position ? $position->base_commission_rate : 0.0;
        } elseif ($fieldAgent instanceof Employee) {
            $commissionRate = $fieldAgent->getPosition()->getCommissionRate();
        } else {
            throw new \InvalidArgumentException('Invalid field agent type');
        }

        // Return 0 for inactive investments
        if ($investment->status !== 'active') {
            return 0.0;
        }

        // Calculate simple commission: investment amount * commission rate
        return ($investment->amount * $commissionRate) / 100;
    }

    public function calculateReferralCommission(Employee $fieldAgent, User $referredUser, Investment $investment): CommissionCalculationResult
    {
        $this->validateFieldAgentEligibility($fieldAgent);
        
        if (!$fieldAgent->hasUser()) {
            throw CommissionCalculationException::noUserAccountLinked($fieldAgent->getId()->toString());
        }

        // Use the existing referral matrix service to calculate referral commissions
        $matrixCommissions = $this->referralMatrixService->calculateMatrixCommissions($investment);
        
        // Find the commission for this field agent
        $agentCommission = collect($matrixCommissions)->first(function ($commission) use ($fieldAgent) {
            return $commission['referrer_id'] === $fieldAgent->getUser()->id;
        });

        if (!$agentCommission) {
            return new CommissionCalculationResult(
                $fieldAgent,
                $investment,
                0.0,
                0.0,
                1.0,
                1.0,
                new DateTimeImmutable(),
                [
                    'commission_type' => 'referral',
                    'reason' => 'No referral commission applicable for this agent'
                ]
            );
        }

        return new CommissionCalculationResult(
            $fieldAgent,
            $investment,
            $agentCommission['amount'],
            $agentCommission['calculation_details']['tier_rate'],
            $agentCommission['calculation_details']['matrix_multiplier'],
            1.0,
            new DateTimeImmutable(),
            [
                'commission_type' => 'referral',
                'referral_level' => $agentCommission['level'],
                'matrix_position' => $agentCommission['matrix_position'],
                'tier_name' => $agentCommission['tier_name'],
                'calculation_details' => $agentCommission['calculation_details']
            ]
        );
    }

    public function calculatePerformanceBonus(Employee $fieldAgent, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): PerformanceBonusResult
    {
        $this->validateFieldAgentEligibility($fieldAgent);

        $performanceMetrics = $fieldAgent->getLastPerformanceMetrics();
        if (!$performanceMetrics) {
            return new PerformanceBonusResult(
                $fieldAgent,
                0.0,
                $periodStart,
                $periodEnd,
                'No performance metrics available'
            );
        }

        // Calculate bonus based on performance metrics
        $bonusAmount = 0.0;
        $bonusDetails = [];

        // Investment facilitation bonus
        $investmentBonus = $this->calculateInvestmentFacilitationBonus($performanceMetrics);
        $bonusAmount += $investmentBonus;
        $bonusDetails['investment_facilitation_bonus'] = $investmentBonus;

        // Client retention bonus
        $retentionBonus = $this->calculateClientRetentionBonus($performanceMetrics);
        $bonusAmount += $retentionBonus;
        $bonusDetails['client_retention_bonus'] = $retentionBonus;

        // Goal achievement bonus
        $goalBonus = $this->calculateGoalAchievementBonus($performanceMetrics);
        $bonusAmount += $goalBonus;
        $bonusDetails['goal_achievement_bonus'] = $goalBonus;

        // New client acquisition bonus
        $acquisitionBonus = $this->calculateNewClientAcquisitionBonus($performanceMetrics);
        $bonusAmount += $acquisitionBonus;
        $bonusDetails['new_client_acquisition_bonus'] = $acquisitionBonus;

        return new PerformanceBonusResult(
            $fieldAgent,
            $bonusAmount,
            $periodStart,
            $periodEnd,
            'Performance bonus calculated based on metrics',
            $bonusDetails
        );
    }

    public function calculateMonthlyCommissions(Employee $fieldAgent, DateTimeImmutable $month): MonthlyCommissionSummary
    {
        $monthStart = $month->modify('first day of this month')->setTime(0, 0, 0);
        $monthEnd = $month->modify('last day of this month')->setTime(23, 59, 59);

        // Get all investments facilitated by this field agent in the month
        $facilitatedInvestments = $this->getFacilitatedInvestments($fieldAgent, $monthStart, $monthEnd);
        
        $totalCommissions = 0.0;
        $commissionBreakdown = [];
        $investmentCount = 0;

        foreach ($facilitatedInvestments as $investment) {
            $commission = $this->calculateFieldAgentCommission($fieldAgent, $investment);
            $totalCommissions += $commission->getCommissionAmount();
            $investmentCount++;
            
            $commissionBreakdown[] = [
                'investment_id' => $investment->id,
                'investment_amount' => $investment->amount,
                'commission_amount' => $commission->getCommissionAmount(),
                'commission_rate' => $commission->getBaseCommissionRate(),
                'tier_multiplier' => $commission->getTierMultiplier(),
                'performance_multiplier' => $commission->getPerformanceMultiplier()
            ];
        }

        // Add referral commissions
        $referralCommissions = $this->getReferralCommissions($fieldAgent, $monthStart, $monthEnd);
        $totalCommissions += $referralCommissions;

        // Add performance bonus
        $performanceBonus = $this->calculatePerformanceBonus($fieldAgent, $monthStart, $monthEnd);
        $totalCommissions += $performanceBonus->getBonusAmount();

        return new MonthlyCommissionSummary(
            $fieldAgent,
            $month,
            $totalCommissions,
            $investmentCount,
            $referralCommissions,
            $performanceBonus->getBonusAmount(),
            $commissionBreakdown
        );
    }

    public function calculateQuarterlyCommissions(Employee $fieldAgent, int $year, int $quarter): QuarterlyCommissionSummary
    {
        $quarterStart = $this->getQuarterStart($year, $quarter);
        $quarterEnd = $this->getQuarterEnd($year, $quarter);

        $monthlyCommissions = [];
        $totalCommissions = 0.0;
        $totalInvestments = 0;
        $totalReferralCommissions = 0.0;
        $totalPerformanceBonuses = 0.0;

        // Calculate for each month in the quarter
        $currentMonth = $quarterStart;
        while ($currentMonth <= $quarterEnd) {
            $monthlyData = $this->calculateMonthlyCommissions($fieldAgent, $currentMonth);
            $monthlyCommissions[] = $monthlyData;
            
            $totalCommissions += $monthlyData->getTotalCommissions();
            $totalInvestments += $monthlyData->getInvestmentCount();
            $totalReferralCommissions += $monthlyData->getReferralCommissions();
            $totalPerformanceBonuses += $monthlyData->getPerformanceBonus();
            
            $currentMonth = $currentMonth->add(new DateInterval('P1M'));
        }

        // Calculate quarterly performance bonus
        $quarterlyBonus = $this->calculateQuarterlyPerformanceBonus($fieldAgent, $quarterStart, $quarterEnd);

        return new QuarterlyCommissionSummary(
            $fieldAgent,
            $year,
            $quarter,
            $totalCommissions + $quarterlyBonus,
            $totalInvestments,
            $totalReferralCommissions,
            $totalPerformanceBonuses + $quarterlyBonus,
            $monthlyCommissions
        );
    }

    public function calculateAnnualCommissions(Employee $fieldAgent, int $year): AnnualCommissionSummary
    {
        $quarterlyCommissions = [];
        $totalCommissions = 0.0;
        $totalInvestments = 0;
        $totalReferralCommissions = 0.0;
        $totalPerformanceBonuses = 0.0;

        // Calculate for each quarter
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $quarterlyData = $this->calculateQuarterlyCommissions($fieldAgent, $year, $quarter);
            $quarterlyCommissions[] = $quarterlyData;
            
            $totalCommissions += $quarterlyData->getTotalCommissions();
            $totalInvestments += $quarterlyData->getInvestmentCount();
            $totalReferralCommissions += $quarterlyData->getReferralCommissions();
            $totalPerformanceBonuses += $quarterlyData->getPerformanceBonuses();
        }

        // Calculate annual performance bonus
        $yearStart = new DateTimeImmutable("{$year}-01-01");
        $yearEnd = new DateTimeImmutable("{$year}-12-31");
        $annualBonus = $this->calculateAnnualPerformanceBonus($fieldAgent, $yearStart, $yearEnd);

        return new AnnualCommissionSummary(
            $fieldAgent,
            $year,
            $totalCommissions + $annualBonus,
            $totalInvestments,
            $totalReferralCommissions,
            $totalPerformanceBonuses + $annualBonus,
            $quarterlyCommissions
        );
    }

    private function validateFieldAgentEligibility(Employee $fieldAgent): void
    {
        if (!$fieldAgent->isActive()) {
            throw CommissionCalculationException::inactiveEmployee($fieldAgent->getId()->toString());
        }

        if (!$fieldAgent->isEligibleForCommission()) {
            throw CommissionCalculationException::notEligibleForCommission($fieldAgent->getId()->toString());
        }
    }

    private function validateInvestment(Investment $investment): void
    {
        if ($investment->status !== 'active') {
            throw CommissionCalculationException::invalidInvestmentStatus($investment->id ?? 0, $investment->status);
        }

        if ((float)$investment->amount <= 0) {
            throw CommissionCalculationException::invalidInvestmentAmount($investment->id ?? 0, (float)$investment->amount);
        }
    }

    private function getTierMultiplier(Investment $investment): float
    {
        $tier = $this->investmentTierService->calculateTierForAmount((float)$investment->amount);
        
        if (!$tier) {
            return 1.0;
        }

        // Higher tiers get higher commission multipliers
        return match ($tier->name) {
            'Basic' => 1.0,
            'Starter' => 1.1,
            'Builder' => 1.2,
            'Leader' => 1.3,
            'Elite' => 1.5,
            default => 1.0
        };
    }

    private function getPerformanceMultiplier(Employee $fieldAgent): float
    {
        $performanceMetrics = $fieldAgent->getLastPerformanceMetrics();
        
        if (!$performanceMetrics) {
            return 1.0;
        }

        $overallScore = $performanceMetrics->calculateOverallScore();
        
        // Performance-based multipliers
        return match (true) {
            $overallScore >= 9.0 => 1.3, // Exceptional performance
            $overallScore >= 8.0 => 1.2, // Excellent performance
            $overallScore >= 7.0 => 1.1, // Good performance
            $overallScore >= 6.0 => 1.0, // Average performance
            default => 0.9 // Below average performance
        };
    }

    private function applyCommissionCaps(float $commission, Investment $investment, Employee $fieldAgent): float
    {
        // Apply maximum commission cap (e.g., 20% of investment amount)
        $maxCommission = $investment->amount * 0.20;
        
        // Apply minimum commission threshold
        $minCommission = 100.0; // Minimum K100 commission
        
        return max($minCommission, min($commission, $maxCommission));
    }

    private function calculateInvestmentFacilitationBonus(object $performanceMetrics): float
    {
        $investmentsFacilitated = $performanceMetrics->getInvestmentsFacilitated();
        
        // Bonus tiers for investment facilitation
        return match (true) {
            $investmentsFacilitated >= 20 => 5000.0, // K5,000 for 20+ investments
            $investmentsFacilitated >= 15 => 3000.0, // K3,000 for 15+ investments
            $investmentsFacilitated >= 10 => 2000.0, // K2,000 for 10+ investments
            $investmentsFacilitated >= 5 => 1000.0,  // K1,000 for 5+ investments
            default => 0.0
        };
    }

    private function calculateClientRetentionBonus(object $performanceMetrics): float
    {
        $retentionRate = $performanceMetrics->getClientRetentionRate();
        
        // Bonus for high client retention
        return match (true) {
            $retentionRate >= 95 => 2000.0, // K2,000 for 95%+ retention
            $retentionRate >= 90 => 1500.0, // K1,500 for 90%+ retention
            $retentionRate >= 85 => 1000.0, // K1,000 for 85%+ retention
            $retentionRate >= 80 => 500.0,  // K500 for 80%+ retention
            default => 0.0
        };
    }

    private function calculateGoalAchievementBonus(object $performanceMetrics): float
    {
        $goalAchievementRate = $performanceMetrics->getGoalAchievementRate();
        
        // Bonus for goal achievement
        return match (true) {
            $goalAchievementRate >= 100 => 3000.0, // K3,000 for 100%+ achievement
            $goalAchievementRate >= 90 => 2000.0,  // K2,000 for 90%+ achievement
            $goalAchievementRate >= 80 => 1000.0,  // K1,000 for 80%+ achievement
            default => 0.0
        };
    }

    private function calculateNewClientAcquisitionBonus(object $performanceMetrics): float
    {
        $newClients = $performanceMetrics->getNewClientAcquisitions();
        
        // Bonus per new client acquired
        return $newClients * 200.0; // K200 per new client
    }

    private function getFacilitatedInvestments(Employee $fieldAgent, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        // In a real implementation, this would query the database for investments
        // facilitated by this field agent within the date range
        // For now, we'll return an empty array as a placeholder
        return [];
    }

    private function getReferralCommissions(Employee $fieldAgent, DateTimeImmutable $start, DateTimeImmutable $end): float
    {
        // In a real implementation, this would calculate referral commissions
        // for the field agent within the date range
        return 0.0;
    }

    private function calculateQuarterlyPerformanceBonus(Employee $fieldAgent, DateTimeImmutable $start, DateTimeImmutable $end): float
    {
        // Quarterly performance bonus calculation
        $performanceMetrics = $fieldAgent->getLastPerformanceMetrics();
        
        if (!$performanceMetrics) {
            return 0.0;
        }

        $overallScore = $performanceMetrics->calculateOverallScore();
        
        // Quarterly bonus based on overall performance
        return match (true) {
            $overallScore >= 9.0 => 10000.0, // K10,000 for exceptional quarterly performance
            $overallScore >= 8.0 => 7500.0,  // K7,500 for excellent quarterly performance
            $overallScore >= 7.0 => 5000.0,  // K5,000 for good quarterly performance
            default => 0.0
        };
    }

    private function calculateAnnualPerformanceBonus(Employee $fieldAgent, DateTimeImmutable $start, DateTimeImmutable $end): float
    {
        // Annual performance bonus calculation
        $performanceMetrics = $fieldAgent->getLastPerformanceMetrics();
        
        if (!$performanceMetrics) {
            return 0.0;
        }

        $overallScore = $performanceMetrics->calculateOverallScore();
        
        // Annual bonus based on overall performance
        return match (true) {
            $overallScore >= 9.0 => 50000.0, // K50,000 for exceptional annual performance
            $overallScore >= 8.0 => 35000.0, // K35,000 for excellent annual performance
            $overallScore >= 7.0 => 25000.0, // K25,000 for good annual performance
            default => 0.0
        };
    }

    private function getQuarterStart(int $year, int $quarter): DateTimeImmutable
    {
        $month = ($quarter - 1) * 3 + 1;
        return new DateTimeImmutable(sprintf("%d-%02d-01", $year, $month));
    }

    private function getQuarterEnd(int $year, int $quarter): DateTimeImmutable
    {
        $month = $quarter * 3;
        $lastDay = (new DateTimeImmutable(sprintf("%d-%02d-01", $year, $month)))->format('t');
        return new DateTimeImmutable(sprintf("%d-%02d-%s", $year, $month, $lastDay));
    }
}

// Data Transfer Objects for commission calculation results

class CommissionCalculationResult
{
    public function __construct(
        private Employee $employee,
        private Investment $investment,
        private float $commissionAmount,
        private float $baseCommissionRate,
        private float $tierMultiplier,
        private float $performanceMultiplier,
        private DateTimeImmutable $calculatedAt,
        private array $calculationDetails = []
    ) {}

    public function getEmployee(): Employee { return $this->employee; }
    public function getInvestment(): Investment { return $this->investment; }
    public function getCommissionAmount(): float { return $this->commissionAmount; }
    public function getBaseCommissionRate(): float { return $this->baseCommissionRate; }
    public function getTierMultiplier(): float { return $this->tierMultiplier; }
    public function getPerformanceMultiplier(): float { return $this->performanceMultiplier; }
    public function getCalculatedAt(): DateTimeImmutable { return $this->calculatedAt; }
    public function getCalculationDetails(): array { return $this->calculationDetails; }
}

class PerformanceBonusResult
{
    public function __construct(
        private Employee $employee,
        private float $bonusAmount,
        private DateTimeImmutable $periodStart,
        private DateTimeImmutable $periodEnd,
        private string $description,
        private array $bonusDetails = []
    ) {}

    public function getEmployee(): Employee { return $this->employee; }
    public function getBonusAmount(): float { return $this->bonusAmount; }
    public function getPeriodStart(): DateTimeImmutable { return $this->periodStart; }
    public function getPeriodEnd(): DateTimeImmutable { return $this->periodEnd; }
    public function getDescription(): string { return $this->description; }
    public function getBonusDetails(): array { return $this->bonusDetails; }
}

class MonthlyCommissionSummary
{
    public function __construct(
        private Employee $employee,
        private DateTimeImmutable $month,
        private float $totalCommissions,
        private int $investmentCount,
        private float $referralCommissions,
        private float $performanceBonus,
        private array $commissionBreakdown
    ) {}

    public function getEmployee(): Employee { return $this->employee; }
    public function getMonth(): DateTimeImmutable { return $this->month; }
    public function getTotalCommissions(): float { return $this->totalCommissions; }
    public function getInvestmentCount(): int { return $this->investmentCount; }
    public function getReferralCommissions(): float { return $this->referralCommissions; }
    public function getPerformanceBonus(): float { return $this->performanceBonus; }
    public function getCommissionBreakdown(): array { return $this->commissionBreakdown; }
}

class QuarterlyCommissionSummary
{
    public function __construct(
        private Employee $employee,
        private int $year,
        private int $quarter,
        private float $totalCommissions,
        private int $investmentCount,
        private float $referralCommissions,
        private float $performanceBonuses,
        private array $monthlyCommissions
    ) {}

    public function getEmployee(): Employee { return $this->employee; }
    public function getYear(): int { return $this->year; }
    public function getQuarter(): int { return $this->quarter; }
    public function getTotalCommissions(): float { return $this->totalCommissions; }
    public function getInvestmentCount(): int { return $this->investmentCount; }
    public function getReferralCommissions(): float { return $this->referralCommissions; }
    public function getPerformanceBonuses(): float { return $this->performanceBonuses; }
    public function getMonthlyCommissions(): array { return $this->monthlyCommissions; }
}

class AnnualCommissionSummary
{
    public function __construct(
        private Employee $employee,
        private int $year,
        private float $totalCommissions,
        private int $investmentCount,
        private float $referralCommissions,
        private float $performanceBonuses,
        private array $quarterlyCommissions
    ) {}

    public function getEmployee(): Employee { return $this->employee; }
    public function getYear(): int { return $this->year; }
    public function getTotalCommissions(): float { return $this->totalCommissions; }
    public function getInvestmentCount(): int { return $this->investmentCount; }
    public function getReferralCommissions(): float { return $this->referralCommissions; }
    public function getPerformanceBonuses(): float { return $this->performanceBonuses; }
    public function getQuarterlyCommissions(): array { return $this->quarterlyCommissions; }
}