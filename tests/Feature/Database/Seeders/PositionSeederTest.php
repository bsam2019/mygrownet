<?php

namespace Tests\Feature\Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\PositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed departments first as positions depend on them
        $this->artisan('db:seed', ['--class' => DepartmentSeeder::class]);
    }

    public function test_position_seeder_creates_required_positions(): void
    {
        $this->artisan('db:seed', ['--class' => PositionSeeder::class]);

        $expectedPositions = [
            'HR Manager',
            'HR Specialist',
            'Investment Director',
            'Portfolio Manager',
            'Field Operations Manager',
            'Senior Field Agent',
            'Field Agent',
            'Finance Manager',
            'Payroll Administrator',
            'IT Manager',
            'Compliance Officer',
            'Customer Service Manager',
            'Customer Service Representative'
        ];

        foreach ($expectedPositions as $positionTitle) {
            $this->assertTrue(
                PositionModel::where('title', $positionTitle)->exists(),
                "Position '{$positionTitle}' was not created"
            );
        }
    }

    public function test_positions_are_assigned_to_correct_departments(): void
    {
        $this->artisan('db:seed', ['--class' => PositionSeeder::class]);

        // HR Manager should be in HR department
        $hrDept = DepartmentModel::where('name', 'Human Resources')->first();
        $hrManager = PositionModel::where('title', 'HR Manager')->first();
        $this->assertEquals($hrDept->id, $hrManager->department_id);

        // Investment Director should be in Investment Management
        $investmentDept = DepartmentModel::where('name', 'Investment Management')->first();
        $investmentDirector = PositionModel::where('title', 'Investment Director')->first();
        $this->assertEquals($investmentDept->id, $investmentDirector->department_id);

        // Field Agent should be in Field Operations
        $fieldDept = DepartmentModel::where('name', 'Field Operations')->first();
        $fieldAgent = PositionModel::where('title', 'Field Agent')->first();
        $this->assertEquals($fieldDept->id, $fieldAgent->department_id);
    }

    public function test_positions_have_valid_salary_ranges(): void
    {
        $this->artisan('db:seed', ['--class' => PositionSeeder::class]);

        $positions = PositionModel::all();

        foreach ($positions as $position) {
            $this->assertGreaterThan(0, $position->min_salary);
            $this->assertGreaterThan(0, $position->max_salary);
            $this->assertGreaterThan($position->min_salary, $position->max_salary);
            
            // Salary should be reasonable (between K30,000 and K500,000)
            $this->assertGreaterThanOrEqual(30000, $position->min_salary);
            $this->assertLessThanOrEqual(500000, $position->max_salary);
        }
    }

    public function test_management_positions_have_higher_salaries(): void
    {
        $this->artisan('db:seed', ['--class' => PositionSeeder::class]);

        $director = PositionModel::where('title', 'Investment Director')->first();
        $manager = PositionModel::where('title', 'HR Manager')->first();
        $agent = PositionModel::where('title', 'Field Agent')->first();

        // Director should have highest salary
        $this->assertGreaterThan($manager->min_salary, $director->min_salary);
        $this->assertGreaterThan($agent->min_salary, $director->min_salary);

        // Manager should have higher salary than agent
        $this->assertGreaterThan($agent->min_salary, $manager->min_salary);
    }

    public function test_positions_have_valid_levels(): void
    {
        $this->artisan('db:seed', ['--class' => PositionSeeder::class]);

        $positions = PositionModel::whereNotNull('level')->get();

        foreach ($positions as $position) {
            $this->assertGreaterThanOrEqual(1, $position->level);
            $this->assertLessThanOrEqual(10, $position->level);
        }
    }
}