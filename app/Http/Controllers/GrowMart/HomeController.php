<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Repositories\CategoryRepositoryInterface;
use App\Domain\GrowMart\Repositories\ProductRepositoryInterface;
use App\Domain\GrowMart\Services\CartService;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CartService $cartService,
    ) {}

    public function index()
    {
        $categories = $this->categoryRepository->findActive();

        $featuredProducts = $this->productRepository->findFeatured();
        $featuredProducts = array_map(fn($p) => $this->mapProduct($p), $featuredProducts);

        $cartSummary = $this->cartService->getSummary(auth()->id());

        return Inertia::render('GrowMart/Home', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'cartCount' => $cartSummary['item_count'],
        ]);
    }

    public function terms()
    {
        return Inertia::render('GrowMart/Terms', [
            'contactEmail' => config('growmart.contact.email', 'support@growmart.co.zm'),
        ]);
    }

    public function refund()
    {
        return Inertia::render('GrowMart/Refund', [
            'contactEmail' => config('growmart.contact.email', 'support@growmart.co.zm'),
        ]);
    }

    public function contact()
    {
        return Inertia::render('GrowMart/Contact', [
            'contact' => config('growmart.contact'),
        ]);
    }

    private function mapProduct(array $product): array
    {
        return [
            'id' => $product['id'],
            'name' => $product['name'],
            'slug' => $product['slug'],
            'unit' => $product['unit'] ?? '',
            'price' => $product['price'],
            'price_formatted' => 'K' . number_format($product['price'] / 100, 2),
            'compare_price' => $product['compare_price'] ?? null,
            'compare_price_formatted' => isset($product['compare_price']) ? 'K' . number_format($product['compare_price'] / 100, 2) : null,
            'has_discount' => isset($product['compare_price']) && $product['compare_price'] > $product['price'],
            'discount_percentage' => isset($product['compare_price']) && $product['compare_price'] > $product['price']
                ? (int) round((1 - $product['price'] / $product['compare_price']) * 100) : 0,
            'category' => $product['category']['name'] ?? null,
            'image' => $product['images'][0]['path'] ?? null,
            'stock' => (int) ($product['inventory_sum_quantity'] ?? 0),
        ];
    }
}
