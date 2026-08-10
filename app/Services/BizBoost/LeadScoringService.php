<?php

namespace App\Services\BizBoost;

use Illuminate\Support\Facades\DB;

class LeadScoringService
{
    /**
     * Weights for intent scoring actions.
     */
    private const WEIGHTS = [
        'page_view' => 5,
        'product_view' => 5,
        'pricing_view' => 10,
        'whatsapp_click' => 20,
        'form_submit' => 30,
        'quote_request' => 30,
        'appointment_book' => 30,
    ];

    /**
     * Recalculate intent score and intent tier for a customer.
     */
    public function calculateCustomerIntent(int $customerId): array
    {
        $events = DB::table('bizboost_analytics_events')
            ->where('customer_id', $customerId)
            ->get();

        $score = 0;
        foreach ($events as $event) {
            $eventType = $event->event_type ?? $event->type ?? '';
            $score += self::WEIGHTS[$eventType] ?? 5;
        }

        // Determine intent tier
        $tier = 'low';
        if ($score >= 81) {
            $tier = 'high_intent';
        } elseif ($score >= 51) {
            $tier = 'hot';
        } elseif ($score >= 21) {
            $tier = 'interested';
        }

        // Update customer record
        DB::table('bizboost_customers')
            ->where('id', $customerId)
            ->update([
                'intent_score' => $score,
                'intent_tier' => $tier,
                'updated_at' => now(),
            ]);

        return [
            'score' => $score,
            'tier' => $tier,
        ];
    }
}
