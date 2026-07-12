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
            \Log::error("Module config file not found: {$configPath}");
            return false;
        }

        $config = file_get_contents($configPath);
        
        $enabledValue = $enabled ? 'true' : 'false';
        $oppositeValue = $enabled ? 'false' : 'true';
        
        // Pattern to match: 'modulekey' => [ ... 'enabled' => true/false
        // Use non-greedy match (.*?) to handle nested arrays
        $pattern = "/('{$moduleKey}'\s*=>\s*\[.*?'enabled'\s*=>\s*){$oppositeValue}/s";
        
        \Log::info("Attempting to update module: {$moduleKey}", [
            'enabled' => $enabled,
            'pattern' => $pattern,
            'config_path' => $configPath,
            'file_writable' => is_writable($configPath),
        ]);
        
        $newConfig = preg_replace($pattern, '${1}' . $enabledValue, $config, 1, $count);
        
        \Log::info("Pattern match result", [
            'count' => $count,
            'config_changed' => $newConfig !== $config,
            'old_length' => strlen($config),
            'new_length' => strlen($newConfig),
        ]);
        
        if ($count > 0 && $newConfig !== $config) {
            $writeResult = file_put_contents($configPath, $newConfig);
            \Log::info("File write result", [
                'bytes_written' => $writeResult,
                'success' => $writeResult !== false,
            ]);
            
            if ($writeResult === false) {
                \Log::error("Failed to write to config file");
                return false;
            }
            
            self::clearCache();
            \Log::info("Module {$moduleKey} status updated successfully");
            return true;
        }

        \Log::warning("Failed to update module {$moduleKey} status", [
            'count' => $count,
            'config_changed' => $newConfig !== $config,
        ]);
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
     * Filter modules to only those that are enabled (with slug-to-config-key mapping)
     */
    public static function filterEnabledModules(array $modules): array
    {
        $enabledModules = self::getEnabledModules();
        $enabledSlugs = array_keys($enabledModules);

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

        return array_values(array_filter($modules, function ($module) use ($enabledSlugs, $slugToConfigKey) {
            $slug = $module['slug'] ?? '';
            $configKey = $slugToConfigKey[$slug] ?? $slug;

            if ($slug === 'dashboard' || $configKey === 'dashboard') {
                return true;
            }

            return in_array($configKey, $enabledSlugs);
        }));
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
