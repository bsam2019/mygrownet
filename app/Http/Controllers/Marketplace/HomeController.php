<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Domain\Marketplace\Services\ProductService;
use App\Domain\Marketplace\Services\SellerService;
use App\Models\MarketplaceCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private SellerService $sellerService,
    ) {}

    public function index(Request $request)
    {
        $featuredProducts = $this->productService->getFeaturedProducts(8);
        $categories = $this->productService->getCategories();
        
        $latestProducts = $this->productService->getActiveProducts(
            ['sort' => 'newest'],
            12
        );

        return Inertia::render('Marketplace/Home', [
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'categories' => $categories,
            'provinces' => $this->sellerService->getProvinces(),
        ]);
    }

    public function search(Request $request)
    {
        $filters = [
            'search' => $request->q,
            'category_id' => $request->category,
            'province' => $request->province,
            'min_price' => $request->min_price ? $request->min_price * 100 : null,
            'max_price' => $request->max_price ? $request->max_price * 100 : null,
            'sort' => $request->sort ?? 'newest',
        ];

        $products = $this->productService->getActiveProducts($filters, 24);
        $categories = $this->productService->getCategories();

        return Inertia::render('Marketplace/Search', [
            'products' => $products,
            'categories' => $categories,
            'provinces' => $this->sellerService->getProvinces(),
            'filters' => [
                'q' => $request->q ?? '',
                'category' => $request->category ?? '',
                'province' => $request->province ?? '',
                'min_price' => $request->min_price ?? '',
                'max_price' => $request->max_price ?? '',
                'sort' => $request->sort ?? 'newest',
            ],
        ]);
    }

    public function category(Request $request, string $slug)
    {
        $category = MarketplaceCategory::where('slug', $slug)->firstOrFail();
        
        $filters = [
            'category_id' => $category->id,
            'province' => $request->province,
            'sort' => $request->sort ?? 'newest',
        ];

        $products = $this->productService->getActiveProducts($filters, 24);
        $categories = $this->productService->getCategories();

        return Inertia::render('Marketplace/Category', [
            'category' => $category,
            'products' => $products,
            'categories' => $categories,
            'provinces' => $this->sellerService->getProvinces(),
            'filters' => [
                'province' => $request->province ?? '',
                'sort' => $request->sort ?? 'newest',
            ],
        ]);
    }

    public function product(string $slug)
    {
        $product = $this->productService->getBySlug($slug);
        
        if (!$product || $product->status !== 'active') {
            abort(404);
        }

        // Increment views
        $this->productService->incrementViews($product->id);

        // Get related products
        $relatedProducts = $this->productService->getActiveProducts(
            ['category_id' => $product->category_id],
            4
        )->items();

        return Inertia::render('Marketplace/Product', [
            'product' => $product,
            'relatedProducts' => collect($relatedProducts)->filter(fn($p) => $p->id !== $product->id)->values(),
        ]);
    }

    public function seller(int $id)
    {
        $seller = $this->sellerService->getById($id);
        
        if (!$seller || !$seller->is_active) {
            abort(404);
        }

        $products = $this->productService->getActiveProducts(
            ['seller_id' => $seller->id],
            24
        );

        return Inertia::render('Marketplace/Seller', [
            'seller' => $seller,
            'products' => $products,
        ]);
    }
}
