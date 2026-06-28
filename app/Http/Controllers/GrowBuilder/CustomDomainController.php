<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Services\GrowBuilder\CustomDomainService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomDomainController extends Controller
{
    public function __construct(
        private CustomDomainService $domainService
    ) {}

    /**
     * Verify DNS configuration for a domain
     */
    public function verifyDNS(Request $request, int $siteId)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $site = GrowBuilderSite::findOrFail($siteId);

        // Check ownership
        if ($site->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $domain = preg_replace('/^www\./i', '', $validated['domain']);
        $status = $this->domainService->checkDomainStatus($domain);

        return response()->json($status);
    }

    /**
     * Connect a custom domain to a site
     */
    public function connect(Request $request, int $siteId)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $site = GrowBuilderSite::findOrFail($siteId);

        // Check ownership
        if ($site->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Check tier restrictions
        // TODO: Add tier check here

        $result = $this->domainService->addCustomDomain($site, $validated['domain']);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Disconnect custom domain from a site
     */
    public function disconnect(Request $request, int $siteId)
    {
        $site = GrowBuilderSite::findOrFail($siteId);

        // Check ownership
        if ($site->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $result = $this->domainService->removeCustomDomain($site);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Get custom domain status
     */
    public function status(Request $request, int $siteId)
    {
        $site = GrowBuilderSite::findOrFail($siteId);

        // Check ownership
        if ($site->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (!$site->custom_domain) {
            return response()->json([
                'has_custom_domain' => false,
            ]);
        }

        $status = $this->domainService->checkDomainStatus($site->custom_domain);

        return response()->json([
            'has_custom_domain' => true,
            ...$status,
        ]);
    }
}
