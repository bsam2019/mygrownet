<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Materials\Services\MaterialService;
use App\Infrastructure\Persistence\Eloquent\CMS\MaterialModel;
use App\Infrastructure\Persistence\Eloquent\CMS\MaterialCategoryModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    public function __construct(
        private MaterialService $materialService
    ) {}

    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id ?? null;

        if (!$companyId) {
            abort(403, 'No CMS company access');
        }

        $materials = MaterialModel::with(['category'])
            ->forCompany($companyId)
            ->when($request->category_id, fn($q) => $q->byCategory($request->category_id))
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->active !== null, fn($q) => $request->active ? $q->active() : $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(50);

        $categories = MaterialCategoryModel::forCompany($companyId)
            ->active()
            ->ordered()
            ->get();

        return Inertia::render('CMS/Materials/Index', [
            'materials' => $materials,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'search', 'active']),
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $categories = MaterialCategoryModel::forCompany($companyId)
            ->active()
            ->ordered()
            ->get();

        return Inertia::render('CMS/Materials/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:cms_material_categories,id',
            'code' => 'required|string|max:50|unique:cms_materials,code',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'current_price' => 'required|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:200',
            'supplier_code' => 'nullable|string|max:100',
            'lead_time_days' => 'nullable|integer|min:0',
            'specifications' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;

        $material = $this->materialService->createMaterial([
            ...$validated,
            'company_id' => $companyId,
        ]);

        return redirect()
            ->route('cms.materials.index')
            ->with('success', 'Material created successfully');
    }

    public function edit(MaterialModel $material): Response
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($material->company_id !== $companyId) abort(403);

        $material->load('category');

        $categories = MaterialCategoryModel::forCompany($companyId)
            ->active()
            ->ordered()
            ->get();

        return Inertia::render('CMS/Materials/Edit', [
            'material' => $material,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, MaterialModel $material)
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($material->company_id !== $companyId) abort(403);

        $validated = $request->validate([
            'category_id' => 'nullable|exists:cms_material_categories,id',
            'code' => 'required|string|max:50|unique:cms_materials,code,' . $material->id,
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'current_price' => 'required|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:200',
            'supplier_code' => 'nullable|string|max:100',
            'lead_time_days' => 'nullable|integer|min:0',
            'specifications' => 'nullable|array',
            'is_active' => 'boolean',
            'price_change_reason' => 'nullable|string|max:500',
        ]);

        $this->materialService->updateMaterial($material, [
            ...$validated,
            'changed_by' => $request->user()->cmsUser->id,
        ]);

        return redirect()
            ->route('cms.materials.index')
            ->with('success', 'Material updated successfully');
    }

    public function destroy(MaterialModel $material)
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($material->company_id !== $companyId) abort(403);

        try {
            $this->materialService->deleteMaterial($material);
            return back()->with('success', 'Material deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function priceHistory(MaterialModel $material): Response
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($material->company_id !== $companyId) abort(403);

        $history = $material->priceHistory()
            ->with('changedBy.user')
            ->orderBy('effective_date', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/Materials/PriceHistory', [
            'material' => $material,
            'history' => $history,
        ]);
    }

    public function bulkUpdatePrices(Request $request)
    {
        $validated = $request->validate([
            'material_ids' => 'required|array',
            'material_ids.*' => 'exists:cms_materials,id',
            'percentage_change' => 'required|numeric',
            'reason' => 'required|string|max:500',
        ]);

        $updated = $this->materialService->bulkUpdatePrices(
            $validated['material_ids'],
            $validated['percentage_change'],
            $request->user()->cmsUser->id,
            $validated['reason']
        );

        return back()->with('success', "{$updated} materials updated successfully");
    }
}
