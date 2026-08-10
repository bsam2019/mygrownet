<?php

namespace App\Services\BizBoost;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RevenueAttributionService
{
    /**
     * Record completed sale attribution to campaign/lead.
     */
    public function recordAttribution(int $businessId, array $data): int
    {
        if (!Schema::hasTable('bizboost_attributions')) {
            return 0;
        }

        $id = DB::table('bizboost_attributions')->insertGetId([
            'business_id' => $businessId,
            'customer_id' => $data['customer_id'],
            'lead_id' => $data['lead_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'trackable_link_id' => $data['trackable_link_id'] ?? null,
            'source_type' => $data['source_type'] ?? 'stockflow_pos',
            'source_reference_id' => $data['source_reference_id'],
            'attributed_amount_zmw' => $data['attributed_amount_zmw'],
            'attribution_model' => $data['attribution_model'] ?? 'last_touch',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update campaign attributed revenue if campaign linked
        if (!empty($data['campaign_id']) && Schema::hasTable('bizboost_campaigns')) {
            $totalRevenue = DB::table('bizboost_attributions')
                ->where('campaign_id', $data['campaign_id'])
                ->sum('attributed_amount_zmw');

            $campaign = DB::table('bizboost_campaigns')->where('id', $data['campaign_id'])->first();
            $spend = (float) ($campaign->spend_zmw ?? 0);
            $roiRatio = $spend > 0 ? round($totalRevenue / $spend, 2) : 0;

            DB::table('bizboost_campaigns')
                ->where('id', $data['campaign_id'])
                ->update([
                    'attributed_revenue_zmw' => $totalRevenue,
                    'marketing_roi_ratio' => $roiRatio,
                    'updated_at' => now(),
                ]);
        }

        return $id;
    }
}
