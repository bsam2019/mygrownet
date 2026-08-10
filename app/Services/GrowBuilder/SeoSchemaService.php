<?php

namespace App\Services\GrowBuilder;

use Illuminate\Support\Facades\DB;

/**
 * SeoSchemaService — auto-generates JSON-LD LocalBusiness schema, sitemap.xml, and robots.txt.
 *
 * Every GrowBuilder published site automatically emits structured data from the Business Profile
 * so Google can index it for local searches like "pharmacy near me Lusaka".
 *
 * §24 of GROWBUILDER_PLATFORM.md
 */
class SeoSchemaService
{
    /**
     * Build a JSON-LD LocalBusiness schema string from site and Business Profile data.
     * Injected into every static HTML page's <head>.
     */
    public function buildJsonLdSchema(array $site, array $profile): string
    {
        $businessType = $this->mapIndustryToSchemaType($profile['industry'] ?? '');
        $openingHours = $this->formatOpeningHours(
            $profile['opening_hours'] ? json_decode($profile['opening_hours'], true) : []
        );
        $paymentMethods = $this->formatPaymentMethods(
            $profile['payment_methods'] ? json_decode($profile['payment_methods'], true) : []
        );

        $schema = array_filter([
            '@context'      => 'https://schema.org',
            '@type'         => $businessType,
            'name'          => $profile['trade_name'] ?? $profile['legal_name'] ?? $site['name'] ?? '',
            'description'   => $profile['description'] ?? '',
            'url'           => $site['custom_domain'] ? "https://{$site['custom_domain']}" : url("/sites/{$site['subdomain']}"),
            'telephone'     => $profile['phone'] ?? '',
            'email'         => $profile['email'] ?? '',
            'priceRange'    => $profile['price_range'] ?? '',
            'openingHours'  => $openingHours ?: null,
            'paymentAccepted' => $paymentMethods ?: null,
            'address'       => $this->buildAddress($profile),
            'geo'           => ($profile['latitude'] && $profile['longitude']) ? [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $profile['latitude'],
                'longitude' => $profile['longitude'],
            ] : null,
            'image'         => $profile['logo_path'] ? url($profile['logo_path']) : null,
            'vatID'         => $profile['tpin'] ?? null,
            'legalName'     => $profile['legal_name'] ?? null,
        ]);

        return json_encode(array_filter($schema), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Generate sitemap.xml for a site's pages.
     */
    public function generateSitemap(int $siteId): string
    {
        $site  = DB::table('growbuilder_sites')->where('id', $siteId)->first();
        $pages = DB::table('growbuilder_pages')
            ->where('site_id', $siteId)
            ->whereNull('deleted_at')
            ->get(['slug', 'updated_at']);

        $baseUrl = $site->custom_domain
            ? "https://{$site->custom_domain}"
            : url("/sites/{$site->subdomain}");

        $urls = '';
        foreach ($pages as $page) {
            $slug     = ($page->slug === '/' || $page->slug === 'home') ? '' : '/' . ltrim($page->slug, '/');
            $lastmod  = date('Y-m-d', strtotime($page->updated_at));
            $priority = $slug === '' ? '1.0' : '0.8';
            $urls    .= "    <url>\n        <loc>{$baseUrl}{$slug}</loc>\n        <lastmod>{$lastmod}</lastmod>\n        <priority>{$priority}</priority>\n    </url>\n";
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{$urls}</urlset>
XML;
    }

    /**
     * Generate a robots.txt for a site.
     */
    public function generateRobotsTxt(object $site): string
    {
        $sitemapUrl = $site->custom_domain
            ? "https://{$site->custom_domain}/sitemap.xml"
            : url("/sites/{$site->subdomain}/sitemap.xml");

        return <<<TXT
User-agent: *
Allow: /

Sitemap: {$sitemapUrl}
TXT;
    }

    /**
     * Map GrowBuilder industry slug to schema.org business type.
     */
    private function mapIndustryToSchemaType(string $industry): string
    {
        return match(strtolower($industry)) {
            'pharmacy'              => 'Pharmacy',
            'restaurant', 'cafe'   => 'Restaurant',
            'school', 'education'  => 'EducationalOrganization',
            'clinic', 'hospital'   => 'MedicalClinic',
            'hotel', 'lodging'     => 'LodgingBusiness',
            'retail', 'shop'       => 'Store',
            'legal', 'law'         => 'LegalService',
            'accounting', 'finance'=> 'AccountingService',
            'beauty', 'salon'      => 'BeautySalon',
            'construction'         => 'HomeAndConstructionBusiness',
            default                => 'LocalBusiness',
        };
    }

    private function buildAddress(array $profile): ?array
    {
        if (!$profile['physical_address'] && !$profile['city']) {
            return null;
        }

        return array_filter([
            '@type'            => 'PostalAddress',
            'streetAddress'    => $profile['physical_address'] ?? '',
            'addressLocality'  => $profile['city'] ?? '',
            'addressRegion'    => $profile['province'] ?? '',
            'addressCountry'   => $profile['country'] ?? 'ZM',
        ]);
    }

    private function formatOpeningHours(array $hours): array
    {
        if (empty($hours)) {
            return [];
        }

        // Expected format: [{day: 'Monday', open: '08:00', close: '18:00'}]
        $map = [
            'Monday' => 'Mo', 'Tuesday' => 'Tu', 'Wednesday' => 'We',
            'Thursday' => 'Th', 'Friday' => 'Fr', 'Saturday' => 'Sa', 'Sunday' => 'Su',
        ];

        $formatted = [];
        foreach ($hours as $entry) {
            if (empty($entry['open']) || empty($entry['close'])) continue;
            $day = $map[$entry['day'] ?? ''] ?? ($entry['day'] ?? '');
            if ($day) {
                $formatted[] = "{$day} {$entry['open']}-{$entry['close']}";
            }
        }

        return $formatted;
    }

    private function formatPaymentMethods(array $methods): string
    {
        $labels = [
            'mtn_momo'     => 'MTN Mobile Money',
            'airtel_money' => 'Airtel Money',
            'cash'         => 'Cash',
            'card'         => 'Credit Card',
            'paystack'     => 'Paystack',
            'flutterwave'  => 'Flutterwave',
            'bank_transfer'=> 'Bank Transfer',
        ];

        $mapped = array_map(fn($m) => $labels[$m] ?? $m, $methods);
        return implode(', ', $mapped);
    }
}
