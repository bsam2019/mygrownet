<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceVendorModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        $vendors = GrowFinanceVendorModel::forBusiness($businessId)
            ->withCount('expenses')
            ->orderBy('name')
            ->paginate(20);

        // Get vendor usage for limit banner
        $vendorUsage = $this->subscriptionService->canAddVendor($request->user());

        return Inertia::render('GrowFinance/Vendors/Index', [
            'vendors' => $vendors,
            'vendorUsage' => $vendorUsage,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('GrowFinance/Vendors/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Check subscription limits
        $check = $this->subscriptionService->canAddVendor($request->user());
        if (!$check['allowed']) {
            return back()->with('error', 'You\'ve reached your vendor limit. Please upgrade your plan to add more vendors.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'payment_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $businessId = $request->user()->id;

        GrowFinanceVendorModel::create([
            'business_id' => $businessId,
            ...$validated,
        ]);

        return redirect()->route('growfinance.vendors.index')
            ->with('success', 'Vendor added successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $vendor = GrowFinanceVendorModel::forBusiness($businessId)
            ->with(['expenses' => fn($q) => $q->latest('expense_date')->limit(10)])
            ->findOrFail($id);

        return Inertia::render('GrowFinance/Vendors/Show', [
            'vendor' => $vendor,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $vendor = GrowFinanceVendorModel::forBusiness($businessId)
            ->findOrFail($id);

        return Inertia::render('GrowFinance/Vendors/Edit', [
            'vendor' => $vendor,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'payment_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $businessId = $request->user()->id;

        $vendor = GrowFinanceVendorModel::forBusiness($businessId)
            ->findOrFail($id);

        $vendor->update($validated);

        return redirect()->route('growfinance.vendors.index')
            ->with('success', 'Vendor updated successfully!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $vendor = GrowFinanceVendorModel::forBusiness($businessId)
            ->findOrFail($id);

        $vendor->delete();

        return back()->with('success', 'Vendor deleted successfully!');
    }
}
