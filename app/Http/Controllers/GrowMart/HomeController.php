<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Models\GrowMart\GrowMartCategory;
use App\Models\GrowMart\GrowMartProduct;
use App\Domain\GrowMart\Services\CartService;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    public function index()
    {
        $categories = GrowMartCategory::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = GrowMartProduct::with(['category', 'images'])
            ->where('status', 'active')
            ->latest()
            ->take(12)
            ->get()
            ->map(fn($p) => $this->mapProduct($p));

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

    private function mapProduct($product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'unit' => $product->unit,
            'price' => $product->price,
            'price_formatted' => 'K' . number_format($product->price / 100, 2),
            'compare_price' => $product->compare_price,
            'compare_price_formatted' => $product->compare_price ? 'K' . number_format($product->compare_price / 100, 2) : null,
            'has_discount' => $product->compare_price && $product->compare_price > $product->price,
            'discount_percentage' => $product->compare_price && $product->compare_price > $product->price
                ? (int) round((1 - $product->price / $product->compare_price) * 100) : 0,
            'category' => $product->category?->name,
            'image' => $product->images->first()?->path ?? null,
            'stock' => (int) $product->inventory()->sum('quantity'),
        ];
    }
}
