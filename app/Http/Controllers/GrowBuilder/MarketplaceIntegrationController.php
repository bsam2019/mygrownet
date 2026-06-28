<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Services\GrowBuilder\MarketplaceSyncService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketplaceIntegrationController extends Controller
{
    public function __construct(
        private MarketplaceSyncService $syncService
    ) {}

    /**
     * Show marketplace integration settings
     */
    public function show(GrowBuilderSite $site)
    {
        $this->authorize('update', $site);

        $status = $this->syncService->getIntegrationStatus($site);

        return Inertia::render('GrowBuilder/MarketplaceIntegration', [
            'site' => $site->load('marketplaceSeller'),
            'integrationStatus' => $status,
        ]);
    }

    /**
     * Enable marketplace integration
     */
    public function enable(Request $request, GrowBuilderSite $site)
    {
        $this->authorize('update', $site);

        if (!$this->syncService->canEnableIntegration($site)) {
            return back()->with('error', 'Cannot enable marketplace integration. Site must be published.');
        }

        $result = $this->syncService->enableMarketplaceIntegration($site);

        return back()->with('success', $result['message']);
    }

    /**
     * Disable marketplace integration
     */
    public function disable(Request $request, GrowBuilderSite $site)
    {
        $this->authorize('update', $site);

        $result = $this->syncService->disableMarketplaceIntegration($site);

        return back()->with('success', $result['message']);
    }

    /**
     * Get products for site (API endpoint)
     */
    public function products(Request $request, GrowBuilderSite $site)
    {
        // This endpoint can be public for displaying products on the site
        $filters = $request->only(['category_id', 'featured', 'limit']);
        
        $products = $this->syncService->getSiteProducts($site, $filters);

        return response()->json([
            'products' => $products,
        ]);
    }
}
