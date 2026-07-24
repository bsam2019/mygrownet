<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\ProductService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\CategoryRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private BusinessService $businessService,
        private CategoryRepositoryInterface $categoryRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $filters = $request->only(['search', 'category', 'status', 'featured', 'sort']);
        $products = $this->productService->getProducts($business->id, $filters);

        return Inertia::render('BizBoost/Products/Index', [
            'products' => $products,
            'categories' => $this->categoryRepo->findByBusiness($business->id),
            'filters' => $filters,
            'stats' => $this->productService->getProductStats($business->id),
        ]);
    }

    public function create(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Products/Create', [
            'categories' => $this->categoryRepo->findByBusiness($business->id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:biz_boost_products,sku,NULL,id,business_id,' . $request->user()->id,
            'barcode' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:biz_boost_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_digital' => 'boolean',
            'is_service' => 'boolean',
            'track_inventory' => 'boolean',
            'quantity' => 'required_if:track_inventory,true|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1',
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|max:10',
            'dimensions' => 'nullable|array',
            'variants' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'status' => 'required|in:active,draft,archived',
            'featured' => 'boolean',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'meta_data' => 'nullable|array',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->productService->createProduct($business->id, $validated, $request->file('images', []));

        return redirect()->route('bizboost.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $product = $this->productService->getProductById($business->id, $id);

        if (!$product) {
            abort(404);
        }

        return Inertia::render('BizBoost/Products/Edit', [
            'product' => $product->toArray(),
            'categories' => $this->categoryRepo->findByBusiness($business->id),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:biz_boost_products,sku,' . $id . ',id,business_id,' . $request->user()->id,
            'barcode' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:biz_boost_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_digital' => 'boolean',
            'is_service' => 'boolean',
            'track_inventory' => 'boolean',
            'quantity' => 'required_if:track_inventory,true|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1',
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|max:10',
            'dimensions' => 'nullable|array',
            'variants' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'existing_images' => 'nullable|array',
            'status' => 'required|in:active,draft,archived',
            'featured' => 'boolean',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'meta_data' => 'nullable|array',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->productService->updateProduct($business->id, $id, $validated, $request->file('images', []));

        return redirect()->route('bizboost.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->productService->deleteProduct($business->id, $id);

        return redirect()->route('bizboost.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function duplicate(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->productService->duplicateProduct($business->id, $id);

        return redirect()->route('bizboost.products.index')
            ->with('success', 'Product duplicated successfully.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,archive,activate,feature,unfeature',
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $count = $this->productService->bulkAction($business->id, $validated['action'], $validated['ids']);

        return back()->with('success', "{$count} product(s) {$validated['action']}d successfully.");
    }
}