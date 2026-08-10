<?php

namespace App\Services\GrowBuilder;

use App\Domain\GrowBuilder\Contracts\AiGeneratorEngineInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * RetentionDigestService — monthly business performance summary email engine.
 *
 * Generates and sends a monthly performance digest to GrowBuilder site owners
 * with AI-powered improvement suggestions to drive re-engagement.
 *
 * §28 of GROWBUILDER_PLATFORM.md
 */
class RetentionDigestService
{
    public function __construct(
        private BusinessProfileService $profileService,
        private SeoSchemaService $seoService,
    ) {}

    /**
     * Generate a retention digest summary for a site.
     *
     * @param  int $siteId
     * @param  string $periodStart  'Y-m-d' start of reporting period
     * @param  string $periodEnd    'Y-m-d' end of reporting period
     * @return array                Digest data payload
     */
    public function generateDigest(int $siteId, string $periodStart, string $periodEnd): array
    {
        $site    = DB::table('growbuilder_sites')->where('id', $siteId)->first();
        $profile = $this->profileService->getOrCreateForSite($siteId);

        // Collect analytics for the period
        $analytics = $this->collectAnalytics($siteId, $periodStart, $periodEnd);

        // Generate AI improvement suggestions if provider is available
        $suggestions = $this->generateSuggestions($analytics, $profile, $site);

        // Calculate site freshness (days since last edit)
        $daysSinceLastEdit = now()->diffInDays($site->updated_at ?? now());

        return [
            'site_id'            => $siteId,
            'site_name'          => $site->name ?? 'Your Business',
            'period_start'       => $periodStart,
            'period_end'         => $periodEnd,
            'business_name'      => $profile['trade_name'] ?? $profile['legal_name'] ?? $site->name,
            'analytics'          => $analytics,
            'suggestions'        => $suggestions,
            'days_since_edit'    => $daysSinceLastEdit,
            'freshness_warning'  => $daysSinceLastEdit > 45,
            'site_url'           => $site->custom_domain
                ? "https://{$site->custom_domain}"
                : url("/sites/{$site->subdomain}"),
        ];
    }

    /**
     * Collect business outcome and site health metrics for the period.
     */
    private function collectAnalytics(int $siteId, string $start, string $end): array
    {
        $pages = DB::table('growbuilder_page_views')
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $uniqueVisitors = DB::table('growbuilder_page_views')
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->distinct('ip_address')
            ->count('ip_address');

        $enquiries = DB::table('growbuilder_form_submissions')
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $orders = DB::table('growbuilder_orders')
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $revenue = DB::table('growbuilder_orders')
            ->where('site_id', $siteId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');

        // AI usage
        $aiPrompts = DB::table('growbuilder_ai_usage')
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // Top referral source
        $topReferrer = DB::table('growbuilder_page_views')
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->orderByRaw('COUNT(*) DESC')
            ->value('referrer');

        return [
            'page_views'       => $pages,
            'unique_visitors'  => $uniqueVisitors,
            'enquiries'        => $enquiries,
            'orders'           => $orders,
            'revenue_zmw'      => (float) $revenue,
            'ai_prompts_used'  => $aiPrompts,
            'top_referrer'     => $topReferrer ? parse_url($topReferrer, PHP_URL_HOST) : 'Direct',
        ];
    }

    /**
     * Generate improvement suggestions based on analytics data and business profile gaps.
     */
    private function generateSuggestions(array $analytics, array $profile, object $site): array
    {
        $suggestions = [];

        // Site freshness
        $daysSinceEdit = now()->diffInDays($site->updated_at ?? now());
        if ($daysSinceEdit > 45) {
            $suggestions[] = [
                'priority' => 'high',
                'text'     => "Your site hasn't been updated in {$daysSinceEdit} days — update your product prices or add new photos to keep it fresh.",
                'action'   => 'update_site',
            ];
        }

        // Missing WhatsApp
        if (empty($profile['whatsapp'])) {
            $suggestions[] = [
                'priority' => 'high',
                'text'     => 'Add your WhatsApp number — businesses with WhatsApp integration receive 40% more enquiries.',
                'action'   => 'add_whatsapp',
            ];
        }

        // Missing TPIN/PACRA trust badges
        if (empty($profile['tpin']) && empty($profile['pacra_number'])) {
            $suggestions[] = [
                'priority' => 'medium',
                'text'     => 'Add your TPIN or PACRA number to your Business Profile — trust badges increase customer confidence.',
                'action'   => 'add_trust_badges',
            ];
        }

        // Missing opening hours
        if (empty($profile['opening_hours'])) {
            $suggestions[] = [
                'priority' => 'medium',
                'text'     => 'Add your opening hours — customers want to know when you are open before making the trip.',
                'action'   => 'add_hours',
            ];
        }

        // Low enquiry-to-order conversion
        if ($analytics['enquiries'] > 0 && $analytics['orders'] === 0) {
            $suggestions[] = [
                'priority' => 'medium',
                'text'     => "You received {$analytics['enquiries']} enquiries but no completed orders. Consider adding a WhatsApp order button directly on your products.",
                'action'   => 'add_whatsapp_order',
            ];
        }

        // Try AI-powered suggestions if available
        try {
            $aiProvider = app(AiGeneratorEngineInterface::class);
            if ($aiProvider->isAvailable()) {
                $aiDigest = $aiProvider->generateRetentionDigest($analytics, $profile);
                foreach ($aiDigest['suggestions'] ?? [] as $aiSuggestion) {
                    $suggestions[] = array_merge(['priority' => 'medium', 'source' => 'ai'], $aiSuggestion);
                }
            }
        } catch (\Throwable $e) {
            Log::debug('GrowBuilder: AI retention suggestions unavailable', ['error' => $e->getMessage()]);
        }

        return array_slice($suggestions, 0, 5); // Cap at 5 suggestions
    }
}
