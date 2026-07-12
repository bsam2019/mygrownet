<?php

namespace App\Http\Controllers\GrowStart;

use App\Http\Controllers\Controller;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;
use App\Domain\GrowStart\Repositories\TaskRepositoryInterface;
use App\Domain\GrowStart\Services\TaskCompletionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function __construct(
        private JourneyRepositoryInterface $journeyRepository,
        private StageRepositoryInterface $stageRepository,
        private TaskRepositoryInterface $taskRepository,
        private TaskCompletionService $taskCompletionService
    ) {}

    public function index(Request $request, string $slug)
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return redirect()->route('growstart.onboarding');
        }

        $stage = $this->stageRepository->findBySlug($slug);

        if (!$stage) {
            abort(404, 'Stage not found');
        }

        $tasks = $this->taskRepository->findByStage(
            $stage->getId(),
            $journey->getIndustryId(),
            $journey->getCountryId()
        );

        $userTasks = $this->taskRepository->findUserTasksByStage($journey->id(), $stage->getId());
        $userTasksMap = $userTasks->keyBy(fn($ut) => $ut->getTaskId());

        $tasksWithProgress = $tasks->map(function ($task) use ($userTasksMap) {
            $taskArray = $task->toArray();
            $userTask = $userTasksMap->get($task->getId());
            $taskArray['user_task'] = $userTask?->toArray();
            return $taskArray;
        });

        return Inertia::render('GrowStart/Journey/TaskList', [
            'stage' => $stage->toArray(),
            'tasks' => $tasksWithProgress->toArray(),
        ]);
    }

    public function show(Request $request, int $id)
    {
        $task = $this->taskRepository->findById($id);

        if (!$task) {
            abort(404, 'Task not found');
        }

        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);
        $userTask = $journey 
            ? $this->taskRepository->findUserTask($journey->id(), $id) 
            : null;

        return Inertia::render('GrowStart/Journey/TaskDetail', [
            'task' => $task->toArray(),
            'userTask' => $userTask?->toArray(),
        ]);
    }

    public function complete(Request $request, int $id)
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return back()->withErrors(['journey' => 'No active journey found.']);
        }

        $this->taskCompletionService->completeTask($journey->id(), $id);

        return back()->with('success', 'Task completed!');
    }

    public function skip(Request $request, int $id)
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return back()->withErrors(['journey' => 'No active journey found.']);
        }

        $this->taskCompletionService->skipTask($journey->id(), $id);

        return back()->with('success', 'Task skipped.');
    }

    public function start(Request $request, int $id)
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return back()->withErrors(['journey' => 'No active journey found.']);
        }

        $this->taskCompletionService->startTask($journey->id(), $id);

        return back()->with('success', 'Task started.');
    }

    public function updateNotes(Request $request, int $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:5000',
        ]);

        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return back()->withErrors(['journey' => 'No active journey found.']);
        }

        $this->taskCompletionService->updateTaskNotes($journey->id(), $id, $validated['notes']);

        return back()->with('success', 'Notes updated.');
    }

    // API Methods
    public function apiIndex(Request $request, string $slug): JsonResponse
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);
        $stage = $this->stageRepository->findBySlug($slug);

        if (!$stage) {
            return response()->json(['error' => 'Stage not found'], 404);
        }

        $tasks = $this->taskRepository->findByStage(
            $stage->getId(),
            $journey?->getIndustryId(),
            $journey?->getCountryId()
        );

        return response()->json([
            'data' => $tasks->map(fn($t) => $t->toArray())->toArray(),
        ]);
    }

    public function apiComplete(Request $request, int $id): JsonResponse
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return response()->json(['error' => 'No active journey'], 404);
        }

        $userTask = $this->taskCompletionService->completeTask($journey->id(), $id);

        return response()->json(['data' => $userTask->toArray()]);
    }

    public function apiSkip(Request $request, int $id): JsonResponse
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return response()->json(['error' => 'No active journey'], 404);
        }

        $userTask = $this->taskCompletionService->skipTask($journey->id(), $id);

        return response()->json(['data' => $userTask->toArray()]);
    }
}
