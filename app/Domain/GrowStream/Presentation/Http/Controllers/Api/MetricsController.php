<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    private const ALLOWED_EVENTS = [
        'browse.play',
        'watch.start',
        'watch.progress',
        'watch.ended',
        'watch.session',
        'search.submit',
        'signup.subscription',
        'subscription.renewal',
        'creator.subscribe',
        'creator.follow',
        'content.approved',
        'playback.failure',
        'playback.retry',
    ];

    public function record(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event' => 'required|string|max:60',
            'video_id' => 'nullable|integer',
            'creator_id' => 'nullable|integer',
            'session_id' => 'nullable|string|max:64',
            'metadata' => 'nullable|array',
        ]);

        $event = $validated['event'];

        if (! in_array($event, self::ALLOWED_EVENTS, true)) {
            return response()->json(['success' => false, 'error' => 'Unknown event'], 422);
        }

        DB::table('growstream_analytics_events')->insert([
            'event' => $event,
            'user_id' => $request->user()?->id,
            'video_id' => $validated['video_id'] ?? null,
            'creator_id' => $validated['creator_id'] ?? null,
            'session_id' => $validated['session_id'] ?? null,
            'metadata' => isset($validated['metadata'])
                ? json_encode($validated['metadata'])
                : null,
            'occurred_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
