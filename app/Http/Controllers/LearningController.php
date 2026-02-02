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

        // Get the last accessed module to auto-resume
        $lastAccessedModule = $this->learningService->getLastAccessedModule(auth()->id());
        $moduleProgress = null;
        
        // Fallback: If no in-progress module, get the first incomplete module
        if (!$lastAccessedModule) {
            $completedIds = $completions->pluck('learning_module_id')->toArray();
            $lastAccessedModule = $modules->first(function ($module) use ($completedIds) {
                return !in_array($module->id, $completedIds);
            });
        }
        
        if ($lastAccessedModule) {
            $moduleProgress = $this->learningService->getModuleProgress(auth()->id(), $lastAccessedModule->id);
        }

        return Inertia::render('Learning/Dashboard', [
            'modules' => $modules,
            'categories' => $categories,
            'progress' => $progress,
            'completions' => $completions,
            'selectedCategory' => $category,
            'initialModuleSlug' => $lastAccessedModule?->slug,
            'moduleProgress' => $moduleProgress,
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
        
        // Get module progress (current page)
        $moduleProgress = $this->learningService->getModuleProgress(auth()->id(), $module->id);

        return Inertia::render('Learning/Dashboard', [
            'modules' => $modules,
            'categories' => $categories,
            'progress' => $progress,
            'completions' => $completions,
            'selectedCategory' => null,
            'initialModuleSlug' => $slug,
            'moduleProgress' => $moduleProgress,
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
     * Get progress for a specific module
     */
    public function getProgress(int $moduleId)
    {
        $progress = $this->learningService->getModuleProgress(auth()->id(), $moduleId);
        
        return response()->json($progress ?? [
            'current_page' => 0,
            'is_completed' => false,
            'last_accessed_at' => null,
            'time_spent_seconds' => null,
        ]);
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

    /**
     * Update user's progress (current page) in a module
     */
    public function updateProgress(Request $request, int $moduleId)
    {
        $request->validate([
            'current_page' => 'required|integer|min:0',
        ]);

        try {
            $this->learningService->updateProgress(
                auth()->id(),
                $moduleId,
                $request->input('current_page')
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reset progress for a module (start over)
     */
    public function resetProgress(Request $request, int $moduleId)
    {
        try {
            $this->learningService->resetProgress(auth()->id(), $moduleId);

            return back()->with('success', 'Progress reset. Starting from the beginning.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reset progress: ' . $e->getMessage());
        }
    }
}
