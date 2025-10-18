<?php

namespace Tests\Feature\Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\EmployeeClientAssignmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\EmployeeClientAssignmentSeeder;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\PositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeClientAssignmentSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed dependencies
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        $this->artisan('db:seed', ['--class' => DepartmentSeeder::class]);
        $this->artisan('db:seed', ['--class' => PositionSeeder::class]);
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);
        
        // Create some users for client assignments
        User::factory()->count(15)->create();
    }

    public function test_client_assignment_seeder_creates_assignments(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeClientAssignmentSeeder::class]);

        $this->assertGreaterThan(0, EmployeeClientAssignmentModel::count());
    }

    public function test_assignments_have_valid_relationships(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeClientAssignmentSeeder::class]);

        $assignments = EmployeeClientAssignmentModel::with(['employee', 'user'])->get();

        foreach ($assignments as $assignment) {
            // Employee must exist
            $this->assertNotNull($assignment->employee);
            $this->assertInstanceOf(EmployeeModel::class, $assignment->employee);
            
            // User (client) must exist
            $this->assertNotNull($assignment->user);
            $this->assertInstanceOf(User::class, $assignment->user);
        }
    }

    public function test_assignments_have_valid_types(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeClientAssignmentSeeder::class]);

        $assignments = EmployeeClientAssignmentModel::all();
        $validTypes = ['primary', 'secondary', 'support'];

        foreach ($assignments as $assignment) {
            $this->assertContains($assignment->assignment_type, $validTypes);
        }
    }

    public function test_assignments_have_valid_dates(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeClientAssignmentSeeder::class]);

        $assignments = EmployeeClientAssignmentModel::all();

        foreach ($assignments as $assignment) {
            $this->assertNotNull($assignment->assigned_at);
            $this->assertLessThanOrEqual(now(), $assignment->assigned_at);
            
            if ($assignment->unassigned_at) {
                $this->assertGreaterThan($assignment->assigned_at, $assignment->unassigned_at);
            }
        }
    }

    public function test_field_agents_have_more_client_assignments(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeClientAssignmentSeeder::class]);

        $fieldAgents = EmployeeModel::whereHas('position', function ($query) {
            $query->where('title', 'Field Agent');
        })->get();

        $otherEmployees = EmployeeModel::whereHas('position', function ($query) {
            $query->where('title', '!=', 'Field Agent');
        })->get();

        if ($fieldAgents->count() > 0 && $otherEmployees->count() > 0) {
            $fieldAgentAssignments = EmployeeClientAssignmentModel::whereIn('employee_id', $fieldAgents->pluck('id'))->count();
            $otherAssignments = EmployeeClientAssignmentModel::whereIn('employee_id', $otherEmployees->pluck('id'))->count();

            $avgFieldAgentAssignments = $fieldAgentAssignments / $fieldAgents->count();
            $avgOtherAssignments = $otherAssignments / $otherEmployees->count();

            // Field agents should have more client assignments on average
            $this->assertGreaterThan($avgOtherAssignments, $avgFieldAgentAssignments);
        }
    }

    public function test_clients_have_primary_assignments(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeClientAssignmentSeeder::class]);

        $primaryAssignments = EmployeeClientAssignmentModel::where('assignment_type', 'primary')
            ->whereNull('unassigned_at')
            ->get();

        $clientsWithPrimary = $primaryAssignments->pluck('user_id')->unique();
        
        // Most active assignments should be primary
        $this->assertGreaterThan(0, $clientsWithPrimary->count());
    }

    public function test_no_duplicate_primary_assignments(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeClientAssignmentSeeder::class]);

        $activePrimaryAssignments = EmployeeClientAssignmentModel::where('assignment_type', 'primary')
            ->whereNull('unassigned_at')
            ->get();

        $clientIds = $activePrimaryAssignments->pluck('user_id')->toArray();
        $uniqueClientIds = array_unique($clientIds);

        // Each client should have only one active primary assignment
        $this->assertEquals(count($clientIds), count($uniqueClientIds));
    }

    public function test_assignments_are_distributed_over_time(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeClientAssignmentSeeder::class]);

        $assignments = EmployeeClientAssignmentModel::all();
        $assignmentMonths = $assignments->pluck('assigned_at')->map(function ($date) {
            return $date->format('Y-m');
        })->unique();

        // Should have assignments across multiple months
        $this->assertGreaterThan(1, $assignmentMonths->count());
    }

    public function test_some_assignments_are_terminated(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeClientAssignmentSeeder::class]);

        $terminatedAssignments = EmployeeClientAssignmentModel::whereNotNull('unassigned_at')->count();
        $totalAssignments = EmployeeClientAssignmentModel::count();

        // Some assignments should be terminated (realistic scenario)
        $terminationRate = $terminatedAssignments / $totalAssignments;
        $this->assertGreaterThan(0, $terminationRate);
        $this->assertLessThan(0.5, $terminationRate); // Less than 50% terminated
    }

    public function test_assignment_notes_are_meaningful(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeClientAssignmentSeeder::class]);

        $assignmentsWithNotes = EmployeeClientAssignmentModel::whereNotNull('notes')->get();

        foreach ($assignmentsWithNotes as $assignment) {
            $this->assertIsString($assignment->notes);
            $this->assertNotEmpty(trim($assignment->notes));
            $this->assertGreaterThan(10, strlen($assignment->notes)); // Meaningful notes
        }
    }

    public function test_employees_have_reasonable_client_loads(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeClientAssignmentSeeder::class]);

        $employeeClientCounts = EmployeeClientAssignmentModel::whereNull('unassigned_at')
            ->groupBy('employee_id')
            ->selectRaw('employee_id, count(*) as client_count')
            ->pluck('client_count', 'employee_id');

        foreach ($employeeClientCounts as $employeeId => $clientCount) {
            // No employee should have more than 20 active clients
            $this->assertLessThanOrEqual(20, $clientCount);
            
            // Each employee should have at least 1 client
            $this->assertGreaterThanOrEqual(1, $clientCount);
        }
    }
}