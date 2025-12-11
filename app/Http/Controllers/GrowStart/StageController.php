<?php

namespace App\Http\Controllers\GrowStart;

use App\Http\Controllers\Controller;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;
use App\Domain\GrowStart\Repositories\TaskRepositoryInterface;
use App\Domain\GrowStart\Services\JourneyProgressService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class StageController extends Controller
{
    public function __construct(
        private JourneyRepositoryInterface $journeyRepository,
        private StageRepositoryInterface $stageRepository,
        private TaskRepositoryInterface $taskRepository,
        private JourneyProgressService $progressService
    ) {}

    public function index(Request $request): Response
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return redirect()->route('growstart.onboarding');
        }

        $stages = $this->stageRepository->findActive();
        $progress = $this->progressService->calculateProgress($journey);

        return Inertia::render('GrowStart/Stages/Index', [
            'journey' => $journey->toArray(),
            'stages' => $stages->map(fn($s) => $s->toArray())->toArray(),
            'progress' => $progress->toArray(),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return redirect()->route('growstart.onboarding');
        }

        $stage = $this->stageRepository->findBySlug($slug);

        if (!$stage) {
            abort(404, 'Stage not found');
        }

        // Get tasks for this stage
        $tasks = $this->taskRepository->findByStage(
            $stage->getId(),
            $journey->getIndustryId(),
            $journey->getCountryId()
        );

        // Get user's task progress
        $userTasks = $this->taskRepository->findUserTasksByStage($journey->id(), $stage->getId());
        $userTasksMap = $userTasks->keyBy(fn($ut) => $ut->getTaskId());

        // Merge task data with user progress
        $tasksWithProgress = $tasks->map(function ($task) use ($userTasksMap) {
            $taskArray = $task->toArray();
            $userTask = $userTasksMap->get($task->getId());
            $taskArray['user_task'] = $userTask?->toArray();
            return $taskArray;
        });

        $stageProgress = $this->progressService->calculateStageProgress($journey, $stage->getId());
        
        // Get prev/next stages for navigation
        $allStages = $this->stageRepository->findActive();
        $stageIndex = $allStages->search(fn($s) => $s->getId() === $stage->getId());
        $prevStage = $stageIndex > 0 ? $allStages[$stageIndex - 1] : null;
        $nextStage = $stageIndex < $allStages->count() - 1 ? $allStages[$stageIndex + 1] : null;

        return Inertia::render('GrowStart/Stages/Show', [
            'journey' => $journey->toArray(),
            'stage' => $stage->toArray(),
            'tasks' => $tasksWithProgress->toArray(),
            'progress' => $stageProgress,
            'prevStage' => $prevStage?->toArray(),
            'nextStage' => $nextStage?->toArray(),
        ]);
    }

    // API Methods
    public function apiIndex(Request $request): JsonResponse
    {
        $stages = $this->stageRepository->findActive();

        return response()->json([
            'data' => $stages->map(fn($s) => $s->toArray())->toArray(),
        ]);
    }

    public function apiShow(Request $request, string $slug): JsonResponse
    {
        $stage = $this->stageRepository->findBySlug($slug);

        if (!$stage) {
            return response()->json(['error' => 'Stage not found'], 404);
        }

        return response()->json([
            'data' => $stage->toArray(),
        ]);
    }
}
