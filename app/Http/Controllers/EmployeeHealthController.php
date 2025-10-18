<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\EmployeeMonitoringService;
use Illuminate\Support\Facades\Config;

class EmployeeHealthController extends Controller
{
    public function __construct(
        private EmployeeMonitoringService $monitoringService
    ) {}

    /**
     * Get overall health status
     */
    public function health(): JsonResponse
    {
        if (!Config::get('employee-monitoring.health_checks.enabled', true)) {
            return response()->json([
                'status' => 'disabled',
                'message' => 'Health checks are disabled',
            ], 503);
        }

        $healthStatus = $this->monitoringService->getHealthStatus();
        
        $httpStatus = match ($healthStatus['status']) {
            'healthy' => 200,
            'degraded' => 200,
            'critical' => 503,
            default => 503,
        };

        return response()->json($healthStatus, $httpStatus);
    }

    /**
     * Get detailed health information
     */
    public function healthDetailed(): JsonResponse
    {
        $healthStatus = $this->monitoringService->getHealthStatus();
        $performanceMetrics = $this->monitoringService->getPerformanceMetrics(1); // Last hour
        $recentAlerts = $this->monitoringService->getRecentAlerts(10);

        return response()->json([
            'health' => $healthStatus,
            'performance' => $performanceMetrics,
            'recent_alerts' => $recentAlerts,
            'system_info' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage' => [
                    'current_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                    'peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                ],
                'uptime' => $this->getUptime(),
            ],
        ]);
    }

    /**
     * Get performance metrics
     */
    public function metrics(Request $request): JsonResponse
    {
        $hours = $request->input('hours', 24);
        $hours = min(max($hours, 1), 168); // Between 1 hour and 1 week

        $metrics = $this->monitoringService->getPerformanceMetrics($hours);

        return response()->json([
            'period' => [
                'hours' => $hours,
                'start' => now()->subHours($hours)->toISOString(),
                'end' => now()->toISOString(),
            ],
            'metrics' => $metrics,
        ]);
    }

    /**
     * Get recent alerts
     */
    public function alerts(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 50);
        $limit = min(max($limit, 1), 200); // Between 1 and 200

        $alerts = $this->monitoringService->getRecentAlerts($limit);

        return response()->json([
            'alerts' => $alerts,
            'count' => count($alerts),
            'limit' => $limit,
        ]);
    }

    /**
     * Trigger manual health check
     */
    public function checkHealth(): JsonResponse
    {
        $this->monitoringService->logOperation('manual_health_check_triggered', [
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
        ]);

        $healthStatus = $this->monitoringService->getHealthStatus();

        return response()->json([
            'message' => 'Health check completed',
            'health' => $healthStatus,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Clear monitoring data (admin only)
     */
    public function clearData(): JsonResponse
    {
        // This should be protected by appropriate middleware/permissions
        $this->monitoringService->cleanup();

        $this->monitoringService->logOperation('monitoring_data_cleared', [
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
        ], 'warning');

        return response()->json([
            'message' => 'Monitoring data cleared successfully',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get system uptime (simplified)
     */
    private function getUptime(): array
    {
        // This is a simplified version - in production you might want to track actual application uptime
        $loadAvg = sys_getloadavg();
        
        return [
            'load_average' => $loadAvg ? [
                '1min' => $loadAvg[0] ?? null,
                '5min' => $loadAvg[1] ?? null,
                '15min' => $loadAvg[2] ?? null,
            ] : null,
            'server_time' => now()->toISOString(),
        ];
    }
}