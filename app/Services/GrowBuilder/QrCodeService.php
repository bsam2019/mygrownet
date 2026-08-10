<?php

namespace App\Services\GrowBuilder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * QrCodeService — Physical-to-Digital Bridge engine.
 *
 * Generates QR codes linking offline print materials (window stickers, business cards, flyers)
 * to a GrowBuilder published site, WhatsApp ordering flow, or product catalog.
 * UTM attribution feeds directly into BizBoost's physical conversion tracking.
 *
 * §30 of GROWBUILDER_PLATFORM.md
 *
 * QR image generation relies on the `simple-qrcode` package or an external API.
 * If neither is available, returns a Google Charts API URL as fallback.
 */
class QrCodeService
{
    /**
     * Create or retrieve a QR code for a site's main URL.
     */
    public function getOrCreateForSite(int $siteId, string $label = 'main'): array
    {
        $existing = DB::table('growbuilder_qr_codes')
            ->where('site_id', $siteId)
            ->where('label', $label)
            ->first();

        if ($existing) {
            return (array) $existing;
        }

        return $this->createQrCode($siteId, $label);
    }

    /**
     * Create a new UTM-tagged QR code for a specific target URL and label.
     *
     * @param  string $label     'main', 'product-catalog', 'whatsapp', 'custom'
     * @param  string|null $customUrl Override target URL (if null, uses site's public URL)
     */
    public function createQrCode(
        int $siteId,
        string $label = 'main',
        ?string $customUrl = null,
        string $utmSource = 'qr_code',
        string $utmMedium = 'offline',
        ?string $utmCampaign = null
    ): array {
        $site = DB::table('growbuilder_sites')->where('id', $siteId)->first();
        $code = Str::random(12);

        // Build the short redirect URL that goes through our tracker
        $shortUrl = url("/qr/{$code}");

        // Build the actual destination URL with UTM params
        $targetBase = $customUrl ?? ($site->custom_domain
            ? "https://{$site->custom_domain}"
            : url("/sites/{$site->subdomain}"));

        $targetUrl = $this->appendUtm($targetBase, $utmSource, $utmMedium, $utmCampaign ?? "site-{$siteId}");

        // Generate QR code image (PNG data URL via Google Charts API as fallback)
        $imagePath = $this->generateQrImage($shortUrl, $siteId, $code);

        $id = DB::table('growbuilder_qr_codes')->insertGetId([
            'site_id'      => $siteId,
            'code'         => $code,
            'target_url'   => $targetUrl,
            'label'        => $label,
            'utm_source'   => $utmSource,
            'utm_medium'   => $utmMedium,
            'utm_campaign' => $utmCampaign ?? "site-{$siteId}",
            'image_path'   => $imagePath,
            'scan_count'   => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return (array) DB::table('growbuilder_qr_codes')->where('id', $id)->first();
    }

    /**
     * Track a QR code scan and redirect to the target URL.
     * Returns the target URL to redirect to.
     */
    public function trackAndResolve(string $code, ?string $ipAddress = null, ?string $userAgent = null): ?string
    {
        $qr = DB::table('growbuilder_qr_codes')->where('code', $code)->first();
        if (!$qr) {
            return null;
        }

        // Increment scan count
        DB::table('growbuilder_qr_codes')
            ->where('code', $code)
            ->increment('scan_count');

        return $qr->target_url;
    }

    /**
     * Get all QR codes for a site.
     */
    public function getForSite(int $siteId): array
    {
        return DB::table('growbuilder_qr_codes')
            ->where('site_id', $siteId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($qr) => (array) $qr + [
                'short_url'    => url("/qr/{$qr->code}"),
                'google_chart' => $this->getGoogleChartsUrl(url("/qr/{$qr->code}")),
            ])
            ->toArray();
    }

    /**
     * Delete a QR code.
     */
    public function delete(string $code, int $siteId): bool
    {
        return (bool) DB::table('growbuilder_qr_codes')
            ->where('code', $code)
            ->where('site_id', $siteId)
            ->delete();
    }

    private function appendUtm(string $url, string $source, string $medium, string $campaign): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';
        return "{$url}{$separator}utm_source=" . urlencode($source)
            . "&utm_medium=" . urlencode($medium)
            . "&utm_campaign=" . urlencode($campaign);
    }

    private function generateQrImage(string $url, int $siteId, string $code): ?string
    {
        // If the `chillerlan/php-qrcode` or `simplesoftwareio/simple-qrcode` package is available, use it.
        // Otherwise fall back to storing the Google Charts URL as reference.
        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            try {
                $png = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(300)->generate($url);
                $path = "growbuilder/qr/{$siteId}/{$code}.png";
                \Illuminate\Support\Facades\Storage::disk('public')->put($path, $png);
                return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            } catch (\Throwable) {
                // Fall through to Google Charts
            }
        }

        // Fallback: return null (frontend will use Google Charts API directly)
        return null;
    }

    public function getGoogleChartsUrl(string $url): string
    {
        return 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($url) . '&choe=UTF-8';
    }
}
