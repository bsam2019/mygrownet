<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BgfApplication;
use App\Models\BgfProject;
use App\Models\BgfDisbursement;
use App\Models\BgfRepayment;
use App\Models\BgfEvaluation;
use App\Models\BgfContract;
use App\Services\BgfScoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BgfAdminController extends Controller
{
    public function __construct(
        private BgfScoringService $scoringService
    ) {}

    /**
     * BGF Dashboard
     */
    public function dashboard(): Response
    {
        return Inertia::render('Admin/BGF/Dashboard', [
            'stats' => [
                'total_applications' => BgfApplication::count(),
                'pending_review' => BgfApplication::where('status', 'submitted')->count(),
                'approved' => BgfApplication::where('status', 'approved')->count(),
                'active_projects' => BgfProject::whereIn('status', ['active', 'in_progress'])->count(),
                'total_funded' => BgfProject::sum('approved_amount'),
                'total_disbursed' => BgfProject::sum('total_disbursed'),
                'total_repaid' => BgfProject::sum('total_repaid'),
                'pending_disbursements' => BgfDisbursement::where('status', 'pending')->count(),
            ],
            'recentApplications' => BgfApplication::with('user')
                ->latest()
                ->take(10)
                ->get(),
            'activeProjects' => BgfProject::with(['user', 'application'])
                ->whereIn('status', ['active', 'in_progress'])
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }

    /**
     * Applications list
     */
    public function applications(Request $request): Response
    {
        $query = BgfApplication::with('user');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('reference_number', 'like', "%{$request->search}%")
                  ->orWhere('business_name', 'like', "%{$request->search}%")
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%");
                  });
            });
        }

        $applications = $query->latest()->paginate(20);

        return Inertia::render('Admin/BGF/Applications', [
            'applications' => $applications,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show application for review
     */
    public function showApplication(BgfApplication $application): Response
    {
        $application->load(['user', 'evaluations.evaluator', 'project']);

        // Calculate automated score
        $scoreData = $this->scoringService->calculateScore($application);

        return Inertia::render('Admin/BGF/ReviewApplication', [
            'application' => $application,
            'scoreData' => $scoreData,
            'suggestedProfitSharing' => $this->scoringService->determineProfitSharing(
                $scoreData['total_score'],
                $application->amount_requested
            ),
        ]);
    }

    /**
     * Evaluate application
     */
    public function evaluate(Request $request, BgfApplication $application)
    {
        $validated = $request->validate([
            'membership_score' => 'required|integer|min:0|max:15',
            'training_score' => 'required|integer|min:0|max:10',
            'viability_score' => 'required|integer|min:0|max:25',
            'credibility_score' => 'required|integer|min:0|max:15',
            'contribution_score' => 'required|integer|min:0|max:15',
            'risk_control_score' => 'required|integer|min:0|max:10',
            'track_record_score' => 'required|integer|min:0|max:10',
            'recommendation' => 'required|in:approve,reject,request_more_info',
            'overall_notes' => 'nullable|string',
            'risk_level' => 'required|in:low,medium,high',
        ]);

        $validated['evaluator_id'] = auth()->id();
        $validated['application_id'] = $application->id;
        $validated['total_score'] = array_sum([
            $validated['membership_score'],
            $validated['training_score'],
            $validated['viability_score'],
            $validated['credibility_score'],
            $validated['contribution_score'],
            $validated['risk_control_score'],
            $validated['track_record_score'],
        ]);

        $evaluation = BgfEvaluation::create($validated);

        // Update application
        $application->update([
            'score' => $validated['total_score'],
            'evaluated_by' => auth()->id(),
            'evaluated_at' => now(),
            'status' => 'under_review',
        ]);

        return redirect()
            ->route('admin.bgf.applications.show', $application)
            ->with('success', 'Application evaluated successfully.');
    }

    /**
     * Approve application
     */
    public function approve(Request $request, BgfApplication $application)
    {
        $validated = $request->validate([
            'approved_amount' => 'required|numeric|min:1000',
            'member_profit_percentage' => 'required|integer|min:60|max:70',
            'expected_completion_date' => 'required|date|after:today',
            'approval_notes' => 'nullable|string',
        ]);

        if ($application->score < 70) {
            return back()->with('error', 'Application score must be at least 70 to approve.');
        }

        // Create project
        $project = BgfProject::create([
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'approved_amount' => $validated['approved_amount'],
            'member_contribution' => $application->member_contribution,
            'member_profit_percentage' => $validated['member_profit_percentage'],
            'mygrownet_profit_percentage' => 100 - $validated['member_profit_percentage'],
            'expected_completion_date' => $validated['expected_completion_date'],
            'status' => 'pending_contract',
        ]);

        $project->project_number = $project->generateProjectNumber();
        $project->save();

        // Update application
        $application->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('admin.bgf.projects.show', $project)
            ->with('success', 'Application approved! Project created.');
    }

    /**
     * Reject application
     */
    public function reject(Request $request, BgfApplication $application)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('admin.bgf.applications')
            ->with('success', 'Application rejected.');
    }

    /**
     * Projects list
     */
    public function projects(Request $request): Response
    {
        $query = BgfProject::with(['user', 'application']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $projects = $query->latest()->paginate(20);

        return Inertia::render('Admin/BGF/Projects', [
            'projects' => $projects,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Disbursements list
     */
    public function disbursements(Request $request): Response
    {
        $disbursements = BgfDisbursement::with(['project.user', 'project.application'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/BGF/Disbursements', [
            'disbursements' => $disbursements,
        ]);
    }

    /**
     * Repayments list
     */
    public function repayments(Request $request): Response
    {
        $repayments = BgfRepayment::with(['project.user', 'project.application'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/BGF/Repayments', [
            'repayments' => $repayments,
        ]);
    }

    /**
     * Evaluations list
     */
    public function evaluations(Request $request): Response
    {
        $evaluations = BgfEvaluation::with(['application.user', 'evaluator'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/BGF/Evaluations', [
            'evaluations' => $evaluations,
        ]);
    }

    /**
     * Contracts list
     */
    public function contracts(Request $request): Response
    {
        $contracts = BgfContract::with(['project.user', 'project.application'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/BGF/Contracts', [
            'contracts' => $contracts,
        ]);
    }

    /**
     * Show project details
     */
    public function showProject(BgfProject $project): Response
    {
        $project->load([
            'application.user',
            'disbursements' => fn($q) => $q->latest(),
            'repayments' => fn($q) => $q->latest(),
            'contract',
        ]);

        return Inertia::render('Admin/BGF/ProjectDetails', [
            'project' => $project,
        ]);
    }

    /**
     * Analytics
     */
    public function analytics(): Response
    {
        return Inertia::render('Admin/BGF/Analytics', [
            'stats' => [
                'total_applications' => BgfApplication::count(),
                'approval_rate' => $this->calculateApprovalRate(),
                'average_score' => BgfApplication::whereNotNull('score')->avg('score'),
                'total_funded' => BgfProject::sum('approved_amount'),
                'total_repaid' => BgfProject::sum('total_repaid'),
                'success_rate' => $this->calculateSuccessRate(),
            ],
            'applicationsByStatus' => BgfApplication::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get(),
            'projectsByStatus' => BgfProject::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get(),
            'fundingByBusinessType' => BgfApplication::selectRaw('business_type, sum(amount_requested) as total')
                ->where('status', 'approved')
                ->groupBy('business_type')
                ->get(),
        ]);
    }

    private function calculateApprovalRate(): float
    {
        $total = BgfApplication::whereIn('status', ['approved', 'rejected'])->count();
        if ($total === 0) return 0;
        
        $approved = BgfApplication::where('status', 'approved')->count();
        return round(($approved / $total) * 100, 2);
    }

    private function calculateSuccessRate(): float
    {
        $total = BgfProject::whereIn('status', ['completed', 'defaulted'])->count();
        if ($total === 0) return 0;
        
        $completed = BgfProject::where('status', 'completed')->count();
        return round(($completed / $total) * 100, 2);
    }
}
