<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostCategoryModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostProductModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostProductImageModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request)
    {
        $business = $this->getBusiness($request);
        
        $products = $business->products()
            ->with(['primaryImage', 'categoryModel'])
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
            )
            ->when($request->category_id, fn($q, $catId) => 
                $q->where('category_id', $catId)
            )
            ->when($request->category && !$request->category_id, fn($q, $cat) => 
                $q->where('category', $cat)
            )
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Get categories from the new model
        $categories = BizBoostCategoryModel::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        // Also get legacy string-based categories
        $legacyCategories = $business->products()
            ->whereNotNull('category')
            ->whereNull('category_id')
            ->distinct()
            ->pluck('category');

        return Inertia::render('BizBoost/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'legacyCategories' => $legacyCategories,
            'filters' => $request->only(['search', 'category', 'category_id', 'status']),
        ]);
    }

    public function create(Request $request)
    {
        $business = $this->getBusiness($request);
        
        // Check limit
        $result = $this->subscriptionService->canIncrement(
            $request->user(), 'products', 'bizboost'
        );

        if (!$result['allowed']) {
            return Inertia::render('BizBoost/FeatureUpgradeRequired', [
                'feature' => 'products',
                'message' => $result['reason'],
                'suggestedTier' => $result['suggested_tier'] ?? 'basic',
            ]);
        }

        // Get categories from the new model
        $categories = BizBoostCategoryModel::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        // Also get legacy string-based categories
        $legacyCategories = $business->products()
            ->whereNotNull('category')
            ->whereNull('category_id')
            ->distinct()
            ->pluck('category');

        return Inertia::render('BizBoost/Products/Create', [
            'categories' => $categories,
            'legacyCategories' => $legacyCategories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:bizboost_categories,id',
            'stock_quantity' => 'nullable|integer|min:0',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'attributes' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $business = $this->getBusiness($request);

        // Check limit again
        $result = $this->subscriptionService->canIncrement(
            $request->user(), 'products', 'bizboost'
        );

        if (!$result['allowed']) {
            return back()->with('error', $result['reason']);
        }

        // If category_id is provided, verify it belongs to this business
        if (!empty($validated['category_id'])) {
            $categoryExists = BizBoostCategoryModel::where('id', $validated['category_id'])
                ->where('business_id', $business->id)
                ->exists();
            if (!$categoryExists) {
                return back()->withErrors(['category_id' => 'Invalid category selected.']);
            }
        }

        $product = $business->products()->create([
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'category' => $validated['category'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'stock_quantity' => $validated['stock_quantity'] ?? null,
            'track_inventory' => $validated['track_inventory'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'is_featured' => $validated['is_featured'] ?? false,
            'attributes' => $validated['attributes'] ?? null,
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store("bizboost/products/{$business->id}", 'public');
                BizBoostProductImageModel::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'filename' => $image->getClientOriginalName(),
                    'file_size' => $image->getSize(),
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('bizboost.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $product = $business->products()->with('images')->findOrFail($id);

        return Inertia::render('BizBoost/Products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $product = $business->products()->with(['images', 'categoryModel'])->findOrFail($id);

        // Get categories from the new model
        $categories = BizBoostCategoryModel::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        // Also get legacy string-based categories
        $legacyCategories = $business->products()
            ->whereNotNull('category')
            ->whereNull('category_id')
            ->distinct()
            ->pluck('category');

        return Inertia::render('BizBoost/Products/Edit', [
            'product' => $product,
            'categories' => $categories,
            'legacyCategories' => $legacyCategories,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:bizboost_categories,id',
            'stock_quantity' => 'nullable|integer|min:0',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'attributes' => 'nullable|array',
        ]);

        $business = $this->getBusiness($request);

        // If category_id is provided, verify it belongs to this business
        if (!empty($validated['category_id'])) {
            $categoryExists = BizBoostCategoryModel::where('id', $validated['category_id'])
                ->where('business_id', $business->id)
                ->exists();
            if (!$categoryExists) {
                return back()->withErrors(['category_id' => 'Invalid category selected.']);
            }
        }

        $product = $business->products()->findOrFail($id);
        $product->update($validated);

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $product = $business->products()->findOrFail($id);

        // Delete images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return redirect()->route('bizboost.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function uploadImage(Request $request, int $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $business = $this->getBusiness($request);
        $product = $business->products()->findOrFail($id);

        $path = $request->file('image')->store("bizboost/products/{$business->id}", 'public');
        
        $image = BizBoostProductImageModel::create([
            'product_id' => $product->id,
            'path' => $path,
            'filename' => $request->file('image')->getClientOriginalName(),
            'file_size' => $request->file('image')->getSize(),
            'is_primary' => $product->images()->count() === 0,
            'sort_order' => $product->images()->count(),
        ]);

        return response()->json([
            'success' => true,
            'image' => $image,
        ]);
    }

    public function deleteImage(Request $request, int $id, int $imageId)
    {
        $business = $this->getBusiness($request);
        $product = $business->products()->findOrFail($id);
        $image = $product->images()->findOrFail($imageId);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Show category management page.
     */
    public function categories(Request $request)
    {
        $business = $this->getBusiness($request);
        
        // Get categories from the new model
        $categories = BizBoostCategoryModel::where('business_id', $business->id)
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
                'color' => $cat->color,
                'icon' => $cat->icon,
                'is_active' => $cat->is_active,
                'product_count' => $cat->products_count,
            ]);

        // Also get legacy string-based categories not yet migrated
        $legacyCategories = $business->products()
            ->whereNotNull('category')
            ->whereNull('category_id')
            ->selectRaw('category, COUNT(*) as product_count')
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->map(fn($cat) => [
                'id' => null,
                'name' => $cat->category,
                'slug' => Str::slug($cat->category),
                'description' => null,
                'color' => null,
                'icon' => null,
                'is_active' => true,
                'product_count' => $cat->product_count,
                'is_legacy' => true,
            ]);

        return Inertia::render('BizBoost/Products/Categories', [
            'categories' => $categories,
            'legacyCategories' => $legacyCategories,
        ]);
    }

    /**
     * Store a new category.
     */
    public function storeCategory(Request $request)
    {
        $business = $this->getBusiness($request);
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
        ]);

        // Check if category already exists
        $exists = BizBoostCategoryModel::where('business_id', $business->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A category with this name already exists.']);
        }

        $category = BizBoostCategoryModel::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'sort_order' => BizBoostCategoryModel::where('business_id', $business->id)->max('sort_order') + 1,
        ]);

        return back()->with('success', "Category '{$category->name}' created successfully.");
    }

    /**
     * Update a category.
     */
    public function updateCategory(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        
        $category = BizBoostCategoryModel::where('business_id', $business->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        // Check if name is taken by another category
        $exists = BizBoostCategoryModel::where('business_id', $business->id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A category with this name already exists.']);
        }

        $category->update($validated);

        return back()->with('success', "Category '{$category->name}' updated successfully.");
    }

    /**
     * Delete a category.
     */
    public function destroyCategory(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        
        $category = BizBoostCategoryModel::where('business_id', $business->id)
            ->findOrFail($id);

        $categoryName = $category->name;
        
        // Products will have category_id set to null due to nullOnDelete constraint
        $category->delete();

        return back()->with('success', "Category '{$categoryName}' deleted. Products are now uncategorized.");
    }

    /**
     * Migrate a legacy string-based category to the new model.
     */
    public function migrateLegacyCategory(Request $request)
    {
        $business = $this->getBusiness($request);
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        // Create the category if it doesn't exist
        $category = BizBoostCategoryModel::firstOrCreate(
            ['business_id' => $business->id, 'name' => $validated['name']],
            ['sort_order' => BizBoostCategoryModel::where('business_id', $business->id)->max('sort_order') + 1]
        );

        // Update all products with this legacy category name
        $updated = $business->products()
            ->where('category', $validated['name'])
            ->whereNull('category_id')
            ->update(['category_id' => $category->id]);

        return back()->with('success', "Migrated {$updated} product(s) to category '{$category->name}'.");
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }
}
