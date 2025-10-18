<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class EmployeeDashboardIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin@test.com'
        ]);
    }

    /** @test */
    public function admin_dashboard_includes_employee_management_data()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/Dashboard/Index')
                ->has('employeeManagement')
                ->has('employeeManagement.stats')
                ->has('employeeManagement.recentActivities')
                ->has('employeeManagement.departmentOverview')
                ->has('employeeManagement.performanceStats')
        );
    }

    /** @test */
    public function employee_management_api_endpoints_require_admin_access()
    {
        // Test without authentication
        $response = $this->get('/api/admin/employee-management-summary');
        $response->assertStatus(302); // Redirects to login

        // Test with regular user
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);
        
        $response = $this->get('/api/admin/employee-management-summary');
        $response->assertStatus(403);

        // Test with admin user
        $this->actingAs($this->admin);
        
        $response = $this->get('/api/admin/employee-management-summary');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'stats' => [
                'totalEmployees',
                'activeEmployees',
                'newHires',
                'departments'
            ],
            'recentActivities',
            'departmentOverview',
            'performanceStats'
        ]);
    }

    /** @test */
    public function department_overview_api_returns_correct_structure()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/api/admin/department-overview');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'stats' => [
                'totalDepartments',
                'averageEmployeesPerDept'
            ],
            'performance',
            'heads',
            'recentChanges'
        ]);
    }

    /** @test */
    public function performance_stats_api_returns_correct_structure()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/api/admin/performance-stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'stats' => [
                'averageScore',
                'topPerformers',
                'goalAchievementRate',
                'totalCommissions'
            ],
            'distribution',
            'topPerformers',
            'trends'
        ]);
    }

    /** @test */
    public function non_admin_users_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }
}