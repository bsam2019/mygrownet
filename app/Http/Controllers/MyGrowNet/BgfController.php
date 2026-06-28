<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Models\BgfApplication;
use App\Models\BgfProject;
use App\Services\BgfScoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BgfController extends Controller
{
    public function __construct(
        private BgfScoringService $scoringService
    ) {}

    /**
     * Display BGF overview page
     */
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('MyGrowNet/BGF/Index', [
            'stats' => [
                'total_applications' => $user->bgfApplications()->count(),
                'approved_applications' => $user->bgfApplications()->where('status', 'approved')->count(),
                'active_projects' => $user->bgfProjects()->whereIn('status', ['active', 'in_progress'])->count(),
                'completed_projects' => $user->bgfProjects()->where('status', 'completed')->count(),
                'total_funded' => $user->bgfProjects()->sum('approved_amount'),
                'total_repaid' => $user->bgfProjects()->sum('total_repaid'),
            ],
            'recentApplications' => $user->bgfApplications()
                ->latest()
                ->take(5)
                ->get(),
            'activeProjects' => $user->bgfProjects()
                ->whereIn('status', ['active', 'in_progress'])
                ->with('application')
                ->get(),
        ]);
    }

    /**
     * Show application form
     */
    public function create(): Response
    {
        return Inertia::render('MyGrowNet/BGF/Apply', [
            'businessTypes' => [
                'agriculture' => 'Agriculture',
                'manufacturing' => 'Manufacturing',
                'trade' => 'Trade',
                'services' => 'Services',
                'education' => 'Education',
            ],
            'orderTypes' => [
                'order_fulfillment' => 'Order Fulfillment',
                'venture' => 'New Venture',
                'expansion' => 'Business Expansion',
            ],
        ]);
    }

    /**
     * Store new application
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_description' => 'required|string',
            'business_type' => 'required|in:agriculture,manufacturing,trade,services,education',
            'order_type' => 'required|in:order_fulfillment,venture,expansion',
            'amount_requested' => 'required|numeric|min:1000|max:50000',
            'member_contribution' => 'required|numeric|min:0',
            'expected_profit' => 'required|numeric|min:0',
            'completion_period_days' => 'required|integer|min:30|max:120',
            'feasibility_summary' => 'required|string|min:100',
            'order_proof' => 'nullable|string',
            'client_name' => 'nullable|string|max:255',
            'client_contact' => 'nullable|string|max:255',
            'tpin' => 'nullable|string|max:50',
            'business_account' => 'nullable|string|max:255',
            'has_business_experience' => 'boolean',
            'previous_projects' => 'nullable|string',
            'documents' => 'nullable|array',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['total_project_cost'] = $validated['amount_requested'] + $validated['member_contribution'];
        $validated['status'] = 'draft';
        
        // Generate reference number before creating
        $validated['reference_number'] = 'BGF-APP-' . date('Y') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

        $application = BgfApplication::create($validated);

        return redirect()
            ->route('mygrownet.bgf.show', $application)
            ->with('success', 'Application created successfully. Review and submit when ready.');
    }

    /**
     * Show application details
     */
    public function show(BgfApplication $application): Response
    {
        $this->authorize('view', $application);

        $application->load(['user', 'evaluator', 'reviewer', 'evaluations', 'project']);

        return Inertia::render('MyGrowNet/BGF/Show', [
            'application' => $application,
            'canEdit' => $application->status === 'draft',
            'canSubmit' => $application->status === 'draft',
        ]);
    }

    /**
     * Submit application for review
     */
    public function submit(BgfApplication $application)
    {
        $this->authorize('update', $application);

        if ($application->status !== 'draft') {
            return back()->with('error', 'Only draft applications can be submitted.');
        }

        // Validate minimum requirements
        if ($application->getMemberContributionPercentage() < 20) {
            return back()->with('error', 'Member contribution must be at least 20% of total project cost.');
        }

        $application->update(['status' => 'submitted']);

        return redirect()
            ->route('mygrownet.bgf.show', $application)
            ->with('success', 'Application submitted successfully. You will be notified once reviewed.');
    }

    /**
     * Show my applications list
     */
    public function applications(): Response
    {
        $applications = auth()->user()
            ->bgfApplications()
            ->latest()
            ->paginate(10);

        return Inertia::render('MyGrowNet/BGF/Applications', [
            'applications' => $applications,
        ]);
    }

    /**
     * Show my projects list
     */
    public function projects(): Response
    {
        $projects = auth()->user()
            ->bgfProjects()
            ->with(['application', 'disbursements', 'repayments'])
            ->latest()
            ->paginate(10);

        return Inertia::render('MyGrowNet/BGF/Projects', [
            'projects' => $projects,
        ]);
    }

    /**
     * Show project details
     */
    public function showProject(BgfProject $project): Response
    {
        $this->authorize('view', $project);

        $project->load([
            'application',
            'disbursements' => fn($q) => $q->latest(),
            'repayments' => fn($q) => $q->latest(),
            'contract',
        ]);

        return Inertia::render('MyGrowNet/BGF/ProjectDetails', [
            'project' => $project,
        ]);
    }
}
