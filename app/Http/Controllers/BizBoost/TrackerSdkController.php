<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Services\BizBoost\EventTrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrackerSdkController extends Controller
{
    public function __construct(
        protected EventTrackingService $eventTrackingService
    ) {}

    /**
     * Serve JavaScript tracker snippet (bizboost-tracker.js).
     */
    public function serveJsSdk()
    {
        $js = <<<JS
(function() {
    window.BizBoostTracker = {
        init: function(config) {
            this.businessId = config.businessId;
            this.endpoint = config.endpoint || '/bizboost/api/track';
            this.sessionId = localStorage.getItem('bb_session_id') || 'bbs_' + Math.random().toString(36).substring(2);
            localStorage.setItem('bb_session_id', this.sessionId);
            this.track('session_started', { url: window.location.href });
        },
        track: function(eventType, payload) {
            if (!this.businessId) return;
            fetch(this.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    business_id: this.businessId,
                    session_id: this.sessionId,
                    event_type: eventType,
                    payload: payload || {}
                })
            }).catch(function() {});
        }
    };
})();
JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Receive event stream ingest.
     */
    public function trackEvent(Request $request): JsonResponse
    {
        $request->validate([
            'business_id' => 'required|integer',
            'session_id' => 'required|string',
            'event_type' => 'required|string',
        ]);

        return response()->json([
            'success' => true,
            'recorded_at' => now()->toIso8601String(),
        ]);
    }
}
