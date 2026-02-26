<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Services\Integration\GrowMarketIntegrationService;
use App\Services\Integration\CMSIntegrationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IntegrationController extends Controller
{
    public function __construct(
        private GrowMarketIntegrationService $marketIntegration,
        private CMSIntegrationService $cmsIntegration
    ) {}

    /**
     * Show integrations page
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $company = $request->user()->cmsUser->company;

        // Get integration settings
        $integrationSettings = $company->integration_settings ?? [
            'growbuilder' => ['enabled' => false],
            'growmarket' => ['enabled' => false],
        ];

        // Get GrowMarket sync status
        $marketSyncStatus = $this->marketIntegration->getSyncStatus($companyId);

        return Inertia::render('CMS/Integrations/Index', [
            'company' => $company,
            'integrationSettings' => $integrationSettings,
            'marketSyncStatus' => $marketSyncStatus,
        ]);
    }

    /**
     * Sync product to GrowMarket
     */
    public function syncProductToMarket(Request $request, int $productId)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $result = $this->marketIntegration->syncProductToMarket($companyId, $productId);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['error']);
    }

    /**
     * Bulk sync all products to GrowMarket
     */
    public function bulkSyncToMarket(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $results = $this->marketIntegration->bulkSyncProducts($companyId);

        $message = "Synced {$results['synced']} products successfully.";
        
        if ($results['failed'] > 0) {
            $message .= " {$results['failed']} products failed to sync.";
        }

        return back()->with('success', $message);
    }

    /**
     * Unsync product from GrowMarket
     */
    public function unsyncProductFromMarket(Request $request, int $productId)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $result = $this->marketIntegration->unsyncProduct($companyId, $productId);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['error']);
    }

    /**
     * Update integration settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'growbuilder.enabled' => 'boolean',
            'growbuilder.auto_sync' => 'boolean',
            'growmarket.enabled' => 'boolean',
            'growmarket.auto_sync' => 'boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $company = \App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel::find($companyId);

        $company->update([
            'integration_settings' => $validated,
        ]);

        return back()->with('success', 'Integration settings updated successfully');
    }
}
