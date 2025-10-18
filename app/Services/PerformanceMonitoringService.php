<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PerformanceMonitoringService
{
    /**
     * Performance thresholds (in milliseconds)
     */
    const SLOW_QUERY_THRESHOLD = 1000;
    const VERY_SLOW_QUERY_THRESHOLD = 3000;
    const CACHE_HIT_RATE_THRESHOLD = 80; // percentage

    /**
     * Monitor query performance
     */
    public function monitorQuery(string $queryName, callable $callback, array $context = []): mixed
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        try {
            $result = $callback();
            $this->recordQueryMetrics($queryName, $startTime, $startMemory, true, $context);
            return $result;
        } catch (\Exception $e) {
            $this->recordQueryMetrics($queryName, $startTime, $startMemory, false, $context, $e);
            throw $e;
        }
    }

    /**
     * Record query performance metrics
     */
    private function recordQueryMetrics(
        string $queryName,
        float $startTime,
        int $startMemory,
        bool $success,
        array $context = [],
        ?\Exception $exception = null
    ): void {
        $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        $memoryUsed = memory_get_usage() - $startMemory;
        
        $metrics = [
            'query_name' => $queryName,
            'execution_time_ms' => round($executionTime, 2),
            'memory_used_bytes' => $memoryUsed,
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'success' => $success,
            'timestamp' => now()->toISOString(),
            'context' => $context
        ];

        if ($exception) {
            $metrics['error'] = [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }

        // Log slow queries
        if ($executionTime > self::SLOW_QUERY_THRESHOLD) {
            $level = $executionTime > self::VERY_SLOW_QUERY_THRESHOLD ? 'warning' : 'info';
            Log::$level('Slow query detected', $metrics);
        }

        // Store metrics for analysis
        $this->storeMetrics($metrics);
    }

    /**
     * Store performance metrics
     */
    private function storeMetrics(array $metrics): void
    {
        $cacheKey = 'performance_metrics_' . date('Y-m-d-H');
        $existingMetrics = Cache::get($cacheKey, []);
        $existingMetrics[] = $metrics;
        
        // Keep only last 1000 metrics per hour to prevent memory issues
        if (count($existingMetrics) > 1000) {
            $existingMetrics = array_slice($existingMetrics, -1000);
        }
        
        Cache::put($cacheKey, $existingMetrics, 3600); // Store for 1 hour
    }

    /**
     * Get performance statistics for a time period
     */
    public function getPerformanceStats(Carbon $startTime, Carbon $endTime): array
    {
        $stats = [
            'total_queries' => 0,
            'successful_queries' => 0,
            'failed_queries' => 0,
            'average_execution_time' => 0,
            'max_execution_time' => 0,
            'min_execution_time' => PHP_FLOAT_MAX,
            'slow_queries' => 0,
            'very_slow_queries' => 0,
            'total_memory_used' => 0,
            'average_memory_used' => 0,
            'query_breakdown' => [],
            'hourly_breakdown' => []
        ];

        $current = $startTime->copy();
        while ($current <= $endTime) {
            $cacheKey = 'performance_metrics_' . $current->format('Y-m-d-H');
            $hourlyMetrics = Cache::get($cacheKey, []);
            
            foreach ($hourlyMetrics as $metric) {
                $this->aggregateMetric($stats, $metric);
            }
            
            $current->addHour();
        }

        // Calculate averages
        if ($stats['total_queries'] > 0) {
            $stats['average_execution_time'] = round($stats['average_execution_time'] / $stats['total_queries'], 2);
            $stats['average_memory_used'] = round($stats['total_memory_used'] / $stats['total_queries'], 2);
            $stats['success_rate'] = round(($stats['successful_queries'] / $stats['total_queries']) * 100, 2);
        }

        if ($stats['min_execution_time'] === PHP_FLOAT_MAX) {
            $stats['min_execution_time'] = 0;
        }

        return $stats;
    }

    /**
     * Aggregate individual metric into stats
     */
    private function aggregateMetric(array &$stats, array $metric): void
    {
        $stats['total_queries']++;
        
        if ($metric['success']) {
            $stats['successful_queries']++;
        } else {
            $stats['failed_queries']++;
        }

        $executionTime = $metric['execution_time_ms'];
        $stats['average_execution_time'] += $executionTime;
        $stats['max_execution_time'] = max($stats['max_execution_time'], $executionTime);
        $stats['min_execution_time'] = min($stats['min_execution_time'], $executionTime);

        if ($executionTime > self::SLOW_QUERY_THRESHOLD) {
            $stats['slow_queries']++;
        }
        if ($executionTime > self::VERY_SLOW_QUERY_THRESHOLD) {
            $stats['very_slow_queries']++;
        }

        $memoryUsed = $metric['memory_used_bytes'];
        $stats['total_memory_used'] += $memoryUsed;

        // Query breakdown
        $queryName = $metric['query_name'];
        if (!isset($stats['query_breakdown'][$queryName])) {
            $stats['query_breakdown'][$queryName] = [
                'count' => 0,
                'total_time' => 0,
                'avg_time' => 0,
                'max_time' => 0,
                'failures' => 0
            ];
        }

        $stats['query_breakdown'][$queryName]['count']++;
        $stats['query_breakdown'][$queryName]['total_time'] += $executionTime;
        $stats['query_breakdown'][$queryName]['max_time'] = max(
            $stats['query_breakdown'][$queryName]['max_time'],
            $executionTime
        );
        
        if (!$metric['success']) {
            $stats['query_breakdown'][$queryName]['failures']++;
        }

        // Calculate average for this query
        $stats['query_breakdown'][$queryName]['avg_time'] = round(
            $stats['query_breakdown'][$queryName]['total_time'] / $stats['query_breakdown'][$queryName]['count'],
            2
        );
    }

    /**
     * Get cache performance metrics
     */
    public function getCachePerformanceMetrics(): array
    {
        $cacheKey = 'cache_performance_' . date('Y-m-d');
        
        return Cache::remember($cacheKey, 3600, function () {
            // This would typically integrate with your cache driver's stats
            // For now, we'll return basic metrics
            return [
                'hit_rate' => $this->calculateCacheHitRate(),
                'miss_rate' => 100 - $this->calculateCacheHitRate(),
                'total_keys' => $this->getCacheKeyCount(),
                'memory_usage' => $this->getCacheMemoryUsage(),
                'recommendations' => $this->getCacheRecommendations()
            ];
        });
    }

    /**
     * Calculate cache hit rate (simplified implementation)
     */
    private function calculateCacheHitRate(): float
    {
        // This is a simplified implementation
        // In production, you'd integrate with your cache driver's actual stats
        $hits = Cache::get('cache_hits_today', 0);
        $misses = Cache::get('cache_misses_today', 0);
        $total = $hits + $misses;
        
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    /**
     * Get approximate cache key count
     */
    private function getCacheKeyCount(): int
    {
        // This is a simplified implementation
        // Different cache drivers would have different ways to get this
        return Cache::get('cache_key_count', 0);
    }

    /**
     * Get cache memory usage (simplified)
     */
    private function getCacheMemoryUsage(): array
    {
        return [
            'used_mb' => 0, // Would be implemented based on cache driver
            'available_mb' => 0,
            'usage_percentage' => 0
        ];
    }

    /**
     * Get cache optimization recommendations
     */
    private function getCacheRecommendations(): array
    {
        $recommendations = [];
        $hitRate = $this->calculateCacheHitRate();
        
        if ($hitRate < self::CACHE_HIT_RATE_THRESHOLD) {
            $recommendations[] = [
                'type' => 'low_hit_rate',
                'message' => "Cache hit rate is {$hitRate}%, consider reviewing cache keys and TTL values",
                'priority' => 'high'
            ];
        }

        // Add more recommendations based on metrics
        $stats = $this->getPerformanceStats(now()->subHour(), now());
        
        if ($stats['slow_queries'] > 10) {
            $recommendations[] = [
                'type' => 'slow_queries',
                'message' => "Detected {$stats['slow_queries']} slow queries in the last hour, consider adding more caching",
                'priority' => 'medium'
            ];
        }

        return $recommendations;
    }

    /**
     * Get system resource metrics
     */
    public function getSystemMetrics(): array
    {
        return [
            'memory' => [
                'current_usage_mb' => round(memory_get_usage() / 1024 / 1024, 2),
                'peak_usage_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
                'limit_mb' => $this->getMemoryLimit()
            ],
            'database' => [
                'active_connections' => $this->getDatabaseConnections(),
                'slow_queries' => $this->getSlowQueryCount(),
                'query_cache_hit_rate' => $this->getDatabaseCacheHitRate()
            ],
            'php' => [
                'version' => PHP_VERSION,
                'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status()['opcache_enabled'] ?? false,
                'max_execution_time' => ini_get('max_execution_time')
            ]
        ];
    }

    /**
     * Get memory limit in MB
     */
    private function getMemoryLimit(): float
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') {
            return -1; // No limit
        }
        
        $value = (int) $limit;
        $unit = strtoupper(substr($limit, -1));
        
        switch ($unit) {
            case 'G':
                return $value * 1024;
            case 'M':
                return $value;
            case 'K':
                return $value / 1024;
            default:
                return $value / 1024 / 1024;
        }
    }

    /**
     * Get database connection count
     */
    private function getDatabaseConnections(): int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get slow query count
     */
    private function getSlowQueryCount(): int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Slow_queries'");
            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get database cache hit rate
     */
    private function getDatabaseCacheHitRate(): float
    {
        try {
            $qcacheHits = DB::select("SHOW STATUS LIKE 'Qcache_hits'")[0]->Value ?? 0;
            $qcacheInserts = DB::select("SHOW STATUS LIKE 'Qcache_inserts'")[0]->Value ?? 0;
            $total = $qcacheHits + $qcacheInserts;
            
            return $total > 0 ? round(($qcacheHits / $total) * 100, 2) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Generate performance report
     */
    public function generatePerformanceReport(Carbon $startTime, Carbon $endTime): array
    {
        return [
            'period' => [
                'start' => $startTime->toISOString(),
                'end' => $endTime->toISOString(),
                'duration_hours' => $startTime->diffInHours($endTime)
            ],
            'query_performance' => $this->getPerformanceStats($startTime, $endTime),
            'cache_performance' => $this->getCachePerformanceMetrics(),
            'system_metrics' => $this->getSystemMetrics(),
            'recommendations' => $this->getCacheRecommendations(),
            'generated_at' => now()->toISOString()
        ];
    }

    /**
     * Clean up old performance metrics
     */
    public function cleanupOldMetrics(int $daysToKeep = 7): void
    {
        $cutoffDate = now()->subDays($daysToKeep);
        $current = $cutoffDate->copy();
        
        while ($current <= now()) {
            $cacheKey = 'performance_metrics_' . $current->format('Y-m-d-H');
            Cache::forget($cacheKey);
            $current->addHour();
        }
        
        Log::info('Cleaned up performance metrics', [
            'cutoff_date' => $cutoffDate->toISOString(),
            'days_kept' => $daysToKeep
        ]);
    }
}