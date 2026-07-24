<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\ProductService;
use App\Domain\BizBoost\Services\BusinessService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private BusinessService $businessService,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $categories = $this->productService->getCategories($business->id);

        return Inertia::render('BizBoost/Categories/Index', [
            'categories' => $categories,
            'stats' => $this->productService->getCategoryStats($business->id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|integer|exists:biz_boost_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->productService->createCategory($business->id, $validated, $request->file('image'));

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|integer|exists:biz_boost_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->productService->updateCategory($business->id, $id, $validated, $request->file('image'));

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->productService->deleteCategory($business->id, $id);

        return back()->with('success', 'Category deleted successfully.');
    }
}