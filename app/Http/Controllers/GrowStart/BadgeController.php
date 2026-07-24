<?php

namespace App\Http\Controllers\GrowStart;

use App\Http\Controllers\Controller;
use App\Domain\GrowStart\Repositories\BadgeRepositoryInterface;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class BadgeController extends Controller
{
    public function __construct(
        private BadgeRepositoryInterface $badgeRepo,
        private JourneyRepositoryInterface $journeyRepository
    ) {}

    public function index(Request $request): Response
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        $allBadges = $this->badgeRepo->findAll();

        $earnedBadges = collect([]);
        if ($journey) {
            $earnedBadges = $this->badgeRepo->findEarnedByJourneyId($journey->id());
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

        $earnedBadges = $this->badgeRepo->findEarnedByJourneyId($journey->id());

        return Inertia::render('GrowStart/Badges/Earned', [
            'badges' => $earnedBadges,
        ]);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        $allBadges = $this->badgeRepo->findAll();

        $earnedBadgeIds = [];
        if ($journey) {
            $earnedBadgeIds = $this->badgeRepo->findEarnedByJourneyId($journey->id())
                ->pluck('id')
                ->toArray();
        }

        $badges = $allBadges->map(function ($badge) use ($earnedBadgeIds) {
            return [
                'id' => $badge['id'],
                'name' => $badge['name'],
                'slug' => $badge['slug'],
                'description' => $badge['description'],
                'icon' => $badge['icon'],
                'points' => $badge['points'],
                'earned' => in_array($badge['id'], $earnedBadgeIds),
            ];
        });

        return response()->json(['data' => $badges]);
    }
}