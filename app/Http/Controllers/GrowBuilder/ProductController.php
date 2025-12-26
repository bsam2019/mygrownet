<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {}

    public function index(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $products = GrowBuilderProduct::where('site_id', $siteId)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('GrowBuilder/Products/Index', [
            'site' => $this->siteToArray($site),
            'products' => $products,
        ]);
    }

    public function create(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $categories = GrowBuilderProduct::where('site_id', $siteId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

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

        $slug = Str::slug($validated['name']);
        $existingCount = GrowBuilderProduct::where('site_id', $siteId)
            ->where('slug', 'like', $slug . '%')
            ->count();
        
        if ($existingCount > 0) {
            $slug .= '-' . ($existingCount + 1);
        }

        $product = GrowBuilderProduct::create([
            'site_id' => $siteId,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'price' => (int) ($validated['price'] * 100), // Convert to Ngwee
            'compare_price' => isset($validated['compare_price']) ? (int) ($validated['compare_price'] * 100) : null,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'track_stock' => $validated['track_stock'] ?? true,
            'sku' => $validated['sku'] ?? null,
            'category' => $validated['category'] ?? null,
            'images' => $validated['images'] ?? [],
            'is_active' => $validated['is_active'] ?? true,
            'is_featured' => $validated['is_featured'] ?? false,
        ]);

        return redirect()->route('growbuilder.products.index', $siteId)
            ->with('success', 'Product created successfully');
    }

    public function edit(Request $request, int $siteId, int $productId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $product = GrowBuilderProduct::where('site_id', $siteId)
            ->findOrFail($productId);

        $categories = GrowBuilderProduct::where('site_id', $siteId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

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

        $product = GrowBuilderProduct::where('site_id', $siteId)
            ->findOrFail($productId);

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

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'price' => (int) ($validated['price'] * 100),
            'compare_price' => isset($validated['compare_price']) ? (int) ($validated['compare_price'] * 100) : null,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'track_stock' => $validated['track_stock'] ?? true,
            'sku' => $validated['sku'] ?? null,
            'category' => $validated['category'] ?? null,
            'images' => $validated['images'] ?? [],
            'is_active' => $validated['is_active'] ?? true,
            'is_featured' => $validated['is_featured'] ?? false,
        ]);

        return redirect()->route('growbuilder.products.index', $siteId)
            ->with('success', 'Product updated successfully');
    }

    public function destroy(Request $request, int $siteId, int $productId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $product = GrowBuilderProduct::where('site_id', $siteId)
            ->findOrFail($productId);

        $product->delete();

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
