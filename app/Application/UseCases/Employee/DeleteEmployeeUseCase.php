<?php

declare(strict_types=1);

namespace App\Application\UseCases\Employee;

use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\EmploymentStatus;
use App\Domain\Employee\Events\EmployeeTerminated;
use App\Domain\Employee\Exceptions\EmployeeNotFoundException;
use DateTimeImmutable;

final class DeleteEmployeeUseCase
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function execute(EmployeeId $employeeId, string $reason = 'Terminated'): bool
    {
        $employee = $this->employeeRepository->findById($employeeId);
        
        if (!$employee) {
            throw EmployeeNotFoundException::withId($employeeId->toString());
        }

        // Change employment status to terminated instead of hard delete
        $terminationStatus = EmploymentStatus::terminated($reason, new DateTimeImmutable());
        $employee->changeEmploymentStatus($terminationStatus);

        // Add termination note
        $employee->addNotes("Employee terminated: {$reason}");

        // Save the updated employee
        $success = $this->employeeRepository->save($employee);

        if ($success) {
            // Dispatch domain event
            event(new EmployeeTerminated(
                $employee->getId(), 
                $employee->getEmail(),
                $reason,
                new DateTimeImmutable()
            ));
        }

        return $success;
    }

    public function hardDelete(EmployeeId $employeeId): bool
    {
        // This should only be used in exceptional circumstances
        // and with proper authorization
        return $this->employeeRepository->delete((int)$employeeId->toString());
    }
}