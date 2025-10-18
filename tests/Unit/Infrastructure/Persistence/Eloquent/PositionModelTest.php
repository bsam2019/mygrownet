<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_position_has_fillable_attributes(): void
    {
        $fillable = [
            'title',
            'description',
            'department_id',
            'min_salary',
            'max_salary',
            'base_commission_rate',
            'performance_commission_rate',
            'permissions',
            'level',
            'is_active',
        ];

        $position = new PositionModel();
        
        $this->assertEquals($fillable, $position->getFillable());
    }

    public function test_position_casts_attributes_correctly(): void
    {
        $position = PositionModel::factory()->create([
            'min_salary' => '50000.00',
            'max_salary' => '75000.00',
            'base_commission_rate' => '0.1050',
            'performance_commission_rate' => '0.0500',
            'permissions' => ['permission1', 'permission2'],
            'level' => '3',
            'is_active' => '1',
        ]);

        $this->assertIsFloat($position->min_salary);
        $this->assertIsFloat($position->max_salary);
        $this->assertIsFloat($position->base_commission_rate);
        $this->assertIsFloat($position->performance_commission_rate);
        $this->assertIsArray($position->permissions);
        $this->assertIsInt($position->level);
        $this->assertIsBool($position->is_active);
    }

    public function test_position_belongs_to_department(): void
    {
        $department = DepartmentModel::factory()->create();
        $position = PositionModel::factory()->create([
            'department_id' => $department->id,
        ]);

        $this->assertInstanceOf(DepartmentModel::class, $position->department);
        $this->assertEquals($department->id, $position->department->id);
    }

    public function test_position_has_many_employees(): void
    {
        $position = PositionModel::factory()->create();
        $employees = EmployeeModel::factory()->count(4)->create([
            'position_id' => $position->id,
        ]);

        $this->assertCount(4, $position->employees);
        
        foreach ($employees as $employee) {
            $this->assertTrue($position->employees->contains($employee));
        }
    }

    public function test_active_scope_filters_active_positions(): void
    {
        PositionModel::factory()->count(3)->create(['is_active' => true]);
        PositionModel::factory()->count(2)->create(['is_active' => false]);

        $activePositions = PositionModel::active()->get();

        $this->assertCount(3, $activePositions);
        
        foreach ($activePositions as $position) {
            $this->assertTrue($position->is_active);
        }
    }

    public function test_commission_eligible_scope_filters_commission_eligible_positions(): void
    {
        PositionModel::factory()->count(2)->create(['base_commission_rate' => 0.10]);
        PositionModel::factory()->count(3)->create(['base_commission_rate' => 0, 'performance_commission_rate' => 0]);

        $commissionEligiblePositions = PositionModel::commissionEligible()->get();

        $this->assertCount(2, $commissionEligiblePositions);
        
        foreach ($commissionEligiblePositions as $position) {
            $this->assertTrue($position->base_commission_rate > 0 || $position->performance_commission_rate > 0);
        }
    }

    public function test_in_department_scope_filters_by_department(): void
    {
        $department1 = DepartmentModel::factory()->create();
        $department2 = DepartmentModel::factory()->create();
        
        PositionModel::factory()->count(3)->create(['department_id' => $department1->id]);
        PositionModel::factory()->count(2)->create(['department_id' => $department2->id]);

        $positionsInDepartment1 = PositionModel::inDepartment($department1->id)->get();

        $this->assertCount(3, $positionsInDepartment1);
        
        foreach ($positionsInDepartment1 as $position) {
            $this->assertEquals($department1->id, $position->department_id);
        }
    }

    public function test_with_salary_range_scope_filters_by_salary_range(): void
    {
        PositionModel::factory()->create([
            'min_salary' => 30000,
            'max_salary' => 50000,
        ]);
        PositionModel::factory()->create([
            'min_salary' => 60000,
            'max_salary' => 80000,
        ]);
        PositionModel::factory()->create([
            'min_salary' => 40000,
            'max_salary' => 70000,
        ]);

        $positionsWithMinSalary = PositionModel::withSalaryRange(35000)->get();
        $positionsWithMaxSalary = PositionModel::withSalaryRange(null, 75000)->get();
        $positionsWithBothLimits = PositionModel::withSalaryRange(35000, 75000)->get();

        $this->assertCount(2, $positionsWithMinSalary); // 60k-80k and 40k-70k
        $this->assertCount(2, $positionsWithMaxSalary); // 30k-50k and 40k-70k
        $this->assertCount(1, $positionsWithBothLimits); // 40k-70k
    }

    public function test_with_employee_count_scope_includes_employee_count(): void
    {
        $position = PositionModel::factory()->create();
        EmployeeModel::factory()->count(3)->create(['position_id' => $position->id]);

        $positionWithCount = PositionModel::withEmployeeCount()->find($position->id);

        $this->assertEquals(3, $positionWithCount->employees_count);
    }

    public function test_position_can_be_created_with_factory(): void
    {
        $position = PositionModel::factory()->create();

        $this->assertInstanceOf(PositionModel::class, $position);
        $this->assertNotNull($position->title);
        $this->assertNotNull($position->description);
        $this->assertIsFloat($position->min_salary);
        $this->assertIsFloat($position->max_salary);
        $this->assertIsFloat($position->base_commission_rate);
        $this->assertIsFloat($position->performance_commission_rate);
        $this->assertIsBool($position->is_active);
    }

    public function test_position_factory_states_work_correctly(): void
    {
        $activePosition = PositionModel::factory()->active()->create();
        $inactivePosition = PositionModel::factory()->inactive()->create();
        $commissionEligiblePosition = PositionModel::factory()->commissionEligible()->create();
        $noCommissionPosition = PositionModel::factory()->noCommission()->create();

        $this->assertTrue($activePosition->is_active);
        $this->assertFalse($inactivePosition->is_active);
        $this->assertTrue($commissionEligiblePosition->base_commission_rate > 0 || $commissionEligiblePosition->performance_commission_rate > 0);
        $this->assertEquals(0, $noCommissionPosition->base_commission_rate);
        $this->assertEquals(0, $noCommissionPosition->performance_commission_rate);
    }

    public function test_field_agent_factory_state(): void
    {
        $fieldAgent = PositionModel::factory()->fieldAgent()->create();

        $this->assertEquals('Field Agent', $fieldAgent->title);
        $this->assertEquals(0.10, $fieldAgent->base_commission_rate);
        $this->assertEquals(0.05, $fieldAgent->performance_commission_rate);
        $this->assertTrue($fieldAgent->is_active);
        $this->assertIsArray($fieldAgent->permissions);
        $this->assertEquals(2, $fieldAgent->level);
    }

    public function test_manager_factory_state(): void
    {
        $manager = PositionModel::factory()->manager()->create();

        $this->assertStringContainsString('Manager', $manager->title);
        $this->assertEquals(0, $manager->base_commission_rate);
        $this->assertEquals(0, $manager->performance_commission_rate);
        $this->assertTrue($manager->is_active);
        $this->assertGreaterThanOrEqual(60000, $manager->min_salary);
        $this->assertEquals(4, $manager->level);
    }

    public function test_position_soft_deletes(): void
    {
        $position = PositionModel::factory()->create();
        $positionId = $position->id;

        $position->delete();

        $this->assertSoftDeleted('positions', ['id' => $positionId]);
        $this->assertNotNull($position->fresh()->deleted_at);
    }
}