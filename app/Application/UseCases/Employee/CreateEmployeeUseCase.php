<?php

declare(strict_types=1);

namespace App\Application\UseCases\Employee;

use App\Application\DTOs\Employee\CreateEmployeeDTO;
use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\Services\EmployeeRegistrationService;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\Phone;
use App\Domain\Employee\ValueObjects\Salary;
use App\Domain\Employee\Events\EmployeeHired;
use App\Domain\Employee\Exceptions\EmployeeAlreadyExistsException;
use DateTimeImmutable;

final class CreateEmployeeUseCase
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EmployeeRegistrationService $registrationService
    ) {}

    public function execute(CreateEmployeeDTO $dto): Employee
    {
        // Check if employee already exists
        $existingEmployee = $this->employeeRepository->findByEmail(
            Email::fromString($dto->email)
        );

        if ($existingEmployee) {
            throw EmployeeAlreadyExistsException::withEmail($dto->email);
        }

        // Create employee through domain service
        $employee = $this->registrationService->registerEmployee(
            $dto->employeeNumber,
            $dto->firstName,
            $dto->lastName,
            Email::fromString($dto->email),
            new DateTimeImmutable($dto->hireDate),
            $dto->department,
            $dto->position,
            Salary::fromKwacha($dto->baseSalary),
            $dto->phone ? Phone::fromString($dto->phone) : null,
            $dto->address,
            $dto->manager
        );

        // Persist employee
        $this->employeeRepository->save($employee);

        // Dispatch domain event
        event(new EmployeeHired($employee->getId(), $employee->getEmail()));

        return $employee;
    }
}