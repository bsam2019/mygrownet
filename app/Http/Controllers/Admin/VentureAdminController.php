<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureCategoryModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDocumentModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureUpdateModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDividendModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareTransferModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureResolutionModel;
use App\Domain\VentureBuilder\Services\VentureService;
use App\Domain\VentureBuilder\Services\VentureInvestmentService;
use App\Domain\VentureBuilder\Services\VentureDividendService;
use App\Domain\VentureBuilder\Services\VentureCacheService;
use App\Domain\VentureBuilder\Services\VentureShareTransferService;
use App\Domain\VentureBuilder\Services\VentureVoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VentureAdminController extends Controller
{
    public function __construct(
        private readonly VentureService $ventureService,
        private readonly VentureInvestmentService $investmentService,
        private readonly VentureDividendService $dividendService,
        private readonly VentureCacheService $cacheService,
        private readonly VentureShareTransferService $shareTransferService,
        private readonly VentureVoteService $voteService,
    ) {}

    /**
     * Display admin dashboard
     */
    public function dashboard(): Response
    {
        $stats = $this->cacheService->getAdminDashboardStats();
        $stats['pending_transfers'] = VentureShareTransferModel::where('status', 'pending')->count();
        $stats['open_resolutions'] = VentureResolutionModel::where('status', 'voting')->count();

        $recentVentures = VentureModel::with(['category', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentInvestments = VentureInvestmentModel::with(['venture', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Ventures/Dashboard', [
            'stats' => $stats,
            'recentVentures' => $recentVentures,
            'recentInvestments' => $recentInvestments,
        ]);
    }

    /**
     * Display all ventures
     */
    public function index(Request $request): Response
    {
        $ventures = VentureModel::with(['category', 'creator'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Ventures/Index', [
            'ventures' => $ventures,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show create form
     */
    public function create(): Response
    {
        $categories = VentureCategoryModel::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/Ventures/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store new venture
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:venture_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'business_model' => 'nullable|string',
            'funding_target' => 'required|numeric|min:1000',
            'minimum_investment' => 'required|numeric|min:100',
            'maximum_investment' => 'nullable|numeric|gt:minimum_investment',
            'share_price' => 'nullable|numeric|min:1',
            'funding_start_date' => 'nullable|date',
            'funding_end_date' => 'nullable|date|after:funding_start_date',
            'expected_launch_date' => 'nullable|date',
            'risk_factors' => 'nullable|string',
            'expected_roi_months' => 'nullable|integer|min:1',
            'is_featured' => 'boolean',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'draft';

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('ventures', 'public');
        }

        VentureModel::create($validated);

        return redirect()->route('admin.ventures.index')
            ->with('success', 'Venture created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(VentureModel $venture): Response
    {
        $venture->load(['category', 'creator']);

        $categories = VentureCategoryModel::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/Ventures/Edit', [
            'venture' => $venture,
            'categories' => $categories,
        ]);
    }

    /**
     * Update venture
     */
    public function update(Request $request, VentureModel $venture)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:venture_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'business_model' => 'nullable|string',
            'funding_target' => 'required|numeric|min:1000',
            'minimum_investment' => 'required|numeric|min:100',
            'maximum_investment' => 'nullable|numeric|gt:minimum_investment',
            'share_price' => 'nullable|numeric|min:1',
            'funding_start_date' => 'nullable|date',
            'funding_end_date' => 'nullable|date|after:funding_start_date',
            'expected_launch_date' => 'nullable|date',
            'risk_factors' => 'nullable|string',
            'expected_roi_months' => 'nullable|integer|min:1',
            'is_featured' => 'boolean',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validated['title'] !== $venture->title) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('ventures', 'public');
        }

        $venture->update($validated);

        return back()->with('success', 'Venture updated successfully.');
    }

    /**
     * Delete venture
     */
    public function destroy(VentureModel $venture)
    {
        if (!in_array($venture->status, ['draft', 'review', 'cancelled'])) {
            return back()->with('error', 'Only draft, review, or cancelled ventures can be deleted.');
        }

        $venture->delete();

        return redirect()->route('admin.ventures.index')
            ->with('success', 'Venture deleted successfully.');
    }

    /**
     * Approve venture
     */
    public function approve(VentureModel $venture)
    {
        try {
            $this->ventureService->transitionStatus($venture->id, 'approved');
            $venture->update([
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            return back()->with('success', 'Venture approved successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Launch funding
     */
    public function launchFunding(VentureModel $venture)
    {
        try {
            $this->ventureService->transitionStatus($venture->id, 'funding');
            $venture->update(['funding_start_date' => $venture->funding_start_date ?? now()]);
            return back()->with('success', 'Funding launched successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Close funding
     */
    public function closeFunding(VentureModel $venture)
    {
        try {
            $this->ventureService->transitionStatus($venture->id, 'funded');
            $venture->update(['funding_end_date' => now()]);
            return back()->with('success', 'Funding closed successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Activate venture
     */
    public function activate(VentureModel $venture)
    {
        try {
            $this->ventureService->transitionStatus($venture->id, 'active');
            return back()->with('success', 'Venture activated successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Register company for a funded venture
     */
    public function registerCompany(Request $request, VentureModel $venture)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_registration_number' => 'required|string|max:255',
            'mygrownet_equity_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $this->ventureService->registerCompany(
                $venture->id,
                $validated['company_name'],
                $validated['company_registration_number'],
                $validated['mygrownet_equity_percentage']
            );
            return back()->with('success', 'Company registered successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display all investments across all ventures
     */
    public function allInvestments(Request $request): Response
    {
        $investments = VentureInvestmentModel::with(['user', 'venture'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_investments' => VentureInvestmentModel::count(),
            'pending_investments' => VentureInvestmentModel::where('status', 'pending')->count(),
            'confirmed_investments' => VentureInvestmentModel::whereIn('status', ['confirmed', 'completed'])->count(),
            'total_amount' => VentureInvestmentModel::whereIn('status', ['confirmed', 'completed'])->sum('amount'),
        ];

        return Inertia::render('Admin/Ventures/Investments', [
            'investments' => $investments,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Display venture investments
     */
    public function investments(VentureModel $venture): Response
    {
        $investments = VentureInvestmentModel::with(['user'])
            ->where('venture_id', $venture->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Ventures/Investments', [
            'venture' => $venture,
            'investments' => $investments,
        ]);
    }

    /**
     * Confirm an investment
     */
    public function confirmInvestment(VentureInvestmentModel $investment)
    {
        try {
            $this->investmentService->confirmInvestment($investment->id);
            return back()->with('success', 'Investment confirmed successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Refund an investment
     */
    public function refundInvestment(VentureInvestmentModel $investment)
    {
        try {
            $this->investmentService->refundInvestment($investment->id);
            return back()->with('success', 'Investment refunded successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display shareholders for a venture
     */
    public function shareholders(VentureModel $venture): Response
    {
        $shareholders = VentureShareholderModel::with(['user', 'investment'])
            ->where('venture_id', $venture->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Ventures/Shareholders', [
            'venture' => $venture,
            'shareholders' => $shareholders,
        ]);
    }

    /**
     * Register shareholders from confirmed investments
     */
    public function registerShareholders(VentureModel $venture, Request $request)
    {
        $request->validate([
            'investment_ids' => 'required|array',
            'investment_ids.*' => 'exists:venture_investments,id',
        ]);

        $count = 0;
        foreach ($request->investment_ids as $id) {
            $investment = VentureInvestmentModel::find($id);
            if (!$investment || $investment->is_shareholder) {
                continue;
            }

            try {
                $this->investmentService->registerShareholder($investment->id);
                $investment->update(['is_shareholder' => true, 'shareholder_registered_at' => now()]);
                $count++;
            } catch (\Exception $e) {
                continue;
            }
        }

        return back()->with('success', "{$count} shareholder(s) registered successfully.");
    }

    /**
     * Display documents for a venture
     */
    public function documents(VentureModel $venture): Response
    {
        $documents = VentureDocumentModel::with(['uploader'])
            ->where('venture_id', $venture->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Ventures/Documents', [
            'venture' => $venture,
            'documents' => $documents,
        ]);
    }

    /**
     * Upload a document
     */
    public function uploadDocument(Request $request, VentureModel $venture)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:business_plan,financial_report,shareholder_agreement,certificate,compliance_document,update_report,other',
            'visibility' => 'required|in:public,investors_only,shareholders_only,admin_only',
            'file' => 'required|file|max:10240',
            'is_confidential' => 'boolean',
        ]);

        $file = $request->file('file');
        $path = $file->store('venture-documents/' . $venture->id, 'public');

        VentureDocumentModel::create([
            'venture_id' => $venture->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'visibility' => $validated['visibility'],
            'is_confidential' => $validated['is_confidential'] ?? false,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Delete a document
     */
    public function deleteDocument(VentureDocumentModel $document)
    {
        $document->delete();
        return back()->with('success', 'Document deleted successfully.');
    }

    /**
     * Display updates for a venture
     */
    public function updates(VentureModel $venture): Response
    {
        $updates = VentureUpdateModel::with(['author'])
            ->where('venture_id', $venture->id)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Ventures/Updates', [
            'venture' => $venture,
            'updates' => $updates,
        ]);
    }

    /**
     * Create an update
     */
    public function createUpdate(Request $request, VentureModel $venture)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:milestone,financial,operational,announcement,alert,general',
            'visibility' => 'required|in:public,investors_only,shareholders_only',
            'send_notification' => 'boolean',
            'is_pinned' => 'boolean',
        ]);

        VentureUpdateModel::create([
            'venture_id' => $venture->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'visibility' => $validated['visibility'],
            'send_notification' => $validated['send_notification'] ?? false,
            'is_pinned' => $validated['is_pinned'] ?? false,
            'posted_by' => auth()->id(),
            'published_at' => now(),
        ]);

        return back()->with('success', 'Update created successfully.');
    }

    /**
     * Update an update
     */
    public function updateUpdate(Request $request, VentureUpdateModel $update)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:milestone,financial,operational,announcement,alert,general',
            'visibility' => 'required|in:public,investors_only,shareholders_only',
            'is_pinned' => 'boolean',
        ]);

        $update->update($validated);

        return back()->with('success', 'Update updated successfully.');
    }

    /**
     * Delete an update
     */
    public function deleteUpdate(VentureUpdateModel $update)
    {
        $update->delete();
        return back()->with('success', 'Update deleted successfully.');
    }

    /**
     * Display dividends for a venture
     */
    public function dividends(VentureModel $venture): Response
    {
        $dividends = VentureDividendModel::with(['shareholder.user', 'processor'])
            ->where('venture_id', $venture->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Ventures/Dividends', [
            'venture' => $venture,
            'dividends' => $dividends,
        ]);
    }

    /**
     * Declare dividends for a venture
     */
    public function declareDividend(Request $request, VentureModel $venture)
    {
        $validated = $request->validate([
            'dividend_period' => 'required|string|max:50',
            'total_amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->dividendService->declareDividend(
                $venture->id,
                $validated['dividend_period'],
                $validated['total_amount'],
                $validated['notes'] ?? null
            );

            return back()->with('success', 'Dividends declared successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process a dividend payment
     */
    public function processDividend(VentureDividendModel $dividend)
    {
        try {
            $this->dividendService->processDividend($dividend->id);
            return back()->with('success', 'Dividend processed successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display all share transfers
     */
    public function allTransfers(): Response
    {
        $transfers = VentureShareTransferModel::with(['venture', 'fromUser', 'toUser', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Ventures/Transfers', [
            'transfers' => $transfers,
        ]);
    }

    /**
     * Display transfers for a specific venture
     */
    public function transfers(VentureModel $venture): Response
    {
        $transfers = VentureShareTransferModel::with(['fromUser', 'toUser', 'approver'])
            ->where('venture_id', $venture->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Ventures/Transfers', [
            'venture' => $venture,
            'transfers' => $transfers,
        ]);
    }

    /**
     * Approve a share transfer
     */
    public function approveTransfer(VentureShareTransferModel $transfer)
    {
        try {
            $this->shareTransferService->approveTransfer($transfer->id);

            return back()->with('success', 'Share transfer approved and processed successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve transfer. Please try again.');
        }
    }

    /**
     * Reject a share transfer
     */
    public function rejectTransfer(Request $request, VentureShareTransferModel $transfer)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->shareTransferService->rejectTransfer($transfer->id, $validated['admin_notes'] ?? null);

            return back()->with('success', 'Share transfer rejected.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display resolutions for a venture
     */
    public function resolutions(VentureModel $venture): Response
    {
        $resolutions = VentureResolutionModel::with(['creator'])
            ->where('venture_id', $venture->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Ventures/Resolutions', [
            'venture' => $venture,
            'resolutions' => $resolutions,
        ]);
    }

    /**
     * Create a resolution
     */
    public function createResolution(Request $request, VentureModel $venture)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:ordinary,special,board',
            'voting_ends_at' => 'nullable|date|after:now',
            'pass_threshold_percentage' => 'nullable|numeric|min:1|max:100',
        ]);

        try {
            $this->voteService->createResolution(
                $venture->id,
                $validated['title'],
                $validated['description'],
                $validated['type'],
                $validated['voting_ends_at'] ?? null,
                $validated['pass_threshold_percentage'] ?? 50.0,
            );

            return back()->with('success', 'Resolution created successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Open voting for a resolution
     */
    public function openVoting(VentureResolutionModel $resolution)
    {
        try {
            $this->voteService->openVoting($resolution->id);

            return back()->with('success', 'Voting is now open for this resolution.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tally voting results
     */
    public function tallyResults(VentureResolutionModel $resolution)
    {
        try {
            $this->voteService->tallyResults($resolution->id);

            return back()->with('success', 'Voting results have been tallied.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display analytics
     */
    public function analytics(): Response
    {
        $totalInvested = VentureInvestmentModel::confirmed()->sum('amount');
        $averageInvestment = VentureInvestmentModel::confirmed()->avg('amount');
        $statusBreakdown = VentureModel::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $topVentures = VentureModel::with(['category'])
            ->where('total_raised', '>', 0)
            ->orderBy('total_raised', 'desc')
            ->limit(10)
            ->get(['id', 'title', 'total_raised', 'funding_target', 'investor_count', 'status']);
        $totalShareholders = VentureShareholderModel::active()->count();
        $totalDividendsPaid = VentureDividendModel::where('status', 'paid')->sum('amount');

        return Inertia::render('Admin/Ventures/Analytics', [
            'totalInvested' => $totalInvested,
            'averageInvestment' => $averageInvestment,
            'totalShareholders' => $totalShareholders,
            'totalDividendsPaid' => $totalDividendsPaid,
            'statusBreakdown' => $statusBreakdown,
            'topVentures' => $topVentures,
        ]);
    }

    /**
     * Display categories
     */
    public function categories(): Response
    {
        $categories = VentureCategoryModel::orderBy('sort_order')->get();

        return Inertia::render('Admin/Ventures/Categories', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store new category
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:venture_categories,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = true;

        VentureCategoryModel::create($validated);

        return back()->with('success', 'Category created successfully.');
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, VentureCategoryModel $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:venture_categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return back()->with('success', 'Category updated successfully.');
    }
}
