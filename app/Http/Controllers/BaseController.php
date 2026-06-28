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
        $enabledModules = \App\Services\ModuleService::getEnabledModules();
        $enabledSlugs = array_keys($enabledModules);
        
        // Map module slugs to config keys
        $slugToConfigKey = [
            'grownet' => 'grownet',
            'mygrownet-core' => 'grownet',
            'mlm-dashboard' => 'grownet',
            'growbuilder' => 'growbuilder',
            'bizboost' => 'bizboost',
            'growfinance' => 'growfinance',
            'growbiz' => 'growbiz',
            'cms' => 'cms',
            'marketplace' => 'growmarket',
            'shop' => 'growmarket',
            'growmarket' => 'growmarket',
            'learning' => 'library',
            'education' => 'library',
            'library' => 'library',
            'lifeplus' => 'lifeplus',
            'health' => 'lifeplus',
            'wellness' => 'lifeplus',
            'ubumi' => 'ubumi',
            'mygrow-save' => 'wallet',
            'wallet' => 'wallet',
            'messaging' => 'messaging',
            'announcements' => 'announcements',
            'community' => 'community',
            'support' => 'support',
            'workshops' => 'workshops',
            'profit-sharing' => 'profit_sharing',
            'inventory' => 'inventory',
            'pos' => 'pos',
            'bgf' => 'bgf',
            'ventures' => 'venture_builder',
            'venture-builder' => 'venture_builder',
        ];
        
        return array_values(array_filter($modules, function($module) use ($enabledSlugs, $slugToConfigKey) {
            $slug = $module['slug'] ?? '';
            $configKey = $slugToConfigKey[$slug] ?? $slug;
            
            // Always show dashboard
            if ($slug === 'dashboard' || $configKey === 'dashboard') {
                return true;
            }
            
            return in_array($configKey, $enabledSlugs);
        }));
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