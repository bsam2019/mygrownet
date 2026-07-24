<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Domain\Marketplace\Services\ProductService;
use App\Domain\Marketplace\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private SellerService $sellerService,
    ) {}

    public function index(Request $request)
    {
        $featuredProducts = $this->addComputedFields(
            $this->productService->getFeaturedProducts(8)
        );
        $categories = $this->productService->getCategories();

        $latestProducts = $this->addComputedFields(
            $this->productService->getActiveProducts(
                ['sort' => 'newest'],
                12
            )
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
        $categoryIds = null;

        if ($request->subcategory) {
            $categoryIds = [(int) $request->subcategory];
        } elseif ($request->category) {
            $parentId = (int) $request->category;
            $childIds = DB::table('marketplace_categories')
                ->where('parent_id', $parentId)
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

        $products = $this->addComputedFields(
            $this->productService->getActiveProducts($filters, 24)
        );
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
        $category = DB::table('marketplace_categories')->where('slug', $slug)->first();

        if (!$category) {
            abort(404);
        }

        $categoryIds = [$category->id];
        $subcategories = [];

        if (is_null($category->parent_id)) {
            $subcategories = DB::table('marketplace_categories')
                ->where('parent_id', $category->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn($c) => (array) $c)
                ->toArray();

            if ($request->subcategory) {
                $categoryIds = [(int) $request->subcategory];
            } else {
                $categoryIds = array_merge($categoryIds, array_column($subcategories, 'id'));
            }
        }

        $filters = [
            'category_ids' => $categoryIds,
            'province' => $request->province,
            'sort' => $request->sort ?? 'newest',
        ];

        $products = $this->addComputedFields(
            $this->productService->getActiveProducts($filters, 24)
        );
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

        if (!$product || $product['status'] !== 'active') {
            abort(404);
        }

        $this->productService->incrementViews($product['id']);

        $relatedProducts = $this->addComputedFields(
            $this->productService->getActiveProducts(
                ['category_id' => $product['category_id']],
                4
            )
        );

        $ogImage = $this->computePrimaryImageUrl($product['images'] ?? [])
            ?: asset('images/marketplace-default-product.jpg');
        $ogTitle = $product['name'] . ' - MyGrowNet Marketplace';
        $ogDescription = \Str::limit(strip_tags($product['description']), 150);
        $ogUrl = route('marketplace.product', $product['slug']);
        $ogPrice = 'K' . number_format($product['price'] / 100, 2);

        return Inertia::render('Marketplace/Product', [
            'product' => $this->addComputedFieldsToProduct($product),
            'relatedProducts' => collect($relatedProducts)->filter(fn($p) => $p['id'] !== $product['id'])->values(),
            'meta' => [
                'title' => $ogTitle,
                'description' => $ogDescription,
                'image' => $ogImage,
                'url' => $ogUrl,
                'type' => 'product',
                'price' => $ogPrice,
                'currency' => 'ZMW',
            ],
        ]);
    }

    public function seller(Request $request, string $id)
    {
        $seller = $this->sellerService->getById((int) $id);

        if (!$seller || !$seller['is_active']) {
            abort(404);
        }

        $products = $this->addComputedFields(
            $this->productService->getActiveProducts(
                ['seller_id' => $seller['id']],
                24
            )
        );

        $followersCount = DB::table('marketplace_seller_followers')
            ->where('seller_id', $seller['id'])
            ->count();

        $isFollowing = false;
        if ($request->user()) {
            $isFollowing = DB::table('marketplace_seller_followers')
                ->where('seller_id', $seller['id'])
                ->where('user_id', $request->user()->id)
                ->exists();
        }

        $reviewCount = DB::table('marketplace_reviews')
            ->where('seller_id', $seller['id'])
            ->where('is_approved', true)
            ->count();

        $ratingBreakdown = DB::table('marketplace_reviews')
            ->where('seller_id', $seller['id'])
            ->where('is_approved', true)
            ->select('rating', DB::raw('count(*) as count'))
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

        $reviews = DB::table('marketplace_reviews')
            ->leftJoin('users', 'marketplace_reviews.buyer_id', '=', 'users.id')
            ->leftJoin('marketplace_products', 'marketplace_reviews.product_id', '=', 'marketplace_products.id')
            ->where('marketplace_reviews.seller_id', $seller['id'])
            ->where('marketplace_reviews.is_approved', true)
            ->select(
                'marketplace_reviews.*',
                'users.id as buyer_user_id',
                'users.name as buyer_name',
                'marketplace_products.id as product_id',
                'marketplace_products.name as product_name',
                'marketplace_products.slug as product_slug'
            )
            ->orderByDesc('marketplace_reviews.created_at')
            ->limit(10)
            ->get()
            ->toArray();

        $categoryData = DB::table('marketplace_products')
            ->where('seller_id', $seller['id'])
            ->where('status', 'active')
            ->join('marketplace_categories', 'marketplace_products.category_id', '=', 'marketplace_categories.id')
            ->select('marketplace_categories.id', 'marketplace_categories.name', 'marketplace_categories.slug')
            ->distinct()
            ->get()
            ->toArray();

        $categories = array_map(function ($cat) use ($seller) {
            $count = DB::table('marketplace_products')
                ->where('seller_id', $seller['id'])
                ->where('status', 'active')
                ->where('category_id', $cat->id)
                ->count();
            return [
                'id' => (int) $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'count' => $count,
            ];
        }, $categoryData);

        $logoUrl = $seller['logo_path'] ? asset('storage/' . $seller['logo_path']) : null;
        $coverImageUrl = $seller['cover_image_path'] ? asset('storage/' . $seller['cover_image_path']) : null;

        $ogImage = $coverImageUrl ?: $logoUrl ?: asset('images/marketplace-default-shop.jpg');
        $ogTitle = $seller['business_name'] . ' - MyGrowNet Marketplace';
        $ogDescription = $seller['description']
            ? \Str::limit($seller['description'], 150)
            : "Shop from {$seller['business_name']} on MyGrowNet Marketplace. " . (is_array($products) ? count($products) : 0) . " products available.";
        $ogUrl = route('marketplace.seller.show', $seller['id']);

        return Inertia::render('Marketplace/Seller', [
            'seller' => array_merge($seller, [
                'logo_url' => $logoUrl,
                'cover_image_url' => $coverImageUrl,
                'followers_count' => $followersCount,
                'review_count' => $reviewCount,
            ]),
            'products' => $products,
            'isFollowing' => $isFollowing,
            'ratingBreakdown' => $breakdown,
            'reviews' => $reviews,
            'categories' => $categories,
            'meta' => [
                'title' => $ogTitle,
                'description' => $ogDescription,
                'image' => $ogImage,
                'url' => $ogUrl,
                'type' => 'website',
            ],
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

    private function addComputedFields(array $products): array
    {
        return array_map(fn(array $p) => $this->addComputedFieldsToProduct($p), $products);
    }

    private function addComputedFieldsToProduct(array $product): array
    {
        $product['primary_image_url'] = $this->computePrimaryImageUrl($product['images'] ?? []);
        $product['image_urls'] = $this->computeImageUrls($product['images'] ?? []);
        $product['formatted_price'] = 'K' . number_format($product['price'] / 100, 2);
        $product['formatted_compare_price'] = isset($product['compare_price']) && $product['compare_price']
            ? 'K' . number_format($product['compare_price'] / 100, 2) : null;
        $product['discount_percentage'] = $this->computeDiscountPercentage(
            $product['price'] ?? 0,
            $product['compare_price'] ?? null
        );
        return $product;
    }

    private function computePrimaryImageUrl(array $images): ?string
    {
        if (empty($images[0])) {
            return null;
        }

        $img = $images[0];

        if (is_array($img)) {
            return null;
        }

        if (is_string($img) && (str_starts_with($img, 'http://') || str_starts_with($img, 'https://'))) {
            return $img;
        }

        if (is_string($img) && str_starts_with($img, 'marketplace/')) {
            return \Storage::url($img);
        }

        return asset('storage/' . $img);
    }

    private function computeImageUrls(array $images): array
    {
        return collect($images)->map(function ($img) {
            if (is_array($img)) return null;
            if (is_string($img) && (str_starts_with($img, 'http://') || str_starts_with($img, 'https://'))) return $img;
            if (is_string($img) && str_starts_with($img, 'marketplace/')) return \Storage::url($img);
            return asset('storage/' . $img);
        })->filter()->values()->toArray();
    }

    private function computeDiscountPercentage(int $price, ?int $comparePrice): int
    {
        if (!$comparePrice || $comparePrice <= $price) {
            return 0;
        }
        return (int) round((($comparePrice - $price) / $comparePrice) * 100);
    }
}
