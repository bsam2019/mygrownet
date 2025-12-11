<?php

namespace App\Http\Controllers\GrowStart;

use App\Http\Controllers\Controller;
use App\Models\GrowStart\Badge;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class BadgeController extends Controller
{
    public function __construct(
        private JourneyRepositoryInterface $journeyRepository
    ) {}

    public function index(Request $request): Response
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        $allBadges = Badge::all();
        
        $earnedBadges = collect([]);
        if ($journey) {
            $earnedBadges = Badge::whereHas('journeys', function ($q) use ($journey) {
                $q->where('growstart_user_badges.user_journey_id', $journey->id());
            })->get();
        }

        $earnedBadgeIds = $earnedBadges->pluck('id')->toArray();
        $totalPoints = $earnedBadges->sum('points');

        return Inertia::render('GrowStart/Badges/Index', [
            'badges' => $allBadges,
            'earnedBadges' => $earnedBadges,
            'journey' => $journey?->toArray(),
            'totalPoints' => $totalPoints,
        ]);
    }

    public function earned(Request $request): Response
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return Inertia::render('GrowStart/Badges/Earned', [
                'badges' => [],
            ]);
        }

        $earnedBadges = Badge::whereHas('journeys', function ($q) use ($journey) {
            $q->where('user_journey_id', $journey->id());
        })->get();

        return Inertia::render('GrowStart/Badges/Earned', [
            'badges' => $earnedBadges,
        ]);
    }

    // API Methods
    public function apiIndex(Request $request): JsonResponse
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        $allBadges = Badge::all();
        
        $earnedBadgeIds = [];
        if ($journey) {
            $earnedBadgeIds = Badge::whereHas('journeys', function ($q) use ($journey) {
                $q->where('user_journey_id', $journey->id());
            })->pluck('id')->toArray();
        }

        $badges = $allBadges->map(function ($badge) use ($earnedBadgeIds) {
            return [
                'id' => $badge->id,
                'name' => $badge->name,
                'slug' => $badge->slug,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'points' => $badge->points,
                'earned' => in_array($badge->id, $earnedBadgeIds),
            ];
        });

        return response()->json(['data' => $badges]);
    }
}
