<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShareModulesData
{
    /**
     * Handle an incoming request.
     * Automatically shares modules data with all Inertia responses
     */
    public function handle(Request $request, Closure $next)
    {
        // Only share modules data for authenticated users
        if ($request->user()) {
            Inertia::share([
                'modules' => function () use ($request) {
                    $modules = $this->getModulesData($request);
                    // Debug logging
                    \Log::info('ShareModulesData: Sharing modules', [
                        'user_id' => $request->user()->id,
                        'modules_count' => count($modules),
                        'route' => $request->route()?->getName(),
                    ]);
                    return $modules;
                },
            ]);
        }

        return $next($request);
    }

    /**
     * Get modules data for the current user
     */
    private function getModulesData(Request $request): array
    {
        $user = $request->user();
        
        if (!$user) {
            return [];
        }

        try {
            // Get modules using the same use case as main dashboard for consistency
            $getUserModulesUseCase = app(\App\Application\UseCases\Module\GetUserModulesUseCase::class);
            $moduleDTOs = $getUserModulesUseCase->execute($user);
            
            $modules = array_map(function($dto) {
                return $dto->toArray();
            }, $moduleDTOs);
            
            // Filter modules by enabled status
            return $this->filterEnabledModules($modules);
        } catch (\Exception $e) {
            // Gracefully handle errors - return empty array if modules can't be loaded
            \Log::warning('Failed to load modules data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Filter modules based on enabled configuration
     */
    private function filterEnabledModules(array $modules): array
    {
        return \App\Services\ModuleService::filterEnabledModules($modules);
    }
}