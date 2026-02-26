<?php

namespace App\Http\Middleware;

use App\Services\ModuleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (!ModuleService::isEnabled($module)) {
            // If it's an API request, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'This feature is currently unavailable.',
                ], 503);
            }

            // For web requests, redirect to dashboard with message
            return redirect()->route('dashboard')
                ->with('error', 'This feature is currently unavailable.');
        }

        return $next($request);
    }
}
