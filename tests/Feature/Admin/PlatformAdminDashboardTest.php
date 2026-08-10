<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Admin\PlatformAdminMetricsService;
use Tests\TestCase;

class PlatformAdminDashboardTest extends TestCase
{
    public function test_non_admin_cannot_access_platform_admin_dashboard(): void
    {
        $user = new User();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_metrics_service_returns_ecosystem_apps(): void
    {
        $service = app(PlatformAdminMetricsService::class);
        $data = $service->getDashboardData();

        $this->assertArrayHasKey('platformOverview', $data);
        $this->assertArrayHasKey('appEcosystem', $data);
        $this->assertIsArray($data['appEcosystem']);
        $this->assertNotEmpty($data['appEcosystem']);
    }
}
