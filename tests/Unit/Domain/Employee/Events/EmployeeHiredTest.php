<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Events;

use App\Domain\Employee\Events\EmployeeHired;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\DepartmentId;
use App\Domain\Employee\ValueObjects\PositionId;
use App\Domain\Employee\ValueObjects\Salary;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EmployeeHiredTest extends TestCase
{
    private EmployeeId $employeeId;
    private DepartmentId $departmentId;
    private PositionId $positionId;
    private Salary $salary;
    private DateTimeImmutable $hireDate;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employeeId = EmployeeId::generate();
        $this->departmentId = DepartmentId::generate();
        $this->positionId = PositionId::generate();
        $this->salary = Salary::fromKwacha(50000);
        $this->hireDate = new DateTimeImmutable('2024-01-15');
    }

    public function test_can_create_employee_hired_event(): void
    {
        $event = new EmployeeHired(
            employeeId: $this->employeeId,
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            departmentId: $this->departmentId,
            positionId: $this->positionId,
            baseSalary: $this->salary,
            hireDate: $this->hireDate,
            userId: 123
        );

        $this->assertTrue($this->employeeId->equals($event->employeeId));
        $this->assertEquals('John', $event->firstName);
        $this->assertEquals('Doe', $event->lastName);
        $this->assertEquals('john.doe@example.com', $event->email);
        $this->assertTrue($this->departmentId->equals($event->departmentId));
        $this->assertTrue($this->positionId->equals($event->positionId));
        $this->assertTrue($this->salary->equals($event->baseSalary));
        $this->assertEquals($this->hireDate, $event->hireDate);
        $this->assertEquals(123, $event->userId);
    }

    public function test_can_create_event_without_user_id(): void
    {
        $event = new EmployeeHired(
            employeeId: $this->employeeId,
            firstName: 'Jane',
            lastName: 'Smith',
            email: 'jane.smith@example.com',
            departmentId: $this->departmentId,
            positionId: $this->positionId,
            baseSalary: $this->salary,
            hireDate: $this->hireDate
        );

        $this->assertNull($event->userId);
    }

    public function test_to_array_returns_correct_data(): void
    {
        $event = new EmployeeHired(
            employeeId: $this->employeeId,
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            departmentId: $this->departmentId,
            positionId: $this->positionId,
            baseSalary: $this->salary,
            hireDate: $this->hireDate,
            userId: 123
        );

        $array = $event->toArray();

        $this->assertEquals($this->employeeId->toString(), $array['employee_id']);
        $this->assertEquals('John', $array['first_name']);
        $this->assertEquals('Doe', $array['last_name']);
        $this->assertEquals('john.doe@example.com', $array['email']);
        $this->assertEquals($this->departmentId->toString(), $array['department_id']);
        $this->assertEquals($this->positionId->toString(), $array['position_id']);
        $this->assertEquals(5000000, $array['base_salary']); // 50000 * 100 (ngwee)
        $this->assertEquals('2024-01-15', $array['hire_date']);
        $this->assertEquals(123, $array['user_id']);
        $this->assertArrayHasKey('occurred_at', $array);
    }

    public function test_get_event_name_returns_correct_name(): void
    {
        $event = new EmployeeHired(
            employeeId: $this->employeeId,
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            departmentId: $this->departmentId,
            positionId: $this->positionId,
            baseSalary: $this->salary,
            hireDate: $this->hireDate
        );

        $this->assertEquals('employee.hired', $event->getEventName());
    }    public 
function test_is_valid_returns_true_for_valid_event(): void
    {
        $event = new EmployeeHired(
            employeeId: $this->employeeId,
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            departmentId: $this->departmentId,
            positionId: $this->positionId,
            baseSalary: $this->salary,
            hireDate: $this->hireDate
        );

        $this->assertTrue($event->isValid());
    }

    public function test_is_valid_returns_false_for_empty_first_name(): void
    {
        $event = new EmployeeHired(
            employeeId: $this->employeeId,
            firstName: '',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            departmentId: $this->departmentId,
            positionId: $this->positionId,
            baseSalary: $this->salary,
            hireDate: $this->hireDate
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_invalid_email(): void
    {
        $event = new EmployeeHired(
            employeeId: $this->employeeId,
            firstName: 'John',
            lastName: 'Doe',
            email: 'invalid-email',
            departmentId: $this->departmentId,
            positionId: $this->positionId,
            baseSalary: $this->salary,
            hireDate: $this->hireDate
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_future_hire_date(): void
    {
        $futureDate = new DateTimeImmutable('+1 day');
        
        $event = new EmployeeHired(
            employeeId: $this->employeeId,
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            departmentId: $this->departmentId,
            positionId: $this->positionId,
            baseSalary: $this->salary,
            hireDate: $futureDate
        );

        $this->assertFalse($event->isValid());
    }
}