<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Entities;

use App\Domain\Employee\Entities\Department;
use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Entities\Position;
use App\Domain\Employee\Exceptions\DepartmentException;
use App\Domain\Employee\ValueObjects\DepartmentId;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\EmploymentStatus;
use App\Domain\Employee\ValueObjects\PositionId;
use App\Domain\Employee\ValueObjects\Salary;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DepartmentTest extends TestCase
{
    private Department $department;
    private Employee $employee;
    private Position $position;

    protected function setUp(): void
    {
        $this->department = Department::create(
            'Engineering',
            'Software development department'
        );

        $this->employee = Employee::create(
            'EMP001',
            'John',
            'Doe',
            Email::fromString('john.doe@example.com'),
            new DateTimeImmutable('2023-01-01'),
            $this->department,
            Position::create(
                'Developer',
                'Software development',
                $this->department,
                Salary::fromKwacha(5000),
                Salary::fromKwacha(10000)
            ),
            Salary::fromKwacha(7500)
        );

        $this->position = Position::create(
            'Test Position',
            'Test position description',
            $this->department,
            Salary::fromKwacha(5000),
            Salary::fromKwacha(10000)
        );
    }

    public function test_can_create_department(): void
    {
        $department = Department::create(
            'Marketing',
            'Marketing and sales department'
        );

        $this->assertEquals('Marketing', $department->getName());
        $this->assertEquals('Marketing and sales department', $department->getDescription());
        $this->assertTrue($department->isActive());
        $this->assertNull($department->getHead());
        $this->assertNull($department->getParentDepartment());
        $this->assertTrue($department->isRootDepartment());
        $this->assertEquals(0, $department->getEmployeeCount());
    }

    public function test_can_create_department_with_parent(): void
    {
        $parentDepartment = Department::create('IT', 'Information Technology');
        $childDepartment = new Department(
            DepartmentId::generate(),
            'Development',
            'Software Development',
            $parentDepartment
        );

        $this->assertEquals($parentDepartment, $childDepartment->getParentDepartment());
        $this->assertFalse($childDepartment->isRootDepartment());
        $this->assertEquals(1, $childDepartment->getDepth());
    }

    public function test_can_add_employee(): void
    {
        $this->department->addEmployee($this->employee);

        $this->assertTrue($this->department->hasEmployee($this->employee));
        $this->assertEquals(1, $this->department->getEmployeeCount());
    }

    public function test_cannot_add_same_employee_twice(): void
    {
        $this->department->addEmployee($this->employee);

        $this->expectException(DepartmentException::class);
        $this->expectExceptionMessage('is already in department');

        $this->department->addEmployee($this->employee);
    }

    public function test_can_remove_employee(): void
    {
        $this->department->addEmployee($this->employee);
        $this->department->removeEmployee($this->employee);

        $this->assertFalse($this->department->hasEmployee($this->employee));
        $this->assertEquals(0, $this->department->getEmployeeCount());
    }

    public function test_cannot_remove_employee_not_in_department(): void
    {
        $this->expectException(DepartmentException::class);
        $this->expectExceptionMessage('is not in department');

        $this->department->removeEmployee($this->employee);
    }

    public function test_can_assign_head(): void
    {
        $this->department->addEmployee($this->employee);
        $this->department->assignHead($this->employee);

        $this->assertEquals($this->employee, $this->department->getHead());
    }

    public function test_cannot_assign_head_from_outside_department(): void
    {
        $this->expectException(DepartmentException::class);
        $this->expectExceptionMessage('employee must be in the department');

        $this->department->assignHead($this->employee);
    }

    public function test_removing_head_employee_clears_head_position(): void
    {
        $this->department->addEmployee($this->employee);
        $this->department->assignHead($this->employee);
        $this->department->removeEmployee($this->employee);

        $this->assertNull($this->department->getHead());
    }

    public function test_can_clear_head(): void
    {
        $this->department->addEmployee($this->employee);
        $this->department->assignHead($this->employee);
        $this->department->clearHead();

        $this->assertNull($this->department->getHead());
    }

    public function test_can_add_position(): void
    {
        $this->department->addPosition($this->position);

        $this->assertContains($this->position, $this->department->getPositions());
    }

    public function test_can_remove_position(): void
    {
        $this->department->addPosition($this->position);
        $this->department->removePosition($this->position);

        $this->assertNotContains($this->position, $this->department->getPositions());
    }

    public function test_can_add_sub_department(): void
    {
        $subDepartment = Department::create('Frontend', 'Frontend development team');
        $this->department->addSubDepartment($subDepartment);

        $this->assertContains($subDepartment, $this->department->getSubDepartments());
        $this->assertTrue($this->department->hasSubDepartments());
    }

    public function test_can_remove_sub_department(): void
    {
        $subDepartment = Department::create('Backend', 'Backend development team');
        $this->department->addSubDepartment($subDepartment);
        $this->department->removeSubDepartment($subDepartment);

        $this->assertNotContains($subDepartment, $this->department->getSubDepartments());
    }

    public function test_prevents_circular_reference_in_hierarchy(): void
    {
        $parentDepartment = Department::create('IT', 'Information Technology');
        $childDepartment = new Department(
            DepartmentId::generate(),
            'Development',
            'Software Development',
            $parentDepartment
        );

        $this->expectException(DepartmentException::class);
        $this->expectExceptionMessage('Circular reference detected');

        // Try to make parent a child of its own child
        $childDepartment->addSubDepartment($parentDepartment);
    }

    public function test_can_deactivate_and_activate(): void
    {
        $this->assertTrue($this->department->isActive());

        $this->department->deactivate();
        $this->assertFalse($this->department->isActive());

        $this->department->activate();
        $this->assertTrue($this->department->isActive());
    }

    public function test_can_update_details(): void
    {
        $this->department->updateDetails('New Engineering', 'Updated description');

        $this->assertEquals('New Engineering', $this->department->getName());
        $this->assertEquals('Updated description', $this->department->getDescription());
    }

    public function test_cannot_create_department_with_empty_name(): void
    {
        $this->expectException(DepartmentException::class);
        $this->expectExceptionMessage('Invalid department name');

        Department::create('', 'Description');
    }

    public function test_cannot_create_department_with_name_too_long(): void
    {
        $longName = str_repeat('a', 101);

        $this->expectException(DepartmentException::class);
        $this->expectExceptionMessage('cannot exceed 100 characters');

        Department::create($longName, 'Description');
    }

    public function test_get_depth_calculation(): void
    {
        $rootDepartment = Department::create('Root', 'Root department');
        $level1Department = new Department(
            DepartmentId::generate(),
            'Level 1',
            'Level 1 department',
            $rootDepartment
        );
        $level2Department = new Department(
            DepartmentId::generate(),
            'Level 2',
            'Level 2 department',
            $level1Department
        );

        $this->assertEquals(0, $rootDepartment->getDepth());
        $this->assertEquals(1, $level1Department->getDepth());
        $this->assertEquals(2, $level2Department->getDepth());
    }

    public function test_get_all_ancestors(): void
    {
        $rootDepartment = Department::create('Root', 'Root department');
        $level1Department = new Department(
            DepartmentId::generate(),
            'Level 1',
            'Level 1 department',
            $rootDepartment
        );
        $level2Department = new Department(
            DepartmentId::generate(),
            'Level 2',
            'Level 2 department',
            $level1Department
        );

        $ancestors = $level2Department->getAllAncestors();

        $this->assertCount(2, $ancestors);
        $this->assertEquals($level1Department, $ancestors[0]);
        $this->assertEquals($rootDepartment, $ancestors[1]);
    }

    public function test_get_all_descendants(): void
    {
        $rootDepartment = Department::create('Root', 'Root department');
        $level1Department = Department::create('Level 1', 'Level 1 department');
        $level2Department = Department::create('Level 2', 'Level 2 department');

        $rootDepartment->addSubDepartment($level1Department);
        $level1Department->addSubDepartment($level2Department);

        $descendants = $rootDepartment->getAllDescendants();

        $this->assertCount(2, $descendants);
        $this->assertContains($level1Department, $descendants);
        $this->assertContains($level2Department, $descendants);
    }

    public function test_get_active_employee_count(): void
    {
        $activeEmployee = Employee::create(
            'EMP002',
            'Jane',
            'Active',
            Email::fromString('jane.active@example.com'),
            new DateTimeImmutable('2023-01-01'),
            $this->department,
            $this->position,
            Salary::fromKwacha(7500)
        );
        
        $inactiveEmployee = Employee::create(
            'EMP003',
            'Bob',
            'Inactive',
            Email::fromString('bob.inactive@example.com'),
            new DateTimeImmutable('2023-01-01'),
            $this->department,
            $this->position,
            Salary::fromKwacha(7500)
        );
        
        // Change status to inactive
        $inactiveEmployee->changeEmploymentStatus(EmploymentStatus::inactive('On leave'));

        $this->department->addEmployee($activeEmployee);
        $this->department->addEmployee($inactiveEmployee);

        $this->assertEquals(2, $this->department->getEmployeeCount());
        $this->assertEquals(1, $this->department->getActiveEmployeeCount());
    }
}