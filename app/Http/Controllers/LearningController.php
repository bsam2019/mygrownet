<?php

namespace App\Http\Controllers;

use App\Application\Services\Learning\LearningService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LearningController extends Controller
{
    public function __construct(
        private LearningService $learningService
    ) {}

    /**
     * Display learning modules dashboard (unified view)
     */
    public function index(Request $request)
    {
        $category = $request->query('category');
        
        $modules = $this->learningService->getPublishedModules($category);
        $categories = $this->learningService->getCategories();
        $progress = $this->learningService->getUserProgress(auth()->id());
        $completions = $this->learningService->getUserCompletions(auth()->id());

        return Inertia::render('Learning/Dashboard', [
            'modules' => $modules,
            'categories' => $categories,
            'progress' => $progress,
            'completions' => $completions,
            'selectedCategory' => $category,
        ]);
    }

    /**
     * Display a single learning module (loads in unified dashboard)
     */
    public function show(string $slug)
    {
        $module = $this->learningService->getModuleBySlug($slug);

        if (!$module) {
            abort(404, 'Learning module not found');
        }

        // Get all modules for sidebar
        $modules = $this->learningService->getPublishedModules(null);
        $categories = $this->learningService->getCategories();
        $progress = $this->learningService->getUserProgress(auth()->id());
        $completions = $this->learningService->getUserCompletions(auth()->id());

        return Inertia::render('Learning/Dashboard', [
            'modules' => $modules,
            'categories' => $categories,
            'progress' => $progress,
            'completions' => $completions,
            'selectedCategory' => null,
            'initialModuleSlug' => $slug,
        ]);
    }

    /**
     * Mark module as started
     */
    public function start(Request $request, int $moduleId)
    {
        try {
            $this->learningService->startModule(auth()->id(), $moduleId);

            return back()->with('success', 'Module started successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start module: ' . $e->getMessage());
        }
    }

    /**
     * Complete a learning module
     */
    public function complete(Request $request, int $moduleId)
    {
        $request->validate([
            'time_spent_seconds' => 'nullable|integer|min:0',
        ]);

        try {
            $completed = $this->learningService->completeModule(
                auth()->id(),
                $moduleId,
                $request->input('time_spent_seconds')
            );

            if ($completed) {
                return back()->with('success', 'Module completed! LGR activity recorded.');
            }

            return back()->with('info', 'Module already completed');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to complete module: ' . $e->getMessage());
        }
    }
}
