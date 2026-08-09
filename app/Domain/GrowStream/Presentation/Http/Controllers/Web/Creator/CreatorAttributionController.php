<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web\Creator;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Services\AttributionAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatorAttributionController
{
    public function __construct(
        private AttributionAnalyticsService $attributionService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $creator = CreatorProfile::where('user_id', $user->id)->first();
        $creatorId = $creator?->id ?? 0;

        $summary = $this->attributionService->getSourceBreakdown($creatorId);

        $channelSlug = $creator?->channel_slug ?? strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $user->name));
        $shareableUrl = url("/c/{$channelSlug}");

        return Inertia::render('GrowStream/Creator/AttributionAnalytics', [
            'summary' => $summary,
            'shareableUrl' => $shareableUrl,
        ]);
    }
}
