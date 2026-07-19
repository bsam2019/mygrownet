<?php

namespace App\Http\Controllers\BMS;

use App\Http\Controllers\Controller;
use App\Domain\BMS\Core\Services\AssetService;
use App\Infrastructure\Persistence\Eloquent\BMS\AssetModel;
use App\Infrastructure\Persistence\Eloquent\BMS\AssetAssignmentModel;
use App\Infrastructure\Persistence\Eloquent\BMS\AssetMaintenanceModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CmsUserModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    public function __construct(
        private AssetService $assetService
    ) {}

    public function depreciationRegister(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $assets = AssetModel::forCompany($companyId)
            ->with('depreciation')
            ->orderBy('name')
            ->paginate(20);

        $totalCost = AssetModel::forCompany($companyId)->sum('purchase_cost');
        $totalCurrentValue = AssetModel::forCompany($companyId)->sum('current_value');
        $totalAccumulatedDep = 0;
        foreach (AssetModel::forCompany($companyId)->with('depreciation')->get() as $a) {
            $totalAccumulatedDep += $this->assetService->getAccumulatedDepreciation($a);
        }

        return Inertia::render('BMS/Assets/DepreciationRegister', [
            'assets' => $assets,
            'summary' => [
                'total_cost' => $totalCost,
                'total_current_value' => $totalCurrentValue,
                'total_accumulated_depreciation' => $totalAccumulatedDep,
            ],
        ]);
    }

    public function setupDepreciation(Request $request, AssetModel $asset)
    {
        $validated = $request->validate([
            'method' => 'required|in:straight_line,declining_balance,sum_of_years_digits',
            'useful_life_years' => 'required|integer|min:1|max:100',
            'salvage_value' => 'required|numeric|min:0',
            'depreciation_start_date' => 'nullable|date',
        ]);

        $this->assetService->calculateDepreciation($asset, $validated);

        return back()->with('success', 'Depreciation configured successfully.');
    }

    public function applyDepreciation(Request $request, AssetModel $asset)
    {
        $this->assetService->updateAssetValue($asset);

        return back()->with('success', 'Depreciation applied. Current value updated.');
    }

    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $assets = AssetModel::forCompany($companyId)
            ->forBranch($request->branch_id)
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('asset_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
            )
            ->when($request->category, fn($q) => $q->byCategory($request->category))
            ->when($request->status, fn($q) => $q->byStatus($request->status))
            ->when($request->assigned_to, fn($q) => $q->assignedTo($request->assigned_to))
            ->with(['assignedTo.user', 'createdBy.user', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get categories and staff for filters
        $categories = AssetModel::forCompany($companyId)
            ->select('category')
            ->distinct()
            ->pluck('category');

        $staff = CmsUserModel::where('company_id', $companyId)
            ->with('user')
            ->get();

        $branches = \App\Infrastructure\Persistence\Eloquent\BMS\BranchModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'branch_name']);

        return Inertia::render('BMS/Assets/Index', [
            'assets' => $assets,
            'categories' => $categories,
            'staff' => $staff,
            'filters' => $request->only(['search', 'category', 'status', 'assigned_to', 'branch_id']),
            'branches' => $branches,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BMS/Assets/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'required|numeric|min:0',
            'condition' => 'required|in:excellent,good,fair,poor',
            'location' => 'nullable|string|max:255',
            'warranty_months' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $userId = $request->user()->cmsUser->id;

        $asset = $this->assetService->createAsset([
            ...$validated,
            'company_id' => $companyId,
            'created_by' => $userId,
        ]);

        return redirect()
            ->route('bms.assets.show', $asset->id)
            ->with('success', 'Asset created successfully');
    }

    public function show(AssetModel $asset): Response
    {
        $asset->load([
            'assignedTo.user',
            'createdBy.user',
            'depreciation',
        ]);

        $history = $this->assetService->getAssetHistory($asset->id);

        return Inertia::render('BMS/Assets/Show', [
            'asset' => $asset,
            'assignments' => $history['assignments'],
            'maintenance' => $history['maintenance'],
        ]);
    }

    public function edit(AssetModel $asset): Response
    {
        return Inertia::render('BMS/Assets/Edit', [
            'asset' => $asset,
        ]);
    }

    public function update(Request $request, AssetModel $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'required|numeric|min:0',
            'condition' => 'required|in:excellent,good,fair,poor',
            'location' => 'nullable|string|max:255',
            'warranty_months' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $userId = $request->user()->cmsUser->id;

        $this->assetService->updateAsset($asset, $validated, $userId);

        return redirect()
            ->route('bms.assets.show', $asset->id)
            ->with('success', 'Asset updated successfully');
    }

    public function assign(Request $request, AssetModel $asset)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:cms_users,id',
            'assigned_date' => 'nullable|date',
            'condition_on_assignment' => 'required|in:excellent,good,fair,poor',
            'assignment_notes' => 'nullable|string',
        ]);

        $userId = $request->user()->cmsUser->id;

        $this->assetService->assignAsset([
            'asset_id' => $asset->id,
            ...$validated,
            'assigned_by' => $userId,
        ]);

        return back()->with('success', 'Asset assigned successfully');
    }

    public function returnAsset(Request $request, AssetAssignmentModel $assignment)
    {
        $validated = $request->validate([
            'returned_date' => 'nullable|date',
            'condition_on_return' => 'required|in:excellent,good,fair,poor',
            'return_notes' => 'nullable|string',
        ]);

        $userId = $request->user()->cmsUser->id;

        $this->assetService->returnAsset($assignment, [
            ...$validated,
            'returned_by' => $userId,
        ]);

        return back()->with('success', 'Asset returned successfully');
    }

    public function scheduleMaintenance(Request $request, AssetModel $asset)
    {
        $validated = $request->validate([
            'maintenance_type' => 'required|in:routine,repair,inspection,upgrade',
            'description' => 'required|string',
            'scheduled_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $userId = $request->user()->cmsUser->id;

        $this->assetService->scheduleMaintenance([
            'asset_id' => $asset->id,
            ...$validated,
            'created_by' => $userId,
        ]);

        return back()->with('success', 'Maintenance scheduled successfully');
    }

    public function completeMaintenance(Request $request, AssetMaintenanceModel $maintenance)
    {
        $validated = $request->validate([
            'completed_date' => 'nullable|date',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $userId = $request->user()->cmsUser->id;

        $this->assetService->completeMaintenance($maintenance, [
            ...$validated,
            'completed_by' => $userId,
        ]);

        return back()->with('success', 'Maintenance completed successfully');
    }

    public function maintenance(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $upcoming = $this->assetService->getUpcomingMaintenance($companyId, 30);
        $overdue = $this->assetService->getOverdueMaintenance($companyId);

        return Inertia::render('BMS/Assets/Maintenance', [
            'upcoming' => $upcoming,
            'overdue' => $overdue,
        ]);
    }
}
