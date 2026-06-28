<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\TaskService;
use App\Domain\LifePlus\Services\HabitService;
use App\Domain\LifePlus\Services\ExpenseService;
use App\Domain\LifePlus\Services\KnowledgeService;
use App\Domain\LifePlus\Services\ProfileService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        protected TaskService $taskService,
        protected HabitService $habitService,
        protected ExpenseService $expenseService,
        protected KnowledgeService $knowledgeService,
        protected ProfileService $profileService
    ) {}

    public function index()
    {
        $userId = auth()->id();
        $user = auth()->user();

        // Check if user needs onboarding
        if (!$user->lifeplus_onboarded) {
            return redirect()->route('lifeplus.onboarding');
        }

        return Inertia::render('LifePlus/Home', [
            'todayTasks' => $this->taskService->getTodayTasks($userId),
            'taskStats' => $this->taskService->getStats($userId),
            'habits' => $this->habitService->getWeekProgress($userId),
            'monthSummary' => $this->expenseService->getMonthSummary($userId),
            'dailyTip' => $this->knowledgeService->getDailyTip(),
        ]);
    }

    public function onboarding()
    {
        $user = auth()->user();

        // If already onboarded, redirect to home
        if ($user->lifeplus_onboarded) {
            return redirect()->route('lifeplus.home');
        }

        return Inertia::render('LifePlus/Onboarding', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'categories' => $this->expenseService->getCategories(auth()->id()),
        ]);
    }

    public function completeOnboarding(Request $request)
    {
        $validated = $request->validate([
            'monthly_budget' => 'nullable|numeric|min:0',
            'selected_categories' => 'nullable|array',
            'habits' => 'nullable|array',
            'habits.*.name' => 'required|string|max:100',
        ]);

        $userId = auth()->id();

        // Set up initial budget if provided
        if (!empty($validated['monthly_budget'])) {
            app(\App\Domain\LifePlus\Services\BudgetService::class)->createBudget($userId, [
                'amount' => $validated['monthly_budget'],
                'period' => 'monthly',
            ]);
        }

        // Create initial habits if provided
        if (!empty($validated['habits'])) {
            foreach ($validated['habits'] as $habit) {
                $this->habitService->createHabit($userId, [
                    'name' => $habit['name'],
                    'frequency' => 'daily',
                ]);
            }
        }

        // Mark user as onboarded
        auth()->user()->update(['lifeplus_onboarded' => true]);

        return redirect()->route('lifeplus.home')
            ->with('success', 'Welcome to Life+! Your setup is complete.');
    }
}
