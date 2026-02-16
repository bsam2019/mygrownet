<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\PerformanceCycleModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PerformanceReviewModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PerformanceCriteriaModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PerformanceRatingModel;
use App\Infrastructure\Persistence\Eloquent\CMS\GoalModel;
use App\Infrastructure\Persistence\Eloquent\CMS\GoalProgressModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ImprovementPlanModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PipMilestoneModel;
use Carbon\Carbon;

class PerformanceManagementService
{
    // Performance Cycles
    public function createCycle(int $companyId, array $data): PerformanceCycleModel
    {
        return PerformanceCycleModel::create([
            'company_id' => $companyId,
            'cycle_name' => $data['cycle_name'],
            'cycle_type' => $data['cycle_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'review_deadline' => $data['review_deadline'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'description' => $data['description'] ?? null,
        ]);
    }

    public function activateCycle(int $cycleId): PerformanceCycleModel
    {
        $cycle = PerformanceCycleModel::findOrFail($cycleId);
        $cycle->update(['status' => 'active']);
        return $cycle;
    }

    // Performance Reviews
    public function createReview(int $cycleId, array $data): PerformanceReviewModel
    {
        return PerformanceReviewModel::create([
            'cycle_id' => $cycleId,
            'worker_id' => $data['worker_id'],
            'reviewer_id' => $data['reviewer_id'],
            'review_type' => $data['review_type'],
            'due_date' => $data['due_date'],
            'status' => 'pending',
        ]);
    }

    public function submitReview(int $reviewId, array $data): PerformanceReviewModel
    {
        $review = PerformanceReviewModel::findOrFail($reviewId);
        
        $review->update([
            'status' => 'submitted',
            'submitted_date' => now(),
            'strengths' => $data['strengths'] ?? null,
            'areas_for_improvement' => $data['areas_for_improvement'] ?? null,
            'achievements' => $data['achievements'] ?? null,
            'goals_met' => $data['goals_met'] ?? null,
            'reviewer_comments' => $data['reviewer_comments'] ?? null,
            'employee_comments' => $data['employee_comments'] ?? null,
        ]);

        // Calculate overall rating from criteria ratings
        if (isset($data['ratings']) && is_array($data['ratings'])) {
            $this->saveRatings($reviewId, $data['ratings']);
            $this->calculateOverallRating($reviewId);
        }

        return $review->fresh();
    }

    private function saveRatings(int $reviewId, array $ratings): void
    {
        foreach ($ratings as $rating) {
            PerformanceRatingModel::updateOrCreate(
                [
                    'review_id' => $reviewId,
                    'criteria_id' => $rating['criteria_id'],
                ],
                [
                    'rating' => $rating['rating'],
                    'comments' => $rating['comments'] ?? null,
                ]
            );
        }
    }

    private function calculateOverallRating(int $reviewId): void
    {
        $review = PerformanceReviewModel::with(['ratings.criteria'])->findOrFail($reviewId);
        
        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($review->ratings as $rating) {
            $weight = $rating->criteria->weight_percentage;
            $totalWeight += $weight;
            $weightedSum += ($rating->rating * $weight);
        }

        $overallRating = $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
        
        $review->update(['overall_rating' => $overallRating]);
    }

    // Performance Criteria
    public function createCriteria(int $companyId, array $data): PerformanceCriteriaModel
    {
        return PerformanceCriteriaModel::create([
            'company_id' => $companyId,
            'criteria_name' => $data['criteria_name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'weight_percentage' => $data['weight_percentage'] ?? 10,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    // Goals Management
    public function createGoal(int $companyId, array $data): GoalModel
    {
        return GoalModel::create([
            'company_id' => $companyId,
            'worker_id' => $data['worker_id'],
            'set_by_user_id' => $data['set_by_user_id'],
            'goal_title' => $data['goal_title'],
            'description' => $data['description'],
            'goal_type' => $data['goal_type'],
            'category' => $data['category'],
            'start_date' => $data['start_date'],
            'target_date' => $data['target_date'],
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'not_started',
            'progress_percentage' => 0,
            'success_criteria' => $data['success_criteria'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function updateGoalProgress(int $goalId, int $userId, int $progressPercentage, string $notes): GoalProgressModel
    {
        $goal = GoalModel::findOrFail($goalId);
        
        // Create progress update
        $progressUpdate = GoalProgressModel::create([
            'goal_id' => $goalId,
            'updated_by_user_id' => $userId,
            'progress_percentage' => $progressPercentage,
            'update_notes' => $notes,
            'update_date' => now(),
        ]);

        // Update goal status and progress
        $status = $goal->status;
        if ($progressPercentage > 0 && $status === 'not_started') {
            $status = 'in_progress';
        }
        if ($progressPercentage >= 100) {
            $status = 'completed';
        }

        $goal->update([
            'progress_percentage' => $progressPercentage,
            'status' => $status,
            'completed_date' => $progressPercentage >= 100 ? now() : null,
        ]);

        return $progressUpdate;
    }

    // Performance Improvement Plans
    public function createImprovementPlan(int $companyId, array $data): ImprovementPlanModel
    {
        $plan = ImprovementPlanModel::create([
            'company_id' => $companyId,
            'worker_id' => $data['worker_id'],
            'created_by_user_id' => $data['created_by_user_id'],
            'plan_title' => $data['plan_title'],
            'performance_issues' => $data['performance_issues'],
            'improvement_actions' => $data['improvement_actions'],
            'support_provided' => $data['support_provided'] ?? null,
            'start_date' => $data['start_date'],
            'review_date' => $data['review_date'],
            'end_date' => $data['end_date'],
            'status' => 'active',
        ]);

        // Create milestones if provided
        if (isset($data['milestones']) && is_array($data['milestones'])) {
            foreach ($data['milestones'] as $milestone) {
                PipMilestoneModel::create([
                    'improvement_plan_id' => $plan->id,
                    'milestone_title' => $milestone['title'],
                    'description' => $milestone['description'],
                    'target_date' => $milestone['target_date'],
                ]);
            }
        }

        return $plan->load('milestones');
    }

    public function completePipMilestone(int $milestoneId, ?string $notes = null): PipMilestoneModel
    {
        $milestone = PipMilestoneModel::findOrFail($milestoneId);
        
        $milestone->update([
            'is_completed' => true,
            'completed_date' => now(),
            'completion_notes' => $notes,
        ]);

        return $milestone;
    }

    public function closePip(int $planId, string $status, ?string $outcomeNotes = null): ImprovementPlanModel
    {
        $plan = ImprovementPlanModel::findOrFail($planId);
        
        $plan->update([
            'status' => $status,
            'outcome_notes' => $outcomeNotes,
        ]);

        return $plan;
    }

    // Analytics
    public function getWorkerPerformanceHistory(int $workerId)
    {
        return PerformanceReviewModel::where('worker_id', $workerId)
            ->with(['cycle', 'reviewer', 'ratings.criteria'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCompanyPerformanceStats(int $companyId)
    {
        $reviews = PerformanceReviewModel::whereHas('cycle', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->where('status', 'completed')->get();

        return [
            'total_reviews' => $reviews->count(),
            'average_rating' => $reviews->avg('overall_rating'),
            'pending_reviews' => PerformanceReviewModel::whereHas('cycle', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->where('status', 'pending')->count(),
            'overdue_reviews' => PerformanceReviewModel::whereHas('cycle', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->overdue()->count(),
        ];
    }
}
