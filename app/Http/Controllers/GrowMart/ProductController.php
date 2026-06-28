<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartCategory;
use App\Domain\GrowMart\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    private function reviewData(int $productId): array
    {
        $reviews = \App\Models\GrowMart\GrowMartReview::with('user')
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'user_name' => $r->user->name,
                'rating' => $r->rating,
                'review_text' => $r->review_text,
                'created_at' => $r->created_at->diffForHumans(),
            ]);

        $avg = \App\Models\GrowMart\GrowMartReview::where('product_id', $productId)
            ->where('is_approved', true)
            ->avg('rating');

        return [
            'reviews' => $reviews,
            'average_rating' => $avg ? round($avg, 1) : 0,
            'total_reviews' => $reviews->count(),
        ];
    }

    private function wishlistProductIds(): array
    {
        if (!auth()->check()) return [];
        return \App\Models\GrowMart\GrowMartWishlistItem::where('user_id', auth()->id())
            ->pluck('product_id')
            ->toArray();
    }

    public function index(Request $request)
    {
        $query = GrowMartProduct::with(['category', 'images'])
            ->where('status', 'active');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $term = $request->q;
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category)
                  ->orWhere('parent_id', GrowMartCategory::where('slug', $request->category)->value('id'));
            });
        }

        $sort = $request->sort ?? 'latest';
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        $products = $query->paginate(20)->withQueryString();

        $formatted = $products->through(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'unit' => $p->unit,
            'price' => $p->price,
            'price_formatted' => 'K' . number_format($p->price / 100, 2),
            'compare_price' => $p->compare_price,
            'compare_price_formatted' => $p->compare_price ? 'K' . number_format($p->compare_price / 100, 2) : null,
            'has_discount' => $p->compare_price && $p->compare_price > $p->price,
            'discount_percentage' => $p->compare_price && $p->compare_price > $p->price
                ? (int) round((1 - $p->price / $p->compare_price) * 100) : 0,
            'category' => $p->category?->name,
            'image' => $p->images->first()?->path ?? null,
            'stock' => (int) $p->inventory()->sum('quantity'),
        ]);

        $categories = GrowMartCategory::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $cartSummary = $this->cartService->getSummary(auth()->id());

        return Inertia::render('GrowMart/Products/Index', [
            'products' => $formatted,
            'categories' => $categories,
            'filters' => $request->only(['q', 'category', 'sort']),
            'cartCount' => $cartSummary['item_count'],
            'wishlistProductIds' => $this->wishlistProductIds(),
        ]);
    }

    public function show(string $slug)
    {
        $product = GrowMartProduct::with(['category', 'images', 'inventory'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $relatedProducts = GrowMartProduct::with(['images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->take(6)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'unit' => $p->unit,
                'price' => $p->price,
                'price_formatted' => 'K' . number_format($p->price / 100, 2),
                'image' => $p->images->first()?->path ?? null,
            ]);

        $cartSummary = $this->cartService->getSummary(auth()->id());
        $reviewData = $this->reviewData($product->id);
        $wishlisted = auth()->check()
            ? \App\Models\GrowMart\GrowMartWishlistItem::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->exists()
            : false;

        return Inertia::render('GrowMart/Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'unit' => $product->unit,
                'price' => $product->price,
                'price_formatted' => 'K' . number_format($product->price / 100, 2),
                'compare_price' => $product->compare_price,
                'compare_price_formatted' => $product->compare_price ? 'K' . number_format($product->compare_price / 100, 2) : null,
                'has_discount' => $product->compare_price && $product->compare_price > $product->price,
                'discount_percentage' => $product->compare_price && $product->compare_price > $product->price
                    ? (int) round((1 - $product->price / $product->compare_price) * 100) : 0,
                'category' => $product->category?->name,
                'images' => $product->images->map(fn($i) => ['id' => $i->id, 'path' => $i->path]),
                'stock' => (int) $product->inventory->sum('quantity'),
                'wishlisted' => $wishlisted,
            ],
            'relatedProducts' => $relatedProducts,
            'cartCount' => $cartSummary['item_count'],
            'reviews' => $reviewData['reviews'],
            'averageRating' => $reviewData['average_rating'],
            'totalReviews' => $reviewData['total_reviews'],
        ]);
    }
}
