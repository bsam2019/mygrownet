<?php

namespace App\Services\BizBoost;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerHubService
{
    /**
     * Get aggregated single customer view profile.
     */
    public function getCustomerProfile(int $businessId, int $customerId): ?array
    {
        $customer = DB::table('bizboost_customers')
            ->where('business_id', $businessId)
            ->where('id', $customerId)
            ->first();

        if (!$customer) {
            return null;
        }

        // Tags
        $tags = DB::table('bizboost_customer_tags as t')
            ->join('bizboost_customer_tag_pivot as p', 't.id', '=', 'p.tag_id')
            ->where('p.customer_id', $customerId)
            ->select('t.id', 't.name', 't.color')
            ->get();

        // Leads history
        $leads = collect([]);
        if (Schema::hasTable('bizboost_leads')) {
            $leads = DB::table('bizboost_leads as l')
                ->leftJoin('bizboost_pipeline_stages as s', 'l.stage_id', '=', 's.id')
                ->where('l.customer_id', $customerId)
                ->select('l.*', 's.name as stage_name', 's.color as stage_color')
                ->orderBy('l.created_at', 'desc')
                ->get();
        }

        // Attributed purchases & quotes (from StockFlow / BizDocs / GrowMart)
        $attributions = collect([]);
        if (Schema::hasTable('bizboost_attributions')) {
            $attributions = DB::table('bizboost_attributions')
                ->where('customer_id', $customerId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Calculate total Customer Lifetime Value (CLV)
        $clvZmw = (float) ($customer->clv_zmw ?? $customer->total_spent ?? 0);

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'whatsapp' => $customer->whatsapp,
            'address' => $customer->address,
            'notes' => $customer->notes,
            'source' => $customer->source ?? 'direct',
            'intent_score' => (int) ($customer->intent_score ?? 0),
            'intent_tier' => $customer->intent_tier ?? 'low',
            'clv_zmw' => $clvZmw,
            'total_orders' => (int) ($customer->total_orders ?? 0),
            'last_purchase_at' => $customer->last_purchase_at,
            'tags' => $tags,
            'leads' => $leads,
            'attributions' => $attributions,
        ];
    }
}
