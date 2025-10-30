<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureCategoryModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VentureAdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard(): Response
    {
        $stats = [
            'total_ventures' => VentureModel::count(),
            'active_ventures' => VentureModel::whereIn('status', ['funding', 'funded', 'active'])->count(),
            'total_raised' => VentureModel::sum('total_raised'),
            'total_investors' => VentureInvestmentModel::distinct('user_id')->count(),
            'pending_investments' => VentureInvestmentModel::where('status', 'pending')->count(),
        ];

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
            'funding_start_date' => 'nullable|date',
            'funding_end_date' => 'nullable|date|after:funding_start_date',
            'expected_launch_date' => 'nullable|date',
            'risk_factors' => 'nullable|string',
            'expected_roi_months' => 'nullable|integer|min:1',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'draft';

        $venture = VentureModel::create($validated);

        return redirect()->route('admin.ventures.edit', $venture)
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
            'funding_start_date' => 'nullable|date',
            'funding_end_date' => 'nullable|date|after:funding_start_date',
            'expected_launch_date' => 'nullable|date',
            'risk_factors' => 'nullable|string',
            'expected_roi_months' => 'nullable|integer|min:1',
            'is_featured' => 'boolean',
        ]);

        if ($validated['title'] !== $venture->title) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $venture->update($validated);

        return back()->with('success', 'Venture updated successfully.');
    }

    /**
     * Approve venture
     */
    public function approve(VentureModel $venture)
    {
        if (!in_array($venture->status, ['draft', 'review'])) {
            return back()->with('error', 'Only draft or review ventures can be approved.');
        }

        $venture->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Venture approved successfully.');
    }

    /**
     * Launch funding
     */
    public function launchFunding(VentureModel $venture)
    {
        if ($venture->status !== 'approved') {
            return back()->with('error', 'Only approved ventures can launch funding.');
        }

        $venture->update([
            'status' => 'funding',
            'funding_start_date' => $venture->funding_start_date ?? now(),
        ]);

        return back()->with('success', 'Funding launched successfully.');
    }

    /**
     * Close funding
     */
    public function closeFunding(VentureModel $venture)
    {
        if ($venture->status !== 'funding') {
            return back()->with('error', 'Only ventures in funding can be closed.');
        }

        $venture->update([
            'status' => 'funded',
            'funding_end_date' => now(),
        ]);

        return back()->with('success', 'Funding closed successfully.');
    }

    /**
     * Activate venture
     */
    public function activate(VentureModel $venture)
    {
        if ($venture->status !== 'funded') {
            return back()->with('error', 'Only funded ventures can be activated.');
        }

        $venture->update([
            'status' => 'active',
        ]);

        return back()->with('success', 'Venture activated successfully.');
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
            'confirmed_investments' => VentureInvestmentModel::where('status', 'confirmed')->count(),
            'total_amount' => VentureInvestmentModel::where('status', 'confirmed')->sum('amount'),
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
     * Display analytics
     */
    public function analytics(): Response
    {
        // Placeholder for analytics
        return Inertia::render('Admin/Ventures/Analytics');
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
