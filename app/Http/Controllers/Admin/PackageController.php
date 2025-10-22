<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::orderBy('price')->get();

        return Inertia::render('Admin/Packages/Index', [
            'packages' => $packages,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,annual,one-time',
            'professional_level' => 'nullable|in:associate,professional,senior,manager,director,executive,ambassador',
            'lp_requirement' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $package = Package::create($request->all());

        activity()
            ->performedOn($package)
            ->withProperties($request->all())
            ->log('Package created by admin');

        return back()->with('success', 'Package created successfully');
    }

    public function update(Request $request, Package $package)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,annual,one-time,upgrade',
            'professional_level' => 'nullable|in:associate,professional,senior,manager,director,executive,ambassador',
            'lp_requirement' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $oldData = $package->toArray();
        $package->update($request->all());

        activity()
            ->performedOn($package)
            ->withProperties([
                'old' => $oldData,
                'new' => $request->all(),
            ])
            ->log('Package updated by admin');

        return back()->with('success', 'Package updated successfully');
    }

    public function destroy(Package $package)
    {
        // Check if package is in use
        $subscriptionsCount = $package->subscriptions()->count();
        
        if ($subscriptionsCount > 0) {
            return back()->with('error', "Cannot delete package. It has {$subscriptionsCount} active subscriptions.");
        }

        activity()
            ->performedOn($package)
            ->withProperties($package->toArray())
            ->log('Package deleted by admin');

        $package->delete();

        return back()->with('success', 'Package deleted successfully');
    }

    public function toggleStatus(Package $package)
    {
        $package->update(['is_active' => !$package->is_active]);

        activity()
            ->performedOn($package)
            ->withProperties([
                'is_active' => $package->is_active,
            ])
            ->log('Package status toggled by admin');

        return back()->with('success', 'Package status updated successfully');
    }
}
