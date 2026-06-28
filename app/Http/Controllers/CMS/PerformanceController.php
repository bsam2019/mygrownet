<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\PerformanceManagementService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PerformanceController extends Controller
{
    public function __construct(
        private PerformanceManagementService $performanceService
    ) {}

    // Performance Cycles
    public function cyclesIndex(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $cycles = \App\Infrastructure\Persistence\Eloquent\CMS\PerformanceCycleModel::where('company_id', $companyId)
            ->with('reviews')
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('CMS/Performance/Cycles', [
            'cycles' => $cycles,
        ]);
    }

    public function cyclesStore(Request $request)
    {
        $validated = $request->validate([
            'cycle_name' => 'required|string|max:255',
            'cycle_type' => 'required|in:annual,semi_annual,quarterly,probation,project',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'review_deadline' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $this->performanceService->createCycle($request->user()->company_id, $validated);

        return back()->with('success', 'Performance cycle created successfully');
    }

    public function cyclesActivate(Request $request, int $cycleId)
    {
        $this->performanceService->activateCycle($cycleId);

        return back()->with('success', 'Performance cycle activated');
    }

    // Performance Reviews
    public function reviewsIndex(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $reviews = \App\Infrastructure\Persistence\Eloquent\CMS\PerformanceReviewModel::whereHas('cycle', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['worker', 'reviewer', 'cycle'])
            ->orderBy('due_date', 'desc')
            ->get();

        return Inertia::render('CMS/Performance/Reviews', [
            'reviews' => $reviews,
        ]);
    }

    public function reviewsCreate(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $cycles = \App\Infrastructure\Persistence\Eloquent\CMS\PerformanceCycleModel::where('company_id', $companyId)
            ->active()
            ->get();
        
        $workers = \App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        return Inertia::render('CMS/Performance/CreateReview', [
            'cycles' => $cycles,
            'workers' => $workers,
        ]);
    }

    public function reviewsStore(Request $request)
    {
        $validated = $request->validate([
            'cycle_id' => 'required|exists:cms_performance_cycles,id',
            'worker_id' => 'required|exists:cms_workers,id',
            'review_type' => 'required|in:self,manager,peer,360',
            'due_date' => 'required|date',
        ]);

        $validated['reviewer_id'] = $request->user()->id;

        $this->performanceService->createReview($validated['cycle_id'], $validated);

        return redirect()->route('cms.performance.reviews.index')
            ->with('success', 'Performance review created successfully');
    }

    public function reviewsShow(Request $request, int $reviewId)
    {
        $review = \App\Infrastructure\Persistence\Eloquent\CMS\PerformanceReviewModel::with([
            'worker',
            'reviewer',
            'cycle',
            'ratings.criteria'
        ])->findOrFail($reviewId);

        $criteria = \App\Infrastructure\Persistence\Eloquent\CMS\PerformanceCriteriaModel::where('company_id', $request->user()->company_id)
            ->active()
            ->get();

        return Inertia::render('CMS/Performance/ReviewForm', [
            'review' => $review,
            'criteria' => $criteria,
        ]);
    }

    public function reviewsSubmit(Request $request, int $reviewId)
    {
        $validated = $request->validate([
            'strengths' => 'nullable|string',
            'areas_for_improvement' => 'nullable|string',
            'achievements' => 'nullable|string',
            'goals_met' => 'nullable|string',
            'reviewer_comments' => 'nullable|string',
            'employee_comments' => 'nullable|string',
            'ratings' => 'required|array',
            'ratings.*.criteria_id' => 'required|exists:cms_performance_criteria,id',
            'ratings.*.rating' => 'required|numeric|min:1|max:5',
            'ratings.*.comments' => 'nullable|string',
        ]);

        $this->performanceService->submitReview($reviewId, $validated);

        return redirect()->route('cms.performance.reviews.index')
            ->with('success', 'Performance review submitted successfully');
    }

    // Goals
    public function goalsIndex(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $goals = \App\Infrastructure\Persistence\Eloquent\CMS\GoalModel::where('company_id', $companyId)
            ->with(['worker', 'setBy'])
            ->orderBy('target_date', 'desc')
            ->get();

        return Inertia::render('CMS/Performance/Goals', [
            'goals' => $goals,
        ]);
    }

    public function goalsStore(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'goal_title' => 'required|string|max:255',
            'description' => 'required|string',
            'goal_type' => 'required|in:individual,team,department,company',
            'category' => 'required|in:performance,development,project,behavioral,other',
            'start_date' => 'required|date',
            'target_date' => 'required|date|after:start_date',
            'priority' => 'required|in:low,medium,high,critical',
            'success_criteria' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['set_by_user_id'] = $request->user()->id;

        $this->performanceService->createGoal($request->user()->company_id, $validated);

        return back()->with('success', 'Goal created successfully');
    }

    public function goalsUpdateProgress(Request $request, int $goalId)
    {
        $validated = $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'notes' => 'required|string',
        ]);

        $this->performanceService->updateGoalProgress(
            $goalId,
            $request->user()->id,
            $validated['progress_percentage'],
            $validated['notes']
        );

        return back()->with('success', 'Goal progress updated');
    }

    // Performance Improvement Plans
    public function pipsIndex(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $pips = \App\Infrastructure\Persistence\Eloquent\CMS\ImprovementPlanModel::where('company_id', $companyId)
            ->with(['worker', 'createdBy', 'milestones'])
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('CMS/Performance/ImprovementPlans', [
            'pips' => $pips,
        ]);
    }

    public function pipsStore(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'plan_title' => 'required|string|max:255',
            'performance_issues' => 'required|string',
            'improvement_actions' => 'required|string',
            'support_provided' => 'nullable|string',
            'start_date' => 'required|date',
            'review_date' => 'required|date|after:start_date',
            'end_date' => 'required|date|after:review_date',
            'milestones' => 'nullable|array',
            'milestones.*.title' => 'required|string',
            'milestones.*.description' => 'required|string',
            'milestones.*.target_date' => 'required|date',
        ]);

        $validated['created_by_user_id'] = $request->user()->id;

        $this->performanceService->createImprovementPlan($request->user()->company_id, $validated);

        return back()->with('success', 'Performance improvement plan created successfully');
    }

    public function pipsMilestoneComplete(Request $request, int $milestoneId)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $this->performanceService->completePipMilestone($milestoneId, $validated['notes'] ?? null);

        return back()->with('success', 'Milestone marked as complete');
    }

    public function pipsClose(Request $request, int $pipId)
    {
        $validated = $request->validate([
            'status' => 'required|in:successful,unsuccessful,extended,cancelled',
            'outcome_notes' => 'nullable|string',
        ]);

        $this->performanceService->closePip($pipId, $validated['status'], $validated['outcome_notes'] ?? null);

        return back()->with('success', 'Performance improvement plan closed');
    }
}
