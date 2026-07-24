<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Repositories\ProductRepositoryInterface;
use App\Domain\GrowMart\Repositories\CategoryRepositoryInterface;
use App\Domain\GrowMart\Repositories\ReviewRepositoryInterface;
use App\Domain\GrowMart\Repositories\WishlistRepositoryInterface;
use App\Domain\GrowMart\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ReviewRepositoryInterface $reviewRepository,
        private readonly WishlistRepositoryInterface $wishlistRepository,
        private readonly CartService $cartService,
    ) {}

    public function index(Request $request)
    {
        $products = $this->productRepository->findActive([
            'q' => $request->q,
            'category' => $request->category,
            'sort' => $request->sort,
            'per_page' => 20,
        ]);

        $formatted = array_map(fn($p) => [
            'id' => $p['id'],
            'name' => $p['name'],
            'slug' => $p['slug'],
            'unit' => $p['unit'] ?? '',
            'price' => $p['price'],
            'price_formatted' => 'K' . number_format($p['price'] / 100, 2),
            'compare_price' => $p['compare_price'] ?? null,
            'compare_price_formatted' => isset($p['compare_price']) ? 'K' . number_format($p['compare_price'] / 100, 2) : null,
            'has_discount' => isset($p['compare_price']) && $p['compare_price'] > $p['price'],
            'discount_percentage' => isset($p['compare_price']) && $p['compare_price'] > $p['price']
                ? (int) round((1 - $p['price'] / $p['compare_price']) * 100) : 0,
            'category' => $p['category']['name'] ?? null,
            'image' => $p['images'][0]['path'] ?? null,
            'stock' => (int) ($p['inventory_sum_quantity'] ?? 0),
        ], $products['data'] ?? []);

        $categories = $this->categoryRepository->findActive();

        $cartSummary = $this->cartService->getSummary(auth()->id());
        $wishlistIds = auth()->check() ? $this->wishlistRepository->findProductIdsByUser(auth()->id()) : [];

        return Inertia::render('GrowMart/Products/Index', [
            'products' => $formatted,
            'categories' => $categories,
            'filters' => $request->only(['q', 'category', 'sort']),
            'cartCount' => $cartSummary['item_count'],
            'wishlistProductIds' => $wishlistIds,
        ]);
    }

    public function show(string $slug)
    {
        $product = $this->productRepository->findBySlug($slug);
        if (!$product || ($product['status'] ?? '') !== 'active') {
            abort(404);
        }

        $relatedProducts = $this->productRepository->findRelated($product['id'], $product['category_id']);

        $cartSummary = $this->cartService->getSummary(auth()->id());

        $reviewData = $this->reviewRepository->findByProduct($product['id']);
        $averageRating = $this->reviewRepository->getAverageRating($product['id']);

        $reviews = array_map(fn($r) => [
            'id' => $r['id'],
            'user_name' => $r['user']['name'] ?? 'Unknown',
            'rating' => $r['rating'],
            'review_text' => $r['review_text'] ?? '',
            'created_at' => \Carbon\Carbon::parse($r['created_at'])->diffForHumans(),
        ], $reviewData);

        $wishlisted = auth()->check()
            ? $this->wishlistRepository->isWishlisted(auth()->id(), $product['id'])
            : false;

        $totalStock = collect($product['inventory'] ?? [])->sum('quantity');

        return Inertia::render('GrowMart/Products/Show', [
            'product' => [
                'id' => $product['id'],
                'name' => $product['name'],
                'slug' => $product['slug'],
                'description' => $product['description'] ?? '',
                'unit' => $product['unit'] ?? '',
                'price' => $product['price'],
                'price_formatted' => 'K' . number_format($product['price'] / 100, 2),
                'compare_price' => $product['compare_price'] ?? null,
                'compare_price_formatted' => isset($product['compare_price']) ? 'K' . number_format($product['compare_price'] / 100, 2) : null,
                'has_discount' => isset($product['compare_price']) && $product['compare_price'] > $product['price'],
                'discount_percentage' => isset($product['compare_price']) && $product['compare_price'] > $product['price']
                    ? (int) round((1 - $product['price'] / $product['compare_price']) * 100) : 0,
                'category' => $product['category']['name'] ?? null,
                'images' => array_map(fn($i) => ['id' => $i['id'], 'path' => $i['path']], $product['images'] ?? []),
                'stock' => (int) $totalStock,
                'wishlisted' => $wishlisted,
            ],
            'relatedProducts' => array_map(fn($p) => [
                'id' => $p['id'],
                'name' => $p['name'],
                'slug' => $p['slug'],
                'unit' => $p['unit'] ?? '',
                'price' => $p['price'],
                'price_formatted' => 'K' . number_format($p['price'] / 100, 2),
                'image' => $p['images'][0]['path'] ?? null,
            ], $relatedProducts),
            'cartCount' => $cartSummary['item_count'],
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'totalReviews' => count($reviews),
        ]);
    }
}
