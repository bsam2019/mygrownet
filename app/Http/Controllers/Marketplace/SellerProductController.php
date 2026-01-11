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
            $message = $seller->kyc_status === 'pending' 
                ? 'Your account is under review. You can add products once your verification is approved.'
                : 'Your account must be verified before adding products.';
            
            return redirect()->route('marketplace.seller.products.index')
                ->withErrors(['seller' => $message]);
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
            'images' => 'nullable|array|max:8',
            'images.*' => 'image|max:5120',
            'media_ids' => 'nullable|array|max:8',
            'media_ids.*' => 'integer|exists:marketplace_seller_media,id',
        ]);

        // Ensure at least one image source is provided
        $hasImages = !empty($request->file('images'));
        $hasMediaIds = !empty($request->media_ids);
        
        if (!$hasImages && !$hasMediaIds) {
            return back()->withErrors(['images' => 'At least one product image is required.']);
        }

        // Process new uploaded images
        $images = $this->processProductImages($request->file('images', []), $seller);
        
        // Add images from media library references (no re-upload needed)
        if ($hasMediaIds) {
            $mediaItems = \App\Models\MarketplaceSellerMedia::whereIn('id', $request->media_ids)
                ->where('seller_id', $seller->id)
                ->get();
            
            foreach ($mediaItems as $media) {
                $images[] = $media->path;
            }
        }

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
            'images' => 'nullable|array|max:8',
            'images.*' => 'image|max:5120',
            'existing_images' => 'nullable|array|max:8',
            'existing_images.*' => 'string',
            'media_ids' => 'nullable|array|max:8',
            'media_ids.*' => 'integer|exists:marketplace_seller_media,id',
        ]);

        // Start with existing images that weren't removed
        $images = $request->existing_images ?? [];

        // Add new uploaded images
        $newImages = $this->processProductImages($request->file('images', []), $seller);
        $images = array_merge($images, $newImages);
        
        // Add images from media library references (no re-upload needed)
        if (!empty($request->media_ids)) {
            $mediaItems = \App\Models\MarketplaceSellerMedia::whereIn('id', $request->media_ids)
                ->where('seller_id', $seller->id)
                ->get();
            
            foreach ($mediaItems as $media) {
                $images[] = $media->path;
            }
        }

        // Ensure at least one image
        if (empty($images)) {
            return back()->withErrors(['images' => 'At least one product image is required.']);
        }

        $this->productService->update($id, [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'price' => (int) ($validated['price'] * 100),
            'compare_price' => $validated['compare_price'] ? (int) ($validated['compare_price'] * 100) : null,
            'stock_quantity' => $validated['stock_quantity'],
            'images' => array_values($images),
            'status' => in_array($product->status, ['rejected', 'changes_requested']) ? 'pending' : $product->status,
        ]);

        return redirect()->route('marketplace.seller.products.index')
            ->with('success', 'Product updated and resubmitted for review.');
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
     * Submit an appeal for a rejected product
     */
    public function appeal(Request $request, int $id)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);
        $product = $this->productService->getById($id);

        if (!$seller || !$product || $product->seller_id !== $seller->id) {
            abort(404);
        }

        if ($product->status !== 'rejected') {
            return back()->withErrors(['appeal' => 'Only rejected products can be appealed.']);
        }

        if ($product->appeal_message) {
            return back()->withErrors(['appeal' => 'This product has already been appealed.']);
        }

        $validated = $request->validate([
            'appeal_message' => 'required|string|min:20|max:1000',
        ]);

        $this->productService->update($id, [
            'appeal_message' => $validated['appeal_message'],
            'appealed_at' => now(),
        ]);

        return back()->with('success', 'Appeal submitted. Our team will review your product again.');
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
