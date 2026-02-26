<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ModuleService
{
    /**
     * Check if a module is enabled
     */
    public static function isEnabled(string $moduleKey): bool
    {
        return Cache::remember("module.{$moduleKey}.enabled", 3600, function () use ($moduleKey) {
            $module = config("modules.modules.{$moduleKey}");
            
            if (!$module) {
                return false;
            }

            // Always enabled modules cannot be disabled
            if (isset($module['always_enabled']) && $module['always_enabled']) {
                return true;
            }

            return $module['enabled'] ?? false;
        });
    }

    /**
     * Get all enabled modules
     */
    public static function getEnabledModules(): array
    {
        return Cache::remember('modules.enabled', 3600, function () {
            $modules = config('modules.modules', []);
            
            return collect($modules)
                ->filter(fn($module) => $module['enabled'] ?? false)
                ->toArray();
        });
    }

    /**
     * Get modules by navigation group
     */
    public static function getModulesByGroup(string $group): array
    {
        $modules = self::getEnabledModules();
        
        return collect($modules)
            ->filter(fn($module) => ($module['nav_group'] ?? null) === $group)
            ->toArray();
    }

    /**
     * Get all navigation groups with their modules
     */
    public static function getNavigationGroups(): array
    {
        $groups = config('modules.nav_groups', []);
        $modules = self::getEnabledModules();

        return collect($groups)
            ->map(function ($group, $key) use ($modules) {
                $groupModules = collect($modules)
                    ->filter(fn($module) => ($module['nav_group'] ?? null) === $key)
                    ->values()
                    ->toArray();

                return array_merge($group, [
                    'key' => $key,
                    'modules' => $groupModules,
                ]);
            })
            ->filter(fn($group) => count($group['modules']) > 0)
            ->sortBy('order')
            ->values()
            ->toArray();
    }

    /**
     * Enable a module
     */
    public static function enable(string $moduleKey): bool
    {
        if (!config("modules.modules.{$moduleKey}")) {
            return false;
        }

        return self::updateModuleStatus($moduleKey, true);
    }

    /**
     * Disable a module
     */
    public static function disable(string $moduleKey): bool
    {
        $module = config("modules.modules.{$moduleKey}");
        
        if (!$module) {
            return false;
        }

        // Cannot disable always_enabled modules
        if (isset($module['always_enabled']) && $module['always_enabled']) {
            return false;
        }

        return self::updateModuleStatus($moduleKey, false);
    }

    /**
     * Update module status in config file
     */
    private static function updateModuleStatus(string $moduleKey, bool $enabled): bool
    {
        $configPath = config_path('modules.php');
        
        if (!file_exists($configPath)) {
            return false;
        }

        $config = file_get_contents($configPath);
        
        // More robust pattern that handles multi-line module definitions
        $enabledValue = $enabled ? 'true' : 'false';
        $oppositeValue = $enabled ? 'false' : 'true';
        
        // Pattern to match the module's enabled setting
        $pattern = "/('{$moduleKey}'\s*=>\s*\[[^\]]*'enabled'\s*=>\s*){$oppositeValue}/s";
        $replacement = "${1}{$enabledValue}";
        
        $newConfig = preg_replace($pattern, $replacement, $config, 1, $count);
        
        if ($count === 0) {
            // Pattern didn't match, try without quotes
            $pattern = "/(\"{$moduleKey}\"\s*=>\s*\[[^\]]*'enabled'\s*=>\s*){$oppositeValue}/s";
            $newConfig = preg_replace($pattern, $replacement, $config, 1, $count);
        }
        
        if ($count > 0 && $newConfig !== $config) {
            file_put_contents($configPath, $newConfig);
            self::clearCache();
            return true;
        }

        return false;
    }

    /**
     * Clear module cache
     */
    public static function clearCache(): void
    {
        Cache::forget('modules.enabled');
        
        $modules = config('modules.modules', []);
        foreach (array_keys($modules) as $key) {
            Cache::forget("module.{$key}.enabled");
        }
    }

    /**
     * Get module configuration
     */
    public static function getModule(string $moduleKey): ?array
    {
        $module = config("modules.modules.{$moduleKey}");
        
        if (!$module) {
            return null;
        }

        return array_merge($module, [
            'key' => $moduleKey,
            'is_enabled' => self::isEnabled($moduleKey),
        ]);
    }

    /**
     * Get all modules (enabled and disabled)
     */
    public static function getAllModules(): array
    {
        $modules = config('modules.modules', []);
        
        return collect($modules)
            ->map(function ($module, $key) {
                return array_merge($module, [
                    'key' => $key,
                    'is_enabled' => self::isEnabled($key),
                ]);
            })
            ->toArray();
    }
}
