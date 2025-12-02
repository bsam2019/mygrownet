<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Services;

use App\Domain\GrowBiz\Entities\Employee;
use App\Domain\GrowBiz\Exceptions\DuplicateEmployeeException;
use App\Domain\GrowBiz\Exceptions\EmployeeHasActiveTasksException;
use App\Domain\GrowBiz\Exceptions\EmployeeNotFoundException;
use App\Domain\GrowBiz\Exceptions\OperationFailedException;
use App\Domain\GrowBiz\Repositories\EmployeeRepositoryInterface;
use App\Domain\GrowBiz\Repositories\TaskRepositoryInterface;
use App\Domain\GrowBiz\ValueObjects\EmployeeId;
use App\Domain\GrowBiz\ValueObjects\EmployeeStatus;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmployeeManagementService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * @throws DuplicateEmployeeException
     * @throws OperationFailedException
     */
    public function createEmployee(
        int $ownerId,
        string $firstName,
        string $lastName,
        ?string $email = null,
        ?string $phone = null,
        ?string $position = null,
        ?string $department = null,
        ?string $hireDate = null,
        ?float $hourlyRate = null,
        ?string $notes = null
    ): Employee {
        try {
            // Check for duplicate email if provided
            if ($email && $this->employeeRepository->findByEmail($ownerId, $email)) {
                throw new DuplicateEmployeeException($email);
            }

            $employee = Employee::create(
                managerId: $ownerId,
                firstName: $firstName,
                lastName: $lastName,
                email: $email,
                phone: $phone,
                position: $position,
                department: $department,
                hireDate: $hireDate,
                hourlyRate: $hourlyRate,
                notes: $notes
            );

            $savedEmployee = $this->employeeRepository->save($employee);

            Log::info('Employee created', [
                'employee_id' => $savedEmployee->id(),
                'manager_id' => $ownerId,
            ]);

            return $savedEmployee;
        } catch (DuplicateEmployeeException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to create employee', [
                'manager_id' => $ownerId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('create employee', $e->getMessage());
        }
    }

    /**
     * @throws EmployeeNotFoundException
     * @throws OperationFailedException
     */
    public function updateEmployee(int $employeeId, array $data): Employee
    {
        try {
            $employee = $this->employeeRepository->findById(EmployeeId::fromInt($employeeId));
            
            if (!$employee) {
                throw new EmployeeNotFoundException($employeeId);
            }

            $employee->update(
                firstName: $data['first_name'] ?? $employee->firstName(),
                lastName: $data['last_name'] ?? $employee->lastName(),
                email: $data['email'] ?? $employee->email(),
                phone: $data['phone'] ?? $employee->phone(),
                position: $data['position'] ?? $employee->position(),
                department: $data['department'] ?? $employee->department(),
                hireDate: $data['hire_date'] ?? $employee->hireDate()?->format('Y-m-d'),
                hourlyRate: isset($data['hourly_rate']) ? (float) $data['hourly_rate'] : $employee->hourlyRate(),
                notes: $data['notes'] ?? $employee->notes()
            );

            if (isset($data['status'])) {
                $employee->updateStatus(EmployeeStatus::fromString($data['status']));
            }

            $savedEmployee = $this->employeeRepository->save($employee);

            Log::info('Employee updated', [
                'employee_id' => $employeeId,
            ]);

            return $savedEmployee;
        } catch (EmployeeNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to update employee', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('update employee', $e->getMessage());
        }
    }

    /**
     * @throws EmployeeNotFoundException
     * @throws OperationFailedException
     */
    public function updateEmployeeStatus(int $employeeId, string $status): Employee
    {
        try {
            $employee = $this->employeeRepository->findById(EmployeeId::fromInt($employeeId));
            
            if (!$employee) {
                throw new EmployeeNotFoundException($employeeId);
            }

            $employee->updateStatus(EmployeeStatus::fromString($status));
            
            $savedEmployee = $this->employeeRepository->save($employee);

            Log::info('Employee status updated', [
                'employee_id' => $employeeId,
                'new_status' => $status,
            ]);

            return $savedEmployee;
        } catch (EmployeeNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to update employee status', [
                'employee_id' => $employeeId,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('update employee status', $e->getMessage());
        }
    }

    /**
     * @throws EmployeeNotFoundException
     */
    public function getEmployeeById(int $employeeId): Employee
    {
        $employee = $this->employeeRepository->findById(EmployeeId::fromInt($employeeId));
        
        if (!$employee) {
            throw new EmployeeNotFoundException($employeeId);
        }

        return $employee;
    }

    public function getEmployeesForOwner(int $ownerId, array $filters = []): array
    {
        try {
            return $this->employeeRepository->findByOwnerWithFilters($ownerId, $filters);
        } catch (Throwable $e) {
            Log::error('Failed to fetch employees', [
                'owner_id' => $ownerId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getEmployeeStatistics(int $ownerId): array
    {
        try {
            $employees = $this->employeeRepository->findByManagerId($ownerId);
            
            $total = count($employees);
            $active = 0;
            $inactive = 0;
            $onLeave = 0;

            foreach ($employees as $employee) {
                $status = $employee->status()->value();
                
                if ($status === 'active') $active++;
                elseif ($status === 'inactive') $inactive++;
                elseif ($status === 'on_leave') $onLeave++;
            }

            return [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'on_leave' => $onLeave,
            ];
        } catch (Throwable $e) {
            Log::error('Failed to get employee statistics', [
                'owner_id' => $ownerId,
                'error' => $e->getMessage(),
            ]);
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'on_leave' => 0,
            ];
        }
    }

    public function getDepartments(int $ownerId): array
    {
        try {
            return $this->employeeRepository->getDistinctDepartments($ownerId);
        } catch (Throwable $e) {
            Log::error('Failed to get departments', [
                'owner_id' => $ownerId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getEmployeeTaskStats(int $employeeId): array
    {
        try {
            return $this->taskRepository->getTaskStatsByEmployee($employeeId);
        } catch (Throwable $e) {
            Log::error('Failed to get employee task stats', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);
            return [
                'total' => 0,
                'completed' => 0,
                'in_progress' => 0,
                'pending' => 0,
                'completion_rate' => 0,
            ];
        }
    }

    public function getEmployeeTasks(int $employeeId): array
    {
        try {
            return $this->taskRepository->findByEmployeeId($employeeId);
        } catch (Throwable $e) {
            Log::error('Failed to get employee tasks', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * @throws EmployeeNotFoundException
     * @throws EmployeeHasActiveTasksException
     * @throws OperationFailedException
     */
    public function deleteEmployee(int $employeeId): void
    {
        try {
            $employee = $this->employeeRepository->findById(EmployeeId::fromInt($employeeId));
            
            if (!$employee) {
                throw new EmployeeNotFoundException($employeeId);
            }

            // Check for active tasks
            $taskStats = $this->taskRepository->getTaskStatsByEmployee($employeeId);
            $activeTasks = ($taskStats['pending'] ?? 0) + ($taskStats['in_progress'] ?? 0);
            
            if ($activeTasks > 0) {
                throw new EmployeeHasActiveTasksException($employeeId, $activeTasks);
            }

            $this->employeeRepository->delete(EmployeeId::fromInt($employeeId));

            Log::info('Employee deleted', [
                'employee_id' => $employeeId,
            ]);
        } catch (EmployeeNotFoundException | EmployeeHasActiveTasksException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to delete employee', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('delete employee', $e->getMessage());
        }
    }

    /**
     * @throws OperationFailedException
     */
    public function addEmployee(
        int $managerId,
        string $name,
        ?string $phone = null,
        ?string $email = null,
        ?string $role = null,
        ?int $userId = null
    ): Employee {
        try {
            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $employee = Employee::create(
                managerId: $managerId,
                firstName: $firstName,
                lastName: $lastName,
                email: $email,
                phone: $phone,
                position: $role,
                userId: $userId
            );

            return $this->employeeRepository->save($employee);
        } catch (Throwable $e) {
            Log::error('Failed to add employee', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('add employee', $e->getMessage());
        }
    }

    /**
     * @throws EmployeeNotFoundException
     * @throws OperationFailedException
     */
    public function activateEmployee(EmployeeId $employeeId): Employee
    {
        try {
            $employee = $this->employeeRepository->findById($employeeId);
            
            if (!$employee) {
                throw new EmployeeNotFoundException($employeeId->toInt());
            }

            $employee->activate();
            
            return $this->employeeRepository->save($employee);
        } catch (EmployeeNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to activate employee', [
                'employee_id' => $employeeId->toInt(),
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('activate employee', $e->getMessage());
        }
    }

    /**
     * @throws EmployeeNotFoundException
     * @throws OperationFailedException
     */
    public function deactivateEmployee(EmployeeId $employeeId): Employee
    {
        try {
            $employee = $this->employeeRepository->findById($employeeId);
            
            if (!$employee) {
                throw new EmployeeNotFoundException($employeeId->toInt());
            }

            $employee->deactivate();
            
            return $this->employeeRepository->save($employee);
        } catch (EmployeeNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to deactivate employee', [
                'employee_id' => $employeeId->toInt(),
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('deactivate employee', $e->getMessage());
        }
    }

    public function getEmployeesByManager(int $managerId): array
    {
        try {
            return $this->employeeRepository->findByManagerId($managerId);
        } catch (Throwable $e) {
            Log::error('Failed to get employees by manager', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function getActiveEmployees(int $managerId): array
    {
        try {
            return $this->employeeRepository->findActiveByManagerId($managerId);
        } catch (Throwable $e) {
            Log::error('Failed to get active employees', [
                'manager_id' => $managerId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
