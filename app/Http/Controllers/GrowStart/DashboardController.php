<?php

namespace App\Http\Controllers\GrowStart;

use App\Http\Controllers\Controller;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;
use App\Domain\GrowStart\Repositories\BadgeRepositoryInterface;
use App\Domain\GrowStart\Services\JourneyProgressService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private JourneyRepositoryInterface $journeyRepository,
        private StageRepositoryInterface $stageRepository,
        private JourneyProgressService $progressService,
        private BadgeRepositoryInterface $badgeRepo
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $journey = $this->journeyRepository->findActiveByUserId($user->id);

        if (!$journey) {
            return Inertia::render('GrowStart/Onboarding/Index', [
                'hasExistingJourney' => $this->journeyRepository->findByUserId($user->id) !== null,
            ]);
        }

        $progress = $this->progressService->calculateProgress($journey);
        $timeline = $this->progressService->getTimelineStatus($journey);
        $nextTasks = $this->progressService->getNextTasks($journey, 5);
        $weeklyGoals = $this->progressService->getWeeklyGoals($journey);

        $stages = $this->stageRepository->findActive();

        $recentBadges = $this->badgeRepo->findEarnedByJourneyIdRecent($journey->id(), 3);

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