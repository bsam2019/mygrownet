<?php

declare(strict_types=1);

namespace App\Domain\Employee\Services;

use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Entities\Department;
use App\Domain\Employee\Entities\Position;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\Phone;
use App\Domain\Employee\ValueObjects\Salary;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Exceptions\EmployeeAlreadyExistsException;
use App\Domain\Employee\Exceptions\EmployeeException;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EmployeeRegistrationService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function registerEmployee(EmployeeRegistrationData $data): Employee
    {
        // Check if employee with this email already exists
        if ($this->employeeRepository->findByEmail($data->email)) {
            throw EmployeeAlreadyExistsException::byEmail($data->email->toString());
        }

        // Generate unique employee number
        $employeeNumber = $this->generateEmployeeNumber();

        // Create the employee entity
        $employee = Employee::create(
            $employeeNumber,
            $data->firstName,
            $data->lastName,
            $data->email,
            $data->hireDate,
            $data->department,
            $data->position,
            $data->baseSalary,
            $data->phone,
            $data->address,
            $data->manager
        );

        // Save the employee
        $this->employeeRepository->save($employee);

        // Create system account if requested
        if ($data->createSystemAccount) {
            $user = $this->createSystemAccount($employee, $data->temporaryPassword);
            $employee->assignToUser($user);
            $this->assignPermissions($employee);
            $this->employeeRepository->save($employee);
        }

        return $employee;
    }

    public function createSystemAccount(Employee $employee, ?string $temporaryPassword = null): User
    {
        // Check if user with this email already exists
        $existingUser = User::where('email', $employee->getEmail()->toString())->first();
        if ($existingUser) {
            throw EmployeeException::userAccountAlreadyExists($employee->getEmail()->toString());
        }

        // Generate temporary password if not provided
        $password = $temporaryPassword ?? $this->generateTemporaryPassword();

        // Create the user account
        $user = User::create([
            'name' => $employee->getFullName(),
            'email' => $employee->getEmail()->toString(),
            'password' => Hash::make($password),
            'phone' => $employee->getPhone()?->toString(),
            'address' => $employee->getAddress(),
            'status' => 'active',
            'is_admin' => false,
            'requires_id_verification' => false,
            'is_id_verified' => true, // Employees are pre-verified
            'id_verified_at' => now(),
        ]);

        // Store the temporary password for the user to change on first login
        // In a real implementation, you might want to send this via email or secure channel
        $user->update(['notes' => "Temporary password: {$password}. Please change on first login."]);

        return $user;
    }

    public function assignPermissions(Employee $employee): void
    {
        if (!$employee->hasUser()) {
            throw EmployeeException::noUserAccountAssigned($employee->getId()->toString());
        }

        $user = $employee->getUser();
        $position = $employee->getPosition();
        $department = $employee->getDepartment();

        // Assign role based on position
        $roleName = $this->determineRoleFromPosition($position);
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->assignRole($role);

        // Assign department-specific permissions
        $this->assignDepartmentPermissions($user, $department);

        // Assign position-specific permissions
        $this->assignPositionPermissions($user, $position);

        // Assign base employee permissions
        $this->assignBaseEmployeePermissions($user);
    }

    public function updateEmployeePermissions(Employee $employee): void
    {
        if (!$employee->hasUser()) {
            return;
        }

        $user = $employee->getUser();
        
        // Remove all existing roles and permissions
        $user->roles()->detach();
        $user->permissions()->detach();

        // Reassign permissions based on current position and department
        $this->assignPermissions($employee);
    }

    private function generateEmployeeNumber(): string
    {
        $prefix = 'EMP';
        $year = date('Y');
        
        // Find the last employee number for this year
        $lastEmployee = $this->employeeRepository->findLastEmployeeForYear((int)$year);
        
        if ($lastEmployee) {
            // Extract the sequence number and increment
            $lastNumber = $lastEmployee->getEmployeeNumber();
            $sequence = (int)substr($lastNumber, -4) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s%s%04d', $prefix, $year, $sequence);
    }

    private function generateTemporaryPassword(): string
    {
        // Generate a secure temporary password
        return Str::random(12);
    }

    private function determineRoleFromPosition(Position $position): string
    {
        $positionTitle = strtolower($position->getTitle());

        // Map position titles to roles
        return match (true) {
            str_contains($positionTitle, 'manager') || str_contains($positionTitle, 'head') => 'Department Manager',
            str_contains($positionTitle, 'hr') => 'HR Manager',
            str_contains($positionTitle, 'field agent') || str_contains($positionTitle, 'agent') => 'Field Agent',
            str_contains($positionTitle, 'admin') => 'Admin',
            str_contains($positionTitle, 'supervisor') => 'Supervisor',
            default => 'Employee'
        };
    }

    private function assignDepartmentPermissions(User $user, Department $department): void
    {
        $departmentName = strtolower($department->getName());

        // Create department-specific permissions if they don't exist
        $permissions = [];

        switch ($departmentName) {
            case 'human resources':
            case 'hr':
                $permissions = [
                    'view_all_employees',
                    'create_employees',
                    'edit_employees',
                    'manage_employee_performance',
                    'view_payroll',
                    'manage_departments',
                    'manage_positions'
                ];
                break;

            case 'sales':
            case 'field operations':
                $permissions = [
                    'view_assigned_clients',
                    'manage_client_portfolios',
                    'view_commission_reports',
                    'create_investment_records',
                    'view_performance_metrics'
                ];
                break;

            case 'finance':
            case 'accounting':
                $permissions = [
                    'view_payroll',
                    'process_commissions',
                    'view_financial_reports',
                    'manage_employee_compensation',
                    'view_all_transactions'
                ];
                break;

            case 'administration':
            case 'management':
                $permissions = [
                    'view_all_employees',
                    'view_department_reports',
                    'manage_organizational_structure',
                    'view_performance_analytics',
                    'access_admin_dashboard'
                ];
                break;

            default:
                $permissions = [
                    'view_own_profile',
                    'view_department_colleagues'
                ];
        }

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $user->givePermissionTo($permission);
        }
    }

    private function assignPositionPermissions(User $user, Position $position): void
    {
        $positionTitle = strtolower($position->getTitle());

        // Position-specific permissions
        $permissions = [];

        if (str_contains($positionTitle, 'manager') || str_contains($positionTitle, 'head')) {
            $permissions = array_merge($permissions, [
                'manage_team_members',
                'approve_time_off',
                'conduct_performance_reviews',
                'view_team_reports',
                'assign_tasks'
            ]);
        }

        if (str_contains($positionTitle, 'field agent') || str_contains($positionTitle, 'agent')) {
            $permissions = array_merge($permissions, [
                'manage_assigned_clients',
                'create_investment_opportunities',
                'view_commission_calculations',
                'update_client_interactions',
                'access_field_agent_dashboard'
            ]);
        }

        if (str_contains($positionTitle, 'hr')) {
            $permissions = array_merge($permissions, [
                'manage_all_employees',
                'access_hr_dashboard',
                'manage_employee_lifecycle',
                'view_compliance_reports',
                'manage_employee_permissions'
            ]);
        }

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $user->givePermissionTo($permission);
        }
    }

    private function assignBaseEmployeePermissions(User $user): void
    {
        // Base permissions that all employees should have
        $basePermissions = [
            'view_own_profile',
            'edit_own_profile',
            'view_own_performance',
            'access_employee_dashboard',
            'view_company_announcements',
            'submit_time_off_requests',
            'view_own_payroll'
        ];

        foreach ($basePermissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $user->givePermissionTo($permission);
        }
    }
}

class EmployeeRegistrationData
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly Email $email,
        public readonly DateTimeImmutable $hireDate,
        public readonly Department $department,
        public readonly Position $position,
        public readonly Salary $baseSalary,
        public readonly bool $createSystemAccount = true,
        public readonly ?Phone $phone = null,
        public readonly ?string $address = null,
        public readonly ?Employee $manager = null,
        public readonly ?string $temporaryPassword = null
    ) {}

    public static function create(
        string $firstName,
        string $lastName,
        string $email,
        DateTimeImmutable $hireDate,
        Department $department,
        Position $position,
        Salary $baseSalary,
        bool $createSystemAccount = true,
        ?string $phone = null,
        ?string $address = null,
        ?Employee $manager = null,
        ?string $temporaryPassword = null
    ): self {
        return new self(
            $firstName,
            $lastName,
            Email::fromString($email),
            $hireDate,
            $department,
            $position,
            $baseSalary,
            $createSystemAccount,
            $phone ? Phone::fromString($phone) : null,
            $address,
            $manager,
            $temporaryPassword
        );
    }
}