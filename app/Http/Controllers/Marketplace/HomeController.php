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
        // Determine which category IDs to filter by
        $categoryIds = null;
        
        if ($request->subcategory) {
            // If subcategory is selected, filter by that specific subcategory
            $categoryIds = [(int) $request->subcategory];
        } elseif ($request->category) {
            // If only parent category is selected, include parent and all its children
            $parentId = (int) $request->category;
            $childIds = MarketplaceCategory::where('parent_id', $parentId)
                ->pluck('id')
                ->toArray();
            $categoryIds = array_merge([$parentId], $childIds);
        }

        $filters = [
            'search' => $request->q,
            'category_ids' => $categoryIds,
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
                'subcategory' => $request->subcategory ?? '',
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
        
        // If this is a parent category, also include products from subcategories
        $categoryIds = [$category->id];
        $subcategories = [];
        
        if (is_null($category->parent_id)) {
            // This is a parent category - get subcategories
            $subcategories = MarketplaceCategory::where('parent_id', $category->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
            
            // Check if filtering by specific subcategory
            if ($request->subcategory) {
                $categoryIds = [(int) $request->subcategory];
            } else {
                // Include all subcategory IDs for product filtering
                $categoryIds = array_merge($categoryIds, $subcategories->pluck('id')->toArray());
            }
        }
        
        $filters = [
            'category_ids' => $categoryIds,
            'province' => $request->province,
            'sort' => $request->sort ?? 'newest',
        ];

        $products = $this->productService->getActiveProducts($filters, 24);
        $categories = $this->productService->getCategories();

        return Inertia::render('Marketplace/Category', [
            'category' => $category,
            'products' => $products,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'provinces' => $this->sellerService->getProvinces(),
            'filters' => [
                'province' => $request->province ?? '',
                'sort' => $request->sort ?? 'newest',
                'subcategory' => $request->subcategory ?? '',
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

    public function seller(Request $request, string $id)
    {
        $seller = $this->sellerService->getById((int) $id);
        
        if (!$seller || !$seller->is_active) {
            abort(404);
        }

        $products = $this->productService->getActiveProducts(
            ['seller_id' => $seller->id],
            24
        );

        // Get follower count
        $followersCount = $seller->followers()->count();
        
        // Check if current user is following
        $isFollowing = false;
        if ($request->user()) {
            $isFollowing = $seller->followers()
                ->where('user_id', $request->user()->id)
                ->exists();
        }

        // Get review stats
        $reviewCount = $seller->reviews()->where('is_approved', true)->count();
        
        // Get rating breakdown
        $ratingBreakdown = $seller->reviews()
            ->where('is_approved', true)
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $breakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $ratingBreakdown[$i] ?? 0;
            $breakdown[$i] = [
                'count' => $count,
                'percentage' => $reviewCount > 0 ? round(($count / $reviewCount) * 100) : 0,
            ];
        }

        // Get recent reviews
        $reviews = $seller->reviews()
            ->with(['buyer:id,name', 'product:id,name,slug'])
            ->where('is_approved', true)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Get product categories for this seller
        $categories = $seller->products()
            ->where('status', 'active')
            ->with('category:id,name,slug')
            ->get()
            ->pluck('category')
            ->unique('id')
            ->map(function ($cat) use ($seller) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'count' => $seller->products()
                        ->where('status', 'active')
                        ->where('category_id', $cat->id)
                        ->count(),
                ];
            })
            ->values();

        return Inertia::render('Marketplace/Seller', [
            'seller' => array_merge($seller->toArray(), [
                'followers_count' => $followersCount,
                'review_count' => $reviewCount,
            ]),
            'products' => $products,
            'isFollowing' => $isFollowing,
            'ratingBreakdown' => $breakdown,
            'reviews' => $reviews,
            'categories' => $categories,
        ]);
    }

    public function helpCenter()
    {
        return Inertia::render('Marketplace/HelpCenter');
    }

    public function buyerProtection()
    {
        return Inertia::render('Marketplace/BuyerProtection');
    }

    public function sellerGuide()
    {
        return Inertia::render('Marketplace/SellerGuide');
    }

    public function about()
    {
        return Inertia::render('Marketplace/About');
    }

    public function terms()
    {
        return Inertia::render('Marketplace/Terms');
    }

    public function privacy()
    {
        return Inertia::render('Marketplace/Privacy');
    }
}
