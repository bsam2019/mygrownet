<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PerformanceMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PerformanceMonitoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private PerformanceMonitoringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PerformanceMonitoringService();
    }

    public function test_monitor_query_records_successful_execution()
    {
        Log::shouldReceive('info')->never();
        Log::shouldReceive('warning')->never();
        
        $result = $this->service->monitorQuery('test_query', function () {
            usleep(100000); // 100ms
            return 'test_result';
        });

        $this->assertEquals('test_result', $result);
        
        // Check that metrics were stored
        $cacheKey = 'performance_metrics_' . date('Y-m-d-H');
        $metrics = Cache::get($cacheKey, []);
        
        $this->assertNotEmpty($metrics);
        $this->assertEquals('test_query', $metrics[0]['query_name']);
        $this->assertTrue($metrics[0]['success']);
        $this->assertGreaterThan(90, $metrics[0]['execution_time_ms']); // Should be around 100ms
    }

    public function test_monitor_query_records_failed_execution()
    {
        $exception = new \Exception('Test exception');
        
        try {
            $this->service->monitorQuery('failing_query', function () use ($exception) {
                throw $exception;
            });
        } catch (\Exception $e) {
            // Expected
        }

        $cacheKey = 'performance_metrics_' . date('Y-m-d-H');
        $metrics = Cache::get($cacheKey, []);
        
        $this->assertNotEmpty($metrics);
        $this->assertEquals('failing_query', $metrics[0]['query_name']);
        $this->assertFalse($metrics[0]['success']);
        $this->assertArrayHasKey('error', $metrics[0]);
        $this->assertEquals('Test exception', $metrics[0]['error']['message']);
    }

    public function test_monitor_query_logs_slow_queries()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Slow query detected', \Mockery::type('array'));

        $this->service->monitorQuery('slow_query', function () {
            usleep(1100000); // 1.1 seconds (over threshold)
            return 'result';
        });
    }

    public function test_monitor_query_logs_very_slow_queries_as_warning()
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Slow query detected', \Mockery::type('array'));

        $this->service->monitorQuery('very_slow_query', function () {
            usleep(3100000); // 3.1 seconds (over very slow threshold)
            return 'result';
        });
    }

    public function test_get_performance_stats_aggregates_metrics_correctly()
    {
        // Create test metrics
        $testMetrics = [
            [
                'query_name' => 'query1',
                'execution_time_ms' => 100,
                'memory_used_bytes' => 1024,
                'success' => true,
                'timestamp' => now()->toISOString()
            ],
            [
                'query_name' => 'query1',
                'execution_time_ms' => 200,
                'memory_used_bytes' => 2048,
                'success' => true,
                'timestamp' => now()->toISOString()
            ],
            [
                'query_name' => 'query2',
                'execution_time_ms' => 1500, // Slow query
                'memory_used_bytes' => 4096,
                'success' => false,
                'timestamp' => now()->toISOString()
            ]
        ];

        $cacheKey = 'performance_metrics_' . date('Y-m-d-H');
        Cache::put($cacheKey, $testMetrics, 3600);

        $stats = $this->service->getPerformanceStats(now()->subHour(), now());

        $this->assertEquals(3, $stats['total_queries']);
        $this->assertEquals(2, $stats['successful_queries']);
        $this->assertEquals(1, $stats['failed_queries']);
        $this->assertEquals(66.67, $stats['success_rate']);
        $this->assertEquals(600, $stats['average_execution_time']); // (100+200+1500)/3
        $this->assertEquals(1500, $stats['max_execution_time']);
        $this->assertEquals(100, $stats['min_execution_time']);
        $this->assertEquals(1, $stats['slow_queries']);
        $this->assertEquals(1, $stats['very_slow_queries']);
    }

    public function test_get_performance_stats_handles_empty_data()
    {
        $stats = $this->service->getPerformanceStats(now()->subHour(), now());

        $this->assertEquals(0, $stats['total_queries']);
        $this->assertEquals(0, $stats['successful_queries']);
        $this->assertEquals(0, $stats['failed_queries']);
        $this->assertEquals(0, $stats['average_execution_time']);
        $this->assertEquals(0, $stats['max_execution_time']);
        $this->assertEquals(0, $stats['min_execution_time']);
    }

    public function test_get_performance_stats_includes_query_breakdown()
    {
        $testMetrics = [
            [
                'query_name' => 'dashboard_query',
                'execution_time_ms' => 150,
                'memory_used_bytes' => 1024,
                'success' => true,
                'timestamp' => now()->toISOString()
            ],
            [
                'query_name' => 'dashboard_query',
                'execution_time_ms' => 250,
                'memory_used_bytes' => 2048,
                'success' => true,
                'timestamp' => now()->toISOString()
            ],
            [
                'query_name' => 'user_query',
                'execution_time_ms' => 100,
                'memory_used_bytes' => 512,
                'success' => false,
                'timestamp' => now()->toISOString()
            ]
        ];

        $cacheKey = 'performance_metrics_' . date('Y-m-d-H');
        Cache::put($cacheKey, $testMetrics, 3600);

        $stats = $this->service->getPerformanceStats(now()->subHour(), now());

        $this->assertArrayHasKey('query_breakdown', $stats);
        $this->assertArrayHasKey('dashboard_query', $stats['query_breakdown']);
        $this->assertArrayHasKey('user_query', $stats['query_breakdown']);

        $dashboardStats = $stats['query_breakdown']['dashboard_query'];
        $this->assertEquals(2, $dashboardStats['count']);
        $this->assertEquals(200, $dashboardStats['avg_time']); // (150+250)/2
        $this->assertEquals(250, $dashboardStats['max_time']);
        $this->assertEquals(0, $dashboardStats['failures']);

        $userStats = $stats['query_breakdown']['user_query'];
        $this->assertEquals(1, $userStats['count']);
        $this->assertEquals(100, $userStats['avg_time']);
        $this->assertEquals(1, $userStats['failures']);
    }

    public function test_get_cache_performance_metrics_returns_cache_stats()
    {
        $metrics = $this->service->getCachePerformanceMetrics();

        $this->assertArrayHasKey('hit_rate', $metrics);
        $this->assertArrayHasKey('miss_rate', $metrics);
        $this->assertArrayHasKey('total_keys', $metrics);
        $this->assertArrayHasKey('memory_usage', $metrics);
        $this->assertArrayHasKey('recommendations', $metrics);
    }

    public function test_get_system_metrics_returns_system_information()
    {
        $metrics = $this->service->getSystemMetrics();

        $this->assertArrayHasKey('memory', $metrics);
        $this->assertArrayHasKey('database', $metrics);
        $this->assertArrayHasKey('php', $metrics);

        // Check memory metrics
        $this->assertArrayHasKey('current_usage_mb', $metrics['memory']);
        $this->assertArrayHasKey('peak_usage_mb', $metrics['memory']);
        $this->assertArrayHasKey('limit_mb', $metrics['memory']);

        // Check PHP metrics
        $this->assertEquals(PHP_VERSION, $metrics['php']['version']);
        $this->assertArrayHasKey('opcache_enabled', $metrics['php']);
    }

    public function test_generate_performance_report_includes_all_sections()
    {
        $startTime = now()->subHour();
        $endTime = now();

        $report = $this->service->generatePerformanceReport($startTime, $endTime);

        $this->assertArrayHasKey('period', $report);
        $this->assertArrayHasKey('query_performance', $report);
        $this->assertArrayHasKey('cache_performance', $report);
        $this->assertArrayHasKey('system_metrics', $report);
        $this->assertArrayHasKey('recommendations', $report);
        $this->assertArrayHasKey('generated_at', $report);

        // Check period information
        $this->assertEquals($startTime->toISOString(), $report['period']['start']);
        $this->assertEquals($endTime->toISOString(), $report['period']['end']);
        $this->assertEquals(1, $report['period']['duration_hours']);
    }

    public function test_cleanup_old_metrics_removes_old_cache_entries()
    {
        // Create old metrics
        $oldCacheKey = 'performance_metrics_' . now()->subDays(10)->format('Y-m-d-H');
        $recentCacheKey = 'performance_metrics_' . now()->format('Y-m-d-H');
        
        Cache::put($oldCacheKey, ['old' => 'data'], 3600);
        Cache::put($recentCacheKey, ['recent' => 'data'], 3600);

        $this->assertTrue(Cache::has($oldCacheKey));
        $this->assertTrue(Cache::has($recentCacheKey));

        // Cleanup with 7 days retention
        $this->service->cleanupOldMetrics(7);

        // Old data should be removed, recent data should remain
        $this->assertFalse(Cache::has($oldCacheKey));
        $this->assertTrue(Cache::has($recentCacheKey));
    }

    public function test_performance_thresholds_are_defined()
    {
        $this->assertEquals(1000, PerformanceMonitoringService::SLOW_QUERY_THRESHOLD);
        $this->assertEquals(3000, PerformanceMonitoringService::VERY_SLOW_QUERY_THRESHOLD);
        $this->assertEquals(80, PerformanceMonitoringService::CACHE_HIT_RATE_THRESHOLD);
    }

    public function test_monitor_query_includes_context_in_metrics()
    {
        $context = ['user_id' => 123, 'action' => 'dashboard_load'];

        $this->service->monitorQuery('contextual_query', function () {
            return 'result';
        }, $context);

        $cacheKey = 'performance_metrics_' . date('Y-m-d-H');
        $metrics = Cache::get($cacheKey, []);

        $this->assertNotEmpty($metrics);
        $this->assertEquals($context, $metrics[0]['context']);
    }
}