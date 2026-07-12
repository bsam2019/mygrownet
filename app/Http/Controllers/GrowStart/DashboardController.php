<?php

namespace App\Http\Controllers\GrowStart;

use App\Http\Controllers\Controller;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;
use App\Domain\GrowStart\Services\JourneyProgressService;
use App\Models\GrowStart\Badge;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private JourneyRepositoryInterface $journeyRepository,
        private StageRepositoryInterface $stageRepository,
        private JourneyProgressService $progressService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $journey = $this->journeyRepository->findActiveByUserId($user->id);

        // If no active journey, redirect to onboarding
        if (!$journey) {
            return Inertia::render('GrowStart/Onboarding/Index', [
                'hasExistingJourney' => $this->journeyRepository->findByUserId($user->id) !== null,
            ]);
        }

        // Calculate progress
        $progress = $this->progressService->calculateProgress($journey);
        $timeline = $this->progressService->getTimelineStatus($journey);
        $nextTasks = $this->progressService->getNextTasks($journey, 5);
        $weeklyGoals = $this->progressService->getWeeklyGoals($journey);

        // Get stages
        $stages = $this->stageRepository->findActive();

        // Get recent badges
        $recentBadges = Badge::whereHas('journeys', function ($q) use ($journey) {
            $q->where('growstart_user_badges.user_journey_id', $journey->id());
        })->latest()->take(3)->get();

        return Inertia::render('GrowStart/Dashboard', [
            'journey' => $journey->toArray(),
            'progress' => $progress->toArray(),
            'timeline' => $timeline,
            'nextTasks' => $nextTasks,
            'weeklyGoals' => $weeklyGoals,
            'stages' => $stages->map(fn($s) => $s->toArray())->toArray(),
            'recentBadges' => $recentBadges,
        ]);
    }
}
