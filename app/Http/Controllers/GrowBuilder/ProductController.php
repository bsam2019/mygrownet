<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\ProductRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\Money;
use App\Domain\GrowBuilder\ValueObjects\ProductId;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Http\Controllers\Controller;
use App\Services\GrowBuilder\TierRestrictionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private ProductRepositoryInterface $productRepository,
        private TierRestrictionService $tierRestrictionService,
    ) {}

    public function index(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $siteVo = SiteId::fromInt($siteId);
        $products = $this->productRepository->getAllForSitePaginated($siteVo, 20);
        $tierRestrictions = $this->tierRestrictionService->getRestrictions($request->user());
        $productCount = $this->productRepository->countForSite($siteVo);

        return Inertia::render('GrowBuilder/Products/Index', [
            'site' => $this->siteToArray($site),
            'products' => $products,
            'tierRestrictions' => $tierRestrictions,
            'productCount' => $productCount,
            'canAddProduct' => $this->tierRestrictionService->canAddProduct($request->user(), $productCount),
        ]);
    }

    public function create(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $categories = $this->productRepository->getCategoriesForSite(SiteId::fromInt($siteId));

        return Inertia::render('GrowBuilder/Products/Create', [
            'site' => $this->siteToArray($site),
            'categories' => $categories,
        ]);
    }

    public function store(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $siteVo = SiteId::fromInt($siteId);

        $productCount = $this->productRepository->countForSite($siteVo);
        if (!$this->tierRestrictionService->canAddProduct($request->user(), $productCount)) {
            $limit = $this->tierRestrictionService->getProductsLimit($request->user());
            return back()->withErrors([
                'limit' => $limit === 0 
                    ? 'E-commerce is not available on your plan. Upgrade to Starter or higher to sell products.'
                    : "You've reached your product limit ({$limit}). Upgrade to Business plan for unlimited products."
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'track_stock' => 'boolean',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'images' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $slug = $this->productRepository->generateUniqueSlug($siteId, $validated['name']);

        $product = \App\Domain\GrowBuilder\Entities\Product::create(
            siteId: $siteId,
            name: $validated['name'],
            slug: $slug,
            priceInNgwee: (int) ($validated['price'] * 100),
            description: $validated['description'] ?? null,
            stockQuantity: $validated['stock_quantity'] ?? 0,
        );

        if (!empty($validated['images'])) {
            $product->setImages($validated['images']);
        }
        if (isset($validated['compare_price'])) {
            $product->updatePricing((int) ($validated['price'] * 100), (int) ($validated['compare_price'] * 100));
        }
        if (isset($validated['is_active']) && !$validated['is_active']) {
            $product->deactivate();
        }
        if (isset($validated['is_featured']) && $validated['is_featured']) {
            $product->setFeatured(true);
        }

        $this->productRepository->save($product);

        return redirect()->route('growbuilder.products.index', $siteId)
            ->with('success', 'Product created successfully');
    }

    public function edit(Request $request, int $siteId, int $productId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $product = $this->productRepository->findByIdForSite(ProductId::fromInt($productId), SiteId::fromInt($siteId));

        if (!$product) {
            abort(404);
        }

        $categories = $this->productRepository->getCategoriesForSite(SiteId::fromInt($siteId));

        return Inertia::render('GrowBuilder/Products/Edit', [
            'site' => $this->siteToArray($site),
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, int $siteId, int $productId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $product = $this->productRepository->findByIdForSite(ProductId::fromInt($productId), SiteId::fromInt($siteId));

        if (!$product) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'track_stock' => 'boolean',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'images' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $product->updateDetails(
            name: $validated['name'],
            description: $validated['description'] ?? null,
            shortDescription: $validated['short_description'] ?? null,
            category: $validated['category'] ?? null,
        );

        $product->updatePricing(
            priceInNgwee: (int) ($validated['price'] * 100),
            comparePriceInNgwee: isset($validated['compare_price']) ? (int) ($validated['compare_price'] * 100) : null,
        );

        $product->updateStock($validated['stock_quantity'] ?? 0);

        if (isset($validated['images'])) {
            $product->setImages($validated['images']);
        }

        if (!$validated['is_active']) {
            $product->deactivate();
        } else {
            $product->activate();
        }

        $product->setFeatured($validated['is_featured'] ?? false);

        $this->productRepository->save($product);

        return redirect()->route('growbuilder.products.index', $siteId)
            ->with('success', 'Product updated successfully');
    }

    public function destroy(Request $request, int $siteId, int $productId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $this->productRepository->delete(ProductId::fromInt($productId));

        return redirect()->route('growbuilder.products.index', $siteId)
            ->with('success', 'Product deleted successfully');
    }

    private function siteToArray($site): array
    {
        return [
            'id' => $site->getId()->value(),
            'name' => $site->getName(),
            'subdomain' => $site->getSubdomain()->value(),
            'plan' => $site->getPlan()->value(),
        ];
    }
}
