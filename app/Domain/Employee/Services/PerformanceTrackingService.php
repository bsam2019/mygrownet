<?php

declare(strict_types=1);

namespace App\Domain\Employee\Services;

use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Entities\EmployeePerformance;
use App\Domain\Employee\ValueObjects\PerformanceMetrics;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Exceptions\PerformanceReviewNotFoundException;
use App\Domain\Employee\Exceptions\InvalidPerformanceMetricsException;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use DateTimeImmutable;
use DateInterval;

class PerformanceTrackingService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function createPerformanceReview(PerformanceReviewData $data): EmployeePerformance
    {
        $this->validatePerformanceReviewData($data);

        $performanceMetrics = new PerformanceMetrics(
            $data->investmentsFacilitated,
            $data->clientRetentionRate,
            $data->commissionGenerated,
            $data->newClientAcquisitions,
            $data->goalAchievementRate,
            $data->evaluationPeriodStart,
            $data->evaluationPeriodEnd
        );

        $performance = EmployeePerformance::create(
            $data->employee,
            $data->evaluationPeriodStart,
            $data->evaluationPeriodEnd,
            $performanceMetrics,
            $data->reviewer,
            $data->reviewNotes,
            $data->goalsNextPeriod
        );

        // Update employee's performance metrics
        $data->employee->updatePerformance($performanceMetrics, new DateTimeImmutable());

        // Save the employee with updated performance
        $this->employeeRepository->save($data->employee);

        return $performance;
    }

    public function setPerformanceGoals(Employee $employee, array $goals, DateTimeImmutable $targetDate): void
    {
        $this->validateGoals($goals);

        // Note: In a full implementation, you'd save the goals to a separate goals repository
        // Goals are different from performance reviews and should be stored separately
        // For now, we just validate the goals structure
    }

    public function trackGoalProgress(Employee $employee, array $goalUpdates): array
    {
        $currentPerformance = $employee->getLastPerformanceMetrics();
        
        if (!$currentPerformance) {
            throw InvalidPerformanceMetricsException::noPerformanceMetricsFound($employee->getId()->toString());
        }

        $progressReport = [];
        
        foreach ($goalUpdates as $goalId => $currentValue) {
            $progressReport[$goalId] = $this->calculateGoalProgress($goalId, $currentValue, $currentPerformance);
        }

        return $progressReport;
    }

    public function calculatePerformanceTrends(Employee $employee, int $periodsBack = 4): array
    {
        // In a full implementation, you'd fetch historical performance data
        // For now, we'll simulate trend calculation
        
        $currentMetrics = $employee->getLastPerformanceMetrics();
        if (!$currentMetrics) {
            return [];
        }

        return [
            'investment_trend' => $this->calculateTrend('investments_facilitated', $employee, $periodsBack),
            'retention_trend' => $this->calculateTrend('client_retention_rate', $employee, $periodsBack),
            'commission_trend' => $this->calculateTrend('commission_generated', $employee, $periodsBack),
            'acquisition_trend' => $this->calculateTrend('new_client_acquisitions', $employee, $periodsBack),
            'goal_achievement_trend' => $this->calculateTrend('goal_achievement_rate', $employee, $periodsBack),
            'overall_trend' => $this->calculateOverallTrend($employee, $periodsBack)
        ];
    }

    public function generatePerformanceRecommendations(Employee $employee): array
    {
        $metrics = $employee->getLastPerformanceMetrics();
        if (!$metrics) {
            return ['No performance data available for recommendations'];
        }

        $recommendations = [];

        // Investment facilitation recommendations
        if ($metrics->getInvestmentsFacilitated() < 5) {
            $recommendations[] = [
                'category' => 'Investment Facilitation',
                'priority' => 'high',
                'recommendation' => 'Focus on increasing client engagement and investment opportunities. Consider additional training on investment products.',
                'target_improvement' => '20% increase in monthly investments facilitated'
            ];
        }

        // Client retention recommendations
        if ($metrics->getClientRetentionRate() < 80) {
            $recommendations[] = [
                'category' => 'Client Retention',
                'priority' => 'high',
                'recommendation' => 'Implement regular client check-ins and improve customer service skills. Consider client satisfaction surveys.',
                'target_improvement' => 'Achieve 85%+ client retention rate'
            ];
        }

        // Commission generation recommendations
        if ($metrics->getCommissionGenerated() < 10000) {
            $recommendations[] = [
                'category' => 'Commission Generation',
                'priority' => 'medium',
                'recommendation' => 'Focus on higher-value investment opportunities and improve closing techniques.',
                'target_improvement' => 'Increase monthly commission by 25%'
            ];
        }

        // New client acquisition recommendations
        if ($metrics->getNewClientAcquisitions() < 3) {
            $recommendations[] = [
                'category' => 'Client Acquisition',
                'priority' => 'medium',
                'recommendation' => 'Enhance networking activities and referral programs. Consider digital marketing training.',
                'target_improvement' => 'Acquire 5+ new clients per month'
            ];
        }

        // Goal achievement recommendations
        if ($metrics->getGoalAchievementRate() < 70) {
            $recommendations[] = [
                'category' => 'Goal Achievement',
                'priority' => 'high',
                'recommendation' => 'Review goal-setting process and create more specific, measurable objectives. Consider time management training.',
                'target_improvement' => 'Achieve 80%+ goal completion rate'
            ];
        }

        // Overall performance recommendations
        $overallScore = $metrics->calculateOverallScore();
        if ($overallScore < 7.0) {
            $recommendations[] = [
                'category' => 'Overall Performance',
                'priority' => 'high',
                'recommendation' => 'Consider comprehensive performance improvement plan with regular coaching sessions.',
                'target_improvement' => 'Achieve overall performance score of 8.0+'
            ];
        }

        return $recommendations;
    }

    public function compareEmployeePerformance(Employee $employee1, Employee $employee2): array
    {
        $metrics1 = $employee1->getLastPerformanceMetrics();
        $metrics2 = $employee2->getLastPerformanceMetrics();

        if (!$metrics1 || !$metrics2) {
            throw InvalidPerformanceMetricsException::insufficientDataForComparison();
        }

        return [
            'employee1' => [
                'name' => $employee1->getFullName(),
                'overall_score' => $metrics1->calculateOverallScore(),
                'investments_facilitated' => $metrics1->getInvestmentsFacilitated(),
                'client_retention_rate' => $metrics1->getClientRetentionRate(),
                'commission_generated' => $metrics1->getCommissionGenerated(),
                'new_client_acquisitions' => $metrics1->getNewClientAcquisitions(),
                'goal_achievement_rate' => $metrics1->getGoalAchievementRate()
            ],
            'employee2' => [
                'name' => $employee2->getFullName(),
                'overall_score' => $metrics2->calculateOverallScore(),
                'investments_facilitated' => $metrics2->getInvestmentsFacilitated(),
                'client_retention_rate' => $metrics2->getClientRetentionRate(),
                'commission_generated' => $metrics2->getCommissionGenerated(),
                'new_client_acquisitions' => $metrics2->getNewClientAcquisitions(),
                'goal_achievement_rate' => $metrics2->getGoalAchievementRate()
            ],
            'comparison' => $metrics1->compareWith($metrics2)
        ];
    }

    public function identifyTopPerformers(array $employees, int $limit = 5): array
    {
        $performanceData = [];

        foreach ($employees as $employee) {
            $metrics = $employee->getLastPerformanceMetrics();
            if ($metrics) {
                $performanceData[] = [
                    'employee' => $employee,
                    'score' => $metrics->calculateOverallScore(),
                    'metrics' => $metrics
                ];
            }
        }

        // Sort by performance score descending
        usort($performanceData, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($performanceData, 0, $limit);
    }

    public function identifyUnderperformers(array $employees, float $threshold = 6.0): array
    {
        $underperformers = [];

        foreach ($employees as $employee) {
            $metrics = $employee->getLastPerformanceMetrics();
            if ($metrics && $metrics->calculateOverallScore() < $threshold) {
                $underperformers[] = [
                    'employee' => $employee,
                    'score' => $metrics->calculateOverallScore(),
                    'metrics' => $metrics,
                    'recommendations' => $this->generatePerformanceRecommendations($employee)
                ];
            }
        }

        // Sort by performance score ascending (worst first)
        usort($underperformers, fn($a, $b) => $a['score'] <=> $b['score']);

        return $underperformers;
    }

    public function calculateDepartmentPerformanceAverage(array $employees): array
    {
        $totalScore = 0;
        $totalInvestments = 0;
        $totalRetention = 0;
        $totalCommission = 0;
        $totalAcquisitions = 0;
        $totalGoalAchievement = 0;
        $count = 0;

        foreach ($employees as $employee) {
            $metrics = $employee->getLastPerformanceMetrics();
            if ($metrics) {
                $totalScore += $metrics->calculateOverallScore();
                $totalInvestments += $metrics->getInvestmentsFacilitated();
                $totalRetention += $metrics->getClientRetentionRate();
                $totalCommission += $metrics->getCommissionGenerated();
                $totalAcquisitions += $metrics->getNewClientAcquisitions();
                $totalGoalAchievement += $metrics->getGoalAchievementRate();
                $count++;
            }
        }

        if ($count === 0) {
            return [];
        }

        return [
            'average_overall_score' => round($totalScore / $count, 2),
            'average_investments_facilitated' => round($totalInvestments / $count, 2),
            'average_client_retention_rate' => round($totalRetention / $count, 2),
            'average_commission_generated' => round($totalCommission / $count, 2),
            'average_new_client_acquisitions' => round($totalAcquisitions / $count, 2),
            'average_goal_achievement_rate' => round($totalGoalAchievement / $count, 2),
            'employee_count' => $count
        ];
    }

    private function validatePerformanceReviewData(PerformanceReviewData $data): void
    {
        if ($data->evaluationPeriodStart >= $data->evaluationPeriodEnd) {
            throw InvalidPerformanceMetricsException::invalidEvaluationPeriod($data->evaluationPeriodStart, $data->evaluationPeriodEnd);
        }

        if ($data->clientRetentionRate < 0 || $data->clientRetentionRate > 100) {
            throw InvalidPerformanceMetricsException::invalidRetentionRate($data->clientRetentionRate);
        }

        if ($data->goalAchievementRate < 0 || $data->goalAchievementRate > 100) {
            throw InvalidPerformanceMetricsException::invalidGoalAchievementRate($data->goalAchievementRate);
        }

        if ($data->investmentsFacilitated < 0) {
            throw InvalidPerformanceMetricsException::invalidInvestmentCount($data->investmentsFacilitated);
        }

        if ($data->commissionGenerated < 0) {
            throw InvalidPerformanceMetricsException::invalidCommissionAmount($data->commissionGenerated);
        }

        if ($data->newClientAcquisitions < 0) {
            throw InvalidPerformanceMetricsException::invalidClientAcquisitionCount($data->newClientAcquisitions);
        }
    }

    private function validateGoals(array $goals): void
    {
        foreach ($goals as $goal) {
            if (!isset($goal['description']) || empty(trim($goal['description']))) {
                throw InvalidPerformanceMetricsException::invalidGoalDescription();
            }

            if (!isset($goal['target']) || !is_numeric($goal['target'])) {
                throw InvalidPerformanceMetricsException::invalidGoalTarget();
            }

            if (!isset($goal['deadline']) || !($goal['deadline'] instanceof DateTimeImmutable)) {
                throw InvalidPerformanceMetricsException::invalidGoalDeadline();
            }
        }
    }

    private function calculateGoalProgress(string $goalId, float $currentValue, PerformanceMetrics $metrics): array
    {
        // This would typically fetch the goal target from storage
        // For now, we'll use some default targets based on goal type
        $targets = [
            'investments_facilitated' => 10,
            'client_retention_rate' => 85,
            'commission_generated' => 15000,
            'new_client_acquisitions' => 5,
            'goal_achievement_rate' => 80
        ];

        $target = $targets[$goalId] ?? 100;
        $progress = min(100, ($currentValue / $target) * 100);

        return [
            'goal_id' => $goalId,
            'current_value' => $currentValue,
            'target_value' => $target,
            'progress_percentage' => round($progress, 2),
            'status' => $this->determineGoalStatus($progress)
        ];
    }

    private function determineGoalStatus(float $progress): string
    {
        return match (true) {
            $progress >= 100 => 'achieved',
            $progress >= 80 => 'on_track',
            $progress >= 60 => 'needs_attention',
            default => 'at_risk'
        };
    }

    private function calculateTrend(string $metric, Employee $employee, int $periods): array
    {
        // In a full implementation, this would fetch historical data
        // For now, we'll simulate trend data
        $currentMetrics = $employee->getLastPerformanceMetrics();
        if (!$currentMetrics) {
            return [];
        }

        $currentValue = match ($metric) {
            'investments_facilitated' => $currentMetrics->getInvestmentsFacilitated(),
            'client_retention_rate' => $currentMetrics->getClientRetentionRate(),
            'commission_generated' => $currentMetrics->getCommissionGenerated(),
            'new_client_acquisitions' => $currentMetrics->getNewClientAcquisitions(),
            'goal_achievement_rate' => $currentMetrics->getGoalAchievementRate(),
            default => 0
        };

        // Simulate historical data with some variation
        $historicalData = [];
        for ($i = $periods; $i > 0; $i--) {
            $variation = (rand(-20, 20) / 100); // ±20% variation
            $historicalData[] = $currentValue * (1 + $variation);
        }
        $historicalData[] = $currentValue;

        return [
            'metric' => $metric,
            'current_value' => $currentValue,
            'historical_data' => $historicalData,
            'trend_direction' => $this->calculateTrendDirection($historicalData),
            'trend_percentage' => $this->calculateTrendPercentage($historicalData)
        ];
    }

    private function calculateOverallTrend(Employee $employee, int $periods): array
    {
        $currentMetrics = $employee->getLastPerformanceMetrics();
        if (!$currentMetrics) {
            return [];
        }

        $currentScore = $currentMetrics->calculateOverallScore();
        
        // Simulate historical scores
        $historicalScores = [];
        for ($i = $periods; $i > 0; $i--) {
            $variation = (rand(-15, 15) / 100); // ±15% variation
            $historicalScores[] = $currentScore * (1 + $variation);
        }
        $historicalScores[] = $currentScore;

        return [
            'metric' => 'overall_performance',
            'current_value' => $currentScore,
            'historical_data' => $historicalScores,
            'trend_direction' => $this->calculateTrendDirection($historicalScores),
            'trend_percentage' => $this->calculateTrendPercentage($historicalScores)
        ];
    }

    private function calculateTrendDirection(array $data): string
    {
        if (count($data) < 2) {
            return 'stable';
        }

        $first = $data[0];
        $last = end($data);
        $change = (($last - $first) / $first) * 100;

        return match (true) {
            $change > 5 => 'improving',
            $change < -5 => 'declining',
            default => 'stable'
        };
    }

    private function calculateTrendPercentage(array $data): float
    {
        if (count($data) < 2) {
            return 0.0;
        }

        $first = $data[0];
        $last = end($data);
        
        if ($first == 0) {
            return 0.0;
        }

        return round((($last - $first) / $first) * 100, 2);
    }
}

class PerformanceReviewData
{
    public function __construct(
        public readonly Employee $employee,
        public readonly DateTimeImmutable $evaluationPeriodStart,
        public readonly DateTimeImmutable $evaluationPeriodEnd,
        public readonly float $investmentsFacilitated,
        public readonly float $clientRetentionRate,
        public readonly float $commissionGenerated,
        public readonly int $newClientAcquisitions,
        public readonly float $goalAchievementRate,
        public readonly Employee $reviewer,
        public readonly ?string $reviewNotes = null,
        public readonly array $goalsNextPeriod = []
    ) {}

    public static function create(
        Employee $employee,
        DateTimeImmutable $evaluationPeriodStart,
        DateTimeImmutable $evaluationPeriodEnd,
        float $investmentsFacilitated,
        float $clientRetentionRate,
        float $commissionGenerated,
        int $newClientAcquisitions,
        float $goalAchievementRate,
        Employee $reviewer,
        ?string $reviewNotes = null,
        array $goalsNextPeriod = []
    ): self {
        return new self(
            $employee,
            $evaluationPeriodStart,
            $evaluationPeriodEnd,
            $investmentsFacilitated,
            $clientRetentionRate,
            $commissionGenerated,
            $newClientAcquisitions,
            $goalAchievementRate,
            $reviewer,
            $reviewNotes,
            $goalsNextPeriod
        );
    }
}