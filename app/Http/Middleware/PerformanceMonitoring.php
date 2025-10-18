<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PerformanceMonitoringService;
use Illuminate\Support\Facades\Log;

class PerformanceMonitoring
{
    private PerformanceMonitoringService $monitoringService;

    public function __construct(PerformanceMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip monitoring for certain routes to avoid overhead
        if ($this->shouldSkipMonitoring($request)) {
            return $next($request);
        }

        $routeName = $this->getRouteName($request);
        $context = $this->buildContext($request);

        return $this->monitoringService->monitorQuery(
            "http_request_{$routeName}",
            function () use ($next, $request) {
                return $next($request);
            },
            $context
        );
    }

    /**
     * Determine if monitoring should be skipped for this request
     */
    private function shouldSkipMonitoring(Request $request): bool
    {
        $skipRoutes = [
            'telescope.*',
            'horizon.*',
            '_debugbar.*',
            'health-check',
            'ping'
        ];

        $currentRoute = $request->route()?->getName();
        
        foreach ($skipRoutes as $pattern) {
            if (fnmatch($pattern, $currentRoute)) {
                return true;
            }
        }

        // Skip monitoring for static assets
        if ($request->is('css/*', 'js/*', 'images/*', 'fonts/*')) {
            return true;
        }

        // Skip monitoring for API health checks
        if ($request->is('api/health', 'api/status')) {
            return true;
        }

        return false;
    }

    /**
     * Get a meaningful route name for monitoring
     */
    private function getRouteName(Request $request): string
    {
        $route = $request->route();
        
        if ($route && $route->getName()) {
            return $route->getName();
        }

        // Fallback to method and URI pattern
        $method = $request->method();
        $uri = $request->getPathInfo();
        
        // Replace dynamic segments with placeholders
        $uri = preg_replace('/\/\d+/', '/{id}', $uri);
        $uri = preg_replace('/\/[a-f0-9-]{36}/', '/{uuid}', $uri);
        
        return strtolower($method) . '_' . str_replace(['/', '-'], ['_', '_'], trim($uri, '/'));
    }

    /**
     * Build context information for monitoring
     */
    private function buildContext(Request $request): array
    {
        return [
            'method' => $request->method(),
            'uri' => $request->getPathInfo(),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'query_count' => 0, // Will be populated by query listener if enabled
            'memory_start' => memory_get_usage(),
            'timestamp' => now()->toISOString()
        ];
    }
}