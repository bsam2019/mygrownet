<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\HabitService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HabitController extends Controller
{
    public function __construct(protected HabitService $habitService) {}

    public function index()
    {
        $userId = auth()->id();

        return Inertia::render('LifePlus/Tasks/Habits', [
            'habits' => $this->habitService->getHabits($userId),
            'weekProgress' => $this->habitService->getWeekProgress($userId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'frequency' => 'nullable|in:daily,weekly',
            'reminder_time' => 'nullable|date_format:H:i',
        ]);

        $habit = $this->habitService->createHabit(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($habit, 201);
        }

        return back()->with('success', 'Habit created');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'frequency' => 'nullable|in:daily,weekly',
            'reminder_time' => 'nullable|date_format:H:i',
            'is_active' => 'nullable|boolean',
        ]);

        $habit = $this->habitService->updateHabit($id, auth()->id(), $validated);

        if (!$habit) {
            return response()->json(['error' => 'Habit not found'], 404);
        }

        if ($request->wantsJson()) {
            return response()->json($habit);
        }

        return back()->with('success', 'Habit updated');
    }

    public function log(Request $request, int $id)
    {
        $date = $request->get('date');
        $habit = $this->habitService->logHabit($id, auth()->id(), $date);

        if (!$habit) {
            return back()->with('error', 'Habit not found');
        }

        return back()->with('success', 'Habit logged');
    }

    public function destroy(int $id)
    {
        $deleted = $this->habitService->deleteHabit($id, auth()->id());

        if (!$deleted) {
            return response()->json(['error' => 'Habit not found'], 404);
        }

        return back()->with('success', 'Habit deleted');
    }

    public function weekProgress()
    {
        return response()->json(
            $this->habitService->getWeekProgress(auth()->id())
        );
    }
}
