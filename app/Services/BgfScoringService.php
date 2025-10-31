<?php

namespace App\Services;

use App\Models\BgfApplication;
use App\Models\User;
use Carbon\Carbon;

class BgfScoringService
{
    /**
     * Calculate automated score for BGF application
     */
    public function calculateScore(BgfApplication $application): array
    {
        $user = $application->user;
        
        $scores = [
            'membership_score' => $this->calculateMembershipScore($user),
            'training_score' => $this->calculateTrainingScore($user),
            'viability_score' => $this->calculateViabilityScore($application),
            'credibility_score' => $this->calculateCredibilityScore($application),
            'contribution_score' => $this->calculateContributionScore($application),
            'risk_control_score' => $this->calculateRiskControlScore($application),
            'track_record_score' => $this->calculateTrackRecordScore($user),
        ];

        $totalScore = array_sum($scores);

        return [
            'scores' => $scores,
            'total_score' => $totalScore,
            'passing' => $totalScore >= 70,
            'risk_level' => $this->determineRiskLevel($totalScore),
        ];
    }

    /**
     * Membership Tenure & Activity (15%)
     */
    private function calculateMembershipScore(User $user): int
    {
        $score = 0;
        $maxScore = 15;

        // Account age (5 points)
        $accountAge = Carbon::parse($user->created_at)->diffInMonths(now());
        if ($accountAge >= 6) $score += 5;
        elseif ($accountAge >= 3) $score += 3;
        elseif ($accountAge >= 2) $score += 2;

        // Life Points (5 points)
        $lifePoints = $user->life_points ?? 0;
        if ($lifePoints >= 500) $score += 5;
        elseif ($lifePoints >= 300) $score += 3;
        elseif ($lifePoints >= 100) $score += 2;

        // Active subscription (5 points)
        if ($user->hasActiveSubscription()) {
            $score += 5;
        }

        return min($score, $maxScore);
    }

    /**
     * Training Completion (10%)
     */
    private function calculateTrainingScore(User $user): int
    {
        $score = 0;
        $maxScore = 10;

        // Check if user has completed required training modules
        // This would integrate with your training/library system
        $completedModules = $this->getCompletedTrainingModules($user);

        if ($completedModules >= 3) $score = 10;
        elseif ($completedModules >= 2) $score = 7;
        elseif ($completedModules >= 1) $score = 4;

        return min($score, $maxScore);
    }

    /**
     * Business Viability (25%)
     */
    private function calculateViabilityScore(BgfApplication $application): int
    {
        $score = 0;
        $maxScore = 25;

        // Profit margin (10 points)
        $profitMargin = $application->getExpectedProfitMargin();
        if ($profitMargin >= 30) $score += 10;
        elseif ($profitMargin >= 20) $score += 7;
        elseif ($profitMargin >= 10) $score += 4;

        // Completion period (5 points)
        if ($application->completion_period_days <= 60) $score += 5;
        elseif ($application->completion_period_days <= 90) $score += 3;

        // Has order proof (5 points)
        if (!empty($application->order_proof)) $score += 5;

        // Feasibility summary quality (5 points)
        if (strlen($application->feasibility_summary) >= 200) $score += 5;
        elseif (strlen($application->feasibility_summary) >= 100) $score += 3;

        return min($score, $maxScore);
    }

    /**
     * Credibility & References (15%)
     */
    private function calculateCredibilityScore(BgfApplication $application): int
    {
        $score = 0;
        $maxScore = 15;

        // Has TPIN (5 points)
        if (!empty($application->tpin)) $score += 5;

        // Has business account (5 points)
        if (!empty($application->business_account)) $score += 5;

        // Client verification (5 points)
        if (!empty($application->client_name) && !empty($application->client_contact)) {
            $score += 5;
        }

        return min($score, $maxScore);
    }

    /**
     * Member Contribution (15%)
     */
    private function calculateContributionScore(BgfApplication $application): int
    {
        $score = 0;
        $maxScore = 15;

        $contributionPercentage = $application->getMemberContributionPercentage();

        if ($contributionPercentage >= 30) $score = 15;
        elseif ($contributionPercentage >= 25) $score = 12;
        elseif ($contributionPercentage >= 20) $score = 10;
        elseif ($contributionPercentage >= 15) $score = 7;
        elseif ($contributionPercentage >= 10) $score = 4;

        return min($score, $maxScore);
    }

    /**
     * Risk Control Measures (10%)
     */
    private function calculateRiskControlScore(BgfApplication $application): int
    {
        $score = 0;
        $maxScore = 10;

        // Has supporting documents (5 points)
        if (!empty($application->documents) && count($application->documents) > 0) {
            $score += 5;
        }

        // Business experience (5 points)
        if ($application->has_business_experience) {
            $score += 5;
        }

        return min($score, $maxScore);
    }

    /**
     * Previous Project Success (10%)
     */
    private function calculateTrackRecordScore(User $user): int
    {
        $score = 0;
        $maxScore = 10;

        // Check previous BGF projects
        $completedProjects = $user->bgfProjects()
            ->where('status', 'completed')
            ->count();

        if ($completedProjects >= 3) $score = 10;
        elseif ($completedProjects >= 2) $score = 7;
        elseif ($completedProjects >= 1) $score = 5;

        // Check for defaults
        $defaultedProjects = $user->bgfProjects()
            ->where('status', 'defaulted')
            ->count();

        if ($defaultedProjects > 0) {
            $score = max(0, $score - ($defaultedProjects * 3));
        }

        return min($score, $maxScore);
    }

    /**
     * Determine risk level based on total score
     */
    private function determineRiskLevel(int $totalScore): string
    {
        if ($totalScore >= 80) return 'low';
        if ($totalScore >= 70) return 'medium';
        return 'high';
    }

    /**
     * Get completed training modules count
     */
    private function getCompletedTrainingModules(User $user): int
    {
        // This would integrate with your actual training/library system
        // For now, return a placeholder
        return 0;
    }

    /**
     * Determine profit sharing percentage based on score and risk
     */
    public function determineProfitSharing(int $totalScore, float $amountRequested): array
    {
        // High score, low risk: 70% member, 30% MyGrowNet
        if ($totalScore >= 80 && $amountRequested < 20000) {
            return ['member' => 70, 'mygrownet' => 30];
        }

        // Medium score or medium amount: 65% member, 35% MyGrowNet
        if ($totalScore >= 70 && $amountRequested < 50000) {
            return ['member' => 65, 'mygrownet' => 35];
        }

        // High risk or new member: 60% member, 40% MyGrowNet
        return ['member' => 60, 'mygrownet' => 40];
    }
}
