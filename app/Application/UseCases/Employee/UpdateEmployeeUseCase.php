<?php

declare(strict_types=1);

namespace App\Application\UseCases\Employee;

use App\Application\DTOs\Employee\UpdateEmployeeDTO;
use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\Phone;
use App\Domain\Employee\ValueObjects\Salary;
use App\Domain\Employee\Events\EmployeeUpdated;
use App\Domain\Employee\Exceptions\EmployeeNotFoundException;

final class UpdateEmployeeUseCase
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function execute(EmployeeId $employeeId, UpdateEmployeeDTO $dto): Employee
    {
        $employee = $this->employeeRepository->findById($employeeId);
        
        if (!$employee) {
            throw EmployeeNotFoundException::withId($employeeId->toString());
        }

        // Update personal details
        $employee->updatePersonalDetails(
            $dto->firstName,
            $dto->lastName,
            Email::fromString($dto->email),
            $dto->phone ? Phone::fromString($dto->phone) : null,
            $dto->address
        );

        // Update salary if provided
        if ($dto->salary) {
            $employee->updateSalary(Salary::fromKwacha($dto->salary));
        }

        // Update employment status if provided
        if ($dto->employmentStatus) {
            $employee->changeEmploymentStatus($dto->employmentStatus);
        }

        // Transfer to new department/position if provided
        if ($dto->department && $dto->position) {
            $employee->transferToDepartment($dto->department, $dto->position);
        }

        // Update manager if provided
        if ($dto->manager) {
            $employee->assignManager($dto->manager);
        } elseif ($dto->removeManager) {
            $employee->removeManager();
        }

        // Add notes if provided
        if ($dto->notes) {
            $employee->addNotes($dto->notes);
        }

        // Persist changes
        $this->employeeRepository->save($employee);

        // Dispatch domain event
        event(new EmployeeUpdated($employee->getId(), $employee->getEmail()));

        return $employee;
    }
}