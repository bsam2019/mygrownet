<?php

namespace Tests\Integration\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AdminDashboardEmployeeIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $adminUser;
    private DepartmentModel $department;
    private PositionModel $position;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->adminUser = User::factory()->create(['is_admin' => true]);
        
        // Create test department and position
        $this->department = DepartmentModel::factory()->create();
        $this->position = PositionModel::factory()->create([
            'department_id' => $this->department->id
        ]);
    }

    public function test_admin_dashboard_includes_employee_management_data()
    {
        // Create test employees
        EmployeeModel::factory()->count(5)->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'active'
        ]);
        
        $this->actingAs($this->adminUser);
        
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

    public function test_employee_management_summary_api_endpoint()
    {
        EmployeeModel::factory()->count(3)->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id
        ]);
        
        $this->actingAs($this->adminUser);
        
        $response = $this->get('/api/admin/employee-management-summary');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
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

    public function test_department_overview_api_endpoint()
    {
        $this->actingAs($this->adminUser);
        
        $response = $this->get('/api/admin/department-overview');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'stats',
                'performance',
                'heads',
                'recentChanges'
            ]);
    }

    public function test_performance_stats_api_endpoint()
    {
        $employee = EmployeeModel::factory()->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id
        ]);
        
        EmployeePerformanceModel::factory()->create([
            'employee_id' => $employee->id,
            'overall_score' => 8.5
        ]);
        
        $this->actingAs($this->adminUser);
        
        $response = $this->get('/api/admin/performance-stats');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'stats',
                'distribution',
                'topPerformers',
                'trends',
                'commissionStats'
            ]);
    }

    public function test_non_admin_cannot_access_employee_management_endpoints()
    {
        $regularUser = User::factory()->create(['is_admin' => false]);
        $this->actingAs($regularUser);
        
        $response = $this->get('/api/admin/employee-management-summary');
        $response->assertStatus(403);
        
        $response = $this->get('/api/admin/department-overview');
        $response->assertStatus(403);
        
        $response = $this->get('/api/admin/performance-stats');
        $response->assertStatus(403);
    }
}