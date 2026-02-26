<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    /**
     * Display module management page
     */
    public function index(): Response
    {
        $modules = ModuleService::getAllModules();
        $groups = config('modules.nav_groups', []);

        return Inertia::render('Admin/Modules/Index', [
            'modules' => $modules,
            'groups' => $groups,
        ]);
    }

    /**
     * Toggle module status
     */
    public function toggle(Request $request, string $moduleKey)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $module = ModuleService::getModule($moduleKey);

        if (!$module) {
            return back()->with('error', 'Module not found.');
        }

        // Check if module can be disabled
        if (isset($module['always_enabled']) && $module['always_enabled'] && !$request->enabled) {
            return back()->with('error', 'This module cannot be disabled.');
        }

        $success = $request->enabled 
            ? ModuleService::enable($moduleKey)
            : ModuleService::disable($moduleKey);

        if ($success) {
            $status = $request->enabled ? 'enabled' : 'disabled';
            return back()->with('success', "Module {$module['name']} has been {$status}.");
        }

        return back()->with('error', 'Failed to update module status.');
    }

    /**
     * Clear module cache
     */
    public function clearCache()
    {
        ModuleService::clearCache();

        return back()->with('success', 'Module cache cleared successfully.');
    }
}
