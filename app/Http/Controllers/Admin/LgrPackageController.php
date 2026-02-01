<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LgrPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LgrPackageController extends Controller
{
    public function index(): Response
    {
        $packages = LgrPackage::orderBy('sort_order')
            ->orderBy('package_amount')
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'slug' => $package->slug,
                    'package_amount' => $package->package_amount,
                    'daily_lgr_rate' => $package->daily_lgr_rate,
                    'duration_days' => $package->duration_days,
                    'total_reward' => $package->total_reward,
                    'is_active' => $package->is_active,
                    'sort_order' => $package->sort_order,
                    'description' => $package->description,
                    'features' => $package->features,
                    'roi_percentage' => $package->getRoiPercentage(),
                    'is_calculation_correct' => $package->isRewardCalculationCorrect(),
                    'formatted_package_amount' => $package->formatted_package_amount,
                    'formatted_daily_rate' => $package->formatted_daily_rate,
                    'formatted_total_reward' => $package->formatted_total_reward,
                    'users_count' => $package->users()->count(),
                ];
            });

        return Inertia::render('Admin/LGR/Packages', [
            'packages' => $packages,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:lgr_packages,slug',
            'package_amount' => 'required|numeric|min:0',
            'daily_lgr_rate' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1|max:365',
            'total_reward' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
        ]);

        $package = LgrPackage::create($validated);

        return redirect()
            ->route('admin.lgr.packages.index')
            ->with('success', "Package '{$package->name}' created successfully");
    }

    public function update(Request $request, LgrPackage $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:lgr_packages,slug,' . $package->id,
            'package_amount' => 'required|numeric|min:0',
            'daily_lgr_rate' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1|max:365',
            'total_reward' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
        ]);

        $package->update($validated);

        return redirect()
            ->route('admin.lgr.packages.index')
            ->with('success', "Package '{$package->name}' updated successfully");
    }

    public function destroy(LgrPackage $package)
    {
        // Check if any users are using this package
        $usersCount = $package->users()->count();
        
        if ($usersCount > 0) {
            return redirect()
                ->route('admin.lgr.packages.index')
                ->withErrors(['error' => "Cannot delete package '{$package->name}' because {$usersCount} users are using it"]);
        }

        $packageName = $package->name;
        $package->delete();

        return redirect()
            ->route('admin.lgr.packages.index')
            ->with('success', "Package '{$packageName}' deleted successfully");
    }

    public function toggleActive(LgrPackage $package)
    {
        $package->update(['is_active' => !$package->is_active]);

        $status = $package->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('admin.lgr.packages.index')
            ->with('success', "Package '{$package->name}' {$status} successfully");
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'packages' => 'required|array',
            'packages.*.id' => 'required|exists:lgr_packages,id',
            'packages.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['packages'] as $packageData) {
            LgrPackage::where('id', $packageData['id'])
                ->update(['sort_order' => $packageData['sort_order']]);
        }

        return redirect()
            ->route('admin.lgr.packages.index')
            ->with('success', 'Package order updated successfully');
    }
}
