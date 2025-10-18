<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Services;

use App\Domain\Employee\Services\EmployeeRegistrationService;
use App\Domain\Employee\Services\EmployeeRegistrationData;
use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Entities\Department;
use App\Domain\Employee\Entities\Position;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\Phone;
use App\Domain\Employee\ValueObjects\Salary;
use App\Domain\Employee\ValueObjects\DepartmentId;
use App\Domain\Employee\ValueObjects\PositionId;
use App\Domain\Employee\Exceptions\EmployeeAlreadyExistsException;
use App\Domain\Employee\Exceptions\EmployeeException;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EmployeeRegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmployeeRepositoryInterface $employeeRepository;
    private EmployeeRegistrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employeeRepository = Mockery::mock(EmployeeRepositoryInterface::class);
        $this->service = new EmployeeRegistrationService($this->employeeRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_register_employee_without_system_account(): void
    {
        // Arrange
        $department = $this->createDepartment();
        $position = $this->createPosition($department);
        
        $data = EmployeeRegistrationData::create(
            'John',
            'Doe',
            'john.doe@example.com',
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(50000),
            false // Don't create system account
        );

        $this->employeeRepository
            ->shouldReceive('findByEmail')
            ->with(Mockery::type(Email::class))
            ->once()
            ->andReturn(null);

        $this->employeeRepository
            ->shouldReceive('findLastEmployeeForYear')
            ->with(2025)
            ->once()
            ->andReturn(null);

        $this->employeeRepository
            ->shouldReceive('save')
            ->with(Mockery::type(Employee::class))
            ->once()
            ->andReturn(true);

        // Act
        $employee = $this->service->registerEmployee($data);

        // Assert
        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertEquals('John', $employee->getFirstName());
        $this->assertEquals('Doe', $employee->getLastName());
        $this->assertEquals('john.doe@example.com', $employee->getEmail()->toString());
        $this->assertEquals('EMP20250001', $employee->getEmployeeNumber());
        $this->assertFalse($employee->hasUser());
    }

    public function test_can_register_employee_with_system_account(): void
    {
        // Arrange
        $department = $this->createDepartment();
        $position = $this->createPosition($department);
        
        $data = EmployeeRegistrationData::create(
            'Jane',
            'Smith',
            'jane.smith@example.com',
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(60000),
            true, // Create system account
            '+260971234567'
        );

        $this->employeeRepository
            ->shouldReceive('findByEmail')
            ->with(Mockery::type(Email::class))
            ->once()
            ->andReturn(null);

        $this->employeeRepository
            ->shouldReceive('findLastEmployeeForYear')
            ->with(2025)
            ->once()
            ->andReturn(null);

        $this->employeeRepository
            ->shouldReceive('save')
            ->with(Mockery::type(Employee::class))
            ->twice() // Once for initial save, once after user assignment
            ->andReturn(true);

        // Act
        $employee = $this->service->registerEmployee($data);

        // Assert
        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertTrue($employee->hasUser());
        $this->assertEquals('Jane Smith', $employee->getUser()->name);
        $this->assertEquals('jane.smith@example.com', $employee->getUser()->email);
        $this->assertEquals('+260971234567', $employee->getUser()->phone);
    }

    public function test_throws_exception_when_employee_email_already_exists(): void
    {
        // Arrange
        $department = $this->createDepartment();
        $position = $this->createPosition($department);
        
        $existingEmployee = $this->createEmployee();
        
        $data = EmployeeRegistrationData::create(
            'John',
            'Doe',
            'existing@example.com',
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(50000)
        );

        $this->employeeRepository
            ->shouldReceive('findByEmail')
            ->with(Mockery::type(Email::class))
            ->once()
            ->andReturn($existingEmployee);

        // Act & Assert
        $this->expectException(EmployeeAlreadyExistsException::class);
        $this->service->registerEmployee($data);
    }

    public function test_generates_sequential_employee_numbers(): void
    {
        // Arrange
        $department = $this->createDepartment();
        $position = $this->createPosition($department);
        
        $lastEmployee = $this->createEmployee('EMP20250005');
        
        $data = EmployeeRegistrationData::create(
            'John',
            'Doe',
            'john.doe@example.com',
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(50000),
            false
        );

        $this->employeeRepository
            ->shouldReceive('findByEmail')
            ->with(Mockery::type(Email::class))
            ->once()
            ->andReturn(null);

        $this->employeeRepository
            ->shouldReceive('findLastEmployeeForYear')
            ->with(2025)
            ->once()
            ->andReturn($lastEmployee);

        $this->employeeRepository
            ->shouldReceive('save')
            ->with(Mockery::type(Employee::class))
            ->once()
            ->andReturn(true);

        // Act
        $employee = $this->service->registerEmployee($data);

        // Assert
        $this->assertEquals('EMP20250006', $employee->getEmployeeNumber());
    }

    public function test_throws_exception_when_user_account_already_exists(): void
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);
        
        $department = $this->createDepartment();
        $position = $this->createPosition($department);
        $employee = $this->createEmployee();

        // Act & Assert
        $this->expectException(EmployeeException::class);
        $this->expectExceptionMessage('User account with email existing@example.com already exists');
        
        $this->service->createSystemAccount($employee);
    }

    public function test_creates_system_account_with_temporary_password(): void
    {
        // Arrange
        $department = $this->createDepartment();
        $position = $this->createPosition($department);
        $employee = $this->createEmployee();

        // Act
        $user = $this->service->createSystemAccount($employee, 'TempPass123');

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($employee->getFullName(), $user->name);
        $this->assertEquals($employee->getEmail()->toString(), $user->email);
        $this->assertTrue($user->is_id_verified);
        $this->assertStringContainsString('TempPass123', $user->notes);
    }

    public function test_assigns_role_based_on_position(): void
    {
        // Arrange
        $department = $this->createDepartment();
        $position = $this->createPosition($department, 'HR Manager');
        $employee = $this->createEmployee();
        $user = User::factory()->create();
        $employee->assignToUser($user);

        // Act
        $this->service->assignPermissions($employee);

        // Assert
        $this->assertTrue($user->hasRole('HR Manager'));
    }

    public function test_assigns_department_specific_permissions(): void
    {
        // Arrange
        $department = $this->createDepartment('Human Resources');
        $position = $this->createPosition($department);
        $employee = $this->createEmployee();
        $user = User::factory()->create();
        $employee->assignToUser($user);

        // Act
        $this->service->assignPermissions($employee);

        // Assert
        $this->assertTrue($user->hasPermissionTo('view_all_employees'));
        $this->assertTrue($user->hasPermissionTo('manage_employee_performance'));
    }

    public function test_assigns_position_specific_permissions(): void
    {
        // Arrange
        $department = $this->createDepartment();
        $position = $this->createPosition($department, 'Field Agent');
        $employee = $this->createEmployee();
        $user = User::factory()->create();
        $employee->assignToUser($user);

        // Act
        $this->service->assignPermissions($employee);

        // Assert
        $this->assertTrue($user->hasPermissionTo('manage_assigned_clients'));
        $this->assertTrue($user->hasPermissionTo('access_field_agent_dashboard'));
    }

    public function test_assigns_base_employee_permissions(): void
    {
        // Arrange
        $department = $this->createDepartment();
        $position = $this->createPosition($department);
        $employee = $this->createEmployee();
        $user = User::factory()->create();
        $employee->assignToUser($user);

        // Act
        $this->service->assignPermissions($employee);

        // Assert
        $this->assertTrue($user->hasPermissionTo('view_own_profile'));
        $this->assertTrue($user->hasPermissionTo('access_employee_dashboard'));
    }

    public function test_throws_exception_when_assigning_permissions_without_user(): void
    {
        // Arrange
        $employee = $this->createEmployee();

        // Act & Assert
        $this->expectException(EmployeeException::class);
        $this->expectExceptionMessage('No user account assigned to employee');
        
        $this->service->assignPermissions($employee);
    }

    public function test_updates_employee_permissions(): void
    {
        // Arrange
        $department = $this->createDepartment();
        $position = $this->createPosition($department);
        $employee = $this->createEmployee();
        $user = User::factory()->create();
        $employee->assignToUser($user);

        // Give user some existing permissions
        $role = Role::create(['name' => 'Old Role']);
        $user->assignRole($role);

        // Act
        $this->service->updateEmployeePermissions($employee);

        // Assert
        $this->assertFalse($user->hasRole('Old Role'));
        $this->assertTrue($user->hasPermissionTo('view_own_profile'));
    }

    private function createDepartment(string $name = 'IT Department'): Department
    {
        return new Department(
            DepartmentId::generate(),
            $name,
            'Test department description',
            null, // parent department
            true  // isActive
        );
    }

    private function createPosition(Department $department, string $title = 'Software Developer'): Position
    {
        return new Position(
            PositionId::generate(),
            $title,
            'Test position description',
            $department,
            Salary::fromKwacha(40000),
            Salary::fromKwacha(80000),
            false,
            0.0
        );
    }

    private function createEmployee(string $employeeNumber = 'EMP20250001'): Employee
    {
        $department = $this->createDepartment();
        $position = $this->createPosition($department);
        
        return Employee::create(
            $employeeNumber,
            'Test',
            'Employee',
            Email::fromString('test@example.com'),
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(50000)
        );
    }
}