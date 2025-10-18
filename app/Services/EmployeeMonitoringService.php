<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class EmployeeMonitoringService
{
    private array $config;
    private array $metrics = [];
    private float $startTime;

    public function __construct()
    {
        $this->config = Config::get('employee-monitoring', []);
        $this->startTime = microtime(true);
    }

    /**
     * Log employee operation with context
     */
    public function logOperation(string $operation, array $context = [], string $level = 'info'): void
    {
        $logData = [
            'operation' => $operation,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
            'context' => $context,
        ];

        // Log to appropriate channel based on operation type
        $channel = $this->getLogChannel($operation);
        Log::channel($channel)->log($level, "Employee operation: {$operation}", $logData);

        // Track metrics
        $this->trackMetric($operation, $context);

        // Check for alerts
        $this->checkAlerts($operation, $context, $level);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $context = [], string $level = 'warning'): void
    {
        $securityData = [
            'event' => $event,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
            'context' => $context,
            'session_id' => session()->getId(),
        ];

        Log::channel('employee_security')->log($level, "Security event: {$event}", $securityData);

        // Track failed attempts
        if (str_contains($event, 'failed') || str_contains($event, 'unauthorized')) {
            $this->trackFailedAttempt($event, $context);
        }

        // Immediate alert for critical security events
        if ($level === 'error' || $level === 'critical') {
            $this->sendSecurityAlert($event, $securityData);
        }
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(string $operation, float $duration, array $context = []): void
    {
        $performanceData = [
            'operation' => $operation,
            'duration_ms' => round($duration * 1000, 2),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'timestamp' => now()->toISOString(),
            'context' => $context,
        ];

        Log::channel('employee_performance')->info("Performance: {$operation}", $performanceData);

        // Check performance thresholds
        $this->checkPerformanceThresholds($operation, $duration, $performanceData);
    }

    /**
     * Log business metrics
     */
    public function logBusinessMetric(string $metric, $value, array $context = []): void
    {
        $metricData = [
            'metric' => $metric,
            'value' => $value,
            'timestamp' => now()->toISOString(),
            'context' => $context,
        ];

        Log::channel('employee_operations')->info("Business metric: {$metric}", $metricData);

        // Store in cache for real-time monitoring
        $cacheKey = "employee_metric_{$metric}_" . now()->format('Y-m-d-H');
        $currentValue = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $currentValue + (is_numeric($value) ? $value : 1), 3600);
    }

    /**
     * Start performance tracking for an operation
     */
    public function startTracking(string $operation): string
    {
        $trackingId = uniqid($operation . '_', true);
        Cache::put("tracking_{$trackingId}", [
            'operation' => $operation,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
        ], 300); // 5 minutes

        return $trackingId;
    }

    /**
     * End performance tracking and log results
     */
    public function endTracking(string $trackingId, array $context = []): void
    {
        $trackingData = Cache::get("tracking_{$trackingId}");
        if (!$trackingData) {
            return;
        }

        $duration = microtime(true) - $trackingData['start_time'];
        $memoryUsed = memory_get_usage(true) - $trackingData['start_memory'];

        $this->logPerformance($trackingData['operation'], $duration, array_merge($context, [
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
        ]));

        Cache::forget("tracking_{$trackingId}");
    }

    /**
     * Get health check status
     */
    public function getHealthStatus(): array
    {
        $checks = $this->config['health_checks']['checks'] ?? [];
        $results = [];

        foreach ($checks as $checkName => $checkConfig) {
            if (!($checkConfig['enabled'] ?? true)) {
                continue;
            }

            $results[$checkName] = $this->runHealthCheck($checkName, $checkConfig);
        }

        return [
            'status' => $this->determineOverallHealth($results),
            'timestamp' => now()->toISOString(),
            'checks' => $results,
        ];
    }

    /**
     * Get performance metrics summary
     */
    public function getPerformanceMetrics(int $hours = 24): array
    {
        $metrics = [];
        $startTime = now()->subHours($hours);

        // Get cached metrics
        for ($i = 0; $i < $hours; $i++) {
            $hour = $startTime->copy()->addHours($i);
            $hourKey = $hour->format('Y-m-d-H');

            foreach (['employee_operations', 'commission_calculations', 'performance_reviews'] as $operation) {
                $cacheKey = "employee_metric_{$operation}_{$hourKey}";
                $value = Cache::get($cacheKey, 0);
                
                $metrics[$operation][] = [
                    'hour' => $hour->toISOString(),
                    'value' => $value,
                ];
            }
        }

        return $metrics;
    }

    /**
     * Get recent alerts
     */
    public function getRecentAlerts(int $limit = 50): array
    {
        return Cache::get('employee_recent_alerts', []);
    }

    /**
     * Clear old monitoring data
     */
    public function cleanup(): void
    {
        $retentionDays = $this->config['data_retention'] ?? [];

        // Clear old cached metrics
        $patterns = [
            'employee_metric_*',
            'tracking_*',
            'failed_attempts_*',
        ];

        foreach ($patterns as $pattern) {
            $keys = Cache::getRedis()->keys($pattern);
            foreach ($keys as $key) {
                $keyParts = explode('_', $key);
                if (count($keyParts) >= 3) {
                    $timestamp = end($keyParts);
                    if (Carbon::createFromFormat('Y-m-d-H', $timestamp)?->lt(now()->subDays(7))) {
                        Cache::forget($key);
                    }
                }
            }
        }

        $this->logOperation('monitoring_cleanup_completed', [
            'retention_days' => $retentionDays,
        ]);
    }

    /**
     * Get appropriate log channel for operation
     */
    private function getLogChannel(string $operation): string
    {
        if (str_contains($operation, 'commission')) {
            return 'employee_commissions';
        }

        if (str_contains($operation, 'performance')) {
            return 'employee_performance';
        }

        if (str_contains($operation, 'security') || str_contains($operation, 'unauthorized')) {
            return 'employee_security';
        }

        return 'employee_operations';
    }

    /**
     * Track operation metrics
     */
    private function trackMetric(string $operation, array $context): void
    {
        $hour = now()->format('Y-m-d-H');
        $cacheKey = "employee_metric_{$operation}_{$hour}";
        
        $currentCount = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $currentCount + 1, 3600);
    }

    /**
     * Check for alert conditions
     */
    private function checkAlerts(string $operation, array $context, string $level): void
    {
        if (!($this->config['alerting']['enabled'] ?? true)) {
            return;
        }

        $rules = $this->config['alerting']['rules'] ?? [];

        // Check error rate
        if ($level === 'error' || $level === 'critical') {
            $this->checkErrorRateAlert($operation);
        }

        // Check specific operation alerts
        foreach ($rules as $ruleName => $rule) {
            if ($this->shouldTriggerAlert($ruleName, $rule, $operation, $context, $level)) {
                $this->sendAlert($ruleName, $rule, [
                    'operation' => $operation,
                    'context' => $context,
                    'level' => $level,
                ]);
            }
        }
    }

    /**
     * Check performance thresholds
     */
    private function checkPerformanceThresholds(string $operation, float $duration, array $performanceData): void
    {
        $thresholds = $this->config['performance']['slow_operations'] ?? [];
        $threshold = $thresholds[$operation] ?? $this->config['performance']['query_time_threshold'] ?? 1000;

        $durationMs = $duration * 1000;

        if ($durationMs > $threshold) {
            $this->logOperation('slow_operation_detected', [
                'operation' => $operation,
                'duration_ms' => $durationMs,
                'threshold_ms' => $threshold,
                'performance_data' => $performanceData,
            ], 'warning');

            // Send alert for very slow operations
            if ($durationMs > $threshold * 2) {
                $this->sendAlert('slow_performance', [
                    'threshold' => $threshold * 2,
                    'severity' => 'warning',
                ], $performanceData);
            }
        }
    }

    /**
     * Track failed attempts for security monitoring
     */
    private function trackFailedAttempt(string $event, array $context): void
    {
        $ip = request()->ip();
        $cacheKey = "failed_attempts_{$event}_{$ip}_" . now()->format('Y-m-d-H-i');
        
        $attempts = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $attempts + 1, 300); // 5 minutes

        $threshold = $this->config['security']['failed_login_threshold'] ?? 5;
        
        if ($attempts + 1 >= $threshold) {
            $this->sendSecurityAlert('threshold_exceeded', [
                'event' => $event,
                'ip' => $ip,
                'attempts' => $attempts + 1,
                'threshold' => $threshold,
                'context' => $context,
            ]);
        }
    }

    /**
     * Run individual health check
     */
    private function runHealthCheck(string $checkName, array $config): array
    {
        $startTime = microtime(true);
        
        try {
            $result = match ($checkName) {
                'database_connectivity' => $this->checkDatabaseConnectivity($config),
                'employee_tables_accessible' => $this->checkEmployeeTables($config),
                'cache_connectivity' => $this->checkCacheConnectivity($config),
                'commission_calculation_service' => $this->checkCommissionService($config),
                'performance_tracking_service' => $this->checkPerformanceService($config),
                'employee_permissions' => $this->checkEmployeePermissions($config),
                default => ['status' => 'unknown', 'message' => 'Unknown health check'],
            };
        } catch (\Exception $e) {
            $result = [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'error' => true,
            ];
        }

        $duration = microtime(true) - $startTime;
        $result['duration_ms'] = round($duration * 1000, 2);
        $result['timestamp'] = now()->toISOString();

        return $result;
    }

    /**
     * Check database connectivity
     */
    private function checkDatabaseConnectivity(array $config): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check employee tables accessibility
     */
    private function checkEmployeeTables(array $config): array
    {
        $tables = $config['tables'] ?? ['employees', 'departments', 'positions'];
        $results = [];

        foreach ($tables as $table) {
            try {
                DB::table($table)->limit(1)->count();
                $results[$table] = 'accessible';
            } catch (\Exception $e) {
                $results[$table] = 'failed: ' . $e->getMessage();
            }
        }

        $allHealthy = !in_array(false, array_map(fn($result) => $result === 'accessible', $results));

        return [
            'status' => $allHealthy ? 'healthy' : 'failed',
            'message' => $allHealthy ? 'All tables accessible' : 'Some tables failed',
            'tables' => $results,
        ];
    }

    /**
     * Check cache connectivity
     */
    private function checkCacheConnectivity(array $config): array
    {
        try {
            $testKey = 'health_check_' . uniqid();
            Cache::put($testKey, 'test', 60);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            return [
                'status' => $value === 'test' ? 'healthy' : 'failed',
                'message' => $value === 'test' ? 'Cache working correctly' : 'Cache read/write failed',
            ];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Cache connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check commission calculation service
     */
    private function checkCommissionService(array $config): array
    {
        try {
            // Check if service class exists without instantiating to avoid circular dependencies
            if (class_exists(\App\Domain\Employee\Services\CommissionCalculationService::class)) {
                return ['status' => 'healthy', 'message' => 'Commission service class exists'];
            }
            return ['status' => 'failed', 'message' => 'Commission service class not found'];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Commission service check failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check performance tracking service
     */
    private function checkPerformanceService(array $config): array
    {
        try {
            // Check if service class exists without instantiating to avoid circular dependencies
            if (class_exists(\App\Domain\Employee\Services\PerformanceTrackingService::class)) {
                return ['status' => 'healthy', 'message' => 'Performance service class exists'];
            }
            return ['status' => 'failed', 'message' => 'Performance service class not found'];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Performance service check failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check employee permissions
     */
    private function checkEmployeePermissions(array $config): array
    {
        $requiredPermissions = $config['required_permissions'] ?? [];
        $missing = [];

        foreach ($requiredPermissions as $permission) {
            try {
                $exists = DB::table('permissions')->where('name', $permission)->exists();
                if (!$exists) {
                    $missing[] = $permission;
                }
            } catch (\Exception $e) {
                return ['status' => 'failed', 'message' => 'Permission check failed: ' . $e->getMessage()];
            }
        }

        return [
            'status' => empty($missing) ? 'healthy' : 'failed',
            'message' => empty($missing) ? 'All permissions exist' : 'Missing permissions: ' . implode(', ', $missing),
            'missing_permissions' => $missing,
        ];
    }

    /**
     * Determine overall health status
     */
    private function determineOverallHealth(array $results): string
    {
        $criticalFailed = false;
        $anyFailed = false;

        foreach ($results as $checkName => $result) {
            if ($result['status'] === 'failed') {
                $anyFailed = true;
                $checkConfig = $this->config['health_checks']['checks'][$checkName] ?? [];
                if ($checkConfig['critical'] ?? false) {
                    $criticalFailed = true;
                }
            }
        }

        if ($criticalFailed) {
            return 'critical';
        }

        if ($anyFailed) {
            return 'degraded';
        }

        return 'healthy';
    }

    /**
     * Send security alert
     */
    private function sendSecurityAlert(string $event, array $data): void
    {
        $this->sendAlert('security_event', [
            'severity' => 'critical',
            'threshold' => 1,
        ], array_merge($data, ['event' => $event]));
    }

    /**
     * Check if alert should be triggered
     */
    private function shouldTriggerAlert(string $ruleName, array $rule, string $operation, array $context, string $level): bool
    {
        // Implement alert logic based on rule configuration
        return false; // Simplified for now
    }

    /**
     * Send alert notification
     */
    private function sendAlert(string $alertType, array $rule, array $data): void
    {
        $alert = [
            'type' => $alertType,
            'severity' => $rule['severity'] ?? 'warning',
            'timestamp' => now()->toISOString(),
            'data' => $data,
        ];

        // Store alert
        $recentAlerts = Cache::get('employee_recent_alerts', []);
        array_unshift($recentAlerts, $alert);
        $recentAlerts = array_slice($recentAlerts, 0, 100); // Keep last 100 alerts
        Cache::put('employee_recent_alerts', $recentAlerts, 86400); // 24 hours

        // Log alert
        Log::channel('employee_operations')->warning('Alert triggered', $alert);

        // Send notifications based on configuration
        $this->sendAlertNotifications($alert);
    }

    /**
     * Send alert notifications
     */
    private function sendAlertNotifications(array $alert): void
    {
        $channels = $this->config['alerting']['channels'] ?? [];

        if ($channels['email']['enabled'] ?? false) {
            // Send email notification (implement as needed)
        }

        if ($channels['slack']['enabled'] ?? false) {
            // Send Slack notification (implement as needed)
        }

        if ($channels['database']['enabled'] ?? false) {
            // Store in database (implement as needed)
        }
    }

    /**
     * Check error rate alert
     */
    private function checkErrorRateAlert(string $operation): void
    {
        $hour = now()->format('Y-m-d-H');
        $errorKey = "employee_errors_{$operation}_{$hour}";
        $totalKey = "employee_total_{$operation}_{$hour}";

        $errors = Cache::get($errorKey, 0) + 1;
        $total = Cache::get($totalKey, 0) + 1;

        Cache::put($errorKey, $errors, 3600);
        Cache::put($totalKey, $total, 3600);

        $errorRate = $total > 0 ? $errors / $total : 0;
        $threshold = 0.1; // 10% error rate threshold

        if ($errorRate > $threshold && $total > 10) { // Only alert if we have enough samples
            $this->sendAlert('high_error_rate', [
                'threshold' => $threshold,
                'severity' => 'critical',
            ], [
                'operation' => $operation,
                'error_rate' => $errorRate,
                'errors' => $errors,
                'total' => $total,
            ]);
        }
    }
}