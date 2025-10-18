<?php

namespace Tests\Integration\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeClientAssignmentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DashboardEmployeeIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private EmployeeModel $employee;
    private DepartmentModel $department;
    private PositionModel $position;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test department and position
        $this->department = DepartmentModel::factory()->create([
            'name' => 'Sales Department'
        ]);
        
        $this->position = PositionModel::factory()->create([
            'title' => 'Field Agent',
            'department_id' => $this->department->id
        ]);
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create employee record linked to user
        $this->employee = EmployeeModel::factory()->create([
            'user_id' => $this->user->id,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'active'
        ]);
    }

    public function test_dashboard_includes_employee_data_for_employee_users()
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('dashboard'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Investors/Dashboard/index')
                ->has('employee_data')
                ->where('employee_data.is_employee', true)
                ->where('employee_data.profile.id', $this->employee->id)
                ->where('employee_data.profile.firstName', $this->employee->first_name)
                ->where('employee_data.profile.lastName', $this->employee->last_name)
                ->where('employee_data.profile.department.name', 'Sales Department')
                ->where('employee_data.profile.position.title', 'Field Agent')
        );
    }

    public function test_dashboard_excludes_employee_data_for_non_employee_users()
    {
        $nonEmployeeUser = User::factory()->create();
        $this->actingAs($nonEmployeeUser);
        
        $response = $this->get(route('dashboard'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Investors/Dashboard/index')
                ->where('employee_data', null)
        );
    }

    public function test_dashboard_includes_employee_performance_data()
    {
        // Create performance record
        EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->employee->id,
            'overall_score' => 8.5,
            'goal_achievement_rate' => 85.0,
            'client_retention_rate' => 92.0,
            'new_client_acquisitions' => 5,
            'commission_generated' => 15000.00
        ]);
        
        $this->actingAs($this->user);
        
        $response = $this->get(route('dashboard'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Investors/Dashboard/index')
                ->where('employee_data.performance.overallScore', 8.5)
                ->where('employee_data.performance.goalsAchieved', 85.0)
                ->where('employee_data.performance.clientRetentionRate', 92.0)
                ->where('employee_data.performance.newClientsAcquired', 5)
                ->where('employee_data.performance.revenueGenerated', 15000.00)
        );
    }

    public function test_dashboard_includes_client_portfolio_summary()
    {
        // Create client assignments
        $client1 = User::factory()->create();
        $client2 = User::factory()->create();
        
        EmployeeClientAssignmentModel::factory()->create([
            'employee_id' => $this->employee->id,
            'user_id' => $client1->id,
            'is_active' => true
        ]);
        
        EmployeeClientAssignmentModel::factory()->create([
            'employee_id' => $this->employee->id,
            'user_id' => $client2->id,
            'is_active' => true
        ]);
        
        $this->actingAs($this->user);
        
        $response = $this->get(route('dashboard'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Investors/Dashboard/index')
                ->where('employee_data.client_portfolio.totalClients', 2)
                ->has('employee_data.client_portfolio.commissionSummary')
        );
    }

    public function test_dashboard_includes_commission_summary()
    {
        // Create commission records
        EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'commission_amount' => 5000.00,
            'status' => 'paid',
            'calculation_date' => now()
        ]);
        
        EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'commission_amount' => 3000.00,
            'status' => 'pending',
            'calculation_date' => now()
        ]);
        
        $this->actingAs($this->user);
        
        $response = $this->get(route('dashboard'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Investors/Dashboard/index')
                ->where('employee_data.client_portfolio.commissionSummary.earned', 5000.00)
                ->where('employee_data.client_portfolio.commissionSummary.pending', 3000.00)
        );
    }

    public function test_dashboard_includes_recent_employee_activities()
    {
        // Create commission activity
        EmployeeCommissionModel::factory()->create([
            'employee_id' => $this->employee->id,
            'commission_amount' => 2500.00,
            'status' => 'paid',
            'calculation_date' => now()->subDays(1)
        ]);
        
        // Create performance review activity
        EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->employee->id,
            'overall_score' => 7.8,
            'created_at' => now()->subDays(2)
        ]);
        
        $this->actingAs($this->user);
        
        $response = $this->get(route('dashboard'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Investors/Dashboard/index')
                ->has('employee_data.recent_activities')
                ->where('employee_data.recent_activities.0.type', 'commission')
                ->where('employee_data.recent_activities.1.type', 'performance_review')
        );
    }

    public function test_employee_profile_api_endpoint()
    {
        $this->actingAs($this->user);
        
        $response = $this->get("/api/employee/{$this->employee->id}/profile");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'employee' => [
                    'id',
                    'firstName',
                    'lastName',
                    'employmentStatus',
                    'department',
                    'position',
                    'yearsOfService',
                    'performanceScore'
                ],
                'recentActivity'
            ]);
    }

    public function test_employee_performance_summary_api_endpoint()
    {
        EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->employee->id,
            'overall_score' => 8.2
        ]);
        
        $this->actingAs($this->user);
        
        $response = $this->get("/api/employee/{$this->employee->id}/performance-summary");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'performance' => [
                    'overallScore',
                    'goalsAchieved',
                    'clientRetentionRate',
                    'newClientsAcquired',
                    'revenueGenerated'
                ],
                'recentGoals',
                'performanceTrend'
            ]);
    }

    public function test_employee_client_portfolio_api_endpoint()
    {
        $client = User::factory()->create();
        EmployeeClientAssignmentModel::factory()->create([
            'employee_id' => $this->employee->id,
            'user_id' => $client->id,
            'is_active' => true
        ]);
        
        $this->actingAs($this->user);
        
        $response = $this->get("/api/employee/{$this->employee->id}/client-portfolio");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'summary' => [
                    'totalClients',
                    'activeInvestments',
                    'totalValue'
                ],
                'recentActivity',
                'topClients',
                'commissionSummary'
            ]);
    }

    public function test_unauthorized_access_to_other_employee_data()
    {
        $otherUser = User::factory()->create();
        $otherEmployee = EmployeeModel::factory()->create([
            'user_id' => $otherUser->id,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id
        ]);
        
        $this->actingAs($this->user);
        
        $response = $this->get("/api/employee/{$otherEmployee->id}/profile");
        
        $response->assertStatus(403);
    }

    public function test_dashboard_performance_with_employee_data()
    {
        // Create multiple performance records and commissions
        for ($i = 0; $i < 5; $i++) {
            EmployeePerformanceModel::factory()->create([
                'employee_id' => $this->employee->id
            ]);
            
            EmployeeCommissionModel::factory()->create([
                'employee_id' => $this->employee->id
            ]);
        }
        
        $this->actingAs($this->user);
        
        $startTime = microtime(true);
        $response = $this->get(route('dashboard'));
        $endTime = microtime(true);
        
        $response->assertStatus(200);
        
        // Ensure response time is reasonable (less than 2 seconds)
        $this->assertLessThan(2.0, $endTime - $startTime);
    }
}