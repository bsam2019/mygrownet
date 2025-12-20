<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Domain\Marketplace\Services\SellerService;
use App\Domain\Marketplace\Services\ProductService;
use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SellerProductController extends Controller
{
    public function __construct(
        private SellerService $sellerService,
        private ProductService $productService,
        private ImageProcessingService $imageService,
    ) {}

    public function index(Request $request)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller) {
            return redirect()->route('marketplace.seller.register');
        }

        $filters = [
            'status' => $request->status,
            'category_id' => $request->category,
        ];

        $products = $this->productService->getBySeller($seller->id, $filters, 20);
        $categories = $this->productService->getCategories();

        return Inertia::render('Marketplace/Seller/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'status' => $request->status ?? '',
                'category' => $request->category ?? '',
            ],
        ]);
    }

    public function create(Request $request)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller) {
            return redirect()->route('marketplace.seller.register');
        }

        if (!$seller->canAcceptOrders()) {
            return redirect()->route('marketplace.seller.products.index')
                ->withErrors(['seller' => 'Your account must be verified before adding products.']);
        }

        $categories = $this->productService->getCategories();
        $imageGuidelines = ImageProcessingService::getGuidelines();

        return Inertia::render('Marketplace/Seller/Products/Create', [
            'categories' => $categories,
            'imageGuidelines' => $imageGuidelines,
        ]);
    }

    public function store(Request $request)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller || !$seller->canAcceptOrders()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:marketplace_categories,id',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:1|max:1000000',
            'compare_price' => 'nullable|numeric|min:1|max:1000000',
            'stock_quantity' => 'required|integer|min:0|max:10000',
            'images' => 'required|array|min:1|max:8',
            'images.*' => 'image|max:5120',
        ]);

        // Process images based on current phase
        $images = $this->processProductImages($request->file('images', []), $seller);

        $product = $this->productService->create($seller->id, [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'price' => (int) ($validated['price'] * 100),
            'compare_price' => $validated['compare_price'] ? (int) ($validated['compare_price'] * 100) : null,
            'stock_quantity' => $validated['stock_quantity'],
            'images' => $images,
        ]);

        return redirect()->route('marketplace.seller.products.index')
            ->with('success', 'Product submitted for review.');
    }

    public function edit(Request $request, int $id)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);
        $product = $this->productService->getById($id);

        if (!$seller || !$product || $product->seller_id !== $seller->id) {
            abort(404);
        }

        $categories = $this->productService->getCategories();

        return Inertia::render('Marketplace/Seller/Products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);
        $product = $this->productService->getById($id);

        if (!$seller || !$product || $product->seller_id !== $seller->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:marketplace_categories,id',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:1|max:1000000',
            'compare_price' => 'nullable|numeric|min:1|max:1000000',
            'stock_quantity' => 'required|integer|min:0|max:10000',
            'new_images' => 'nullable|array|max:8',
            'new_images.*' => 'image|max:5120',
            'remove_images' => 'nullable|array',
        ]);

        $images = $product->images ?? [];

        // Remove specified images
        foreach ($request->remove_images ?? [] as $imageToRemove) {
            if (($key = array_search($imageToRemove, $images)) !== false) {
                Storage::disk('public')->delete($imageToRemove);
                unset($images[$key]);
            }
        }

        // Add new images
        $newImages = $this->processProductImages($request->file('new_images', []), $seller);
        $images = array_merge($images, $newImages);

        $this->productService->update($id, [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'price' => (int) ($validated['price'] * 100),
            'compare_price' => $validated['compare_price'] ? (int) ($validated['compare_price'] * 100) : null,
            'stock_quantity' => $validated['stock_quantity'],
            'images' => array_values($images),
            'status' => $product->status === 'rejected' ? 'pending' : $product->status,
        ]);

        return redirect()->route('marketplace.seller.products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);
        $product = $this->productService->getById($id);

        if (!$seller || !$product || $product->seller_id !== $seller->id) {
            abort(404);
        }

        $this->productService->delete($id);

        return redirect()->route('marketplace.seller.products.index')
            ->with('success', 'Product deleted.');
    }

    /**
     * Process product images based on current phase configuration
     */
    private function processProductImages(array $files, $seller): array
    {
        $phase = config('marketplace.images.processing_phase', 'phase2');
        $images = [];

        foreach ($files as $file) {
            $result = match ($phase) {
                'mvp' => [
                    'original' => $this->imageService->uploadRaw($file),
                ],
                'phase2' => $this->imageService->uploadOptimized($file),
                'phase3' => $this->imageService->uploadWithBackgroundRemoval(
                    $file,
                    'marketplace/products',
                    $seller->trust_level === 'top' || $seller->trust_level === 'trusted'
                ),
                'phase4' => $this->imageService->uploadAdvanced($file, 'marketplace/products', [
                    'optimize' => true,
                    'remove_background' => $seller->trust_level === 'top',
                    'add_watermark' => config('marketplace.images.watermark.enabled', false),
                    'enhance' => config('marketplace.images.enhancement.enabled', false),
                    'is_featured' => false, // Can be determined by product status
                ]),
                default => [
                    'original' => $this->imageService->uploadRaw($file),
                ],
            };

            // Store the primary image path (use 'medium' for display, 'original' as fallback)
            $images[] = $result['medium'] ?? $result['original'];
        }

        return $images;
    }
}
