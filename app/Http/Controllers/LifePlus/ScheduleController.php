<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\ScheduleService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function __construct(protected ScheduleService $scheduleService) {}

    public function index(Request $request)
    {
        $userId = auth()->id();
        $date = $request->get('date', now()->toDateString());

        return Inertia::render('LifePlus/Schedule/Index', [
            'schedule' => $this->scheduleService->getScheduleForDate($userId, $date),
            'stats' => $this->scheduleService->getStats($userId),
        ]);
    }

    public function week(Request $request)
    {
        $userId = auth()->id();
        $startDate = $request->get('start_date', now()->startOfWeek()->toDateString());

        return Inertia::render('LifePlus/Schedule/Week', [
            'schedule' => $this->scheduleService->getScheduleForWeek($userId, $startDate),
            'stats' => $this->scheduleService->getStats($userId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'category' => 'nullable|in:work,personal,health,learning,social,other',
            'color' => 'nullable|string|max:7',
            'task_id' => 'nullable|exists:lifeplus_tasks,id',
            'is_recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'nullable|in:daily,weekly,weekdays,weekends',
            'recurrence_end_date' => 'nullable|date|after:date',
            'local_id' => 'nullable|string',
        ]);

        try {
            $block = $this->scheduleService->createScheduleBlock(auth()->id(), $validated);

            if ($request->wantsJson()) {
                return response()->json($block, 201);
            }

            return back()->with('success', 'Schedule block created');
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->withErrors(['time' => $e->getMessage()]);
        }
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'category' => 'nullable|in:work,personal,health,learning,social,other',
            'color' => 'nullable|string|max:7',
            'task_id' => 'nullable|exists:lifeplus_tasks,id',
        ]);

        try {
            $block = $this->scheduleService->updateScheduleBlock($id, auth()->id(), $validated);

            if (!$block) {
                return response()->json(['error' => 'Schedule block not found'], 404);
            }

            if ($request->wantsJson()) {
                return response()->json($block);
            }

            return back()->with('success', 'Schedule block updated');
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->withErrors(['time' => $e->getMessage()]);
        }
    }

    public function toggle(int $id)
    {
        $block = $this->scheduleService->toggleScheduleBlock($id, auth()->id());

        if (!$block) {
            return response()->json(['error' => 'Schedule block not found'], 404);
        }

        return response()->json($block);
    }

    public function destroy(int $id)
    {
        $deleted = $this->scheduleService->deleteScheduleBlock($id, auth()->id());

        if (!$deleted) {
            return response()->json(['error' => 'Schedule block not found'], 404);
        }

        return back()->with('success', 'Schedule block deleted');
    }
}
