<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Events;

use App\Domain\Employee\Events\EmployeePromoted;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\DepartmentId;
use App\Domain\Employee\ValueObjects\PositionId;
use App\Domain\Employee\ValueObjects\Salary;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EmployeePromotedTest extends TestCase
{
    private EmployeeId $employeeId;
    private DepartmentId $previousDepartmentId;
    private DepartmentId $newDepartmentId;
    private PositionId $previousPositionId;
    private PositionId $newPositionId;
    private Salary $previousSalary;
    private Salary $newSalary;
    private DateTimeImmutable $effectiveDate;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employeeId = EmployeeId::generate();
        $this->previousDepartmentId = DepartmentId::generate();
        $this->newDepartmentId = DepartmentId::generate();
        $this->previousPositionId = PositionId::generate();
        $this->newPositionId = PositionId::generate();
        $this->previousSalary = Salary::fromKwacha(50000);
        $this->newSalary = Salary::fromKwacha(60000);
        $this->effectiveDate = new DateTimeImmutable('2024-01-15');
    }

    public function test_can_create_employee_promoted_event(): void
    {
        $event = new EmployeePromoted(
            employeeId: $this->employeeId,
            previousDepartmentId: $this->previousDepartmentId,
            newDepartmentId: $this->newDepartmentId,
            previousPositionId: $this->previousPositionId,
            newPositionId: $this->newPositionId,
            previousSalary: $this->previousSalary,
            newSalary: $this->newSalary,
            promotionReason: 'Excellent performance',
            promotedBy: 123,
            effectiveDate: $this->effectiveDate
        );

        $this->assertTrue($this->employeeId->equals($event->employeeId));
        $this->assertTrue($this->previousDepartmentId->equals($event->previousDepartmentId));
        $this->assertTrue($this->newDepartmentId->equals($event->newDepartmentId));
        $this->assertTrue($this->previousPositionId->equals($event->previousPositionId));
        $this->assertTrue($this->newPositionId->equals($event->newPositionId));
        $this->assertTrue($this->previousSalary->equals($event->previousSalary));
        $this->assertTrue($this->newSalary->equals($event->newSalary));
        $this->assertEquals('Excellent performance', $event->promotionReason);
        $this->assertEquals(123, $event->promotedBy);
        $this->assertEquals($this->effectiveDate, $event->effectiveDate);
    }   
 public function test_to_array_returns_correct_data(): void
    {
        $event = new EmployeePromoted(
            employeeId: $this->employeeId,
            previousDepartmentId: $this->previousDepartmentId,
            newDepartmentId: $this->newDepartmentId,
            previousPositionId: $this->previousPositionId,
            newPositionId: $this->newPositionId,
            previousSalary: $this->previousSalary,
            newSalary: $this->newSalary,
            promotionReason: 'Excellent performance',
            promotedBy: 123,
            effectiveDate: $this->effectiveDate
        );

        $array = $event->toArray();

        $this->assertEquals($this->employeeId->toString(), $array['employee_id']);
        $this->assertEquals($this->previousDepartmentId->toString(), $array['previous_department_id']);
        $this->assertEquals($this->newDepartmentId->toString(), $array['new_department_id']);
        $this->assertEquals($this->previousPositionId->toString(), $array['previous_position_id']);
        $this->assertEquals($this->newPositionId->toString(), $array['new_position_id']);
        $this->assertEquals(5000000, $array['previous_salary']); // 50000 * 100 (ngwee)
        $this->assertEquals(6000000, $array['new_salary']); // 60000 * 100 (ngwee)
        $this->assertEquals('Excellent performance', $array['promotion_reason']);
        $this->assertEquals(123, $array['promoted_by']);
        $this->assertEquals('2024-01-15', $array['effective_date']);
        $this->assertArrayHasKey('occurred_at', $array);
    }

    public function test_get_event_name_returns_correct_name(): void
    {
        $event = new EmployeePromoted(
            employeeId: $this->employeeId,
            previousDepartmentId: $this->previousDepartmentId,
            newDepartmentId: $this->newDepartmentId,
            previousPositionId: $this->previousPositionId,
            newPositionId: $this->newPositionId,
            previousSalary: $this->previousSalary,
            newSalary: $this->newSalary,
            promotionReason: 'Excellent performance',
            promotedBy: 123,
            effectiveDate: $this->effectiveDate
        );

        $this->assertEquals('employee.promoted', $event->getEventName());
    }   
 public function test_is_department_change_returns_true_when_departments_differ(): void
    {
        $event = new EmployeePromoted(
            employeeId: $this->employeeId,
            previousDepartmentId: $this->previousDepartmentId,
            newDepartmentId: $this->newDepartmentId,
            previousPositionId: $this->previousPositionId,
            newPositionId: $this->newPositionId,
            previousSalary: $this->previousSalary,
            newSalary: $this->newSalary,
            promotionReason: 'Department transfer',
            promotedBy: 123,
            effectiveDate: $this->effectiveDate
        );

        $this->assertTrue($event->isDepartmentChange());
    }

    public function test_is_position_change_returns_true_when_positions_differ(): void
    {
        $event = new EmployeePromoted(
            employeeId: $this->employeeId,
            previousDepartmentId: $this->previousDepartmentId,
            newDepartmentId: $this->newDepartmentId,
            previousPositionId: $this->previousPositionId,
            newPositionId: $this->newPositionId,
            previousSalary: $this->previousSalary,
            newSalary: $this->newSalary,
            promotionReason: 'Position upgrade',
            promotedBy: 123,
            effectiveDate: $this->effectiveDate
        );

        $this->assertTrue($event->isPositionChange());
    }

    public function test_is_salary_increase_returns_true_when_salary_increased(): void
    {
        $event = new EmployeePromoted(
            employeeId: $this->employeeId,
            previousDepartmentId: $this->previousDepartmentId,
            newDepartmentId: $this->newDepartmentId,
            previousPositionId: $this->previousPositionId,
            newPositionId: $this->newPositionId,
            previousSalary: $this->previousSalary,
            newSalary: $this->newSalary,
            promotionReason: 'Salary increase',
            promotedBy: 123,
            effectiveDate: $this->effectiveDate
        );

        $this->assertTrue($event->isSalaryIncrease());
    }

    public function test_is_valid_returns_true_for_valid_event(): void
    {
        $event = new EmployeePromoted(
            employeeId: $this->employeeId,
            previousDepartmentId: $this->previousDepartmentId,
            newDepartmentId: $this->newDepartmentId,
            previousPositionId: $this->previousPositionId,
            newPositionId: $this->newPositionId,
            previousSalary: $this->previousSalary,
            newSalary: $this->newSalary,
            promotionReason: 'Excellent performance',
            promotedBy: 123,
            effectiveDate: $this->effectiveDate
        );

        $this->assertTrue($event->isValid());
    }
}