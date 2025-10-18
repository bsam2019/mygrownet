<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Entities;

use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Entities\Department;
use App\Domain\Employee\Entities\Position;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\Phone;
use App\Domain\Employee\ValueObjects\Salary;
use App\Domain\Employee\ValueObjects\EmploymentStatus;
use App\Domain\Employee\ValueObjects\PerformanceMetrics;
use App\Domain\Employee\Exceptions\EmployeeException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EmployeeTest extends TestCase
{
    private Department $department;
    private Position $position;
    private DateTimeImmutable $hireDate;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->department = $this->createMockDepartment();
        $this->position = $this->createMockPosition();
        $this->hireDate = new DateTimeImmutable('2023-01-01');
    }

    public function test_can_create_employee_with_valid_data(): void
    {
        $employee = Employee::create(
            'EMP001',
            'John',
            'Doe',
            Email::fromString('john.doe@example.com'),
            $this->hireDate,
            $this->department,
            $this->position,
            Salary::fromKwacha(50000)
        );

        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertEquals('John', $employee->getFirstName());
        $this->assertEquals('Doe', $employee->getLastName());
        $this->assertEquals('John Doe', $employee->getFullName());
        $this->assertTrue($employee->isActive());
    }

    public function test_cannot_create_employee_with_empty_names(): void
    {
        $this->expectException(EmployeeException::class);
        $this->expectExceptionMessage('First name and last name cannot be empty');

        Employee::create(
            'EMP001',
            '',
            'Doe',
            Email::fromString('john.doe@example.com'),
            $this->hireDate,
            $this->department,
            $this->position,
            Salary::fromKwacha(50000)
        );
    }

    public function test_cannot_create_employee_with_future_hire_date(): void
    {
        $this->expectException(EmployeeException::class);

        Employee::create(
            'EMP001',
            'John',
            'Doe',
            Email::fromString('john.doe@example.com'),
            new DateTimeImmutable('+1 day'),
            $this->department,
            $this->position,
            Salary::fromKwacha(50000)
        );
    }

    public function test_can_assign_and_unassign_user(): void
    {
        $employee = $this->createValidEmployee();
        
        $this->assertFalse($employee->hasUser());
        
        $employee->assignToUser(123);
        $this->assertTrue($employee->hasUser());
        
        $employee->unassignFromUser();
        $this->assertFalse($employee->hasUser());
    }

    public function test_can_update_performance_metrics(): void
    {
        $employee = $this->createValidEmployee();
        $metrics = $this->createMockPerformanceMetrics();
        $reviewDate = new DateTimeImmutable();

        $this->assertFalse($employee->hasPerformanceMetrics());
        
        $employee->updatePerformance($metrics, $reviewDate);
        
        $this->assertTrue($employee->hasPerformanceMetrics());
        $this->assertEquals($metrics, $employee->getLastPerformanceMetrics());
        $this->assertEquals($reviewDate, $employee->getLastPerformanceReview());
    }

    public function test_can_calculate_total_compensation(): void
    {
        $employee = $this->createValidEmployee();
        $baseSalary = Salary::fromKwacha(50000);
        
        // Without performance metrics
        $compensation = $employee->calculateTotalCompensation();
        $this->assertEquals($baseSalary->getAmountInKwacha(), $compensation->getAmountInKwacha());
        
        // With performance metrics (assuming position is commission eligible)
        $metrics = $this->createMockPerformanceMetrics(10000); // K10,000 commission generated
        $employee->updatePerformance($metrics);
        
        $compensationWithCommission = $employee->calculateTotalCompensation();
        $this->assertGreaterThan($baseSalary->getAmountInKwacha(), $compensationWithCommission->getAmountInKwacha());
    }

    public function test_employment_status_transitions(): void
    {
        $employee = $this->createValidEmployee();
        
        $this->assertTrue($employee->isActive());
        $this->assertFalse($employee->isTerminated());
        
        // Valid transition: active -> inactive
        $employee->changeEmploymentStatus(EmploymentStatus::inactive());
        $this->assertFalse($employee->isActive());
        
        // Valid transition: inactive -> terminated
        $employee->changeEmploymentStatus(EmploymentStatus::terminated());
        $this->assertTrue($employee->isTerminated());
        $this->assertNotNull($employee->getTerminationDate());
    }

    public function test_invalid_employment_status_transition_throws_exception(): void
    {
        $employee = $this->createValidEmployee();
        
        // Try invalid transition: active -> terminated (should go through inactive first)
        $this->expectException(EmployeeException::class);
        $employee->changeEmploymentStatus(EmploymentStatus::terminated());
    }

    public function test_cannot_assign_self_as_manager(): void
    {
        $employee = $this->createValidEmployee();
        
        $this->expectException(EmployeeException::class);
        $employee->assignManager($employee);
    }

    public function test_can_transfer_to_different_department(): void
    {
        $employee = $this->createValidEmployee();
        $newDepartment = $this->createMockDepartment('IT Department');
        $newPosition = $this->createMockPosition('Developer', $newDepartment);
        
        $originalDepartment = $employee->getDepartment();
        
        $employee->transferToDepartment($newDepartment, $newPosition);
        
        $this->assertNotEquals($originalDepartment->getId(), $employee->getDepartment()->getId());
        $this->assertEquals($newDepartment->getId(), $employee->getDepartment()->getId());
        $this->assertEquals($newPosition->getId(), $employee->getPosition()->getId());
    }

    public function test_can_update_personal_details(): void
    {
        $employee = $this->createValidEmployee();
        
        $newEmail = Email::fromString('john.updated@example.com');
        $newPhone = Phone::fromString('+260123456789');
        
        $employee->updatePersonalDetails(
            'Jonathan',
            'Smith',
            $newEmail,
            $newPhone,
            '123 New Street'
        );
        
        $this->assertEquals('Jonathan', $employee->getFirstName());
        $this->assertEquals('Smith', $employee->getLastName());
        $this->assertEquals('Jonathan Smith', $employee->getFullName());
        $this->assertEquals($newEmail, $employee->getEmail());
        $this->assertEquals($newPhone, $employee->getPhone());
        $this->assertEquals('123 New Street', $employee->getAddress());
    }

    public function test_can_update_salary_within_position_range(): void
    {
        $employee = $this->createValidEmployee();
        $newSalary = Salary::fromKwacha(60000);
        
        $employee->updateSalary($newSalary);
        
        $this->assertEquals($newSalary->getAmountInKwacha(), $employee->getBaseSalary()->getAmountInKwacha());
    }

    public function test_cannot_update_salary_outside_position_range(): void
    {
        $employee = $this->createValidEmployee();
        $salaryTooHigh = Salary::fromKwacha(200000); // Assuming position max is lower
        
        $this->expectException(EmployeeException::class);
        $employee->updateSalary($salaryTooHigh);
    }

    public function test_can_add_notes(): void
    {
        $employee = $this->createValidEmployee();
        
        $this->assertNull($employee->getNotes());
        
        $employee->addNotes('First note');
        $this->assertStringContains('First note', $employee->getNotes());
        
        $employee->addNotes('Second note');
        $notes = $employee->getNotes();
        $this->assertStringContains('First note', $notes);
        $this->assertStringContains('Second note', $notes);
    }

    public function test_years_of_service_calculation(): void
    {
        $employee = $this->createValidEmployee();
        
        $yearsOfService = $employee->getYearsOfService();
        $expectedYears = $this->hireDate->diff(new DateTimeImmutable())->y;
        
        $this->assertEquals($expectedYears, $yearsOfService);
    }

    public function test_commission_eligibility(): void
    {
        $employee = $this->createValidEmployee();
        
        // Assuming the mock position is commission eligible
        $this->assertTrue($employee->isEligibleForCommission());
        
        // Change status to inactive
        $employee->changeEmploymentStatus(EmploymentStatus::inactive());
        $this->assertFalse($employee->isEligibleForCommission());
    }

    private function createValidEmployee(): Employee
    {
        return Employee::create(
            'EMP001',
            'John',
            'Doe',
            Email::fromString('john.doe@example.com'),
            $this->hireDate,
            $this->department,
            $this->position,
            Salary::fromKwacha(50000)
        );
    }

    private function createMockDepartment(string $name = 'HR Department'): Department
    {
        $mock = $this->createMock(Department::class);
        $mock->method('getId')->willReturn($this->createMock(\App\Domain\Employee\ValueObjects\DepartmentId::class));
        $mock->method('getName')->willReturn($name);
        $mock->method('getAllAncestors')->willReturn([]);
        return $mock;
    }

    private function createMockPosition(string $title = 'HR Manager', ?Department $department = null): Position
    {
        $mock = $this->createMock(Position::class);
        $mock->method('getId')->willReturn($this->createMock(\App\Domain\Employee\ValueObjects\PositionId::class));
        $mock->method('getTitle')->willReturn($title);
        $mock->method('isCommissionEligible')->willReturn(true);
        $mock->method('isSalaryInRange')->willReturn(true);
        $mock->method('getBaseSalaryMin')->willReturn(Salary::fromKwacha(30000));
        $mock->method('getBaseSalaryMax')->willReturn(Salary::fromKwacha(100000));
        $mock->method('calculateCommission')->willReturn(Salary::fromKwacha(5000));
        return $mock;
    }

    private function createMockPerformanceMetrics(float $commissionGenerated = 0): PerformanceMetrics
    {
        $mock = $this->createMock(PerformanceMetrics::class);
        $mock->method('getCommissionGenerated')->willReturn($commissionGenerated);
        return $mock;
    }
}