<?php

namespace Tests\Unit\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ActivityLogService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ActivityLogService();
        $this->user = User::factory()->create();
    }

    public function test_can_log_user_activity()
    {
        $this->service->log($this->user, 'login', 'User logged in successfully');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'login',
            'description' => 'User logged in successfully',
        ]);
    }

    public function test_can_log_activity_with_metadata()
    {
        $metadata = ['ip' => '192.168.1.1', 'user_agent' => 'Test Browser'];
        
        $this->service->log($this->user, 'investment_created', 'Created new investment', $metadata);

        $log = ActivityLog::where('user_id', $this->user->id)->first();
        $this->assertEquals($metadata, $log->metadata);
    }

    public function test_can_retrieve_user_activity_logs()
    {
        $this->service->log($this->user, 'login', 'User logged in');
        $this->service->log($this->user, 'logout', 'User logged out');

        $logs = $this->service->getUserLogs($this->user);

        $this->assertCount(2, $logs);
        $this->assertEquals('logout', $logs->first()->action); // Most recent first
        $this->assertEquals('login', $logs->last()->action);
    }

    public function test_can_retrieve_logs_by_action_type()
    {
        $this->service->log($this->user, 'login', 'User logged in');
        $this->service->log($this->user, 'investment_created', 'Created investment');
        $this->service->log($this->user, 'login', 'User logged in again');

        $loginLogs = $this->service->getLogsByAction('login');

        $this->assertCount(2, $loginLogs);
        $this->assertEquals(['login'], $loginLogs->pluck('action')->unique()->toArray());
    }

    public function test_can_retrieve_logs_within_date_range()
    {
        $yesterday = now()->subDay();
        $tomorrow = now()->addDay();

        $this->service->log($this->user, 'old_action', 'Old action', [], $yesterday);
        $this->service->log($this->user, 'current_action', 'Current action');
        $this->service->log($this->user, 'future_action', 'Future action', [], $tomorrow);

        $todayLogs = $this->service->getLogsByDateRange(now()->startOfDay(), now()->endOfDay());

        $this->assertCount(1, $todayLogs);
        $this->assertEquals('current_action', $todayLogs->first()->action);
    }

    public function test_handles_null_user_gracefully()
    {
        $this->service->log(null, 'system_action', 'System performed action');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => null,
            'action' => 'system_action',
            'description' => 'System performed action',
        ]);
    }

    public function test_can_clean_old_logs()
    {
        $oldDate = now()->subDays(91);
        $recentDate = now()->subDays(30);

        $this->service->log($this->user, 'old_action', 'Old action', [], $oldDate);
        $this->service->log($this->user, 'recent_action', 'Recent action', [], $recentDate);

        $this->service->cleanOldLogs(90);

        $this->assertDatabaseMissing('activity_logs', ['action' => 'old_action']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'recent_action']);
    }

    public function test_can_get_activity_statistics()
    {
        $this->service->log($this->user, 'login', 'Login 1');
        $this->service->log($this->user, 'login', 'Login 2');
        $this->service->log($this->user, 'investment_created', 'Investment created');

        $stats = $this->service->getActivityStats();

        $this->assertArrayHasKey('login', $stats);
        $this->assertEquals(2, $stats['login']);
        $this->assertArrayHasKey('investment_created', $stats);
        $this->assertEquals(1, $stats['investment_created']);
    }
}