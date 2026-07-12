<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

abstract class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Get modules data for the current user
     * This ensures consistent module data across all pages
     */
    protected function getModulesData(Request $request): array
    {
        $user = $request->user();
        
        if (!$user) {
            return [];
        }

        // Get modules using the same use case as main dashboard for consistency
        $getUserModulesUseCase = app(\App\Application\UseCases\Module\GetUserModulesUseCase::class);
        $moduleDTOs = $getUserModulesUseCase->execute($user);
        
        $modules = array_map(function($dto) {
            return $dto->toArray();
        }, $moduleDTOs);
        
        // Filter modules by enabled status
        return $this->filterEnabledModules($modules);
    }

    /**
     * Filter modules based on enabled configuration
     */
    protected function filterEnabledModules(array $modules): array
    {
        return \App\Services\ModuleService::filterEnabledModules($modules);
    }

    /**
     * Add modules data to Inertia response data
     * Call this method in your controller to automatically include modules
     */
    protected function withModules(Request $request, array $data): array
    {
        return array_merge($data, [
            'modules' => $this->getModulesData($request),
        ]);
    }
}