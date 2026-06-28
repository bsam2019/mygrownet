<?php

namespace App\Application\Services\Learning;

use App\Infrastructure\Persistence\Eloquent\Learning\LearningModuleModel;
use App\Infrastructure\Persistence\Eloquent\Learning\LearningCompletionModel;
use App\Services\LgrActivityTrackingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LearningService
{
    public function __construct(
        private LgrActivityTrackingService $lgrTrackingService
    ) {}

    /**
     * Get all published learning modules
     */
    public function getPublishedModules(?string $category = null)
    {
        $query = LearningModuleModel::published()
            ->orderBy('sort_order')
            ->orderBy('created_at');

        if ($category) {
            $query->byCategory($category);
        }

        return $query->get();
    }

    /**
     * Get a single module by slug
     */
    public function getModuleBySlug(string $slug): ?LearningModuleModel
    {
        return LearningModuleModel::where('slug', $slug)
            ->published()
            ->first();
    }

    /**
     * Check if user has completed a module
     */
    public function hasUserCompletedModule(int $userId, int $moduleId): bool
    {
        return LearningCompletionModel::where('user_id', $userId)
            ->where('learning_module_id', $moduleId)
            ->whereNotNull('completed_at')
            ->exists();
    }

    /**
     * Get user's completed modules
     */
    public function getUserCompletions(int $userId)
    {
        return LearningCompletionModel::forUser($userId)
            ->with('module')
            ->orderBy('completed_at', 'desc')
            ->get();
    }

    /**
     * Get user's completion count
     */
    public function getUserCompletionCount(int $userId): int
    {
        return LearningCompletionModel::forUser($userId)->count();
    }

    /**
     * Get user's completion count for today
     */
    public function getUserCompletionCountToday(int $userId): int
    {
        return LearningCompletionModel::forUser($userId)
            ->completedToday()
            ->count();
    }

    /**
     * Mark module as started
     */
    public function startModule(int $userId, int $moduleId): void
    {
        // Check if already completed
        if ($this->hasUserCompletedModule($userId, $moduleId)) {
            return;
        }

        // Check if already started
        $existing = LearningCompletionModel::where('user_id', $userId)
            ->where('learning_module_id', $moduleId)
            ->first();

        if (!$existing) {
            LearningCompletionModel::create([
                'user_id' => $userId,
                'learning_module_id' => $moduleId,
                'started_at' => now(),
                'completed_at' => now(), // Will be updated on completion
            ]);
        }
    }

    /**
     * Complete a learning module
     * CRITICAL: This triggers LGR activity tracking
     */
    public function completeModule(int $userId, int $moduleId, ?int $timeSpentSeconds = null): bool
    {
        try {
            DB::beginTransaction();

            // Get the module
            $module = LearningModuleModel::findOrFail($moduleId);

            // Check if already completed
            if ($this->hasUserCompletedModule($userId, $moduleId)) {
                DB::rollBack();
                return false;
            }

            // Create or update completion record
            LearningCompletionModel::updateOrCreate(
                [
                    'user_id' => $userId,
                    'learning_module_id' => $moduleId,
                ],
                [
                    'completed_at' => now(),
                    'time_spent_seconds' => $timeSpentSeconds,
                ]
            );

            // CRITICAL: Record LGR activity
            $this->lgrTrackingService->recordLearningActivity($userId, $module->title);

            DB::commit();

            Log::info('Learning module completed', [
                'user_id' => $userId,
                'module_id' => $moduleId,
                'module_title' => $module->title,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete learning module: ' . $e->getMessage(), [
                'user_id' => $userId,
                'module_id' => $moduleId,
            ]);
            throw $e;
        }
    }

    /**
     * Get user's progress statistics
     */
    public function getUserProgress(int $userId): array
    {
        $totalModules = LearningModuleModel::published()->count();
        $completedModules = $this->getUserCompletionCount($userId);
        $completedToday = $this->getUserCompletionCountToday($userId);

        return [
            'total_modules' => $totalModules,
            'completed_modules' => $completedModules,
            'completed_today' => $completedToday,
            'completion_percentage' => $totalModules > 0 
                ? round(($completedModules / $totalModules) * 100, 2) 
                : 0,
        ];
    }

    /**
     * Get module progress for a user (current page, completion status)
     */
    public function getModuleProgress(int $userId, int $moduleId): ?array
    {
        $completion = LearningCompletionModel::where('user_id', $userId)
            ->where('learning_module_id', $moduleId)
            ->first();

        if (!$completion) {
            return null;
        }

        return [
            'current_page' => $completion->current_page ?? 0,
            'is_completed' => $completion->completed_at !== null,
            'last_accessed_at' => $completion->last_accessed_at,
            'time_spent_seconds' => $completion->time_spent_seconds,
        ];
    }

    /**
     * Get the last accessed module for a user (for auto-resume)
     */
    public function getLastAccessedModule(int $userId): ?LearningModuleModel
    {
        // Get the most recently accessed incomplete module
        $completion = LearningCompletionModel::where('user_id', $userId)
            ->whereNotNull('last_accessed_at') // Has been accessed
            ->whereNull('completed_at') // Not completed yet
            ->orderBy('last_accessed_at', 'desc') // Most recently accessed
            ->first();

        \Log::info('getLastAccessedModule query result', [
            'user_id' => $userId,
            'completion' => $completion?->toArray(),
            'all_completions' => LearningCompletionModel::where('user_id', $userId)
                ->whereNotNull('last_accessed_at')
                ->whereNull('completed_at')
                ->orderBy('last_accessed_at', 'desc')
                ->get()
                ->toArray(),
        ]);

        if (!$completion) {
            return null;
        }

        return LearningModuleModel::find($completion->learning_module_id);
    }

    /**
     * Update user's current page in a module
     */
    public function updateProgress(int $userId, int $moduleId, int $currentPage): void
    {
        $completion = LearningCompletionModel::where('user_id', $userId)
            ->where('learning_module_id', $moduleId)
            ->first();

        if ($completion) {
            // Update existing record
            $completion->update([
                'current_page' => $currentPage,
                'last_accessed_at' => now(),
            ]);
        } else {
            // Create new record without completed_at (module not completed yet)
            LearningCompletionModel::create([
                'user_id' => $userId,
                'learning_module_id' => $moduleId,
                'current_page' => $currentPage,
                'last_accessed_at' => now(),
                'started_at' => now(),
            ]);
        }
    }

    /**
     * Reset progress for a module (start over)
     */
    public function resetProgress(int $userId, int $moduleId): void
    {
        LearningCompletionModel::where('user_id', $userId)
            ->where('learning_module_id', $moduleId)
            ->update([
                'current_page' => 0,
                'last_accessed_at' => now(),
            ]);
    }

    /**
     * Get available categories
     */
    public function getCategories(): array
    {
        return LearningModuleModel::published()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
    }
}
