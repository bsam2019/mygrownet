<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Events;

use App\Domain\Employee\Events\EmployeeTerminated;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\EmploymentStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EmployeeTerminatedTest extends TestCase
{
    private EmployeeId $employeeId;
    private EmploymentStatus $previousStatus;
    private DateTimeImmutable $terminationDate;
    private DateTimeImmutable $lastWorkingDay;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employeeId = EmployeeId::generate();
        $this->previousStatus = EmploymentStatus::active();
        $this->terminationDate = new DateTimeImmutable('2024-01-15');
        $this->lastWorkingDay = new DateTimeImmutable('2024-01-14');
    }

    public function test_can_create_employee_terminated_event(): void
    {
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $this->previousStatus,
            terminationReason: 'Resignation',
            terminationType: 'voluntary',
            terminatedBy: 123,
            terminationDate: $this->terminationDate,
            lastWorkingDay: $this->lastWorkingDay,
            eligibleForRehire: true,
            notes: 'Good employee, left for better opportunity'
        );

        $this->assertTrue($this->employeeId->equals($event->employeeId));
        $this->assertTrue($this->previousStatus->equals($event->previousStatus));
        $this->assertEquals('Resignation', $event->terminationReason);
        $this->assertEquals('voluntary', $event->terminationType);
        $this->assertEquals(123, $event->terminatedBy);
        $this->assertEquals($this->terminationDate, $event->terminationDate);
        $this->assertEquals($this->lastWorkingDay, $event->lastWorkingDay);
        $this->assertTrue($event->eligibleForRehire);
        $this->assertEquals('Good employee, left for better opportunity', $event->notes);
    }

    public function test_to_array_returns_correct_data(): void
    {
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $this->previousStatus,
            terminationReason: 'Resignation',
            terminationType: 'voluntary',
            terminatedBy: 123,
            terminationDate: $this->terminationDate,
            lastWorkingDay: $this->lastWorkingDay,
            eligibleForRehire: true,
            notes: 'Good employee'
        );

        $array = $event->toArray();

        $this->assertEquals($this->employeeId->toString(), $array['employee_id']);
        $this->assertEquals('active', $array['previous_status']);
        $this->assertEquals('Resignation', $array['termination_reason']);
        $this->assertEquals('voluntary', $array['termination_type']);
        $this->assertEquals(123, $array['terminated_by']);
        $this->assertEquals('2024-01-15', $array['termination_date']);
        $this->assertEquals('2024-01-14', $array['last_working_day']);
        $this->assertTrue($array['eligible_for_rehire']);
        $this->assertEquals('Good employee', $array['notes']);
        $this->assertArrayHasKey('occurred_at', $array);
    }  
  public function test_get_event_name_returns_correct_name(): void
    {
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $this->previousStatus,
            terminationReason: 'Resignation',
            terminationType: 'voluntary',
            terminatedBy: 123,
            terminationDate: $this->terminationDate
        );

        $this->assertEquals('employee.terminated', $event->getEventName());
    }

    public function test_is_voluntary_returns_true_for_voluntary_termination(): void
    {
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $this->previousStatus,
            terminationReason: 'Resignation',
            terminationType: 'voluntary',
            terminatedBy: 123,
            terminationDate: $this->terminationDate
        );

        $this->assertTrue($event->isVoluntary());
        $this->assertFalse($event->isInvoluntary());
        $this->assertFalse($event->isLayoff());
        $this->assertFalse($event->isRetirement());
    }

    public function test_is_involuntary_returns_true_for_involuntary_termination(): void
    {
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $this->previousStatus,
            terminationReason: 'Performance issues',
            terminationType: 'involuntary',
            terminatedBy: 123,
            terminationDate: $this->terminationDate
        );

        $this->assertFalse($event->isVoluntary());
        $this->assertTrue($event->isInvoluntary());
        $this->assertFalse($event->isLayoff());
        $this->assertFalse($event->isRetirement());
    }

    public function test_is_layoff_returns_true_for_layoff_termination(): void
    {
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $this->previousStatus,
            terminationReason: 'Budget cuts',
            terminationType: 'layoff',
            terminatedBy: 123,
            terminationDate: $this->terminationDate
        );

        $this->assertFalse($event->isVoluntary());
        $this->assertFalse($event->isInvoluntary());
        $this->assertTrue($event->isLayoff());
        $this->assertFalse($event->isRetirement());
    }

    public function test_is_retirement_returns_true_for_retirement_termination(): void
    {
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $this->previousStatus,
            terminationReason: 'Reached retirement age',
            terminationType: 'retirement',
            terminatedBy: 123,
            terminationDate: $this->terminationDate
        );

        $this->assertFalse($event->isVoluntary());
        $this->assertFalse($event->isInvoluntary());
        $this->assertFalse($event->isLayoff());
        $this->assertTrue($event->isRetirement());
    }  
  public function test_is_valid_returns_true_for_valid_event(): void
    {
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $this->previousStatus,
            terminationReason: 'Resignation',
            terminationType: 'voluntary',
            terminatedBy: 123,
            terminationDate: $this->terminationDate,
            lastWorkingDay: $this->lastWorkingDay
        );

        $this->assertTrue($event->isValid());
    }

    public function test_is_valid_returns_false_for_empty_termination_reason(): void
    {
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $this->previousStatus,
            terminationReason: '',
            terminationType: 'voluntary',
            terminatedBy: 123,
            terminationDate: $this->terminationDate
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_invalid_termination_type(): void
    {
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $this->previousStatus,
            terminationReason: 'Resignation',
            terminationType: 'invalid_type',
            terminatedBy: 123,
            terminationDate: $this->terminationDate
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_already_terminated_employee(): void
    {
        $terminatedStatus = EmploymentStatus::terminated('Already terminated');
        
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $terminatedStatus,
            terminationReason: 'Resignation',
            terminationType: 'voluntary',
            terminatedBy: 123,
            terminationDate: $this->terminationDate
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_last_working_day_after_termination_date(): void
    {
        $invalidLastWorkingDay = new DateTimeImmutable('2024-01-16'); // After termination date
        
        $event = new EmployeeTerminated(
            employeeId: $this->employeeId,
            previousStatus: $this->previousStatus,
            terminationReason: 'Resignation',
            terminationType: 'voluntary',
            terminatedBy: 123,
            terminationDate: $this->terminationDate,
            lastWorkingDay: $invalidLastWorkingDay
        );

        $this->assertFalse($event->isValid());
    }
}