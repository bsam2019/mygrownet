<?php

namespace App\Services\GrowBuilder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BusinessProfileService — centralized structured business identity engine.
 *
 * The Business Profile is the foundation of GrowBuilder's strategy:
 * a single source of truth for a business's structured data that powers site generation,
 * SEO schema, multi-channel publishing, and WhatsApp ordering flows.
 *
 * §7 of GROWBUILDER_PLATFORM.md
 */
class BusinessProfileService
{
    /**
     * Get the Business Profile for a site.
     * Returns null if not yet created.
     */
    public function getForSite(int $siteId): ?array
    {
        $profile = DB::table('growbuilder_business_profiles')
            ->where('site_id', $siteId)
            ->first();

        return $profile ? (array) $profile : null;
    }

    /**
     * Get or create a Business Profile for a site.
     * If the site belongs to a platform Organization, auto-hydrate from it.
     */
    public function getOrCreateForSite(int $siteId): array
    {
        $existing = $this->getForSite($siteId);
        if ($existing) {
            return $existing;
        }

        // Check if site has a canonical_organization_id
        $site = DB::table('growbuilder_sites')->where('id', $siteId)->first();
        $orgId = $site->canonical_organization_id ?? null;

        $profileData = [
            'site_id'       => $siteId,
            'organization_id' => $orgId,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        // Auto-hydrate from platform Organization if linked
        if ($orgId) {
            $profileData = array_merge($profileData, $this->hydratFromOrganization($orgId));
        }

        DB::table('growbuilder_business_profiles')->insert($profileData);

        return $this->getForSite($siteId);
    }

    /**
     * Create or update the Business Profile for a site.
     */
    public function upsertForSite(int $siteId, array $data): array
    {
        $existing = $this->getForSite($siteId);

        $cleanData = $this->sanitize($data);
        $cleanData['updated_at'] = now();

        if ($existing) {
            DB::table('growbuilder_business_profiles')
                ->where('site_id', $siteId)
                ->update($cleanData);
        } else {
            $cleanData['site_id']    = $siteId;
            $cleanData['created_at'] = now();
            DB::table('growbuilder_business_profiles')->insert($cleanData);
        }

        return $this->getForSite($siteId);
    }

    /**
     * Sync canonical Organization details into the Business Profile.
     * Called when an org's company details are updated via OrganizationWorkspaceController.
     */
    public function syncFromOrganization(int $siteId, int $organizationId): void
    {
        $hydrated = $this->hydratFromOrganization($organizationId);
        $this->upsertForSite($siteId, array_merge($hydrated, ['organization_id' => $organizationId]));

        Log::info('GrowBuilder: synced business profile from org', [
            'site_id' => $siteId,
            'org_id'  => $organizationId,
        ]);
    }

    /**
     * Export the Business Profile as a structured JSON payload for AI generation.
     * This is the structured data fed to AiGeneratorEngineInterface::generateSiteLayout().
     */
    public function toGeneratorPayload(int $siteId): array
    {
        $profile = $this->getOrCreateForSite($siteId);

        return [
            'name'            => $profile['trade_name'] ?? $profile['legal_name'] ?? '',
            'legal_name'      => $profile['legal_name'] ?? '',
            'industry'        => $profile['industry'] ?? '',
            'blueprint'       => $profile['industry_blueprint'] ?? '',
            'description'     => $profile['description'] ?? '',
            'tagline'         => $profile['tagline'] ?? '',
            'phone'           => $profile['phone'] ?? '',
            'whatsapp'        => $profile['whatsapp'] ?? '',
            'email'           => $profile['email'] ?? '',
            'website'         => $profile['website'] ?? '',
            'address'         => $profile['physical_address'] ?? '',
            'city'            => $profile['city'] ?? '',
            'province'        => $profile['province'] ?? '',
            'country'         => $profile['country'] ?? 'ZM',
            'opening_hours'   => $profile['opening_hours'] ? json_decode($profile['opening_hours'], true) : [],
            'services'        => $profile['services_json'] ? json_decode($profile['services_json'], true) : [],
            'trust_badges'    => $profile['trust_badges_json'] ? json_decode($profile['trust_badges_json'], true) : [],
            'payment_methods' => $profile['payment_methods'] ? json_decode($profile['payment_methods'], true) : [],
            'tpin'            => $profile['tpin'] ?? '',
            'pacra_number'    => $profile['pacra_number'] ?? '',
            'pacra_verified'  => (bool)($profile['pacra_verified'] ?? false),
            'tpin_verified'   => (bool)($profile['tpin_verified'] ?? false),
            'price_range'     => $profile['price_range'] ?? '',
            'latitude'        => $profile['latitude'] ?? null,
            'longitude'       => $profile['longitude'] ?? null,
        ];
    }

    /**
     * Pull structured company details from the platform organizations table.
     */
    private function hydratFromOrganization(int $orgId): array
    {
        $org = DB::table('organizations')->where('id', $orgId)->first();
        if (!$org) {
            return [];
        }

        return array_filter([
            'legal_name'       => $org->name ?? null,
            'trade_name'       => $org->name ?? null,
            'email'            => $org->email ?? null,
            'phone'            => $org->phone ?? null,
            'whatsapp'         => $org->phone ?? null,
            'physical_address' => $org->address ?? null,
            'country'          => $org->country ?? 'ZM',
            'tax_number'       => $org->tax_number ?? null,
        ]);
    }

    private function sanitize(array $data): array
    {
        $allowed = [
            'organization_id', 'legal_name', 'trade_name', 'tpin', 'pacra_number',
            'phone', 'whatsapp', 'email', 'website', 'physical_address', 'city',
            'province', 'country', 'industry', 'industry_blueprint', 'opening_hours',
            'services_json', 'trust_badges_json', 'payment_methods', 'logo_path',
            'banner_path', 'tagline', 'description', 'price_range', 'latitude',
            'longitude', 'google_place_id', 'pacra_verified', 'tpin_verified',
        ];

        $clean = array_intersect_key($data, array_flip($allowed));

        // JSON-encode arrays
        foreach (['opening_hours', 'services_json', 'trust_badges_json', 'payment_methods'] as $field) {
            if (isset($clean[$field]) && is_array($clean[$field])) {
                $clean[$field] = json_encode($clean[$field]);
            }
        }

        return $clean;
    }
}
