<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Materials\Services\MaterialService;
use App\Infrastructure\Persistence\Eloquent\CMS\MaterialCategoryModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaterialCategoryController extends Controller
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

        $categories = MaterialCategoryModel::withCount('materials')
            ->forCompany($companyId)
            ->ordered()
            ->get();

        return Inertia::render('CMS/Materials/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;

        $category = $this->materialService->createCategory([
            ...$validated,
            'company_id' => $companyId,
        ]);

        return back()->with('success', 'Category created successfully');
    }

    public function update(Request $request, MaterialCategoryModel $category)
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($category->company_id !== $companyId) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $this->materialService->updateCategory($category, $validated);

        return back()->with('success', 'Category updated successfully');
    }

    public function destroy(MaterialCategoryModel $category)
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($category->company_id !== $companyId) abort(403);

        try {
            $this->materialService->deleteCategory($category);
            return back()->with('success', 'Category deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
