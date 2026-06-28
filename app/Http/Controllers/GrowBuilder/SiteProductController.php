<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Application\GrowBuilder\UseCases\Product\CreateProductUseCase;
use App\Application\GrowBuilder\UseCases\Product\DeleteProductUseCase;
use App\Application\GrowBuilder\UseCases\Product\ListProductsUseCase;
use App\Application\GrowBuilder\UseCases\Product\UpdateProductUseCase;
use App\Domain\GrowBuilder\Product\DTOs\ProductData;
use App\Domain\GrowBuilder\Product\Repositories\ProductRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\SiteUser;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiteProductController extends Controller
{
    public function __construct(
        private ListProductsUseCase $listProductsUseCase,
        private CreateProductUseCase $createProductUseCase,
        private UpdateProductUseCase $updateProductUseCase,
        private DeleteProductUseCase $deleteProductUseCase,
        private ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * List all products for the site
     */
    public function index(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $products = $this->listProductsUseCase->execute($site->id);
        $productCount = $this->productRepository->countForSite($site->id);

        return Inertia::render('SiteMember/Products/Index', [
            'site' => $this->getSiteData($site),
            'settings' => $site->settings,
            'user' => $this->getUserData($user),
            'products' => $products,
            'productCount' => $productCount,
        ]);
    }

    /**
     * Show create product form
     */
    public function create(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $categories = $this->productRepository->getCategoriesForSite($site->id);

        return Inertia::render('SiteMember/Products/Create', [
            'site' => $this->getSiteData($site),
            'settings' => $site->settings,
            'user' => $this->getUserData($user),
            'categories' => $categories,
        ]);
    }

    /**
     * Store a new product
     */
    public function store(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');

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

        $productData = ProductData::fromRequest($validated);
        $this->createProductUseCase->execute($site->id, $productData);

        return redirect()->route('site.member.products.index', $subdomain)
            ->with('success', 'Product created successfully');
    }

    /**
     * Show edit product form
     */
    public function edit(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $product = $this->productRepository->findByIdForSite($id, $site->id);

        if (!$product) {
            abort(404);
        }

        $categories = $this->productRepository->getCategoriesForSite($site->id);

        return Inertia::render('SiteMember/Products/Edit', [
            'site' => $this->getSiteData($site),
            'settings' => $site->settings,
            'user' => $this->getUserData($user),
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    /**
     * Update a product
     */
    public function update(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');

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

        $productData = ProductData::fromRequest($validated);
        $product = $this->updateProductUseCase->execute($id, $site->id, $productData);

        if (!$product) {
            abort(404);
        }

        return redirect()->route('site.member.products.index', $subdomain)
            ->with('success', 'Product updated successfully');
    }

    /**
     * Delete a product
     */
    public function destroy(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');

        $deleted = $this->deleteProductUseCase->execute($id, $site->id);

        if (!$deleted) {
            abort(404);
        }

        return redirect()->route('site.member.products.index', $subdomain)
            ->with('success', 'Product deleted successfully');
    }

    /**
     * Get site data for frontend
     */
    protected function getSiteData($site): array
    {
        $settings = $site->settings ?? [];
        $logo = $settings['navigation']['logo'] ?? $site->logo ?? null;

        return [
            'id' => $site->id,
            'name' => $site->name,
            'subdomain' => $site->subdomain,
            'logo' => $logo,
            'theme' => $site->theme,
        ];
    }

    /**
     * Get user data for frontend
     */
    protected function getUserData(SiteUser $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'role' => $user->role ? [
                'name' => $user->role->name,
                'slug' => $user->role->slug,
                'level' => $user->role->level,
                'type' => $user->role->type ?? 'client',
                'icon' => $user->role->icon,
                'color' => $user->role->color,
            ] : null,
            'permissions' => $user->role
                ? $user->role->permissions->pluck('slug')->toArray()
                : [],
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
