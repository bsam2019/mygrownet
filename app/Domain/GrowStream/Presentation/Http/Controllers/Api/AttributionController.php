<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Api;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Services\AttributionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttributionController extends Controller
{
    public function __construct(
        private AttributionService $attribution,
    ) {}

    /**
     * Resolve a creator shareable link landing. Records the source + visitor
     * session silently. Public (pre-auth) — a visitor may not be logged in.
     */
    public function resolve(Request $request): JsonResponse
    {
        $request->validate([
            'creator_id' => 'required|integer',
            'source' => 'nullable|string|max:60',
            'visitor_session_id' => 'nullable|string|max:64',
        ]);

        $creator = CreatorProfile::where('id', $request->creator_id)->where('is_active', true)->first();
        if (! $creator) {
            return response()->json(['success' => false, 'error' => 'Unknown creator'], 404);
        }

        $session = $request->filled('visitor_session_id')
            ? (string) $request->visitor_session_id
            : $this->attribution->newSessionId();

        $this->attribution->resolve((int) $creator->id, $request->source, $session);

        return response()->json([
            'success' => true,
            'visitor_session_id' => $session,
        ]);
    }

    /**
     * Bind a conversion on sign-up/subscription. Requires auth (the user now
     * has an identity). Updates every event row for the session.
     */
    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'visitor_session_id' => 'required|string|max:64',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }

        $this->attribution->recordConversion($request->visitor_session_id, $user->id);

        return response()->json(['success' => true]);
    }
}
