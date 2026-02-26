<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Benefit;
use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BenefitAdminController extends Controller
{
    public function index()
    {
        $benefits = Benefit::orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('benefit_type');

        return Inertia::render('Admin/Benefits/Index', [
            'benefits' => $benefits,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Benefits/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:apps,cloud,learning,media,resources',
            'benefit_type' => 'required|in:starter_kit,monthly_service,physical_item',
            'description' => 'required|string',
            'icon' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'tier_allocations' => 'nullable|array',
            'is_active' => 'boolean',
            'is_coming_soon' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Benefit::create($validated);

        return redirect()->route('admin.benefits.index')
            ->with('success', 'Benefit created successfully');
    }

    public function edit(Benefit $benefit)
    {
        return Inertia::render('Admin/Benefits/Edit', [
            'benefit' => $benefit,
        ]);
    }

    public function update(Request $request, Benefit $benefit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:apps,cloud,learning,media,resources',
            'benefit_type' => 'required|in:starter_kit,monthly_service,physical_item',
            'description' => 'required|string',
            'icon' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'tier_allocations' => 'nullable|array',
            'is_active' => 'boolean',
            'is_coming_soon' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $benefit->update($validated);

        return redirect()->route('admin.benefits.index')
            ->with('success', 'Benefit updated successfully');
    }

    public function destroy(Benefit $benefit)
    {
        $benefit->delete();

        return redirect()->route('admin.benefits.index')
            ->with('success', 'Benefit deleted successfully');
    }

    /**
     * Manage physical item fulfillment
     */
    public function fulfillment()
    {
        $physicalItems = StarterKitPurchaseModel::with(['user', 'benefits' => function ($query) {
            $query->where('benefit_type', 'physical_item');
        }])
            ->whereHas('benefits', function ($query) {
                $query->where('benefit_type', 'physical_item');
            })
            ->latest()
            ->paginate(50);

        return Inertia::render('Admin/Benefits/Fulfillment', [
            'items' => $physicalItems,
        ]);
    }

    /**
     * Update fulfillment status
     */
    public function updateFulfillment(Request $request, StarterKitPurchaseModel $purchase, Benefit $benefit)
    {
        $validated = $request->validate([
            'fulfillment_status' => 'required|in:pending,issued,delivered',
            'fulfillment_notes' => 'nullable|string',
        ]);

        $timestamp = now();
        $timestampField = match($validated['fulfillment_status']) {
            'issued' => 'issued_at',
            'delivered' => 'delivered_at',
            default => null,
        };

        $updateData = [
            'fulfillment_status' => $validated['fulfillment_status'],
            'fulfillment_notes' => $validated['fulfillment_notes'] ?? null,
        ];

        if ($timestampField) {
            $updateData[$timestampField] = $timestamp;
        }

        $purchase->benefits()->updateExistingPivot($benefit->id, $updateData);

        return back()->with('success', 'Fulfillment status updated successfully');
    }
}
