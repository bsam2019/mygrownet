<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Repositories\ProductRepositoryInterface;
use App\Domain\GrowMart\Repositories\CategoryRepositoryInterface;
use App\Domain\GrowMart\Repositories\WarehouseRepositoryInterface;
use App\Models\GrowMart\GrowMartProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly WarehouseRepositoryInterface $warehouseRepository,
    ) {}

    public function index()
    {
        $products = $this->productRepository->findAll(['per_page' => 20]);

        return Inertia::render('GrowMart/Admin/Products/Index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        return Inertia::render('GrowMart/Admin/Products/Create', [
            'categories' => $this->categoryRepository->findParentCategories(),
            'warehouses' => $this->warehouseRepository->findActive(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:growmart_products,slug',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:growmart_categories,id',
            'status' => 'required|in:active,out_of_stock,discontinued',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'warehouse_id' => 'nullable|exists:growmart_warehouses,id',
            'initial_stock' => 'nullable|integer|min:0',
        ]);

        $product = $this->productRepository->save([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? '',
            'unit' => $validated['unit'],
            'price' => (int) round($validated['price'] * 100),
            'compare_price' => isset($validated['compare_price']) ? (int) round($validated['compare_price'] * 100) : null,
            'category_id' => $validated['category_id'],
            'status' => $validated['status'],
        ]);

        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $index => $imagePath) {
                $storedPath = $this->storeImage($imagePath);
                if ($storedPath) {
                    GrowMartProductImage::create([
                        'product_id' => $product['id'],
                        'path' => $storedPath,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        if (!empty($validated['warehouse_id']) && $validated['initial_stock'] > 0) {
            \App\Models\GrowMart\GrowMartInventory::create([
                'product_id' => $product['id'],
                'warehouse_id' => $validated['warehouse_id'],
                'quantity' => $validated['initial_stock'],
            ]);
        }

        return redirect()->route('admin.growmart.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(int $id)
    {
        $product = $this->productRepository->findById($id);

        return Inertia::render('GrowMart/Admin/Products/Edit', [
            'product' => $product,
            'categories' => $this->categoryRepository->findParentCategories(),
            'warehouses' => $this->warehouseRepository->findActive(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:growmart_products,slug,' . $id,
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:growmart_categories,id',
            'status' => 'required|in:active,out_of_stock,discontinued',
            'images' => 'nullable|array',
            'images.*' => 'string',
        ]);

        $this->productRepository->update($id, [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? '',
            'unit' => $validated['unit'],
            'price' => (int) round($validated['price'] * 100),
            'compare_price' => isset($validated['compare_price']) ? (int) round($validated['compare_price'] * 100) : null,
            'category_id' => $validated['category_id'],
            'status' => $validated['status'],
        ]);

        if ($request->has('images')) {
            $product = $this->productRepository->findById($id);
            foreach ($product['images'] ?? [] as $img) {
                Storage::disk('public')->delete($img['path']);
            }
            GrowMartProductImage::where('product_id', $id)->delete();
            foreach ($validated['images'] as $index => $imagePath) {
                $storedPath = $this->storeImage($imagePath);
                if ($storedPath) {
                    GrowMartProductImage::create([
                        'product_id' => $id,
                        'path' => $storedPath,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        return redirect()->route('admin.growmart.products.index')
            ->with('success', 'Product updated successfully.');
    }

    private function storeImage(string $image): ?string
    {
        if (str_starts_with($image, 'data:image')) {
            $base64 = explode(',', $image)[1] ?? null;
            if (!$base64) return null;
            $decoded = base64_decode($base64);
            if ($decoded === false) return null;
            $ext = match (true) {
                str_contains($image, 'image/jpeg') => 'jpg',
                str_contains($image, 'image/png') => 'png',
                str_contains($image, 'image/webp') => 'webp',
                default => 'jpg',
            };
            $filename = 'growmart/products/' . Str::random(20) . '.' . $ext;
            Storage::disk('public')->put($filename, $decoded);
            return $filename;
        }
        return $image;
    }

    public function destroy(int $id)
    {
        $product = $this->productRepository->findById($id);
        foreach ($product['images'] ?? [] as $image) {
            Storage::disk('public')->delete($image['path']);
        }
        GrowMartProductImage::where('product_id', $id)->delete();
        \App\Models\GrowMart\GrowMartInventory::where('product_id', $id)->delete();
        $this->productRepository->delete($id);

        return redirect()->route('admin.growmart.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
