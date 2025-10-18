<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Employee;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PerformanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private EmployeeModel $employee;
    private EmployeeModel $reviewer;
    private DepartmentModel $department;
    private PositionModel $position;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'create performance reviews', 'slug' => 'create-performance-reviews']);
        Permission::create(['name' => 'update performance reviews', 'slug' => 'update-performance-reviews']);
        Permission::create(['name' => 'view performance reviews', 'slug' => 'view-performance-reviews']);
        Permission::create(['name' => 'delete performance reviews', 'slug' => 'delete-performance-reviews']);
        Permission::create(['name' => 'set employee goals', 'slug' => 'set-employee-goals']);

        // Create role with permissions
        $role = Role::create(['name' => 'HR Manager', 'slug' => 'hr-manager']);
        $role->givePermissionTo([
            'create performance reviews',
            'update performance reviews',
            'view performance reviews',
            'delete performance reviews',
            'set employee goals'
        ]);

        // Create user and assign role
        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        // Create department and position
        $this->department = DepartmentModel::factory()->create([
            'name' => 'Sales Department'
        ]);

        $this->position = PositionModel::factory()->create([
            'title' => 'Field Agent',
            'department_id' => $this->department->id
        ]);

        // Create employees
        $this->employee = EmployeeModel::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'active'
        ]);

        $this->reviewer = EmployeeModel::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'active'
        ]);
    }

    public function test_index_displays_performance_reviews(): void
    {
        // Create performance reviews
        $performance1 = EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'overall_score' => 8.5,
            'rating' => 'meets_expectations'
        ]);

        $performance2 = EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->reviewer->id,
            'reviewer_id' => $this->employee->id,
            'overall_score' => 9.2,
            'rating' => 'outstanding'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('performance.index'));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Performance/Index')
                ->has('performanceReviews.data', 2)
                ->has('employees')
                ->has('filters')
            );
    }

    public function test_index_filters_by_employee(): void
    {
        // Create performance reviews for different employees
        EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id
        ]);

        EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->reviewer->id,
            'reviewer_id' => $this->employee->id
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('performance.index', ['employee_id' => $this->employee->id]));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Performance/Index')
                ->has('performanceReviews.data', 1)
                ->where('performanceReviews.data.0.employee_id', $this->employee->id)
            );
    }

    public function test_index_filters_by_minimum_score(): void
    {
        // Create performance reviews with different scores
        EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'overall_score' => 5.5
        ]);

        EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->reviewer->id,
            'reviewer_id' => $this->employee->id,
            'overall_score' => 8.5
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('performance.index', ['min_score' => 7.0]));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Performance/Index')
                ->has('performanceReviews.data', 1)
                ->where('performanceReviews.data.0.overall_score', 8.5)
            );
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('performance.create'));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Performance/Create')
                ->has('employees')
                ->where('selectedEmployeeId', null)
            );
    }

    public function test_create_with_selected_employee(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('performance.create', ['employee_id' => $this->employee->id]));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Performance/Create')
                ->has('employees')
                ->where('selectedEmployeeId', $this->employee->id)
            );
    }

    public function test_store_creates_performance_review(): void
    {
        $data = [
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'evaluation_period_start' => '2024-01-01',
            'evaluation_period_end' => '2024-03-31',
            'investments_facilitated' => 15.0,
            'investments_facilitated_amount' => 150000.0,
            'client_retention_rate' => 85.5,
            'commission_generated' => 12500.0,
            'new_client_acquisitions' => 8,
            'goal_achievement_rate' => 90.0,
            'review_notes' => 'Excellent performance this quarter.',
            'employee_comments' => 'Thank you for the feedback.',
            'goals_next_period' => [
                'Increase client retention to 90%',
                'Facilitate 20 investments next quarter'
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('performance.store'), $data);

        $response->assertRedirect(route('performance.index'))
            ->assertSessionHas('success', 'Performance review created successfully.');

        $this->assertDatabaseHas('employee_performance', [
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'period_start' => '2024-01-01',
            'period_end' => '2024-03-31',
            'reviewer_comments' => 'Excellent performance this quarter.',
            'employee_comments' => 'Thank you for the feedback.'
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('performance.store'), []);

        $response->assertSessionHasErrors([
            'employee_id',
            'reviewer_id',
            'evaluation_period_start',
            'evaluation_period_end',
            'investments_facilitated',
            'client_retention_rate',
            'commission_generated',
            'new_client_acquisitions',
            'goal_achievement_rate'
        ]);
    }

    public function test_store_validates_evaluation_period(): void
    {
        $data = [
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'evaluation_period_start' => '2024-03-31',
            'evaluation_period_end' => '2024-01-01', // End before start
            'investments_facilitated' => 10.0,
            'client_retention_rate' => 80.0,
            'commission_generated' => 10000.0,
            'new_client_acquisitions' => 5,
            'goal_achievement_rate' => 85.0
        ];

        $response = $this->actingAs($this->user)
            ->post(route('performance.store'), $data);

        $response->assertSessionHasErrors(['evaluation_period_start', 'evaluation_period_end']);
    }

    public function test_store_validates_self_review(): void
    {
        $data = [
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->employee->id, // Same as employee
            'evaluation_period_start' => '2024-01-01',
            'evaluation_period_end' => '2024-03-31',
            'investments_facilitated' => 10.0,
            'client_retention_rate' => 80.0,
            'commission_generated' => 10000.0,
            'new_client_acquisitions' => 5,
            'goal_achievement_rate' => 85.0
        ];

        $response = $this->actingAs($this->user)
            ->post(route('performance.store'), $data);

        $response->assertSessionHasErrors(['reviewer_id']);
    }

    public function test_store_validates_numeric_ranges(): void
    {
        $data = [
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'evaluation_period_start' => '2024-01-01',
            'evaluation_period_end' => '2024-03-31',
            'investments_facilitated' => -5.0, // Negative
            'client_retention_rate' => 150.0, // Over 100%
            'commission_generated' => -1000.0, // Negative
            'new_client_acquisitions' => -2, // Negative
            'goal_achievement_rate' => 120.0 // Over 100%
        ];

        $response = $this->actingAs($this->user)
            ->post(route('performance.store'), $data);

        $response->assertSessionHasErrors([
            'investments_facilitated',
            'client_retention_rate',
            'commission_generated',
            'new_client_acquisitions',
            'goal_achievement_rate'
        ]);
    }

    public function test_show_displays_performance_review(): void
    {
        $performance = EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'overall_score' => 8.5,
            'rating' => 'meets_expectations',
            'reviewer_comments' => 'Good performance overall.',
            'goals_next_period' => ['Goal 1', 'Goal 2']
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('performance.show', $performance));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Performance/Show')
                ->has('performance')
                ->has('trends')
                ->has('recommendations')
                ->where('performance.id', $performance->id)
                ->where('performance.overall_score', 8.5)
                ->where('performance.reviewer_comments', 'Good performance overall.')
            );
    }

    public function test_edit_displays_form_with_data(): void
    {
        $performance = EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'period_start' => '2024-01-01',
            'period_end' => '2024-03-31',
            'metrics' => [
                'investments_facilitated_count' => 15,
                'client_retention_rate' => 85.5,
                'commission_generated' => 12500.0,
                'new_client_acquisitions' => 8,
                'goal_achievement_rate' => 90.0
            ],
            'reviewer_comments' => 'Good work this quarter.'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('performance.edit', $performance));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Performance/Edit')
                ->has('performance')
                ->has('employees')
                ->where('performance.employee_id', $this->employee->id)
                ->where('performance.reviewer_id', $this->reviewer->id)
                ->where('performance.investments_facilitated', 15)
                ->where('performance.review_notes', 'Good work this quarter.')
            );
    }

    public function test_update_modifies_performance_review(): void
    {
        $performance = EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'period_start' => '2024-01-01',
            'period_end' => '2024-03-31',
            'reviewer_comments' => 'Original comment'
        ]);

        $data = [
            'evaluation_period_start' => '2024-01-01',
            'evaluation_period_end' => '2024-03-31',
            'investments_facilitated' => 20.0,
            'investments_facilitated_amount' => 200000.0,
            'client_retention_rate' => 90.0,
            'commission_generated' => 15000.0,
            'new_client_acquisitions' => 10,
            'goal_achievement_rate' => 95.0,
            'review_notes' => 'Updated comment',
            'employee_comments' => 'Updated employee comment',
            'goals_next_period' => ['Updated goal 1', 'Updated goal 2']
        ];

        $response = $this->actingAs($this->user)
            ->put(route('performance.update', $performance), $data);

        $response->assertRedirect(route('performance.show', $performance))
            ->assertSessionHas('success', 'Performance review updated successfully.');

        $performance->refresh();
        $this->assertEquals('Updated comment', $performance->reviewer_comments);
        $this->assertEquals('Updated employee comment', $performance->employee_comments);
        $this->assertEquals(['Updated goal 1', 'Updated goal 2'], $performance->goals_next_period);
    }

    public function test_destroy_deletes_performance_review(): void
    {
        $performance = EmployeePerformanceModel::factory()->create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('performance.destroy', $performance));

        $response->assertRedirect(route('performance.index'))
            ->assertSessionHas('success', 'Performance review deleted successfully.');

        $this->assertSoftDeleted('employee_performance', [
            'id' => $performance->id
        ]);
    }

    public function test_analytics_displays_performance_data(): void
    {
        // Create performance reviews for analytics
        EmployeePerformanceModel::factory()->count(5)->create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'overall_score' => 8.5
        ]);

        EmployeePerformanceModel::factory()->count(3)->create([
            'employee_id' => $this->reviewer->id,
            'reviewer_id' => $this->employee->id,
            'overall_score' => 5.5
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('performance.analytics'));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Performance/Analytics')
                ->has('departmentStats')
                ->has('topPerformers')
                ->has('underperformers')
                ->has('performanceTrends')
            );
    }

    public function test_set_goals_creates_employee_goals(): void
    {
        $data = [
            'employee_id' => $this->employee->id,
            'target_date' => now()->addMonths(3)->format('Y-m-d'),
            'goals' => [
                [
                    'description' => 'Increase client retention to 90%',
                    'target' => 90.0,
                    'deadline' => now()->addMonths(2)->format('Y-m-d'),
                    'priority' => 'high',
                    'category' => 'client_retention'
                ],
                [
                    'description' => 'Facilitate 25 investments',
                    'target' => 25.0,
                    'deadline' => now()->addMonths(3)->format('Y-m-d'),
                    'priority' => 'medium',
                    'category' => 'investment_facilitation'
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('performance.goals.set'), $data);

        $response->assertRedirect()
            ->assertSessionHas('success', 'Goals set successfully.');
    }

    public function test_set_goals_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('performance.goals.set'), []);

        $response->assertSessionHasErrors([
            'employee_id',
            'target_date',
            'goals'
        ]);
    }

    public function test_set_goals_validates_goal_structure(): void
    {
        $data = [
            'employee_id' => $this->employee->id,
            'target_date' => now()->addMonths(3)->format('Y-m-d'),
            'goals' => [
                [
                    'description' => '', // Empty description
                    'target' => -5.0, // Negative target
                    'deadline' => now()->subDays(1)->format('Y-m-d'), // Past deadline
                    'priority' => 'invalid', // Invalid priority
                    'category' => 'invalid' // Invalid category
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('performance.goals.set'), $data);

        $response->assertSessionHasErrors([
            'goals.0.description',
            'goals.0.target',
            'goals.0.deadline',
            'goals.0.priority',
            'goals.0.category'
        ]);
    }

    public function test_track_goals_displays_progress(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('performance.goals.track', $this->employee->id));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Performance/GoalTracking')
                ->has('employee')
                ->has('goalProgress')
                ->where('employee.id', $this->employee->id)
            );
    }

    public function test_unauthorized_user_cannot_access_performance_routes(): void
    {
        $unauthorizedUser = User::factory()->create();

        // Test index
        $response = $this->actingAs($unauthorizedUser)
            ->get(route('performance.index'));
        $response->assertStatus(403);

        // Test create
        $response = $this->actingAs($unauthorizedUser)
            ->get(route('performance.create'));
        $response->assertStatus(403);

        // Test store
        $response = $this->actingAs($unauthorizedUser)
            ->post(route('performance.store'), []);
        $response->assertStatus(403);
    }

    public function test_performance_review_with_goals_next_period(): void
    {
        $data = [
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'evaluation_period_start' => '2024-01-01',
            'evaluation_period_end' => '2024-03-31',
            'investments_facilitated' => 15.0,
            'client_retention_rate' => 85.5,
            'commission_generated' => 12500.0,
            'new_client_acquisitions' => 8,
            'goal_achievement_rate' => 90.0,
            'review_notes' => 'Excellent performance this quarter.',
            'goals_next_period' => [
                'Increase client retention to 90%',
                'Facilitate 20 investments next quarter',
                'Acquire 10 new clients'
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('performance.store'), $data);

        $response->assertRedirect(route('performance.index'));

        $this->assertDatabaseHas('employee_performance', [
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id
        ]);

        $performance = EmployeePerformanceModel::where('employee_id', $this->employee->id)->first();
        $this->assertCount(3, $performance->goals_next_period);
        $this->assertContains('Increase client retention to 90%', $performance->goals_next_period);
    }
}