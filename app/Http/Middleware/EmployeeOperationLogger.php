<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\EmployeeMonitoringService;
use Illuminate\Support\Facades\Log;

class EmployeeOperationLogger
{
    private ?EmployeeMonitoringService $monitoringService = null;

    public function __construct()
    {
        // Lazy load the monitoring service to avoid circular dependencies during bootstrap
    }

    /**
     * Get monitoring service instance (lazy loaded)
     */
    private function getMonitoringService(): EmployeeMonitoringService
    {
        if ($this->monitoringService === null) {
            $this->monitoringService = app(EmployeeMonitoringService::class);
        }
        return $this->monitoringService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Start tracking
        $trackingId = $this->getMonitoringService()->startTracking($this->getOperationName($request));
        
        // Add tracking ID to request for potential use in controllers
        $request->attributes->set('tracking_id', $trackingId);

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        try {
            $response = $next($request);
            
            // Log successful operation
            $this->logOperation($request, $response, $startTime, $startMemory, null);
            
            return $response;
        } catch (\Exception $e) {
            // Log failed operation
            $this->logOperation($request, null, $startTime, $startMemory, $e);
            
            throw $e;
        } finally {
            // End tracking
            $this->getMonitoringService()->endTracking($trackingId, [
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode() ?? 500,
            ]);
        }
    }

    /**
     * Log the operation details
     */
    private function logOperation(
        Request $request, 
        ?Response $response, 
        float $startTime, 
        int $startMemory, 
        ?\Exception $exception
    ): void {
        $duration = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage(true) - $startMemory;
        $operationName = $this->getOperationName($request);
        
        $context = [
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'duration_ms' => round($duration * 1000, 2),
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        if ($response) {
            $context['status_code'] = $response->getStatusCode();
            $context['response_size'] = strlen($response->getContent());
        }

        if ($exception) {
            $context['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        // Add request data for specific operations
        if ($this->shouldLogRequestData($request)) {
            $context['request_data'] = $this->sanitizeRequestData($request);
        }

        // Determine log level
        $level = $this->determineLogLevel($request, $response, $exception);

        // Log the operation
        $this->getMonitoringService()->logOperation($operationName, $context, $level);

        // Log performance metrics
        $this->getMonitoringService()->logPerformance($operationName, $duration, $context);

        // Log business metrics
        $this->logBusinessMetrics($request, $response, $context);

        // Check for security concerns
        $this->checkSecurityConcerns($request, $response, $exception, $context);
    }

    /**
     * Get operation name from request
     */
    private function getOperationName(Request $request): string
    {
        $route = $request->route();
        
        if ($route && $route->getName()) {
            return $route->getName();
        }

        // Fallback to method and path
        $path = $request->path();
        $method = strtolower($request->method());
        
        // Clean up path for employee operations
        $path = preg_replace('/\/\d+/', '/{id}', $path);
        
        return "{$method}_{$path}";
    }

    /**
     * Determine if request data should be logged
     */
    private function shouldLogRequestData(Request $request): bool
    {
        // Log request data for POST, PUT, PATCH operations
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return true;
        }

        // Log for specific sensitive operations
        $sensitiveRoutes = [
            'employees.store',
            'employees.update',
            'employees.destroy',
            'commissions.calculate',
            'commissions.mark-paid',
            'performance-reviews.store',
            'performance-reviews.update',
        ];

        return in_array($request->route()?->getName(), $sensitiveRoutes);
    }

    /**
     * Sanitize request data for logging
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();
        
        // Remove sensitive fields
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_key',
            'secret',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        // Limit data size
        $jsonData = json_encode($data);
        if (strlen($jsonData) > 10000) { // 10KB limit
            return ['message' => 'Request data too large to log', 'size' => strlen($jsonData)];
        }

        return $data;
    }

    /**
     * Determine appropriate log level
     */
    private function determineLogLevel(Request $request, ?Response $response, ?\Exception $exception): string
    {
        if ($exception) {
            return 'error';
        }

        if ($response) {
            $statusCode = $response->getStatusCode();
            
            if ($statusCode >= 500) {
                return 'error';
            }
            
            if ($statusCode >= 400) {
                return 'warning';
            }
            
            if ($statusCode >= 300) {
                return 'info';
            }
        }

        // Special cases for employee operations
        $sensitiveOperations = [
            'employees.destroy',
            'employees.update',
            'commissions.mark-paid',
        ];

        if (in_array($request->route()?->getName(), $sensitiveOperations)) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Log business metrics based on the operation
     */
    private function logBusinessMetrics(Request $request, ?Response $response, array $context): void
    {
        $route = $request->route()?->getName();
        
        if (!$route || !$response || $response->getStatusCode() >= 400) {
            return;
        }

        // Track different business metrics based on the operation
        match ($route) {
            'employees.store' => $this->getMonitoringService()->logBusinessMetric('employee_created', 1, $context),
            'employees.update' => $this->getMonitoringService()->logBusinessMetric('employee_updated', 1, $context),
            'employees.destroy' => $this->getMonitoringService()->logBusinessMetric('employee_terminated', 1, $context),
            'commissions.calculate' => $this->getMonitoringService()->logBusinessMetric('commission_calculated', 1, $context),
            'commissions.mark-paid' => $this->getMonitoringService()->logBusinessMetric('commission_paid', 1, $context),
            'performance-reviews.store' => $this->getMonitoringService()->logBusinessMetric('performance_review_created', 1, $context),
            'performance-reviews.update' => $this->getMonitoringService()->logBusinessMetric('performance_review_updated', 1, $context),
            default => null,
        };

        // Track general API usage
        $this->getMonitoringService()->logBusinessMetric('api_request', 1, [
            'endpoint' => $route,
            'method' => $request->method(),
        ]);
    }

    /**
     * Check for security concerns
     */
    private function checkSecurityConcerns(
        Request $request, 
        ?Response $response, 
        ?\Exception $exception, 
        array $context
    ): void {
        // Check for unauthorized access attempts
        if ($response && $response->getStatusCode() === 403) {
            $this->getMonitoringService()->logSecurityEvent('unauthorized_employee_access', $context, 'warning');
        }

        // Check for authentication failures
        if ($response && $response->getStatusCode() === 401) {
            $this->getMonitoringService()->logSecurityEvent('employee_authentication_failed', $context, 'warning');
        }

        // Check for suspicious activity patterns
        $this->checkSuspiciousActivity($request, $context);

        // Check for data export operations
        if ($this->isDataExportOperation($request)) {
            $this->getMonitoringService()->logSecurityEvent('employee_data_export', $context, 'warning');
        }

        // Check for bulk operations
        if ($this->isBulkOperation($request)) {
            $this->getMonitoringService()->logSecurityEvent('employee_bulk_operation', $context, 'info');
        }
    }

    /**
     * Check for suspicious activity patterns
     */
    private function checkSuspiciousActivity(Request $request, array $context): void
    {
        $ip = $request->ip();
        $userId = auth()->id();
        
        // Check for rapid requests from same IP
        $requestKey = "employee_requests_{$ip}_" . now()->format('Y-m-d-H-i');
        $requestCount = cache()->increment($requestKey, 1);
        cache()->expire($requestKey, 60); // 1 minute
        
        if ($requestCount > 100) { // More than 100 requests per minute
            $this->getMonitoringService()->logSecurityEvent('suspicious_request_rate', array_merge($context, [
                'request_count' => $requestCount,
                'threshold' => 100,
            ]), 'warning');
        }

        // Check for access to multiple employee records
        if ($userId && str_contains($request->path(), 'employees/')) {
            $accessKey = "employee_access_{$userId}_" . now()->format('Y-m-d-H');
            $accessedEmployees = cache()->get($accessKey, []);
            
            if (preg_match('/employees\/(\d+)/', $request->path(), $matches)) {
                $employeeId = $matches[1];
                if (!in_array($employeeId, $accessedEmployees)) {
                    $accessedEmployees[] = $employeeId;
                    cache()->put($accessKey, $accessedEmployees, 3600); // 1 hour
                    
                    if (count($accessedEmployees) > 50) { // Accessing more than 50 different employees per hour
                        $this->getMonitoringService()->logSecurityEvent('excessive_employee_access', array_merge($context, [
                            'accessed_count' => count($accessedEmployees),
                            'threshold' => 50,
                        ]), 'warning');
                    }
                }
            }
        }
    }

    /**
     * Check if this is a data export operation
     */
    private function isDataExportOperation(Request $request): bool
    {
        $exportRoutes = [
            'employees.export',
            'commissions.export',
            'performance-reviews.export',
        ];

        return in_array($request->route()?->getName(), $exportRoutes) ||
               str_contains($request->path(), 'export') ||
               $request->has('export') ||
               str_contains($request->header('Accept', ''), 'csv') ||
               str_contains($request->header('Accept', ''), 'excel');
    }

    /**
     * Check if this is a bulk operation
     */
    private function isBulkOperation(Request $request): bool
    {
        $bulkRoutes = [
            'employees.bulk-update',
            'employees.bulk-delete',
            'commissions.bulk-calculate',
            'commissions.bulk-pay',
        ];

        return in_array($request->route()?->getName(), $bulkRoutes) ||
               str_contains($request->path(), 'bulk') ||
               $request->has('bulk') ||
               (is_array($request->input('ids')) && count($request->input('ids', [])) > 10);
    }
}