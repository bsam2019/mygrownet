<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Services\GrowBuilder\BusinessProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * BusinessProfileController — manages structured business identity data for GrowBuilder sites.
 *
 * The Business Profile is the single source of truth for a business's structured data:
 * identity, contact, trust (TPIN/PACRA), services, hours, location, and payment methods.
 *
 * §7 of GROWBUILDER_PLATFORM.md
 */
class BusinessProfileController extends Controller
{
    public function __construct(private BusinessProfileService $profileService) {}

    /**
     * GET /dashboard/sites/{siteId}/business-profile
     * Render the Business Profile edit page.
     */
    public function edit(int $siteId): Response
    {
        $this->authorizeForSite($siteId);

        $site    = \DB::table('growbuilder_sites')->where('id', $siteId)->firstOrFail();
        $profile = $this->profileService->getOrCreateForSite($siteId);

        return Inertia::render('GrowBuilder/BusinessProfile/Edit', [
            'site'    => (array) $site,
            'profile' => $profile,
            'industryOptions' => $this->getIndustryOptions(),
            'blueprintOptions' => $this->getBlueprintOptions(),
        ]);
    }

    /**
     * GET /dashboard/sites/{siteId}/seo
     * Render the SEO health panel.
     */
    public function seoDashboard(int $siteId): Response
    {
        $this->authorizeForSite($siteId);

        $site    = \DB::table('growbuilder_sites')->where('id', $siteId)->firstOrFail();
        $profile = $this->profileService->getForSite($siteId);

        return Inertia::render('GrowBuilder/Seo/Dashboard', [
            'site'    => (array) $site,
            'profile' => $profile,
        ]);
    }

    /**
     * PUT /dashboard/sites/{siteId}/business-profile
     * Update the Business Profile.
     */
    public function update(Request $request, int $siteId): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeForSite($siteId);

        $validated = $request->validate([
            'legal_name'          => 'nullable|string|max:255',
            'trade_name'          => 'nullable|string|max:255',
            'tpin'                => 'nullable|string|max:50',
            'pacra_number'        => 'nullable|string|max:100',
            'phone'               => 'nullable|string|max:30',
            'whatsapp'            => 'nullable|string|max:30',
            'email'               => 'nullable|email|max:191',
            'website'             => 'nullable|url|max:191',
            'physical_address'    => 'nullable|string|max:500',
            'city'                => 'nullable|string|max:100',
            'province'            => 'nullable|string|max:100',
            'country'             => 'nullable|string|max:10',
            'industry'            => 'nullable|string|max:100',
            'industry_blueprint'  => 'nullable|string|max:100',
            'opening_hours'       => 'nullable|array',
            'services_json'       => 'nullable|array',
            'trust_badges_json'   => 'nullable|array',
            'payment_methods'     => 'nullable|array',
            'tagline'             => 'nullable|string|max:500',
            'description'         => 'nullable|string|max:2000',
            'price_range'         => 'nullable|string|max:20',
            'latitude'            => 'nullable|numeric',
            'longitude'           => 'nullable|numeric',
            'pacra_verified'      => 'nullable|boolean',
            'tpin_verified'       => 'nullable|boolean',
        ]);

        $this->profileService->upsertForSite($siteId, $validated);

        return redirect()->back()->with('success', 'Business profile updated successfully.');
    }

    /**
     * GET /dashboard/sites/{siteId}/business-profile/payload
     * Return the Business Profile as a JSON payload (used by AI generator).
     */
    public function payload(int $siteId): JsonResponse
    {
        $this->authorizeForSite($siteId);
        return response()->json($this->profileService->toGeneratorPayload($siteId));
    }

    /**
     * POST /dashboard/sites/{siteId}/business-profile/sync-org
     * Sync the Business Profile from the linked platform Organization.
     */
    public function syncFromOrg(Request $request, int $siteId): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeForSite($siteId);

        $request->validate(['organization_id' => 'required|integer|exists:organizations,id']);

        $this->profileService->syncFromOrganization($siteId, (int) $request->organization_id);

        // Also save the org link on the site
        \DB::table('growbuilder_sites')->where('id', $siteId)->update([
            'canonical_organization_id' => $request->organization_id,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Business profile synced from organization.');
    }

    private function authorizeForSite(int $siteId): void
    {
        $userId = auth()->id();
        $site   = \DB::table('growbuilder_sites')
            ->where('id', $siteId)
            ->where('user_id', $userId)
            ->first();

        abort_if(!$site, 403, 'You do not have access to this site.');
    }

    private function getIndustryOptions(): array
    {
        return [
            ['value' => 'pharmacy',       'label' => 'Pharmacy / Medical Supply'],
            ['value' => 'clinic',         'label' => 'Clinic / Healthcare'],
            ['value' => 'restaurant',     'label' => 'Restaurant / Café'],
            ['value' => 'retail',         'label' => 'Retail / Shop'],
            ['value' => 'school',         'label' => 'School / Education'],
            ['value' => 'legal',          'label' => 'Legal Services'],
            ['value' => 'accounting',     'label' => 'Accounting / Finance'],
            ['value' => 'construction',   'label' => 'Construction / Building'],
            ['value' => 'beauty',         'label' => 'Beauty / Salon'],
            ['value' => 'hotel',          'label' => 'Hotel / Lodging'],
            ['value' => 'professional',   'label' => 'Professional Services'],
            ['value' => 'other',          'label' => 'Other'],
        ];
    }

    private function getBlueprintOptions(): array
    {
        return [
            ['value' => 'pharmacy',     'label' => 'Pharmacy Blueprint (Phase 1 ✅)'],
            ['value' => 'restaurant',   'label' => 'Restaurant Blueprint (Coming Soon)'],
            ['value' => 'school',       'label' => 'School Blueprint (Coming Soon)'],
            ['value' => 'professional', 'label' => 'Professional Services Blueprint (Coming Soon)'],
        ];
    }
}
